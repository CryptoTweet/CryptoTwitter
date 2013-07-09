<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Description of twitter
 *
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright (c) 2013, Victor Angelier
 * @link http://www.victorangelier.com Personal Website
 * @link http://www.twitter.com/digital_human
 */
class twitter extends MY_Controller {
    
    var $uid = 0;
    
    public function __construct() {
        parent::__construct();
        
        //Controleer of de user is ingelogd of niet
		if(intval($this->session->userdata("uid")) > 0 && $this->session->userdata("auth") == true){	
            $this->uid = $this->session->userdata("uid");
		}else{
            $this->session->sess_destroy();
            die('0');
		}
    }
    
    /**
     * 
     */
    public function index(){
        redirect(site_url());
    }
    
    /**
     * Get users followers
     */
    public function get_followers(){
        $friendlist = $this->mod_twitter->get_followers();
        echo json_encode($friendlist);
        die();
    }
    
    /**
     * Get the list of followers for this user
     */    
    public function get_friends(){
        $friendlist = $this->mod_twitter->get_friends(true);
        if(is_array($friendlist) && count($friendlist) > 0){
            echo json_encode($friendlist);
        }else{
            die();
        }
        die();
    }
    
    /**
     * Temporary method, REMOVE
     */
    public function update_friends(){
        $this->mod_twitter->get_friends_from_twitter();        
        redirect(site_url());
    }
    /**
     * Temporary method, REMOVE
     */
    public function update_followers(){
        $this->mod_twitter->get_followers_from_twitter();
        redirect(site_url());
    }
    
    /**
     * Retweet an existing Tweet
     * @param type $id
     */
    public function retweet($id = 0){
        $this->layout = "empty";
        $id = intval($id);
        if($id > 0){
            $this->load->model("mod_twitter");
            if($this->mod_twitter->retweet($id) !== false){
                echo 1;
            }else{
                echo 0;
            }
        }else{
            echo 0;
        }
        die();
    }
    
    /**
     * Delete a Tweet
     * @param type $id
     */
    public function delete($id = 0){
        $id = intval($id);
        if($id > 0){
            $this->load->model("mod_twitter");
            if($this->mod_twitter->delete($id) !== false){
                echo 1;
            }else{
                echo 0;
            }
        }else{
            echo 0;
        }
        die();
    }
    
    /**
     * Reply to a Tweet
     */
    public function reply(){
        $this->layout = "empty";
        if($this->input->post() && $this->input->post("tweet") != "" &&
                intval($this->input->post("parent")) >= 0){
            $res = intval($this->mod_twitter->reply_tweet($this->input->post()));
            die("{$res}");
        }else{
            die('0');
        }
    }
    
    /**
     * Send a tweet
     */
    public function send_tweet(){        
        $this->layout = "empty";
        if($this->input->post() && $this->input->post("tweet") != "" &&
                intval($this->input->post("recipient")) >= 0){
            $res = intval($this->mod_twitter->send_tweet($this->input->post()));
            die("{$res}");
        }else{
            die('0');
        }
    }
    
    public function etweets($max = 25){
        $this->layout = "empty";        
        if(($tweets = $this->mod_twitter->get_user_tweets()) !== false){
            if(is_array($tweets) && count($tweets) > 0){
                $etweets = array_slice($tweets, 0, $max);
                foreach($etweets as $item){
                    $html = '<div class="row-fluid">
                                <div class="span12 well etweet' . (isset($item["private"]) && $item["private"] == 1 ? " border-orange" : "") . '">
                                       <div class="row-fluid">
                                            <div class="tweet">
                                                <div class="row-fluid">
                                                    <div class="pull-left">
                                                        <img class="avatar" height="50" width="50" src="'. stripslashes($item["avatar"]) .'" />
                                                    </div>
                                                    <div class="span10">
                                                        <div class="row-fluid">
                                                            <span class="pull-right time">' . $item["timespan"] . '</span>
                                                            <span class="text_black bold"><a class="text_black bold" href="'.site_url().'$'.$item["screenname"].'">' . $item["name"] . '</a></span>
                                                            $'.$item["screenname"].'
                                                            <div class="clearfix"></div>                                                            
                                                        </div>
                                                        <div class="row-fluid">
                                                            <p>' . parse_tweet($item["text"]) . '</p>
                                                        </div>
                                                     </div>
                                                 </div>
                                            </div>
                                       </div>
                                       <div class="row-fluid">
                                            <div class="reply hide" data-id="'.$item["id"].'">
                                                <div class="row-fluid">                                
                                                    <textarea class="span12" maxlength="130" id="replytweet" placeholder="Reply"></textarea>                                
                                                </div>
                                                <div class="row-fluid">
                                                    <button type="button" id="reply_to_tweet" class="btn btn-info span4">ETweet!</button>
                                                    <div class="offset8 text-align-right"><span class="charcount">130</span></div>
                                                </div>
                                            </div>
                                       </div>
                                       <div class="row-fluid">                                            
                                            <div class="etweet-tools">
                                                <div class="dropdown pull-right">
                                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-tasks"></i>More</a>
                                                    <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu">
                                                    ';
                                                    if(!isset($item["send"]) && intval($item["twitter_sender_id"]) > 0):
                                                        $html .='<li><a href="#" data-id="'.$item["id"].'" class="reply"><i class="icon-share-alt"></i>&nbsp;<small>Reply</small></a></li>';
                                                        if(!isset($item['retweet']) || $item["retweet"] == 0):
                                                            if($this->mod_twitter->already_retweeted($item['hash']) == false){
                                                                $html .= '<li><a href="#" data-id="'.$item["id"].'" class="retweet"><i class="icon-retweet"></i>&nbsp;<small>Retweet</small></a></li>';
                                                            }
                                                        endif;
                                                    elseif($item["send"] == 1):
                                                        $html .= '<li><a href="#" data-id="'.$item["id"].'" class="delete"><i class="icon-fire"></i>&nbsp;<small>Delete</small></a></li>';
                                                    endif;
                                                    $html .= '
                                                    </ul>
                                                </div>
                                            </div>
                                       </div>
                                </div>
                            </div>';
                    echo $html;
                }
            }else{
                die('0');
            }
        }else{
            die('0');
        }
    }
    
    /**
     * Get user Tweets in JSON
     * @param type $max
     */
    public function get_tweets_json($max = 25){
        $this->layout = "empty";
        if(($tweets = $this->mod_twitter->get_user_tweets()) !== false){
            if(is_array($tweets) && count($tweets) > 0){
                echo json_encode(array_slice($tweets, 0, $max));
            }else{
                die('0');
            }
        }else{
            die('0');
        }
    }
    
    /**
     * Get all tweets in json format (stripped version)
     */
    public function get_twitter_tweets($max = 10){
        $this->load->helper('date');
        $this->layout = "empty";
        $result = array();
        $count = 0;
        if(($tweets = $this->mod_twitter->get_tweets()) !== FALSE){
            foreach($tweets as $tweet){
                $tweet = array(
                    "timespan"      => timespan(strtotime($tweet->created_at),time()),
                    "text"          => $tweet->text,
                    "screenname"    => $tweet->user->screen_name,
                    "name"          => $tweet->user->name,
                    "avatar"        => $tweet->user->profile_image_url
                );
                array_push($result, $tweet);            
                if($count == $max){ break; }
                $count++;
            }
            echo json_encode($result);            
            die();
        }else{
            die('0');
        }        
    }    
    
}
?>
