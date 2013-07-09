<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class account extends MY_Controller
{    

    public function __construct() {
        parent::__construct();
    }
    
    public function index(){   
        $this->register();
    }
    
    /**
     * Verify the users e-mail address
     * 
     * @param string $hash Hashed info about the users account
     */
    public function verify_email($hash = ""){
        if($hash != "" && strlen($hash) == 40){
            if($this->mod_user->verify_email($hash) == true){
                $this->session->set_flashdata("message", "Congratulations! You are now verified and your account is activated!");
            }else{
                $this->session->set_flashdata("message", "Your account could not be activated. Please try again or contact support@cryptotweet.com.");
            }
        }
        redirect(site_url());
    }
    
    /**
     * Register with Twitter account
     */
    public function register(){
        $this->load->file(APPPATH."libraries/twitteroauth/twitteroauth.php");
        $twitteroauth = new TwitterOAuth($this->consumer_key, $this->consumer_secret);  
        
        $url = "";
        
        // If everything goes well..          
        if($twitteroauth->http_code == ""){
            
            $request_token = $twitteroauth->getRequestToken('oob');
            
            if(is_array($request_token) && $request_token['oauth_token'] != "" && $request_token['oauth_token_secret'] != "" && $request_token["oauth_callback_confirmed"] == true){
            }else{
                //Seems we have a problem
                log_message("error", "Error while getting request tokens from Twitter. Account controller.");
                $this->session->set_flashdata("message", "Error while communicating with Twitter. Please try again in 5 minutes or contact support@cryptotweet.com.");
                redirect(site_url());
            }
            
            //Set session info and request an URL
            $this->session->set_userdata("oauth_token", $request_token['oauth_token']);
            $this->session->set_userdata("oauth_token_secret", $request_token['oauth_token_secret']);

            // Let's generate the URL and redirect  
            $url = $twitteroauth->getAuthorizeURL($request_token['oauth_token']); 
            
        }else{
            //We have an invalid resonse from twitter.
            log_message("error", "We have an invalid response from Twitter while loading Register page.");
            $this->session->set_flashdata("message", "Error while communicating with Twitter. Please try again in 5 minutes or contact support@cryptotweet.com.");
            redirect(site_url());
        }
        if($url == ""){
            log_message("error", "We have an invalid response from Twitter while loading Register page. We did not get a valid URL");
            $this->session->set_flashdata("message", "Error while communicating with Twitter. Please try again in 5 minutes or contact support@cryptotweet.com.");
            redirect(site_url());
        }else{
            $data = array(
                "url"   =>  $url
            );
            $this->load->view("apis/twitter_auth", $data);
        }
    }
    
    /**
     * Verify Twitter account with PIN
     */
    public function verify(){  
        //Check if we have had contact with Twitter
        if(!$this->session->userdata("oauth_token") || !$this->session->userdata("oauth_token_secret")){
            redirect(site_url());
        }
        
        if($this->input->post()){
            
            if($this->form_validation->run('twitter_pin_verification') !== FALSE){
                            
                $this->load->file(APPPATH."libraries/twitteroauth/twitteroauth.php");

                // Retrieve our previously generated request token & secret
                $requestToken = $this->session->userdata("oauth_token");
                $requestTokenSecret = $this->session->userdata("oauth_token_secret");

                // Include class file & create object passing request token/secret also
                $oauth = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $requestToken, $requestTokenSecret);

                // Generate access token by providing PIN for Twitter
                $request = $oauth->getAccessToken(NULL, $this->input->post("pin"));

                $accessToken = $request['oauth_token'];
                $accessTokenSecret = $request['oauth_token_secret'];                

                if($accessToken != "" && $accessTokenSecret != ""){
                    $this->session->set_userdata("oauth_token", $accessToken);
                    $this->session->set_userdata("oauth_token_secret", $accessTokenSecret);
                    
                    $this->load->model("mod_twitter");
                    if($this->mod_twitter->check_user_exists() == false){

                        $request["provider"] = 'Twitter';
                        $request['session_id'] = $this->session->userdata('session_id');

                        if(($this->mod_twitter->store_authorized_user($request) !== false)){                        
                            redirect(site_url()."account/finalize");
                        }
                        
                    }else{
                        if($this->mod_twitter->check_user_exists(true) == true){
                            $this->session->set_flashdata("message", "You are already registered. Did you forgot your username or password? You are now authenticated by Twitter. Please change your password.");
                        }else{
                            $this->session->set_flashdata("message", "You are already registered. Unfortunately we could not authenticate you. Please contact support@cryptotwitter.com");
                        }
                        redirect(site_url());
                    }
                }else{
                    $this->session->set_flashdata("message", "Verification with Twitter failed. Please try again or send an e-mail to support@cryptotweet.com.");
                    redirect(site_url()."account/register");
                }
            }

        }
        $this->load->view("apis/twitter_verify");                
    }
    
    /**
     * Finalize the verification process and update profile information
     */
    public function finalize(){
        if(!$this->session->userdata("oauth_token") || !$this->session->userdata("oauth_token_secret")){
            redirect(site_url());
        }
        
        $this->load->file(APPPATH."libraries/twitteroauth/twitteroauth.php");

        // Retrieve our previously generated request token & secret
        $requestToken = $this->session->userdata("oauth_token");
        $requestTokenSecret = $this->session->userdata("oauth_token_secret");

        // Include class file & create object passing request token/secret also
        $oauth = new TwitterOAuth($this->consumer_key, $this->consumer_secret, $requestToken, $requestTokenSecret);
        $credentials = $oauth->get("account/verify_credentials");
        
        if($this->input->post() && $this->input->post("twitter_id") != $credentials->id){
            $_POST["twitter_id"] = $credentials->id;
            $_POST["profile_image"]= $credentials->profile_image_url;
            echo '<script>alert("Did you really think that would work?!");</script>';
        }
        
        //Check is we have an error
        if(!is_null($credentials->errors) && is_object($credentials->errors) || is_array($credentials->errors)){
            redirect(site_url()."account/register");
        }
        
        //If post update profile
        if($this->input->post()){
            if($this->form_validation->run("twitter_profile_confirmation") !== FALSE){
                if($this->input->post("location") != "" && $this->input->post("name") != "" && $credentials->id > 0){
                    
                    $this->load->model("mod_twitter");
                    $res = $this->mod_twitter->update_profile($this->input->post());
                    if($res == 1){

                        $user = $this->mod_user->get_user();
                        if($user != false){
                            //Ok now its time to send the user a e-mail verification
                            $this->load->model("mod_mail");   

                            $hash = sha1($user["user_id"].$this->encrypt($user["email"]).$user["twitter_id"]);
                            $data = array(
                                "link"      =>  site_url()."account/verify_email/{$hash}",
                                "name"      =>  $user["name"],
                                "email"     =>  $user["email"],
                                "subject"   =>  "Account verification"
                            );

                            $this->mod_mail->mail_template('email_verification', $data);
                        }
                        if(intval($this->session->userdata("uid")) > 0 && $this->session->userdata("auth") == true &&
                                $this->session->userdata("oauth_token") != "" && $this->session->userdata("oauth_token_secret") != ""){
                            $this->session->set_flashdata("message", "Congratulations!! Your account has been created. An e-mail has been send to verify it's you and to activate your account.");
                        }else{
                            $this->session->sess_destroy();
                        }                        
                        redirect(site_url());
                    }else{
                        log_message("error", "Error while updating profile in Account controller, method finalize.");
                        $this->session->set_flashdata("message", "Something is wrong. Please try again or contact support@cryptotweet.com");
                        redirect(site_url()."account/finalize");
                    }
                    
                }else{
                    error_log("error", "Form validation was TRUE but some fields where blank.");
                    $this->session->set_flashdata("message", "Something is wrong. Please try again or contact support@cryptotweet.com");
                    redirect(site_url());
                }
            }
        }
        
        $data = array(
            "userinfo"  => $credentials
        );
        $this->load->view("apis/twitter_finalize", $data);
    }
}
?>
