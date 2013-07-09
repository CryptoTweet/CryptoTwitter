<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends MY_Controller
{
    
    public function __construct() {
        parent::__construct();
    }
    
	public function index(){
        if(substr(uri_string(),0,1) == "$"){
                $user = $this->mod_user->get_user_by_hash(sha1($this->encrypt(substr(uri_string(),1))));                
                if($user != false){
                    $data = array(
                        "user"  =>  $user,
                        "tweets"    =>  $this->mod_twitter->get_send_tweets($user["user_id"]),
                        "authorized_count"  =>  $this->mod_twitter->get_authorized_count($user["user_id"]),
                        "friends_count"     =>  $this->mod_twitter->get_friends_count($user["user_id"]),
                        "etweet_count"      =>  $this->mod_twitter->get_etweet_count($user["user_id"])
                    );                    
                    $this->title = "Public profile of {$user["name"]} - Encrypted Twitter";
                    $this->load->view("users/public_profile", $data);
                }else{
                    $this->load->view("frontpage");
                }
        }else{
            if(intval($this->session->userdata("uid")) > 0 && $this->session->userdata("auth") == true){
                if($this->session->userdata("oauth_token") != "" && $this->session->userdata("oauth_token_secret") != ""){
                    $data = array(
                        "authorized_count"  =>  $this->mod_twitter->get_authorized_count(),
                        "friends_count"     =>  $this->mod_twitter->get_friends_count(),
                        "etweet_count"      =>  $this->mod_twitter->get_etweet_count()
                    );
                    $this->load->view('frontpage', $data);
                }else{
                    $this->load->view("frontpage");
                }
            }else{
                $this->load->view('frontpage');
            }
        }
    
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */