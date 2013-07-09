<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		CodeIgniter
 * @author		ExpressionEngine Dev Team
 * @copyright	Copyright (c) 2008 - 2011, EllisLab, Inc.
 * @license		http://codeigniter.com/user_guide/license.html
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */

//--------------------------------------------------------------------------------

/**
 * Encrypt/decrypt library using SSL in CFB mode with an (IV) initialization vector
 * 
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright (c) 2012, Victor Angelier <vangelier@hotmail.com>
 * @link http://www.twitter.com/digital_human
 */
class MY_Encrypt extends CI_Encrypt
{
	/**
	 * variable $iv holds the IV (initialization vector) we use to encrypt and decrypt data. Dont lose it.
	 * 
	 * @example
	 * $size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CFB);
	 * $iv = mcrypt_create_iv($size, MCRYPT_DEV_RANDOM);
	 * @link http://php.net/manual/en/function.mcrypt-create-iv.php PHP MCrypt help
	 * 
	 * @var string The initialization vector we can use
	 */
	protected $iv = "";
	protected $_mcrypt = null;
	public $CI = null;


	/*
	 * Other variables we use defined here
	 */
	private $_openssl_exists = FALSE;
	private $_encryption_method = "AES-256";
	private $_cipher_mode = "CBC";
	private $_cipher_method = "rijndael-256";

	/**
	 * Encryption and Decryption library. Decrypt and Encrypt data with Ryndael 256
	 * @access public
	 */
	public function __construct() {
		parent::__construct();
		
		$this->CI =& get_instance();
		
		$this->_openssl_exists = ( ! function_exists('openssl_encrypt')) ? FALSE : TRUE;
		if($this->_openssl_exists == FALSE){
			log_message("error", "OpenSSL not installed. Please install openssl and php-openssl before using this method.", true);
		}
		
		//Get the IV from the config if its there.
		foreach($this->CI->config->config as $k => $v){
			if($k == "encryption_iv"){
				$this->iv = $v;
				break;
			}
		}
		
		//Validate the length of the IV and get it from the config files
		if(isset($this->CI->config->config["encryption_iv"]) && $this->CI->config->config["encryption_iv"] != ""){
			$this->iv = $this->CI->config->config["encryption_iv"];			
		}else{
            show_error("Please define a 'encryption_iv' in your config file.");
			log_message("error", "Please define a 'encryption_iv' in your config files.".__FILE__."<br />".__LINE__);
		}
		
		log_message("debug", "My_Encrypt initialized.");
	}
	
	/**
	 * Sets the initialization vector
	 * 
	 * @access public
	 * @param string $iv
	 */
	public function set_iv($iv = ""){
		if($iv != ""){
			if($this->get_iv_size() <= strlen($iv) ){
				$iv = substr($iv, strlen($iv)-$this->get_iv_size());
			}else{
				$iv = str_pad($iv, $this->get_iv_size(),"0", STR_PAD_LEFT);
			}
			$this->iv = $iv;			
		}else{
			log_message("error", "MY_Encrypt, we did not get an IV from u. Please check your code or generate an IV.");
		}
	}
	
	/**
	 * Get the IV stored in the config file
	 * 
	 * @access public
	 * @return string
	 */
	public function get_iv(){
		if($this->CI->config->item("encryption_iv") && $this->CI->config->item("encryption_iv") != ""){			
			return ($this->CI->config->item("encryption_iv") == "" ? "No IV value found." : $this->CI->config->item("encryption_iv"));
		}else{
			log_message("error", "Please define a 'encryption_iv' in your config files.");
			return "No IV found in the config files";
		}
	}
	
	
	/**
	 * Gets the size of the IV used for this encryption method
	 * 
	 * @param string $algorithm
	 * @param string $mode
	 * @return FALSE or IV size
	 */
	public function get_iv_size($algorithm = "", $mode = "cbc"){
		if($this->_mcrypt_exists){

			if($algorithm == "") { $algorithm = $this->_cipher_method; }
			if($mode == ""){ $mode = $this->_cipher_mode; }
			
			if($this->_mcrypt == null){
				$this->_mcrypt = mcrypt_module_open(strtolower($algorithm), '', strtolower($mode), '');
				return mcrypt_enc_get_iv_size($this->_mcrypt);
			}else{
				return mcrypt_enc_get_iv_size($this->_mcrypt);
			}
			
		}else{
			log_message("error", "MCRYPT module not found.");
		}
	}
	
