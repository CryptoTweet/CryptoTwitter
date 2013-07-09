<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Description of about
 *
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright (c) 2013, Victor Angelier
 * @link http://www.victorangelier.com Personal Website
 * @link http://www.twitter.com/digital_human
 */
class about extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function index(){
        redirect(site_url()."about/whatis_cryptotwitter");
    }
    
    public function what_is_cryptotwitter(){
        $this->load->view("whatis");
    }
    
    public function how_does_cryptotwitter_work(){
        $this->load->view("howdoesitwork");
    }
}
?>
