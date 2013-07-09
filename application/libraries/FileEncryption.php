<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FileEncryption
 *
 * @author Linus
 */
class FileEncryption {
	
	protected $iv = "";
	protected $_mcrypt = null;
	
	private $_openssl_exists = TRUE;
	private $_encryption_method = "AES-256";
	private $_cipher_mode = "CBC";
	
	public function set_iv($iv = ""){
		$this->iv = $iv; //encryption_iv
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
					return FALSE;
				}
				
			}else{
				//
			}
		}else{
			//
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
					return FALSE;
				}				
			}else{
				//
			}
		}else{
			//
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
						echo "error", "Cant write data to file";
						return false;
					}
					
				}else{
					echo $this->iv;
					echo "error", "IV not found or Openssl not installed";
					return false;
				}				
			}else{
				echo "error", "File does not exists";
				return false;
			}
		}else{
			echo "error", "Invalid parameter";
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
						echo "error", "Cant write data to file";
						return false;
					}
					
				}else{
					echo "error", "IV not found or Openssl not installed";
				}				
			}else{
				echo "error", "File does not exists";
				return false;
			}
		}else{
			echo "error", "Invalid parameter";
			return false;
		}
	}
}
?>
