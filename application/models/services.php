<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Description of services
 *
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright (c) 2013, Victor Angelier
 * @link http://www.victorangelier.com Personal Website
 * @link http://www.twitter.com/digital_human
 */
class services extends MY_Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    /**
	 * Prints a nice debug message
	 * @param mixed $data Data, what can be anything which u want to output.
	 */
	public static function debug($data){
		if($_SERVER["REMOTE_ADDR"] == "youripsucker"){
			echo "<div class=\"container mt30px\"><pre class=\"mt30px\">".print_r($data, true)."</pre></div>\r\n";
		}
	}
    
    /**
	 * Generate a unique password
	 * @return type 
	 */
	public function generate_password(){
		//Generate array met characters
		$a = array_merge(range("a","z"),range(1,10),range("A","Z"));
		shuffle($a);
		$num = rand(1, 10);
		$chars = array_slice($a, $num, 10);
		shuffle($chars);
		return implode('', $chars);
	}
    
    /**
	 * Get user IP Address
	 * @return ip
	 */
	public static function get_ip_address() {
        if (isset($_SERVER)) {
          if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
          } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
          } else {
            $ip = $_SERVER['REMOTE_ADDR'];
          }
        } else {
          if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
          } elseif (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
          } else {
            $ip = getenv('REMOTE_ADDR');
          }
        }

        return $ip;
    }
}
?>
