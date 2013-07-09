<?php
/**
 * Description of default
 *
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright (c) 2013, Victor Angelier
 * @link http://www.victorangelier.com Personal Website
 * @link http://www.twitter.com/digital_human
 */
$this->load->view("default/header");
?>
<div class="container-fluid" id="main">    
    {yield}
</div>
<?php
$this->load->view("default/footer");
?>