	/**
	 * Create an IV for a specific method and mode
	 * 
	 * @access public
	 * @param type $algorithm
	 * @param type $mode
	 * @return string Base64 encoded IV or FALSE
	 */
	public function create_iv($algorithm = "", $mode = "cbc"){
		if($algorithm == "") { $algorithm = $this->_cipher_method; }
		if($mode == ""){ $mode = $this->_cipher_mode; }
		
		$size = (int) $this->get_iv_size($algorithm, $mode);
		if($size > 0){
			$iv = base64_encode(mcrypt_create_iv($size, MCRYPT_RAND)); //MCRYPT_RAND, Prior to PHP 5.3 this was the only supported method
			if($this->get_iv_size() <= strlen($iv) ){
				$iv = substr($iv, strlen($iv)-$this->get_iv_size());
			}else{
				$iv = str_pad($iv, $this->get_iv_size(),"0", STR_PAD_LEFT);
			}
			return $iv;
		}else{
			log_message("error", "Something went wrong along the way while trying to get the correct IV size.");
		}
	}
	
	/**
	 * List all available algorithms to choose from while creating an IV
	 * 
	 * @access public
	 * @return array
	 */
	public function list_algorithms(){
		return mcrypt_list_algorithms();
	}
	
	/**
	 * Get the current available cipher methods
	 * 
	 * @access public
	 * @return array List of available methods
	 */
	public function get_cipher_methods(){
		return openssl_get_cipher_methods(true);
	}
	
	/**
	 * Encrypt the data
	 * 
	 * @access public
	 * @param string $string Data tobe encrypted
	 * @param string $method The encryption method tobe used	
	 * @param string $mode Cipher mode
	 * @return string Base64 encoded encrypted string or FALSE
	 */
	public function encrypt($string = "", $method = "", $mode = ""){
		if($this->iv != "" && is_string($string)){			
			if($this->_openssl_exists){
				
				if($method == ""){ $method = $this->_encryption_method; }
				if($mode == ""){ $mode = $this->_cipher_mode; }
				
				if(($data = @openssl_encrypt($string, strtoupper($method."-".$mode), $this->iv, true, $this->iv)) !== FALSE){
					return base64_encode($data);
				}else{
					if($this->CI->config->config["log_threshold"] > 1){
						log_message("error", "An error occured while trying to encrypt your string. Please check the PHP error logs.");
					}
					return FALSE;
				}
				
			}else{
				if($this->CI->config->config["log_threshold"] > 1){
					log_message("error", "You don't have openssl installed so its not possible to use this library now.");
				}
			}
		}else{
			if($this->CI->config->config["log_threshold"] > 1){
				log_message("error", "No IV found, please check your config files for an 'encryption_iv'.".__FILE__."\r\n".__LINE__);
			}
		}
	}
	
	/**
	 * Decrypt the data
	 * 
	 * @access public 
	 * @param string $string Base64 encoded encypted string
	 * @param string $method
	 * @param string $mode
	 * @return string Decrypted plain text value
	 */
	public function decrypt($string = "", $method = "", $mode = ""){		
		if($this->iv != "" && is_string($string)){
			if($this->_openssl_exists){
				
				if($method == ""){ $method = $this->_encryption_method; }
				if($mode == ""){ $mode = $this->_cipher_mode; }
				
				if(($data = @openssl_decrypt(base64_decode($string), strtoupper($method."-".$mode), $this->iv, true, $this->iv)) !== FALSE){					
					return str_replace(array("'","\""),"", $data);
				}else{					
					if($this->CI->config->config["log_threshold"] > 1){
						log_message("error", "An error occured while trying to decrypt your string. Please check the PHP error logs.");
					}
					return FALSE;
				}				
			}else{
				if($this->CI->config->config["log_threshold"] > 1){
					log_message("error", "You don't have openssl installed so its not possible to use this library now.");
				}
				return FALSE;
			}
		}else{
			if($this->CI->config->config["log_threshold"] > 1){
				log_message("error", "No IV found, please check your config files for an 'encryption_iv'.".__FILE__."\r\n".__LINE__);
			}
			return FALSE;
		}
	}
	
