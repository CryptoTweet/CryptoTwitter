<?php
/**
 * Description of MY_Model
 *
 * @author Victor Angelier
 * @copyright (c) 2012, Wielink Websolutions BV te Nunspeet
 * @package BCI - Beheer Collectieve Inkomensverzekeringen
 * @subpackage Application source
 */

/**
 * Model
 */
class MY_Model extends CI_Model
{
    var $layout = "default";
    var $consumer_key = "";
    var $consumer_secret = "";
    
	public function __construct() {
		parent::__construct();
        
        $this->consumer_key = $this->config->config["consumer_key"];
        $this->consumer_secret = $this->config->config["consumer_secret"];
	}
	
	/**
	 * Initiate Excel library
	 */
	public function load_excel(){
		if(file_exists(APPPATH . "/libraries/phpexcel/PHPExcel.php")){
			$this->load->file(APPPATH . "/libraries/phpexcel/PHPExcel.php");
		}else{
			echo "Excel library not found.";
		}
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
