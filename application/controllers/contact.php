<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Description of contact
 *
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright (c) 2013, Victor Angelier
 * @link http://www.victorangelier.com Personal Website
 * @link http://www.twitter.com/digital_human
 */
class contact extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function index(){
        if($this->input->post()){
            if($this->form_validation->run("contact_form") !== false){
                
                $this->load->model("mod_mail");
                $data = array(
                    "email"   =>  $this->input->post("email"),
                    "name"    =>  $this->input->post("name"),
                    "subject" =>  $this->input->post("type"),
                    "message" =>  $this->input->post("question"),
                    "bcc"     =>  $this->input->post("email") 
                );
                $res = $this->mod_mail->send_mail($data);
                if($res == true){
                    $this->session->set_flashdata("message", "Your message has been send. We will contact your shortly.");
                }else{
                    $this->session->set_flashdata("message", "Your message could not be send. Please contact support@cryptotweet.com");
                }
                redirect(site_url());
            }
        }
        $this->load->view("contact");
    }
}
?>
