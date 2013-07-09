<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Description of MY_Controller
 *
 * @author Victor Angelier
 * @copyright (c) 2012, Wielink Websolutions BV te Nunspeet
 * @package BCI - Beheer Collectieve Inkomensverzekeringen
 * @subpackage Application source
 */

/**
 * Views/Controllers/Models/Service
 */
class MY_Controller extends CI_Controller
{
    var $layout = "default";
	var $consumer_key = "";
    var $consumer_secret = "";
    var $title = "Welcome to encrypted Twitter";
    var $user = null;
    
	public function __construct() {
		parent::__construct();		
		
		setlocale(LC_ALL, 'nld-NLD.UTF-8@euro','nld-NLD');
		setlocale(LC_ALL, 'nl_NL.UTF-8@euro', 'nl_NL');
        $this->form_validation->set_error_delimiters('<span class="validation_error">', '</span>');
        $this->consumer_key = $this->config->config["consumer_key"];
        $this->consumer_secret = $this->config->config["consumer_secret"];
        if(intval($this->session->userdata("uid")) > 0 && $this->session->userdata("auth") == true){
            $this->user = $this->mod_user->get_user();
            $this->title .= "&nbsp;$".$this->user["username"];
        }
	}
 	
	public function pdf($html = ""){		
		$this->load->library("libpdf");
		$this->libpdf->toPDF($html);
	}
    
    /**
	 * Implement encrypt function to all models
	 * @param string $data Data to encrypt
	 * @return string Base64 encoded and encrypted string
	 */
	public function encrypt($data = ""){
		return $this->encrypt->encrypt($data);
	}
	
	/**
	 * Implement decrypt function to all models
	 * @param string $data Base64 encoded data to decrypt
	 * @return string Decrypted plain-text string
	 */
	public function decrypt($data = ""){		
		$res = ($this->encrypt->decrypt($data) == FALSE ? "" : $this->encrypt->decrypt($data));
		return ($res == "" ? $data : $res);
	}
}

?>
