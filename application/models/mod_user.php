<?php
/**
 * Description of mod_user
 *
 * @author Victor Angelier
 * @copyright (c) 2012, Wielink Websolutions BV te Nunspeet
 * @package BCI - Beheer Collectieve Inkomensverzekeringen
 * @subpackage Application source
 */

/**
 * Views/Controllers/Models/Service
 */
class mod_user extends MY_Model 
{	
	/**
	 * Store the current user id
	 * @var int Current userID
	 */
	protected $uid = 0;
	
	public function __construct() {
		parent::__construct();
		
		if($this->session->userdata("uid") && intval($this->session->userdata("uid")) > 0){
			$this->uid = intval($this->session->userdata("uid"));
		}
	}

    /**
     * Verify users e-mail address and activate the account.
     * 
     * @param type $hash
     * @return boolean
     */
    public function verify_email($hash = ""){
        if($hash != "" && strlen($hash) == 40){
            //Update credentials table
            $q = $this->db->query("
                UPDATE credentials
                SET active=1
                WHERE(
                    SHA1(CONCAT(user_id,email,twitter_id))={$this->db->escape($hash)}
                )
                LIMIT 1
            ");
            if($q){
                return true;                
            }else{
                log_message("error", "Error while activating users account through e-mail verification.");
                return false;
            }
        }else{
            log_message("error", "No hash or invalid hash found while trying to verify e-mail address.");
            return false;
        }
    }
    
    /**
     * Get the user by is SHA1 hash
     * 
     * @param type $hash
     */
    public function get_user_by_hash($hash = ""){
       if(strlen($hash) == 40){
           $q = $this->db->query("
               SELECT a.*,b.id AS user_id
               FROM users a
                    INNER JOIN credentials b ON a.id=b.user_id
               WHERE (SHA1(a.username)={$this->db->escape($hash)})
               LIMIT 1
           ");
           if($q && $q->num_rows() == 1){
               $record = current($q->result_array());
               foreach($record as $k => $v){
                   $record[$k] = $this->decrypt($v);
               }
               return $record;
           }else{
               return false;
           }
       }else{
           return false;
       } 
    }
    
    /**
     * Get the keypair of a specific user by its User ID
     * 
     * @param type $user_id
     * @return boolean
     */
    public function get_user_keypair_by_id($user_id = 0){
        $user_id = intval($user_id);
        if($user_id > 0 && $this->uid > 0){
            $q = $this->db->query("
                SELECT *
                FROM certificates a
                WHERE a.user_id={$this->db->escape($user_id)}
                LIMIT 1            
            ");
            if($q && $q->num_rows() == 1){
                $result = current($q->result_array());
                $result["private_key"] = $this->decrypt($result["private_key"]);
                $result["public_key"] = $this->decrypt($result["public_key"]);
                return $result;
            }else{     
                log_message("error", "Users {$user_id} keypair not found.");
                return false;
            }
        }else{
            log_message("error", "Either userid not found or current user not authenticated.");
            return false;
        }
    }
    
    /**
     * Get users public and private key
     * 
     * @return boolean
     */
    public function get_user_keypair(){
        $q = $this->db->query("
            SELECT *
            FROM certificates a
            WHERE a.user_id={$this->db->escape($this->uid)}
            LIMIT 1            
        ");
        if($q && $q->num_rows() == 1){
            $result = current($q->result_array());
            $result["private_key"] = $this->decrypt($result["private_key"]);
            $result["public_key"] = $this->decrypt($result["public_key"]);
            $result["certificate"] = $this->decrypt($result["certificate"]);
            return $result;
        }else{            
            if($this->generate_key_and_certificate() == true){
                $this->get_user_keypair();
            }else{
                log_message("error", "Users {$this->uid} keypair not found.");
                return false;
            }
        }
    }
    
    /**
     * Generate a private key file, and a certificate with public key
     * @return boolean
     */
    public function generate_key_and_certificate(){
        if($this->uid > 0){
            //Generate new key
            $config = array(
                "digest_alg" => "sha512",
                "private_key_bits" => 2048,
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
            );
            $privateKey = openssl_pkey_new($config);

            //Get public key
            $details = openssl_pkey_get_details($privateKey);
            $publicKey = $details['key'];

            //Get private key
            openssl_pkey_export($privateKey, $privkey);
            
            $dn = array(
                "countryName" => "UK",
                "stateOrProvinceName" => "London",
                "localityName" => "Lodon",
                "organizationName" => "Diamond 4 IT Limited",
                "organizationalUnitName" => "CryptoTwitter",
                "commonName" => "{$this->uid}.cryptotweet.com",
                "emailAddress" => "support@cryptotweet.com"
            );
            $csr = openssl_csr_new($dn, $privateKey);
            if(($sscert = openssl_csr_sign($csr, null, $privateKey, 365)) !== false){
                if(openssl_csr_export($csr, $csrout) !== false){
                    if(openssl_x509_export($sscert, $certificate) !== false){
                        
                    }else{
                        log_message("error", "Error while exporting Certificate");                        
                        openssl_x509_free($sscert);
                    }                    
                }else{
                    log_message("error", "Error while exporting signed request.");
                    $certificate = 'null';
                    openssl_x509_free($sscert);
                }
            }else{
                log_message("error", "Error while creating certificate for this user.");
                $certificate = 'null';
            }
            
            //Store in our database
            $q = $this->db->query("
                INSERT INTO certificates SET
                    certificate='{$this->encrypt($this->db->escape_str($certificate))}',
                    user_id={$this->db->escape($this->uid)},
                    private_key='{$this->encrypt($this->db->escape_str($privkey))}',
                    public_key='{$this->encrypt($this->db->escape_str($publicKey))}'
            ");
            if($q){                
                openssl_pkey_free($privateKey);
                return true;
            }else{
                log_message("error", "Keypair could not be generated or INSERT failed.");
                openssl_pkey_free($privateKey);
                return false;                
            }
        }else{
            return false;
        }
    }
	
    /**
     * Get current user
     * @return boolean
     */
    public function get_user(){
        $q = $this->db->query("
                SELECT *
                FROM credentials a
                    INNER JOIN users b ON a.user_id=b.id
                WHERE a.id={$this->db->escape($this->session->userdata("uid"))}
            ");
            if($q && $q->num_rows() > 0){
                $record = current($q->result_array());
                foreach($record as $k => $v){
                    $record[$k] = $this->decrypt($v);
                }
                return $record;
            }else{
                log_message("error", "Can't find current user: {$this->session->userdata("uid")}");
                return false;
            }
    }
    
	/**
	 * Get the current userid
	 * @return int
	 */
	public function uid(){
		return intval($this->session->userdata("uid"));
	}
    
    /**
     * To check if we have a user account already
     * 
     * @param int $user_id  UserID from Users table
     * @return boolean
     */
	public function user_account_exists($user_id = 0){
        if($user_id > 0){
            $q = $this->db->query("
                SELECT id
                FROM credentials
                WHERE user_id={$this->db->escape($user_id)}
            ");
            if($q && $q->num_rows() > 0){
                return true;
            }else{
                return false;
            }
        }else{
            return -1;
        }
    }
    
    
    public function update_profile($info = array()){
        if($this->session->userdata("uid") > 0 && $this->session->userdata("auth") == true){
            if($info['password'] != "" && $info['password_repeat'] != ""){
                $user_pwd = $info["password"];
                $pwd = $this->services->generate_password();
                $user_salt = sha1($pwd);
                $salt = $this->config->config["application_salt"];
                $password = sha1($salt.$user_pwd.$user_salt);
            }else{
                $password = "";
                $salt = "";
            }
            
            //Get users country and hostname
            $ip = services::get_ip_address();
            $hostname = gethostbyaddr($ip);
            $country = geoip_country_code_by_name($hostname);

            //Prepair SQL statement
            $sql = "
                UPDATE credentials SET 
                    email='".$this->encrypt->encrypt($this->db->escape_str($info["email"]))."',                    
                    hostname='{$this->encrypt($hostname)}',
                    ipaddress='{$this->encrypt($ip)}',
                    country='{$country}'
            ";
            //Only if we need to change the password
            if($password != "" && $salt != ""){
                $sql .= ",salt='{$user_salt}',
                    password='{$this->db->escape_str($password)}'";
            }
            $sql .= " WHERE id={$this->db->escape($this->session->userdata("uid"))}";
            $q = $this->db->query($sql);
            if($q){            
                $user = $this->get_user();
                if($user != false){
                    
                    $sql = "
                        UPDATE users SET
                            name={$this->db->escape($info["name"])},
                            location={$this->db->escape($info["location"])}                        
                    ";
                    if($info["personal_website"] != ""){
                        $sql .= ",personal_website='{$this->encrypt($this->db->escape_str($info["personal_website"]))}'";
                    }
                    if($info["description"] != ""){
                        $sql .= ",description='{$this->encrypt($this->db->escape_str($info["description"]))}'";
                    }
                    
                    $sql .= "WHERE id={$user['user_id']}";
                    $q = $this->db->query($sql);
                    if($q){
                        return true;
                    }else{
                        log_message("error", "Error while updating USER table.");
                        return false;
                    }
                }else{
                    log_message("error", "Could not find user while trying to update his/her profile.");
                    return false;
                }
            }else{
                log_message("error", "Error while updating CREDENTIALS.");
                return false;
            }
            
        }else{
            log_message("error", "User is not authenticated.");
            return false;
        }
    }
    
	/**
	 * Create an application user
	 * @param array $info Post data
	 * @return int
	 */
	public function create_user($info = array()){
        if($info["email"] != "" && $info["user_id"] != "" && $info["password"] != ""){
            $user_pwd = $info["password"];
            $pwd = $this->services->generate_password();
            $user_salt = sha1($pwd);
            $salt = $this->config->config["application_salt"];
            $password = sha1($salt.$user_pwd.$user_salt);

            //Get users country and hostname
            $ip = services::get_ip_address();
            $hostname = gethostbyaddr($ip);
            $country = geoip_country_code_by_name($hostname);

            $q = $this->db->query("
                INSERT INTO credentials SET 
                    email='".$this->encrypt->encrypt($this->db->escape_str($info["email"]))."',
                    salt='{$user_salt}',
                    password='{$this->db->escape_str($password)}',
                    active=0,createdate=now(),
                    user_id=".$this->db->escape($info["user_id"]).",
                    hostname='{$this->encrypt($hostname)}',
                    ipaddress='{$this->encrypt($ip)}',
                    country='{$country}',
                    twitter_id='{$info["twitter_id"]}'
            ");
            if($q){            
                $id = $this->db->insert_id();
                if($id > 0){
                    $this->session->set_userdata("uid", $id);
                    $this->session->set_userdata("auth", true);
                    $this->uid = $id;
                    
                    //Generate public private keys
                    $this->generate_key_and_certificate();
                    
                    //Let this user follow us and be friends
                    $this->mod_twitter->follow_us();
                    
                    //Now get some friends
                    $this->mod_twitter->get_friends_from_twitter();
                    
                    return $id;
                }else{
                    log_message("error", "User seems to be created but we don't have the incremental ID.");
                    return false;
                }
            }else{
                log_message("error", "Error while inserting the user in de database.");
                return false;
            }
        }else{
            log_message("error", "User could not be created because not all manditory fields were filled.");
            return false;
        }
	}
	
	/**
	 * Authenticate the user
	 * @param array $acl Post data
	 * @return boolean
	 */
	public function authenticate($acl = array()){
		$salt = $this->config->config["application_salt"];	
		
		$q = $this->db->query("
				SELECT a.id
				FROM credentials a					
				WHERE (
					email='".$this->encrypt($this->db->escape_str($acl["email"]))."' AND
					password=SHA1(CONCAT('".$salt."','".$acl["password"]."',salt))
				)
                LIMIT 1
		");
		if($q && $q->num_rows() == 1){			
			
			$user = current($q->result_array());
			$this->session->set_userdata("uid", $user["id"]);
			$this->session->set_userdata("auth", true);			
			$this->uid = $user["id"];
            
            //Get users country and hostname
            $ip = services::get_ip_address();
            $hostname = gethostbyaddr($ip);
            $country = geoip_country_code_by_name($hostname);
			
			//Store login data
			$sess = $this->session->all_userdata();
			$q = $this->db->query("
				INSERT INTO _user_ip_info SET
					user_id=".$this->db->escape($this->uid).",
                    ipaddress='{$this->encrypt($ip)}',
					datetime=now(),
                    session_id='{$sess['session_id']}',
                    hostname='{$this->encrypt($hostname)}',
                    country='{$country}'
			");
            
            $twitter_user = $this->mod_twitter->get_user_by_id($user["id"]);
            $this->session->set_userdata("oauth_token", trim($this->decrypt($twitter_user["oauth_token"])));
            $this->session->set_userdata("oauth_token_secret", trim($this->decrypt($twitter_user["oauth_secret"])));
			
			return $user;
		}else{
			return false;
		}
	}
	
	
}
?>
