<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Description of messages
 *
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright (c) 2013, Victor Angelier
 * @link http://www.victorangelier.com Personal Website
 * @link http://www.twitter.com/digital_human
 */
class messages extends MY_Controller {
    
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
    
    public function index($hash = "", $verifier = ""){
        if($hash != "" && $verifier != ""){
            $this->session->set_userdata("hash", $hash);
            $this->session->set_userdata("verifier", $verifier);
            redirect(site_url()."users/dashboard");
        }else{
            redirect(site_url());
        }
    }
}
?>
