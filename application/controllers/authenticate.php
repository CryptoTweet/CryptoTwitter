<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Description of authenticate
 *
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright (c) 2013, Victor Angelier
 * @link http://www.victorangelier.com Personal Website
 * @link http://www.twitter.com/digital_human
 */
class authenticate extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function index(){
        if($this->input->post()){
            if($this->form_validation->run('authentication') !== FALSE){
                
                    $this->load->model("mod_user");
                    $res = $this->mod_user->authenticate($this->input->post());

                    if(isset($res["id"]) && $res["id"] > 0 && $this->session->userdata("oauth_token") && $this->session->userdata("oauth_token_secret")){
                        $this->load->file(APPPATH."libraries/twitteroauth/twitteroauth.php");

                        // Include class file & create object passing request token/secret also
                        $oauth = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $this->session->userdata("oauth_token"), $this->session->userdata("oauth_token_secret"));
                        $credentials = $oauth->get("account/verify_credentials");

                        //Check is we have an error
                        if(!is_null($credentials->errors) && is_object($credentials->errors) || is_array($credentials->errors)){                        
                            $this->session->set_flashdata("message", "Could not login to your Twitter account. Are you sure you registered?");
                            redirect(site_url()."account/register");
                        }else{
                            if(($redirect_url = $this->session->userdata("redirect_url")) !== ""){
                                redirect($redirect_url);
                            }else{
                                redirect(site_url());
                            }
                        }

                    }else{
                       $this->session->sess_destroy();
                       $this->session->set_flashdata("message", "Authentication failed."); 
                       redirect(site_url());
                    }
            
            }else{
                $this->load->view('frontpage');
            }
        }else{
            redirect(site_url());
        }
    }
}
?>
