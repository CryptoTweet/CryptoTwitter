<?php
/**
 * Description of CI_Form_validation
 *
 * @author Victor Angelier
 * @copyright (c) 2012, Wielink Websolutions BV te Nunspeet
 * @package BCI - Beheer Collectieve Inkomensverzekeringen
 * @subpackage Application source
 */

/**
 * Views/Controllers/Models/Service
 */
class MY_Form_validation extends CI_Form_validation
{
	/*
	 * Form validation extention of rules
	 */
	public function __construct($rules = array()) {
		parent::__construct($rules);
	}
	
    /**
     * Custom defined Twitter username
     * @param type $str
     * @return type
     */
    public function twitter_user_name($str){
        return ( ! preg_match("/^\p{L}[[a-zA-Z\p{L}.\s]+$/u", $str)) ? FALSE : TRUE;
    }
     /**
	 * Alpha Unicode
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function alpha_unicode($str)
	{
		return ( ! preg_match("/^\p{L}[[a-zA-Z\p{L}.\s]+$/u", $str)) ? FALSE : TRUE;
	}
    
     /**
	 * Alpha
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function alpha($str)
	{
		return ( ! preg_match("/^([a-z\s])+$/i", $str)) ? FALSE : TRUE;
	}
    
	/**
	 * Alpha-numeric with underscores and dashes
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function alpha_dash($str)
	{
		return ( ! preg_match("/^([-a-z0-9_-\s'])+$/i", $str)) ? FALSE : TRUE;
	}
	
	/**
	 * Alpha-numeric
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function alpha_numeric($str)
	{
		return ( ! preg_match("/^([a-z0-9\s])+$/i", $str)) ? FALSE : TRUE;
	}
}
?>
