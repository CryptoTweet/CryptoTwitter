<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Description of logoff
 *
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright (c) 2013, Victor Angelier
 * @link http://www.victorangelier.com Personal Website
 * @link http://www.twitter.com/digital_human
 */
class logoff extends MY_Controller {
    
    public function __construct() {
        parent::__construct();
    }
    
    public function index(){
        $this->session->sess_destroy();
        redirect(site_url());
    }
}
?>
