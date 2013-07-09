<?php
/**
 * Description of requests
 *
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright (c) 2013, Victor Angelier
 * @link http://www.victorangelier.com Personal Website
 * @link http://www.twitter.com/digital_human
 */
class requests extends MY_Controller {
    
    var $layout = "default";
    
    public function __construct() {
        parent::__construct();
        if(intval($this->session->userdata("uid")) > 0 && $this->session->userdata("auth") == true){
            if(!$this->session->userdata("oauth_token") || !$this->session->userdata("oauth_token_secret")){
                $this->session->set_flashdata("message", "You need to login first!");
                $this->session->set_userdata("redirect_url", "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]);
                $this->session->sess_destroy();
                redirect(site_url());
            }
        }else{
            $this->session->set_flashdata("message", "You need to login first!");
            $this->session->set_userdata("redirect_url", "http://".$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"]);
            $this->session->sess_destroy();
            redirect(site_url());
        }
    }
    
    public function index(){
        redirect(site_url());
    }
    
    /**
     * Remove users authorization
     * 
     * @param string $id Hash authorization ID + receiver
     */
    public function revoke_autorization($hashed_id = 0){
        $this->layout = "empty";
        if($hashed_id != "" && strlen($hashed_id) == 40){
            echo $this->mod_twitter->revoke_autorization($hashed_id);
        }else{
            log_message("error", "Not a valid hash {$hashed_id} Request Controller");
            echo 0;
        }
    }
    
    /**
     * Read the authorization request message
     * 
     * @param type $hash
     */
    public function read_authorize_request($hash = ""){        
        if($hash != ""){            
            if(($secret = $this->mod_twitter->get_authorize_request_by_hash($hash)) !== false){

                //Check post and validate
                if($this->input->post()){
                    $this->form_validation->set_rules('answer', 'answer', 'required|min_length[2]|xss_clean');
                    if($this->form_validation->run() !== false){
                        
                        //Calculate how precies the answers are
                        similar_text(strtolower($secret["answer"]), strtolower($this->input->post("answer")), $perc);
                        
                        if($perc >= 95.5){
                            $_POST["similarity"] = $perc;
                            $_POST["id"] = $secret["id"];
                            if(($this->mod_twitter->store_authentication_answer($this->input->post())) !== false){
                                $this->session->set_flashdata("message", "You are now authorized! Congratulations!");
                                redirect(site_url());
                            }else{
                                log_message("error", "Error while storing the users answer.");
                                $this->session->set_flashdata("message", "Something went wrong, we're sorry. Please try again or contact support@cryptotweet.com");
                                redirect(site_url());
                            }
                        }else{
                            $this->session->set_flashdata("message", "Your answer was not correct. Please try again.");
                            redirect(site_url()."requests/read_authorize_request/{$hash}");
                        }
                        
                    }
                }
                
                $this->load->view("users/read_authorization_request", $secret);
                
            }else{
                $this->session->set_flashdata("message", "Could not find your authorization.");
                redirect(site_url());
            }            
        }else{
            $this->session->set_flashdata("message", "Could not find your authorization.");
            redirect(site_url());
        }
    }
    
    /**
     * Send authorization request message
     * 
     * @param type $recipient
     */
    public function send_authorization_request($recipient = 0){
        $this->layout = "modal";
        $recipient = intval($recipient);
        if($recipient < 1){ 
            log_message("error", "Recipient ID not found. Request controller");
            redirect(site_url());
        }
            
        $recipient = $this->mod_twitter->get_twitter_user_by_id($recipient);
        if($recipient == false){ 
            log_message("error", "Recipient not found in our DB. Request controller");
            redirect(site_url()); 
        }
        
        //Check post and run validation
        if($this->input->post()){
            $this->form_validation->set_rules('secret', 'secret', 'required|min_length[2]|xss_clean');
            if($this->form_validation->run() !== false){
                
                //Store the question and answers
                $_POST["recipient"] = $recipient["twitter_id"];
                $id = $this->mod_twitter->store_authorize_request($this->input->post());
                if(strlen($id) == 40){
                    
                    //Send a Direct Message
                    $_POST["auth_id"] = $id;
                    $result = $this->mod_twitter->send_authorize_request($this->input->post());
                    if($result == false && isset($recipient["email"]) && $recipient["email"] != ""){                        
                        //Send an email as fallback
                        $this->load->model("mod_mail");
                        $user = $this->mod_user->get_user();
                        if($user != false){
                            $data = array(
                                "name"              =>  $recipient["name"],
                                "recipient_name"    =>  $recipient["name"],
                                "screenname"        =>  $user["username"],
                                "sender_name"       =>  $user["name"],
                                "link"              =>  site_url()."requests/read_authorize_request/".sha1($id.$recipient["twitter_id"]),
                                "subject"           =>  "Complete your authorization now",
                                "email"             =>  $this->decrypt($recipient["email"])
                            );
                            $this->mod_mail->mail_template("mail_authorization_request", $data);
                            $this->session->set_flashdata("message", "Congratulations, your authorization has been send!");
                            echo 1;
                            die();
                        }else{
                            echo 0;
                            die();
                        }
                    }else{
                        $this->session->set_flashdata("message", "Congratulations, your authorization has been send!");
                        echo 1;
                        die();
                    }
                }else{
                    log_message("error", "Error while storing authorization. Request controller.");
                    echo 0;
                    die();
                }
            }else{
                echo 0;
                die();
            }
        }
        
        $data = array(
            "recipient" =>  $recipient
        );
        $this->load->view("users/send_authorization_request_modal", $data);
    }
}
?>
