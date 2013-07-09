<?php
/**
 * Description of mod_mail
 *
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright (c) 2013, Victor Angelier
 * @link http://www.victorangelier.com Personal Website
 * @link http://www.twitter.com/digital_human
 */
class mod_mail extends MY_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function send_mail($data = array()){
        $this->load->library('email');
        
        $config = array();
        $config['charset'] = 'utf-8';
        $config['wordwrap'] = TRUE;
        $config['mailtype'] = "html";
        $config['useragent'] = "CryptoTwitter";
        $this->email->initialize($config);
        
        $this->email->to('info@cryptotweet.com', 'Encrypted Twitter');
        $this->email->from($data['email'], $data['name']);
        if(isset($data["bcc"]) && $data["bcc"] != ""){
            $this->email->bcc($data["bcc"]);
        }
        $this->email->subject($data['subject']); 
        $this->email->message($data["message"]); 
        $this->email->set_alt_message(strip_tags(html_entity_decode($data["message"])));        
        return $this->email->send();
    }
    
    /**
     * Send emails by using templates
     * 
     * @param string $template  Name of the template to use
     * @param type $data    array('email','subject','name',[attachment]) and variables used in the template itself.
     * @return boolean
     */
    public function mail_template($template = "", $data = array()){
        $this->load->library('email');
        
        $config = array();
        $config['charset'] = 'utf-8';
        $config['wordwrap'] = TRUE;
        $config['mailtype'] = "html";
        $config['useragent'] = "CryptoTwitter";
        $this->email->initialize($config);

        $message = call_user_func(array($this, "data_{$template}"), $data);
        $this->email->from('noreply@cryptotweet.com', 'Encrypted Twitter');
        $this->email->to($data['email'], $data['name']);
        if(isset($data["bcc"]) && $data["bcc"] != ""){
            $this->email->bcc($data["bcc"]);
        }
        $this->email->subject($data['subject']); 
        $this->email->message($message); 
        $this->email->set_alt_message(strip_tags(html_entity_decode($message)));
        if(isset($data['attachment']) && $data['attachment'] != ""){
            $this->email->attach($data['attachment']);
        }
        return $this->email->send();
    }
    
    public function data_email_verification($data = array()){         
        $data = $this->load->view("templates/en_email_verification", $data, true);
        return $data;
    }
    
    public function data_mail_authorization_request($data = array()){         
        $data = $this->load->view("templates/en_mail_authorization_request", $data, true);
        return $data;
    }
    
}
?>