	/**
	 * Encrypt a file using the defined encryption method
	 * 
	 * @access public
	 * @param array $data array("filename" => "", "filepath" => "")
	 * @return boolean
	 */
	public function encrypt_file($data = array()){
		if(is_array($data) && count($data) == 2){
			
			//Check if path has trailing / else fix it
			if(substr($data["filepath"], strlen($data["filepath"])-1) != "/"){
				$data["filepath"]  .= "/";
			}
			//Check if the file exists
			if(file_exists($data["filepath"].$data["filename"])){
				
				$method = ""; $mode = "";
				
				if($this->iv != "" && $this->_openssl_exists){
				
					//Set the method and cipher to use with encryption
					if($method == ""){ $method = $this->_encryption_method; }
					if($mode == ""){ $mode = $this->_cipher_mode; }

					//Get data as base64, encrypt it, and write it to a new file
					$raw = base64_encode(file_get_contents($data["filepath"].$data["filename"]));
					$encrypted_data = $this->encrypt($raw);
					if(file_put_contents($data["filepath"]."enc_".$data["filename"], $encrypted_data)){
						if(rename($data["filepath"]."enc_".$data["filename"], $data["filepath"].$data["filename"])){
							//Ok all good
						}
						return $data["filepath"]."enc_".$data["filename"];
					}else{
						log_message("error", "Cant write data to file");
						return false;
					}
					
				}else{
					log_message("error", "IV not found or Openssl not installed");
					return false;
				}				
			}else{
				log_message("error", "File does not exists");
				return false;
			}
		}else{
			log_message("error", "Invalid parameter");
			return false;
		}
	}
	
	/**
	 * Decrypt an encrypted file using the defined encryption method
	 * 
	 * @access public
	 * @param array $data array("filename" => "", "filepath" => "")
	 * @return boolean
	 */
	public function decrypt_file($data = array()){
		if(is_array($data) && count($data) == 2){
			
			//Check if path has trailing / else fix it
			if(substr($data["filepath"], strlen($data["filepath"])-1) != "/"){
				$data["filepath"]  .= "/";
			}
			//Check if the file exists
			if(file_exists($data["filepath"].$data["filename"])){
				
				$method = ""; $mode = "";
				
				if($this->iv != "" && $this->_openssl_exists){
				
					//Set the method and cipher to use with encryption
					if($method == ""){ $method = $this->_encryption_method; }
					if($mode == ""){ $mode = $this->_cipher_mode; }

					//Get data which is alread base64, decrypt it, and write it to a new file
					$raw = file_get_contents($data["filepath"].$data["filename"]);
					$decrypted_data = $this->decrypt($raw);
					if(file_put_contents($data["filepath"]."dec_".$data["filename"], base64_decode($decrypted_data))){
						if(rename($data["filepath"]."dec_".$data["filename"], $data["filepath"].$data["filename"])){
							//Ok all good
						}
						return $data["filepath"]."dec_".$data["filename"];
					}else{
						log_message("error", "Cant write data to file");
						return false;
					}
					
				}else{
					log_message("error", "IV not found or Openssl not installed");
				}				
			}else{
				log_message("error", "File does not exists");
				return false;
			}
		}else{
			log_message("error", "Invalid parameter");
			return false;
		}
	}
}
// END My Encrypt Class

/* End of file MY_Encrypt.php */
/* Location: ./libraries/MY_Encrypt.php */