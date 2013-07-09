<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Description of users
 *
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright (c) 2013, Victor Angelier
 * @link http://www.victorangelier.com Personal Website
 * @link http://www.twitter.com/digital_human
 */
class users extends MY_Controller {
    
    var $uid = 0;
    
    public function __construct() {
        parent::__construct();
        
        //Controleer of de user is ingelogd of niet
		if(intval($this->session->userdata("uid")) > 0 && $this->session->userdata("auth") == true){	
            $this->uid = $this->session->userdata("uid");
		}else{
            $this->session->sess_destroy();
            redirect(site_url());
		}        
    }
    
    public function index(){
        redirect(site_url()."users/dashboard");
    }
    
    /**
     * Update userprofile
     */
    private function update_profile(){
        if($this->input->post()){
            
            if($this->input->post("password") == ""){
                if($this->form_validation->run("update_profile") !== false){
                    $this->mod_user->update_profile($this->input->post());
                    redirect(site_url()."users/dashboard/#profile");
                }
            }else{
                $this->form_validation->set_rules("name", "name", "required|alpha_dash|alpha_numeric|xss_clean");
                $this->form_validation->set_rules("location", "location", "required|alpha|xss_clean");
                $this->form_validation->set_rules("email", "email", "required|valid_email|xss_clean");                
                $this->form_validation->set_rules("password", "password", "required|alpha_dash|alpha_numeric|xss_clean");
                $this->form_validation->set_rules("password_repeat", "password_repeat", "required|matches[password]|xss_clean");
                if($this->form_validation->run() !== false){
                    $this->mod_user->update_profile($this->input->post());
                    redirect(site_url()."users/dashboard/#profile");
                }
            }
        }        
    }
    
    public function dashboard(){         
        if($this->input->post() && $this->input->post("section") != ""){
            switch($this->input->post("section")){
                case "profile":
                    $this->update_profile();
                    break;
                case "add_friend":
                    $this->form_validation->set_rules("screenname", "screenname", "required|alpha_dash|xss_clean");
                    if($this->form_validation->run() !== false){
                        $res = $this->mod_twitter->add_twitter_friend($this->input->post("screenname"));
                        if($res == -1){
                            $this->session->set_flashdata("message", "The requested user is already in your list of friends.");
                            redirect(site_url()."users/dashboard/#friends");
                        }elseif($res == true){
                            $this->session->set_flashdata("message", "The requested user has been added to your list of friends.");
                            redirect(site_url()."users/dashboard/#friends");
                        }else{
                            $this->session->set_flashdata("message", "The requested could not be found or an error has occured.");
                            redirect(site_url()."users/dashboard/#friends");
                        }
                    }
                    break;
            }
        }
                
        $data = array(
            "userinfo"  =>  $this->mod_user->get_user(),
            "tweets"    =>  $this->mod_twitter->get_user_tweets(),
            "friends"   =>  $this->mod_twitter->get_friends(),
            "followers" =>  $this->mod_twitter->get_followers(),
            "requests"  =>  $this->mod_twitter->get_pending_requests()
        );        
        
        if(!is_array($data["followers"])){
            $data["followers"] = array();
        }
        if(!is_array($data["friends"])){
            $data["friends"] = array();
        }
        $data["count"]  = (count($data["friends"])+count($data["followers"]));
        
        if($this->session->userdata("hash") != "" && $this->session->userdata("verifier") != ""){
            $data["message"] = $this->mod_twitter->get_tweet_by_hash_and_verifier($this->session->userdata("hash"),$this->session->userdata("verifier"));
            $this->session->unset_userdata("hash");
            $this->session->unset_userdata("verifier");
        }
        $this->load->view("users/dashboard", $data);
    }
    
}
?>
