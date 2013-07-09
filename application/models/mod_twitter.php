<?php
/**
 * Description of mod_twitter
 *
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright (c) 2013, Victor Angelier
 * @link http://www.victorangelier.com Personal Website
 * @link http://www.twitter.com/digital_human
 */
class mod_twitter extends MY_Model {
    
    protected $oauth = null;


    public function __construct() {
        parent::__construct();
    }
    
    /**
     * Get the sender of the Tweet without decrypting or checking if use is authenticated.
     * 
     * @param type $id
     * @return boolean|array
     */
    public function get_senderid_by_tweet_id($id = 0){
        $id = intval($id);
        if($id > 0){
            $q = $this->db->query("
                 SELECT a.id,a.user_id,a.recipient,a.tweet_text,a.hash,a.datetime,c.twitter_id AS twitter_sender_id,b.twitter_id
                FROM tweets a
                    INNER JOIN credentials b ON (a.recipient=b.twitter_id OR a.recipient=0)
                    INNER JOIN credentials c ON a.user_id=c.id
                WHERE ( a.id={$this->db->escape($id)} )
                ORDER BY a.datetime DESC
                LIMIT 1   
            ");
            if($q && $q->num_rows() == 1){
                return current($q->result_array());
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    
    /**
     * Reply to a Tweet has a different way of handling. Thats why we have a new method for this.
     * 
     * @param type $data
     * @return boolean
     */
    public function reply_tweet($data = array()){
       if(intval($this->session->userdata("uid")) > 0 && intval($data["parent"]) > 0){
           if($data["tweet"] != "" && intval($data["recipient"]) >= 0){            
                //If tweet is somehow larger then 130. Schrik it to 130
                if(strlen($data["tweet"]) > 130){
                    $data["tweet"] = substr($data["tweet"], 0, 130);
                }

                //Check if recipient exists
                if(intval($data["parent"]) != 0){    //To all followers
                    if(($parent_tweet = $this->get_senderid_by_tweet_id(intval($data["parent"]))) === false){
                        log_message("error", "Error while retreiving parent Tweet while replying");
                        return false;
                    }
                }else{
                    log_message("error", "No parent id found while replying");
                    return false;
                }

                //Check if we have access to Twitter
                if(($oauth = $this->authenticate()) !== FALSE){
                    
                    $recipient = $parent_tweet['twitter_sender_id'];
                    $tweet = $data["tweet"];
                    
                    //First get the Public Key of the current user
                    if(($keypair = $this->mod_user->get_user_keypair()) !== false){                        
                        if(is_array($keypair) && count($keypair) > 0){
                            $publicKey = str_replace("\\n", "\n", $keypair["public_key"]);
                            if(($res = openssl_pkey_get_public($publicKey) !== false)){ 

                                //Ok we have our public key
                                $publicKeyRS = openssl_pkey_get_public($publicKey);
                                $recipient = $this->get_twitter_user_by_id($recipient);

                                //Get public keys of 1 specific recipient, and myself
                                $recipient_keypair = $this->mod_user->get_user_keypair_by_id($recipient["user_id"]);                                
                                        
                                $publicKey = str_replace("\\n", "\n", $recipient_keypair["public_key"]);
                                if(($res = openssl_pkey_get_public($publicKey) !== false)){
                                    $publicKeyRicipient = openssl_pkey_get_public($publicKey);
                                    $publicKeys[$recipient["twitter_id"]] = $publicKeyRicipient;
                                }else{
                                    log_message("error", "Could not create a valid public key for the recipient.");
                                    return false;
                                }
                                
                                //Add our own key
                                $user = $this->mod_user->get_user();
                                $publicKeys[$user["twitter_id"]] = $publicKeyRS;
                                $pubKeyIndex = array();
                                foreach($publicKeys as $id => $key){
                                    array_push($pubKeyIndex, $id);
                                }                    

                                //Seal this Tweet
                                $response = array("tweet" => "", "seals" => array());
                                if(openssl_seal($tweet, $sealed_data, $env_keys, $publicKeys) !== false){
                                    for($x = 0; $x < count($env_keys); $x++){
                                        array_push($response["seals"], array(
                                            "twitter_id" => $pubKeyIndex[$x], 
                                            "key" => base64_encode($env_keys[$x])
                                            )
                                        );
                                    }
                                    $response["tweet"] = base64_encode($sealed_data);
                                    
                                    //Ok we have all information, now handle as normal tweet.
                                    //Encrypt the Tweet and get all associated keys
                                    $encrypted_tweet = $response;

                                    //Save this message
                                    $q = $this->db->query("
                                        INSERT INTO tweets SET 
                                            user_id={$this->db->escape($this->mod_user->uid())},
                                            datetime=now(),
                                            tweet_text='{$this->db->escape_str($encrypted_tweet["tweet"])}',
                                            hash='".sha1($tweet)."',
                                            recipient={$this->db->escape(intval($recipient["twitter_id"]))},
                                            retweet=0
                                    ");
                                    if($q){
                                        $id = $this->db->insert_id();
                                        if($id > 0){

                                            //Store the keys for this Tweet
                                            foreach($encrypted_tweet["seals"] as $key){
                                                $q = $this->db->query("
                                                    INSERT INTO tweet_seals (twitter_id,tweet_id,seal) VALUES({$key['twitter_id']},{$id},'{$this->encrypt($this->db->escape_str($key["key"]))}')
                                                ");
                                                if(!$q){
                                                    log_message("error", "Error while storing Tweet seal keys");
                                                }
                                            }

                                            //Prepair the Tweets
                                            //Check if we need to send a DM or not
                                            if(intval($recipient["twitter_id"]) != 0){
                                                if(($tweet = $this->prepair_tweet(sha1($id), $recipient, sha1($encrypted_tweet["tweet"]))) !== false){

                                                    $status = false;

                                                    //First send the DM
                                                    $message = array(
                                                        "tweet" =>  $tweet["dm"],
                                                        "recipient" =>  intval($recipient["twitter_id"])
                                                    );
                                                    if($this->send_dm($message) !== false){
                                                        $status = true;
                                                    }else{
                                                        log_message("error", "Direct Message could not be send to the recipient while replying");
                                                        $status = false;
                                                    }

                                                }else{
                                                    log_message("error", "Prepair Tweet was false, Tweet not send but stored in our DB while replying.");
                                                    return $status;
                                                }                            
                                            }else{
                                              log_message("error", "No DM or Tweet was send, because recipient was 0, that means to all followers while replying.");
                                              $status = true;
                                            }
                                            return $status;                                        
                                        }else{
                                            log_message("error", "Insert of the Tweet in the table failed. ID is 0.");
                                            return 0;
                                        }                                    
                                    
                                    }else{                                    
                                        log_message("error", "Insert of the Tweet in the table failed. ID is 0.");
                                        return 0;                                    
                                    }  
                                }else{
                                    log_message("error", "Error while encrypting Tweet");
                                    return 0;
                                }  
                            }else{
                                log_message("error", "Error while trying to get Public key of the recipient while replying.");
                                return 0;
                            }
                        }else{
                            log_message("error", "User doesn't seem to have a Keypair while replying.");
                            return 0;
                        }
                    }else{
                        log_message("error", "Error while trying to retrieve uses KeyPair.");
                        return 0;
                    }
                }else{
                    log_message("error", "Error while authenticating with Twitter while replying");
                    return 0;
                }
           }else{
               log_message("error", "Not all parameters where passed while replying.");
               return 0;
           }
       }else{
           log_message("error", "Not all parameters where found while replying.");
           return 0;
       }
    }
    
    /**
     * Get ETweet count
     * 
     * @return boolean|int
     */
    public function get_etweet_count($id = 0){
        if(intval($this->session->userdata("uid")) > 0 || $id > 0){
            if($id < 1){
                $id = $this->session->userdata("uid");
            }
        
            $q = $this->db->query("
                SELECT count(a.id) AS count
                FROM tweets a
                    INNER JOIN credentials b ON a.user_id=b.id
                WHERE b.id={$id}
            ");
            if($q && $q->num_rows() == 1){
                $r = current($q->result_array());
                return (int)$r["count"];
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    
    /**
     * Get friends count / only valid user accounts
     * @return boolean|int
     */
    public function get_friends_count($id = 0){
        if(intval($this->session->userdata("uid")) > 0 || $id > 0){
            if($id < 1){
                $id = $this->session->userdata("uid");
            }
        
            $q = $this->db->query("
                SELECT count(a.id) AS count
                FROM followers_friends a
                    INNER JOIN credentials b ON a.user_id=b.id
                WHERE b.id={$id} AND user_type=1
            ");
            if($q && $q->num_rows() == 1){
                $r = current($q->result_array());
                return (int)$r["count"];
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    
    /**
     * Get authorized friends count
     * 
     * @return boolean|int
     */
    public function get_authorized_count($id = 0){
        if(intval($this->session->userdata("uid")) > 0 || $id > 0){
            if($id < 1){
                $id = $this->session->userdata("uid");
            }
        
            $q = $this->db->query("
                SELECT count(a.id) AS count
                FROM auth_requests a
                    INNER JOIN credentials b ON a.receiver=b.twitter_id
                WHERE ( a.sender={$id} AND b.id!={$id} AND a.completed=1 )
            ");
            if($q && $q->num_rows() == 1){
                $r = current($q->result_array());
                return (int)$r["count"];
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    
    /**
     * Delete a specific Tweet
     * @param type $id
     * @return boolean
     */
    public function delete($id = 0){
        $id = intval($id);
        if(intval($this->session->userdata("uid")) > 0 && $id > 0){
            $q = $this->db->query("
                DELETE 
                FROM tweets
                WHERE id={$this->db->escape($id)}
            ");
            if($q){
                return true;
            }else{
                log_message("error", "Tweet {$id} could not be deleted. Maybe it doesn't exists?");
                return false;
            }
        }else{
            return false;
        }
    }

    /**
     * Check if a Tweet was already Retweeted by the current user
     * @param type $hash
     * @return boolean
     */
    public function already_retweeted($hash = 0){
        if(intval($this->session->userdata("uid")) > 0 && $hash != "" && strlen($hash) == 40){
            $sql = "
                SELECT id
                FROM tweets a
                WHERE (
                    a.hash={$this->db->escape($hash)} AND a.user_id={$this->db->escape($this->mod_user->uid())}
                )
                LIMIT 1
            ";
            $q = $this->db->query($sql);
            if($q && $q->num_rows() == 1){
                return true;
            }else{               
                return false;
            }
        }else{
            log_message("error", "Invalid hash or user is not authenticated. Function get_tweet_by_hash");
            return true;
        }
    }
    
     /**
     * Get a specific Tweet by ID
     * 
     * @param type $id
     * @return boolean
     */
    public function get_tweet_by_id($id = 0){
        $id = intval($id);
        if($id > 0){
            //Helper for timespans
            $this->load->helper('date');
            
            //SQL Statement
            $q = $this->db->query("
                SELECT a.id,a.user_id,a.recipient,a.tweet_text,a.hash,a.datetime,c.twitter_id AS twitter_sender_id,b.twitter_id
                FROM tweets a
                    INNER JOIN credentials b ON (a.recipient=b.twitter_id OR a.recipient=0)
                    INNER JOIN credentials c ON a.user_id=c.id
                WHERE ( a.id={$this->db->escape($id)} )
                ORDER BY a.datetime DESC
                LIMIT 1
            ");
            if($q && $q->num_rows() == 1){
                $tweet = current($q->result_array());
                //Check if this user is authorized to read these Tweets
                if($this->is_authorized($tweet["twitter_sender_id"])){
                    $item = array();

                    //Decrypt received Tweet
                    $decrypted = $this->decrypt_tweet(array(
                        "twitter_id"    => $tweet["twitter_id"],
                        "id"            => $tweet["id"]
                    ));
                    if($decrypted !== false){
                        $item["id"] = $tweet["id"]; 
                        $item["text"] = $decrypted;
                        $sender = $this->get_twitter_user_by_id($tweet["twitter_sender_id"]);
                        $item["screenname"] = $sender['username'];
                        $item["name"] = $sender['name'];
                        $item["avatar"] = $sender["profile_image"];
                        $item["timespan"] = timespan(strtotime($tweet["datetime"]),time());
                        $item["ts"] = strtotime($tweet["datetime"]);
                        $item["private"] = ($tweet["recipient"] == 0 ? "0" : "1");
                        $item["twitter_sender_id"] = $tweet["twitter_sender_id"];
                        return $item;
                    }else{
                        log_message("error", "Error while trying to decrypt Tweet for Retweet.");
                        return false;
                    }
                }else{
                    log_message("error", "This user is not authorized to read this Tweet.");
                    return false;
                }
            }else{
                log_message("error", "Error while retrieving Tweet {$id} for Retweet");
                return false;
            }    
        }else{
            log_message("error", "No ID found while trying to Retweet");
                return false;
        }
    }
    
    /**
     * Retweet an existing Tweet
     * 
     * @param type $id
     * @return boolean
     */
    public function retweet($id = 0){
        $id = intval($id);
        if($id > 0){
            $tweet = $this->get_tweet_by_id($id);
            if($tweet !== false){
                $this->send_tweet(array("tweet" => $tweet["text"], "recipient" => 0, "retweet" => 1));
            }
        }else{
            log_error("error", "No ID found while trying to Retweet");
            return false;
        }
    }
    
    /**
     * Fuction to get information from a Twitter friend
     * @param type $screenname
     * @return boolean
     */
    public function get_twitter_friend($screenname = ""){
        if(($oauth = $this->authenticate()) !== false){
            $res = $oauth->get("users/show", array("screen_name" => $screenname, "include_entities" =>  false));
            if(is_object($res)){
                if(is_array($res->errors) && count($res->errors) > 0){
                    log_error("error", "Error from Twitter while retrieving friend information: {$res->errors[0]->message}");
                    return false;
                }else{
                    services::debug($res);
                }
            }
        }
    }
    
    /**
     * Get friend information by screen_name from Twitter and store it for the current user
     * 
     * @param type $screenname
     * @return boolean
     */
    public function add_twitter_friend($screenname = ""){
        if(intval($this->session->userdata("uid")) < 1){
            return false;
        }
        $user = $this->mod_twitter->get_user_by_id($this->session->userdata("uid"));        
        if(($oauth = $this->authenticate()) !== false){
            $res = $oauth->get("users/show", array("screen_name" => $screenname, "include_entities" =>  false));
            if(is_object($res)){
                if(is_array($res->errors) && count($res->errors) > 0){
                    log_error("error", "Error from Twitter while retrieving friend information: {$res->errors[0]->message}");
                    return false;
                }else{
                    if($user['twitter_id'] != $res->id){
                        //Ok we have found the friend. Now check if he doesn't exist
                        $q = $this->db->query("
                                SELECT id
                                FROM followers_friends
                                WHERE (
                                    user_id={$this->session->userdata("uid")} AND twitter_id='{$res->id}'
                                )
                                LIMIT 1
                        ");
                        if($q && $q->num_rows() > 0){
                            return -1;
                        }else{
                            //User doesn't exists so we can add it
                            $q = $this->db->query("
                                INSERT INTO followers_friends (user_id,twitter_id,username,name,profile_image,`datetime`,user_type)
                                VALUES(
                                    {$this->db->escape($this->session->userdata("uid"))},
                                    {$this->db->escape($res->id)},
                                    {$this->db->escape($res->screen_name)},
                                    {$this->db->escape($res->name)},
                                    {$this->db->escape($res->profile_image_url)},
                                    now(),
                                    1
                                )
                            ");
                            if($q){
                                return true;
                            }else{
                                log_message("error", "Error while storing friend data in the DB");
                                return false;
                            }
                        }
                    }else{
                        log_message("error", "User is trying to add himself.");
                        return -1;
                    }
                }
            }else{
                log_message("error", "Error while retrieving friend information from Twitter.");
                return false;
            }
        }else{
            log_message("error", "Error login in to Twitter to retrieve friend information.");
            return false;
        }
    }
    
    /**
     * Revoke authorization from a specific user
     * 
     * @param int $auth_id
     * @return boolean
     */
    public function revoke_autorization($auth_id = 0){
       if($auth_id != ""){
           $q = $this->db->query("
                SELECT a.id 
                FROM auth_requests a
                    INNER JOIN credentials b ON a.receiver=b.twitter_id
                WHERE (
                    SHA1(CONCAT(a.id,':',b.twitter_id))={$this->db->escape($auth_id)}
                )
                ORDER BY a.completed DESC
                LIMIT 1
           ");
           if($q && $q->num_rows() == 1){
               $r = current($q->result_array());
               $q = $this->db->query("DELETE FROM auth_requests WHERE id={$r['id']}");
               if($q){
                   return 1;
               }else{
                   return 0;
               }
           }else{
               log_message("error", "Error while trying to delete authorization from user {$auth_id}");
               return 0;
           }
       }else{
           log_message("error", "Authorization ID {$auth_id} not found while trying to delete it.");
           return 0;
       }
    }
    
    /**
     * Get pending authorization requests of the current user
     * 
     * @return boolean|array
     */
    public function get_pending_requests(){
        $id = intval($this->session->userdata("uid"));
        if($id > 0){
            $q = $this->db->query("
                SELECT b.twitter_id AS reciever,a.datetime,c.name,c.username
                FROM auth_requests a
                    INNER JOIN credentials b ON a.receiver=b.twitter_id
                    INNER JOIN users c ON b.user_id=c.id
                WHERE (
                    a.sender={$id} AND completed=0
                )
                ORDER BY a.datetime DESC
            ");
            if($q && $q->num_rows() > 0){
                $result = array();
                foreach($q->result_array() as $r){
                    $r["username"] = $this->decrypt($r["username"]);
                    array_push($result, $r);
                }
                return $result;
            }else{
                return false;
            }
        }else{
            log_message("error", "User is not authenticated");
            return false;
        }
    }
    
    /**
     * Check if our friend is validated by the current user
     * 
     * @param type $receiver
     * @return boolean
     */
    public function friend_authorized($receiver = 0){
        $receiver = intval($receiver);
        $id = intval($this->session->userdata("uid"));
        if($receiver > 0 && $id > 0){      
            $sql = "
                SELECT a.id
                FROM auth_requests a
                    INNER JOIN credentials b ON a.receiver=b.twitter_id
                    INNER JOIN credentials c ON a.sender=c.id
                WHERE (
                    b.twitter_id={$receiver} AND a.sender={$id} AND completed=1
                )
                ORDER BY completed DESC
                LIMIT 1
            ";
            //Validate
            $q = $this->db->query($sql);
            if($q && $q->num_rows() == 1){                
                $r = current($q->result_array());
                return $r["id"];
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    /**
     * Check if the current user is authorized to read Tweets from sender
     * 
     * @param type $sender Is the Sender of the Tweet
     * @return boolean
     */
    public function is_authorized($sender = 0){
        $sender = intval($sender);
        $id = intval($this->session->userdata("uid"));
        if($sender > 0 && $id > 0){            
            //Validate
            $q = $this->db->query("
                SELECT a.id
                FROM auth_requests a
                    INNER JOIN credentials b ON a.receiver=b.twitter_id
                    INNER JOIN credentials c ON a.sender=c.id
                WHERE (
                    b.id={$id} AND c.twitter_id={$sender} AND completed=1
                )
                ORDER BY completed DESC
                LIMIT 1
            ");
            if($q && $q->num_rows() == 1){                
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    
    /**
     * Add anser to the table of authentication requests
     * 
     * @param type $data
     * @return boolean
     */
    public function store_authentication_answer($data = array()){
        if(intval($data["id"]) > 0 && $data["similarity"] > 0){
            $q = $this->db->query("
                 UPDATE auth_requests SET                    
                    similarity={$this->db->escape(number_format($data["similarity"],6,".",""))},
                    completedate=now(),
                    completed=1
                 WHERE id={$this->db->escape($data["id"])}
            ");
            if($q){
                return true;
            }else{
                log_message("error", "Error while updating the authentication request table while adding the answer.");
                return false;
            }
        }else{
            log_message("error", "We are missing parameters to add the authentication answer.");
            return false;
        }
    }
    
    /**
     * Get the authorization question by HASH
     * 
     * @param type $hash
     * @return boolean
     */
    public function get_authorize_request_by_hash($hash = ""){
        $user = $this->mod_user->get_user();
        
        if(strlen($hash) == 40 && is_array($user) && count($user) > 0){
            $q = $this->db->query("
                SELECT a.secret,a.id,a.answer
                FROM auth_requests a
                WHERE(
                    SHA1(CONCAT(SHA1(a.id),a.receiver))={$this->db->escape($hash)} AND
                        a.receiver={$user["twitter_id"]} AND a.completed=0
                )
                ORDER BY a.datetime DESC
                LIMIT 1
            ");
            if($q && $q->num_rows() == 1){
                $record = current($q->result_array());               
                $record["secret"] = $this->decrypt($record["secret"]);
                $record["answer"] = $this->decrypt($record["answer"]);                   
                return $record;
            }else{
                log_message("error", "Authorization hash not found. {$hash}");
                return false;
            }
        }else{
            log_message("error", "Invalid hash {$hash}");
            return false;
        }
    }
    
    /**
     * Send authorization request by DM
     * 
     * @param type $data
     * @return boolean
     */
    public function send_authorize_request($data = array()){
       if($data["auth_id"] != "" && intval($data['recipient']) > 0){
           
           $this->load->library("GoogleShortURL");
           
           //Find the request
           $q = $this->db->query("
               SELECT c.username AS sender,d.name AS recipient_name,a.receiver AS recipient
               FROM auth_requests a
                    INNER JOIN credentials b ON a.sender=b.id
                    INNER JOIN users c ON b.user_id=c.id
                    INNER JOIN users d ON a.receiver=d.twitter_id
               WHERE (
                    SHA1(a.id)={$this->db->escape($data["auth_id"])} AND completed=0 AND sender={$this->session->userdata("uid")}
               )
               LIMIT 1
           ");
           if($q && $q->num_rows() == 1){
               
               //Prepair the data en decrypt where needed.
               $record = current($q->result_array());
               $record["sender"] = $this->decrypt($record["sender"]);
               
               $data = array(
                   "recipient_name" =>  $record["recipient_name"],
                   "screenname"     =>  $record['sender'],
                   "shorturl"       =>  $this->googleshorturl->short_url(site_url()."requests/read_authorize_request/".sha1($data["auth_id"].$record["recipient"])) 
               );
               
               $text = $this->load->view("templates/en_authorize_request", $data, true);
               if($text != ""){
                   //Now send the DM
                   return $this->send_dm(array("tweet" =>  $text, "recipient"  =>  $record["recipient"]));
               }else{
                   log_message("error", "Template text for auth request preperation failed");
                   return false;
               }
           }else{         
               log_message("error", "Authorization request could not be found or is already answered");
               return false;
           }
       }else{
           log_message("error", "Not all parameters are found while trying to send an authorize request.");
           return false;
       }
    }
    
    /**
     * Store the authorization request to a users
     * 
     * @param type $data
     * @return boolean
     */
    public function store_authorize_request($data = array()){
        if(intval($this->session->userdata("uid")) > 0 && 
                intval($data['recipient']) > 0 && $data['secret'] != ""){
            $q = $this->db->query("
                    INSERT INTO auth_requests (sender,receiver,datetime,secret,answer)
                    VALUES({$this->db->escape($this->session->userdata("uid"))},{$this->db->escape(intval($data['recipient']))},
                        now(),'{$this->encrypt($this->db->escape_str($data['secret']))}',
                            '{$this->encrypt($this->db->escape_str($data['answer']))}')
            ");
            if($q){
                return sha1($this->db->insert_id());
            }else{
                return false;
            }
        }else{
            log_message("error", "No sender or recipient found while trying to Authorize a friend");
            return false;
        }
    }
    
    /**
     * Get all Tweets this user has send
     */
    public function get_send_tweets($id = 0){
        if($id < 1){
            $id = intval($this->session->userdata("uid"));
        }
        if($id > 0){
            
            //Helper for timespans
            $this->load->helper('date');
            
            $q = $this->db->query("
                SELECT a.id,a.user_id,a.recipient,a.tweet_text,a.hash,a.datetime,c.twitter_id AS twitter_sender_id
                FROM tweets a
                    INNER JOIN credentials c ON a.user_id=c.id
                WHERE ( c.id={$id} )
                ORDER BY a.datetime DESC
            ");            
            if($q && $q->num_rows() > 0){
                $result = array();          
                foreach($q->result_array() as $tweet){
                    $item = array();

                    //Decrypt send Tweet
                    if(($item["text"] = $this->decrypt_tweet(array(
                        "twitter_id"    => $tweet["twitter_sender_id"],
                        "id"            => $tweet["id"]
                    ))) !== false){

                        $sender = $this->get_twitter_user_by_id($tweet["twitter_sender_id"], $id);
                        $item["id"] = $tweet['id'];
                        $item["screenname"] = $sender['username'];
                        $item["name"] = $sender['name'];
                        $item["avatar"] = $sender["profile_image"];
                        $item["timespan"] = timespan(strtotime($tweet["datetime"]),time());
                        $item["ts"] = strtotime($tweet["datetime"]);
                        $item["send"] = 1;                        

                        array_push($result, $item);
                    }
                }
                return $result;
            }else{
                log_message("error", "No messages found for this user {$id}");
                return false;
            }
        }else{
            log_message("error", "User is not authenticated.");
            return false;
        }
    }
    
    /**
     * Get all messages for the current user
     * 
     * @return boolean|array
     */
    public function get_user_tweets(){
        if(intval($this->session->userdata("uid")) > 0 && $this->session->userdata("auth") == true){
            
            //Helper for timespans
            $this->load->helper('date');
            
            $q = $this->db->query("
                SELECT a.id,a.user_id,a.recipient,a.tweet_text,a.hash,a.datetime,c.twitter_id AS twitter_sender_id,b.twitter_id
                FROM tweets a
                    INNER JOIN credentials b ON (a.recipient=b.twitter_id OR a.recipient=0)
                    INNER JOIN credentials c ON a.user_id=c.id
                WHERE ( b.id=".intval($this->session->userdata("uid"))." )
                ORDER BY a.datetime DESC
            ");
            if($q && $q->num_rows() > 0){
                $result = array();
                foreach($q->result_array() as $tweet){
                    //Check if this user is authorized to read these Tweets
                    if($this->is_authorized($tweet["twitter_sender_id"])){
                        $item = array();
                        
                        //Decrypt received Tweet
                        $decrypted = $this->decrypt_tweet(array(
                            "twitter_id"    => $tweet["twitter_id"],
                            "id"            => $tweet["id"]
                        ));
                        if($decrypted !== false){
                            $item["id"] = $tweet["id"];
                            $item["retweet"] = (isset($tweet["retweet"]) ? $tweet["retweet"] : 0);
                            $item["text"] = $decrypted;
                            $sender = $this->get_twitter_user_by_id($tweet["twitter_sender_id"]);
                            $item["screenname"] = $sender['username'];
                            $item["name"] = $sender['name'];
                            $item["avatar"] = $sender["profile_image"];
                            $item["timespan"] = timespan(strtotime($tweet["datetime"]),time());
                            $item["ts"] = strtotime($tweet["datetime"]);
                            $item["private"] = ($tweet["recipient"] == 0 ? "0" : "1");
                            $item["twitter_sender_id"] = $tweet["twitter_sender_id"];
                            $item["hash"] = $tweet["hash"];
                            array_push($result, $item);
                        }
                    }
                    
                }
                $timestamps = array();
                $sends = $this->get_send_tweets();                
                if($sends != false){
                    $result = array_merge($result, $sends);
                }
                foreach($result as $tweet){
                    $timestamps[] = $tweet["ts"];
                }
                array_multisort($timestamps, SORT_DESC, $result);
                return $result;
            }else{
                log_message("error", "No messages found for this user {$this->session->userdata("uid")}");
                return false;
            }
        }else{
            log_message("error", "User is not authenticated.");
            return false;
        }
    }
    
    /**
     * Get the Tweet by using the hash
     * 
     * @param type $hash
     * @return boolean
     */
    public function get_tweet_by_hash_and_verifier($hash = "", $verifier = ""){
        if(intval($this->session->userdata("uid")) > 0 && $hash != "" && $verifier != ""){
            
            //Helper for timespans
            $this->load->helper('date');
            
            //Query the database
            $q = $this->db->query("
                SELECT a.id,user_id,recipient,tweet_text,hash,datetime
                FROM tweets a
                WHERE ( SHA1(a.id)={$this->db->escape($hash)} )
            ");
            if($q && $q->num_rows() == 1){
                $record = current($q->result_array());
                if($record["hash"] == $verifier){
                    //Decrypt our Tweet
                    $decrypted_tweet = $this->decrypt_tweet(array(
                        "id"  =>  $record["id"],
                        "twitter_id"    => $record["recipient"]
                    ));                    
                    $record["datetime"] = timespan(strtotime($record["datetime"]),time());
                    $record["sender"] = $this->get_user_by_id($record["user_id"]);
                    $record["sender"]["username"] = $this->decrypt($record["sender"]["username"]);
                    $record["tweet_text"] = $decrypted_tweet;
                    return $record;
                }else{
                    log_message("error", "Hash: {$record["hash"]} doesnt match {$verifier}");
                    return false;
                }
            }else{
                log_message("error", "Tweet could not be found with this hash {$hash}");
                return false;
            }
            
        }else{
            log_message("error", "No hash was given or user is not authenticated.");
            return false;
        }
    }
    
    /**
     * Decrypt an encrypted Tweet by User PublicKey && SealKey
     * 
     * @param array $data   array(tweet_id, twitter_id)
     * @return boolean|string
     */
    public function decrypt_tweet($data = array()){
        $id = intval($data["id"]);
        $twitter_id = intval($data["twitter_id"]);
        $user = $this->mod_user->get_user();
        
        if($id > 0 && $twitter_id > 0){
            $q = $this->db->query("
                SELECT a.*,c.id AS user_id,b.seal
                FROM tweets a
                    INNER JOIN tweet_seals b ON a.id=b.tweet_id
                    INNER JOIN credentials c ON b.twitter_id=c.twitter_id
                WHERE (a.id={$id} AND b.twitter_id={$twitter_id})
                LIMIT 1
            ");            
            
            if($q && $q->num_rows() == 1){
                $tweet = current($q->result_array());
                
                if(($keypair = $this->mod_user->get_user_keypair_by_id($tweet["user_id"])) != false){
                    
                    //Prepair our private key
                    $privateKey = str_replace("\\n", "\n", $keypair["private_key"]);
                    if(($key = openssl_pkey_get_private($privateKey)) != false){
                        $key = openssl_pkey_get_private($privateKey);

                        //Open the encrypted data now
                        if(openssl_open(base64_decode($this->decrypt($tweet["tweet_text"])), $open_data, base64_decode($this->decrypt($tweet["seal"])), $key) !== false){
                            return $open_data;
                        }else{
                            log_message("error", "Error while decrypting this Tweet");
                        }
                    }else{
                        log_message("error", "Error while preparing PrivateKey for decryption.");
                        return false;
                    }
                }else{
                    log_message("error", "Error while getting keys to decrypt Tweet.");
                    return false;
                }
            }else{
                //log_message("error", "Error while retrieving Tweet for decryption.");
                return false;
            }
        }else{
            log_message("error", "Not all parameters are found while decrypting Tweet.");
            return false;
        }
    }
    
    /**
     * Get public keys of all followers of the current user
     * 
     * @return boolean|array
     */
    public function get_publickeys_of_followers($recipient = 0){
        set_time_limit(0);
        $user = $this->mod_user->get_user();
        if($user["user_id"] > 0){
            
            //Prepair SQL statement
            $sql = "
                SELECT DISTINCT d.twitter_id,f.public_key
                FROM credentials a
                    INNER JOIN followers_friends b ON a.id=b.user_id
                    INNER JOIN auth_requests c ON c.receiver=b.twitter_id AND c.sender=a.id
                    INNER JOIN users d ON c.receiver=d.twitter_id
                    INNER JOIN credentials e ON d.id=e.user_id
                    INNER JOIN certificates f ON f.user_id=e.id
                WHERE (
                    a.user_id={$user["user_id"]} AND c.completed=1
            ";
            if($recipient != 0){                
                $sql .= " AND e.twitter_id={$this->db->escape($recipient)} )";
            }else{
                $sql .= " )";
            }           
            $q = $this->db->query($sql);            
            if($q && $q->num_rows() > 0){
                $result = array();
                foreach($q->result_array() as $keypair){
                    $publicKey = str_replace("\\n", "\n", $this->decrypt($keypair["public_key"]));
                    if(($res = openssl_pkey_get_public($publicKey) !== false)){ 

                        $result[$keypair["twitter_id"]] = openssl_pkey_get_public($publicKey);
                        
                    }else{
                        log_message("error", "Error while getting user {$keypair["twitter_id"]} public key"); 
                    }
                }
                return $result;
            }else{
                log_message("error", "Can't find any registered follower in the friends table that is authorized.");
                return false;
            }
        }else{
            log_message("error", "Can't find the users ID");
            return false;
        }
    }
    
    /**
     * Encrypt the Tweet using the PublicKey of the Sender and all followers
     * 
     * @param string $tweet
     */
    public function encrypt_tweet($tweet = "", $recipient = 0){
        set_time_limit(0);
        
        //First get the Public Key of the current user
        if(($keypair = $this->mod_user->get_user_keypair()) !== false){
            if(is_array($keypair) && count($keypair) > 0){
                $publicKey = str_replace("\\n", "\n", $keypair["public_key"]);
                if(($res = openssl_pkey_get_public($publicKey) !== false)){ 
                    
                    //Ok we have our public key
                    $publicKeyRS = openssl_pkey_get_public($publicKey);
                    
                    //Get public keys of all authorized followers or 1 specific follower, and myself
                    $publicKeys = $this->get_publickeys_of_followers($recipient);
                    
                    //Add our own key
                    $user = $this->mod_user->get_user();
                    $publicKeys[$user["twitter_id"]] = $publicKeyRS;
                    $pubKeyIndex = array();
                    foreach($publicKeys as $id => $key){
                        array_push($pubKeyIndex, $id);
                    }                    
                    
                    //Seal this Tweet
                    $response = array("tweet" => "", "seals" => array());
                    if(openssl_seal($tweet, $sealed_data, $env_keys, $publicKeys) !== false){
                        for($x = 0; $x < count($env_keys); $x++){
                            array_push($response["seals"], array(
                                "twitter_id" => $pubKeyIndex[$x], 
                                "key" => base64_encode($env_keys[$x])
                                )
                            );
                        }
                        $response["tweet"] = base64_encode($sealed_data);
                        return $response;
                    }else{
                        log_message("error", "Error while encrypting Tweet");
                        return false;
                    }                    
                    
                }else{
                    log_message("error", "Could not prepair PublicKey of the current user");
                    return false;
                }
            }else{
                log_message("error", "Current user doesn't seem to have a public/private keypair.");
                return false;
            }
        }else{
            log_message("error", "Current user doesn't seem to have a public/private keypair.");
            return false;
        }
    }
    
    /**
     * Prepair the Tweet message with use of templates
     * @param type $hash
     * @return boolean
     */
    public function prepair_tweet($hash = "", $recipient = "", $verifier = ""){
        if($hash != "" && $recipient != "" && $verifier != ""){
            $this->load->library("GoogleShortURL");
            
            //Get the country to send the right template
            $ip = services::get_ip_address();
            $hostname = gethostbyaddr($ip);
            $country = strtolower(geoip_country_code_by_name($hostname));
            
            //Get the userinfo and short url
            $shorturl = $this->googleshorturl->short_url(site_url()."messages/index/{$hash}/{$verifier}");                        
            $userinfo = $this->mod_user->get_user();
            if(is_array($userinfo)){
            
                //Get the Tweet template
                $template = realpath(APPPATH . "/views/templates/{$country}_tweet_template");
                if(file_exists($template) == FALSE){
                    $template = "templates/en_tweet_template";
                }else{
                    $template = "templates/{$country}_tweet_template";
                }
                $data = array(
                    "shorturl"        => $shorturl,
                    "screenname"      => "$"."{$this->decrypt($userinfo["username"])}",
                    "recipient_name"  => $recipient["name"],
                    "recipient"       => "$"."{$recipient["username"]}"
                );
                $templates = array();
                $templates["tweet"] = $this->load->view($template, $data, true);
                
                //Get the Direct Message template
                $template = realpath(APPPATH . "/views/templates/{$country}_dm_template");
                if(file_exists($template) == FALSE){
                    $template = "templates/en_dm_template";
                }else{
                    $template = "templates/{$country}_dm_template";
                }
                $templates["dm"] = $this->load->view($template, $data, true);                
                
                return $templates;                
            }else{
                log_message("error", "Current user could not be found.");
                return false;
            }
            
        }else{
            log_message("error", "No hash found to work with.");
            return false;
        }
    }
    
    /**
     * Send a direct message to a Twitter user
     * @param type $data
     */
    public function send_dm($data = array()){
        if($data["tweet"] != "" && $data["recipient"] != ""){
            if(($oauth = $this->authenticate()) !== FALSE){
                $res = $oauth->post('direct_messages/new', array('text' => $data["tweet"], 'user_id' => intval($data["recipient"])));
                if(is_object($res)){
                    if(!is_array($res->errors) && is_null($res->errors)){                        
                        return true;
                    }else{
                        log_message("error", "Direct Message could not be send. Invalid response from Twitter.\r\n".$res->errors[0]->message);
                        return false;
                    }
                }else{
                    log_message("error", "Direct Message could not be send. Invalid response from Twitter.");
                    return false;
                }
            }else{
                log_message("error", "Direct Message could not be send. Twitter Authentication failure.");
                return false;
            }
        }else{
            log_message("error", "Direct Message could not be send. Tweet and Recipient are empty.");
            return false;
        }
    }
    
    /**
     * Get all friends from the current user
     * 
     * @param boolean $autorized_only Only get authorized friend
     * @return boolean|array
     */
    public function get_friends($autorized_only = false){
        set_time_limit(0);
        if(intval($this->session->userdata("uid")) > 0){
            $q = $this->db->query("
                SELECT twitter_id,CONCAT('$',username) AS username,name,profile_image
                FROM followers_friends
                WHERE ( user_type=1 AND user_id={$this->session->userdata("uid")} )
                ORDER BY username
            ");
            if($q && $q->num_rows() > 0){
                $result = $q->result_array();                
                if($autorized_only == true){
                    $auth_result = array();
                    foreach($result as $friend){
                        if($this->friend_authorized($friend["twitter_id"]) == true){
                            array_push($auth_result, $friend);
                        }
                    }
                    $result = $auth_result;
                    $auth_result = null;
                }
                array_push($result, array(
                    "twitter_id"    =>  0,
                    "username"      =>  "All followers",
                    "name"          =>  "All followers",
                    "profile_image" =>  ""
                ));
                return $result;
            }else{
                log_message("error", "User doesn't seem to have any friends.");
                return false;
            }
        }else{
            log_message("error", "We can't find the current logged on user ID to get the Friends.");
            return false;
        }
    }
    
    /**
     * Get all followers of the current user
     * 
     * @return boolean|array
     */
    public function get_followers(){
        set_time_limit(0);
        if(intval($this->session->userdata("uid")) > 0){
            $q = $this->db->query("
                SELECT twitter_id,username,name,profile_image
                FROM followers_friends
                WHERE ( user_type=0 AND user_id={$this->session->userdata("uid")} )
                ORDER BY username
            ");
            if($q && $q->num_rows() > 0){
                return $q->result_array();
            }else{
                log_message("error", "User doesn't seem to have any followers.");
                return false;
            }
        }else{
            log_message("error", "We can't find the current logged on user ID to get the Followers.");
            return false;
        }
    }
    
    var $friendlist = array();
    /**
     * Get the friendlist from Twitter
     * 
     * @param type $next_cursor
     * @return boolean
     */
    public function get_friends_from_twitter($next_cursor = 0){
        set_time_limit(0);
        if(($oauth = $this->authenticate()) !== FALSE){
            
            //Set basic settings
            $config = array(
                "skip_status"   =>  "true",
                "include_user_entities" => "false"
            );
            if($next_cursor > 0){ $config["cursor"] = $next_cursor; }
            
            //Send the request to Twitter
            $friends = $oauth->get("friends/list", $config);
            if(!is_null($friends)){
                if(is_object($friends)){
                    
                    //Loop through the list and minify the data for later use
                    foreach($friends->users as $friend){
                        $this->friendlist[$friend->id] = array(
                            "username"  =>  $friend->screen_name,
                            "name"      =>  $friend->name,
                            "profile_image" =>  $friend->profile_image_url
                        );                        
                    }
                    if($friends->next_cursor > 0){
                        $this->get_friends_from_twitter($friends->next_cursor);
                    }
                    
                    //Check if we have followers
                    if(is_array($this->friendlist) && count($this->friendlist) > 0){

                        //Clear the old list
                        $q = $this->db->query("DELETE FROM followers_friends WHERE user_id={$this->session->userdata("uid")} AND user_type=1");
                        if($q){
                            //Loop through the list and insert them into the table
                            foreach($this->friendlist as $id => $user){
                                $q = $this->db->query("
                                    INSERT IGNORE INTO followers_friends (user_id,twitter_id,username,name,profile_image,datetime,user_type)
                                    VALUES({$this->session->userdata("uid")},'{$id}',{$this->db->escape($user["username"])},
                                        {$this->db->escape($user["name"])},{$this->db->escape($user["profile_image"])},now(),1
                                    )
                                ");
                                if($q){
                                    //Go on please
                                }else{
                                    log_message("error", "Error while inserting Followers. We skipped this step for now.");
                                    break;
                                }
                            }
                            return false;
                        }else{
                            log_message("error", "Error while deleting old followers");
                            return false;
                        }
                    }
                    
                }else{
                    log_message("error", "Error while retrieving the friendlist from Twitter.");
                    return false;
                }
            }else{
                log_message("error", "Error while retrieving the friendlist from Twitter.");
                return false;
            }
        }else{
            log_message("error", "Error with authentication check on Twitter");
            return false;
        }
    }
    
    var $followers = array();
    /**
     * Get all followers from this user
     * 
     * @param type $next_cursor
     */
    public function get_followers_from_twitter($next_cursor = 0){
        set_time_limit(0);
        if(($oauth = $this->authenticate()) !== FALSE){
            
            //Configure twitter, and get the list
            $config = array(
                'skip_status' => 'true', 
                'include_user_entities' => 'false'                
            );
            if($next_cursor != 0){ $config['cursor'] = $next_cursor; }
            $list = $oauth->get('followers/list', $config);
            
            if(!is_null($list)){
                if(is_object($list)){    
                    //Loop through the list
                    foreach($list->users as $user){
                        $this->followers[$user->id] = array(
                            "username"          =>  $user->screen_name,
                            "name"              =>  $user->name,
                            "profile_image" =>  $user->profile_image_url
                        );                        
                    }                    
                    if($list->next_cursor > 0){
                        $this->get_followers_from_twitter($list->next_cursor);
                    }
                }else{
                    log_message("error", "Error while retrieving the Followers from Twitter.");
                    return false;
                }
            }else{
                log_message("error", "Error while retrieving the Followers from Twitter.");
                return false;
            }
            
            //Check if we have followers
            if(is_array($this->followers) && count($this->followers) > 0){
                
                //Clear the old list
                $q = $this->db->query("DELETE FROM followers_friends WHERE user_id={$this->session->userdata("uid")} AND user_type=0");
                if($q){
                    //Loop through the list and insert them into the table
                    foreach($this->followers as $id => $user){
                        $q = $this->db->query("
                            INSERT IGNORE INTO followers_friends (user_id,twitter_id,username,name,profile_image,datetime)
                            VALUES({$this->session->userdata("uid")},'{$id}',{$this->db->escape($user["username"])},
                                {$this->db->escape($user["name"])},{$this->db->escape($user["profile_image"])},now()
                            )
                        ");
                        if($q){
                            //Go on please
                        }else{
                            log_message("error", "Error while inserting Followers. We skipped this step for now.");
                            break;
                        }
                    }
                    return false;
                }else{
                    log_message("error", "Error while deleting old followers");
                    return false;
                }
            }
        }else{
            log_message("error", "Error while login to Twitter to get followers list");
            return false;
        }        
    }
    
    /**
     * Let the new registered user follow us
     */
    public function follow_us(){
        if(($oauth = $this->authenticate()) !== FALSE){
            $oauth->post('friendships/create', array('screen_name' => 'CryptoTweet', 'follow' => 'true'));            
        }        
    }
    
    /**
     * Get the friend/follower bij its Twitter ID
     * 
     * @param type $twitter_id
     * @return boolean|array
     */
    public function get_twitter_user_by_id($twitter_id = 0, $user_id = 0){
        if($user_id < 1){
            $user_id = intval($this->session->userdata("uid"));
        }
        if($user_id > 0 && intval($twitter_id) > 0){
            $q = $this->db->query("
                SELECT *
                FROM followers_friends a
                    LEFT OUTER JOIN credentials b ON a.twitter_id=b.twitter_id
                WHERE (
                        a.twitter_id={$this->db->escape(intval($twitter_id))}
                )
                LIMIT 1
            ");     // a.user_id={$this->db->escape($this->session->userdata("uid"))} AND
            if($q && $q->num_rows() == 1){
                return current($q->result_array());
            }else{
                $q = $this->db->query("
                    SELECT *
                    FROM credentials a
                    WHERE (
                        a.twitter_id={$this->db->escape(intval($twitter_id))}
                    )
                    LIMIT 1
                ");     // a.user_id={$this->db->escape($this->session->userdata("uid"))} AND
                if($q && $q->num_rows() == 1){
                    return current($q->result_array());
                }else{
                    log_message("error", "Twitter ID not found in Followers and Friends");
                    return false;
                }
            }
        }else{
            log_message("error", "Twitter ID not found or user is not authenticated.");
            return false;
        }
    }
    
    /**
     * Send Tweet through Twitter
     * @param type $data
     */
    private function tweet($data = array()){
        $res = $oauth->post("statuses/update", array("status" =>  $tweet["tweet"]));
        if(!is_null($res)){
            if(is_object($res)){
                if(!is_array($res->errors)){
                    $status = true;
                }else{
                    $result = false;
                }
                return $result;
            }else{
                log_message("error", "Sending Tweet failed");
                $status = false;
                return false;
            }
        }else{
            log_message("error", "Sending Tweet failed");
            $status = false;
            return false;
        }
    }
    
    /**
     * Send encrypted tweet
     * 
     * @param type $data
     */
    public function send_tweet($data = array()){
        set_time_limit(0);
        if($data["tweet"] != "" && intval($data["recipient"]) >= 0){            
            //If tweet is somehow larger then 130. Schrik it to 130
            if(strlen($data["tweet"]) > 130){
                $data["tweet"] = substr($data["tweet"], 0, 130);
            }
            
            //Check if recipient exists
            if(intval($data["recipient"]) != 0){    //To all followers
                if(($recipient = $this->get_twitter_user_by_id(intval($data["recipient"]))) === false){
                    return false;
                }
            }
            
            //Check if we have access to Twitter
            if(($oauth = $this->authenticate()) !== FALSE){
                
                //Encrypt the Tweet and get all associated keys
                $encrypted_tweet = $this->encrypt_tweet($data["tweet"], intval($data["recipient"]));
                
                //Save this message
                $q = $this->db->query("
                    INSERT INTO tweets SET 
                        user_id={$this->db->escape($this->mod_user->uid())},
                        datetime=now(),
                        tweet_text='{$this->db->escape_str($encrypted_tweet["tweet"])}',
                        hash='".sha1($data["tweet"])."',
                        recipient={$this->db->escape(intval($data["recipient"]))},
                        retweet={$this->db->escape((isset($data["retweet"]) ? $data["retweet"] : 0))}
                ");
                if($q){
                    $id = $this->db->insert_id();
                    if($id > 0){
                        
                        //Store the keys for this Tweet
                        foreach($encrypted_tweet["seals"] as $key){
                            $q = $this->db->query("
                                INSERT INTO tweet_seals (twitter_id,tweet_id,seal) VALUES({$key['twitter_id']},{$id},'{$this->encrypt($this->db->escape_str($key["key"]))}')
                            ");
                            if(!$q){
                                log_message("error", "Error while storing Tweet seal keys");
                            }
                        }
                        
                        //Prepair the Tweets
                        //Check if we need to send a DM or not
                        if(intval($data["recipient"]) != 0){
                            if(($tweet = $this->prepair_tweet(sha1($id), $recipient, sha1($encrypted_tweet["tweet"]))) !== false){
                            
                                $status = false;
                            
                                //First send the DM
                                $message = array(
                                    "tweet" =>  $tweet["dm"],
                                    "recipient" =>  intval($data["recipient"])
                                );
                                if($this->send_dm($message) !== false){
                                    $status = true;
                                }else{
                                    log_message("error", "Direct Message could not be send to the recipient.");
                                    $status = false;
                                }
                            
                                //If user has tweet also ON
                                if(intval($data["tweet_also"]) == 1 || $status == false){
                                    $res = $oauth->post("statuses/update", array(
                                        "status" =>  $tweet["tweet"]
                                    ));
                                    if(is_object($res)){
                                        if(!is_array($res->errors)){
                                            $status = true;
                                        }else{
                                            log_message("error", "Sending Tweet failed");
                                            $status = false;
                                        }
                                    }else{
                                        log_message("error", "Sending Tweet failed");
                                        $status = false;
                                    }
                                }
                                
                            }else{
                                log_message("error", "Prepair Tweet was false, Tweet not send but stored in our DB.");
                                return $status;
                            }                            
                        }else{
                          log_message("error", "No DM or Tweet was send, because recipient was 0, that means to all followers.");
                          $status = true;
                        }
                        return $status;
                    }else{
                        log_message("error", "Can't login to Twitter");
                        return 0;
                    }
                }else{
                    log_message("error", "Insert of the Tweet in the table failed. ID is 0.");
                    return 0;
                }
            }else{
                log_message("error", "Can't save Tweet");
                return 0;
            }
        }else{
            log_message("error", "Tweet is empty");
            return 0;
        }
    }
    
    /**
     * Fact custom function to get what we want from Twitter API
     * 
     * @param type $url
     * @return object Result
     */
    public function get($url = ""){
        if(($oauth = $this->authenticate()) !== FALSE){
            $result = $oauth->get($url);
            if(!is_null($result) && is_object($result)){
                if(!is_array($result->errors)){
                    return $result;
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }else{
            log_message("error", "Error while login to Twitter in the GET method.");
            return false;
        }
    }
    
    /**
     * Authenticate user on Twitter
     * @return boolean|\TwitterOAuth
     */
    public function authenticate(){
        if($this->session->userdata("oauth_token") != "" && $this->session->userdata("oauth_token") != ""){
            if(!class_exists("TwitterOAuth")){
                $this->load->file(APPPATH."libraries/twitteroauth/twitteroauth.php");
            }
            if($this->oauth == null){
                // Include class file & create object passing request token/secret also
                $oauth = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $this->session->userdata("oauth_token"), $this->session->userdata("oauth_token_secret"));
                $credentials = $oauth->get("account/verify_credentials");            

                //Check is we have an error
                if(is_object($credentials) && is_array($credentials->errors)){
                    log_message("error", "Twitter API authentication error occured.");
                    return false;
                }elseif(is_object($credentials)){
                    $this->oauth = $oauth;
                    return $oauth;
                }else{
                    //Let's be lucky
                    $this->oauth = $oauth;
                    return $oauth;
                }    
            }else{
                return $this->oauth;
            }            
        }else{
            log_message("error", "No oAuth tokens found in the session of the current user. File: ".__FILE__."\r\nLine: ".__LINE__);
            return false;
        }
    }
    
    /**
     * Get tweets of the current user
     */
    public function get_tweets(){
        set_time_limit(0);
        if(($oauth = $this->authenticate()) !== FALSE){
            $tweets = $oauth->get("statuses/home_timeline");
            return $tweets;
        }else{
            log_message("error", "Authentication with Twitter failed.");
            return false;
        }
    }
    
    /**
     * Get the user by its user ID
     * @param type $id
     */
    public function get_user_by_id($id = 0){
        if($id > 0){
            $q = $this->db->query("
                SELECT *
                FROM users a
                    INNER JOIN credentials b ON a.id=b.user_id
                WHERE b.id={$this->db->escape($id)}
                LIMIT 1
            ");
            if($q && $q->num_rows() == 1){
                return current($q->result_array());
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
    
    /**
     * Check if current user exists in our DB. Return data if true
     * 
     * @return boolean
     */
    public function check_user_exists($authenticate = false){
        if($this->session->userdata("oauth_token") && $this->session->userdata("oauth_token_secret")){    
            //See if we have a record of these Tokens
            $q = $this->db->query("
                SELECT *
                FROM users
                WHERE (
                    oauth_token='".$this->encrypt($this->db->escape_str($this->session->userdata("oauth_token")))."' AND
                        oauth_secret='".$this->encrypt($this->db->escape_str($this->session->userdata("oauth_token_secret")))."'
                )
                ORDER BY id DESC
                LIMIT 1
            ");
            if($q && $q->num_rows() == 1){
                $record = current($q->result_array());
                
                //If we need to authenticate the user through Twitter
                if($authenticate == true){
                    $q = $this->db->query("
                        SELECT a.*
                        FROM credentials a
                            INNER JOIN users b ON a.user_id=b.id
                        WHERE 
                        (
                            a.user_id={$record["id"]} AND 
                            oauth_token='".$this->encrypt($this->db->escape_str($this->session->userdata("oauth_token")))."' AND
                            oauth_secret='".$this->encrypt($this->db->escape_str($this->session->userdata("oauth_token_secret")))."'
                        )
                        LIMIT 1
                    ");
                    if($q && $q->num_rows() == 1){
                        $r = current($q->result_array());
                        $this->session->set_userdata("uid", $r["id"]);
                        $this->session->set_userdata("auth", true);
                        return true;
                    }else{
                        return false;
                    }
                }else{
                    return $record;
                }
            }else{
                return false;
            }
        }else{
            log_message("error", "No OAuth tokens found in the sesion to check if the user exists.");
            return false;
        }
    }
    
    /**
     * Update user profile in final step of the registration
     * 
     * @param type $data
     * @return boolean
     */
    public function update_profile($data = array()){
        //Prepair the SQL statement
        $sql = "
            UPDATE users SET
                name=".$this->db->escape($data["name"]).",
                location=".$this->db->escape($data["location"]).",
                profile_image=".$this->db->escape($data["profile_image"]).",
                twitter_id=".$this->db->escape($data["twitter_id"])."                        
        ";
        if($data["personal_website"] != ""){
            $sql .= ",personal_website='{$this->encrypt($this->db->escape_str($data["personal_website"]))}'";
        }
        if($data["description"] != ""){
            $sql .= ",description='{$this->encrypt($this->db->escape_str($data["description"]))}'";
        }
        //Add the WHERE part      
        $sql .= "WHERE (session_id=".$this->db->escape($this->session->userdata("session_id"))." AND
            oauth_token='".$this->encrypt($this->db->escape_str($this->session->userdata("oauth_token")))."' )";
        
        $q = $this->db->query($sql);        
        if($q){
            $user = $this->check_user_exists();
            if($user["id"] > 0){
                $data["user_id"] = $user["id"];
                if($this->mod_user->user_account_exists($user["id"]) === FALSE){
                    //Create a user account for this new user
                    $res = $this->mod_user->create_user($data);
                    if($res > 0){
                        return true;
                    }else{
                        log_message("error", "Error while trying to create a new user.");
                        return false;
                    }
                }else{
                    return true;
                }
            }else{
                log_message("error", "User already exists in our Twitter database.");
                return false;
            }
            return true;
        }else{
            log_message("error", "Error while updating user profile. Last step of registration.");
            return false;
        }
    }
    
    /**
     * Store the information we need for future use. Only encrypted!
     * @param type $data
     * @return boolean
     */
    public function store_authorized_user($data = array()){
        $q = $this->db->query("
            INSERT INTO users SET
                session_id=".$this->db->escape($data["session_id"]).",
                oauth_token='".$this->encrypt($this->db->escape_str($data["oauth_token"]))."',
                oauth_secret='".$this->encrypt($this->db->escape_str($data["oauth_token_secret"]))."',
                username='".$this->encrypt($this->db->escape_str($data["screen_name"]))."',
                oauth_uid='".$this->encrypt($this->db->escape_str($data["user_id"]))."',
                oauth_provider=".$this->db->escape($data["provider"])."                
        ");
        if($q){
            $id = $this->db->insert_id();
            return $id;
        }else{
            log_message("error", "Error while saving oAuth Tokens in the database.");
            return false;
        }
    }
}
?>
