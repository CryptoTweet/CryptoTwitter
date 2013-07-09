<?php if(intval($this->session->userdata("uid")) > 0 && $this->session->userdata("auth") == true): ?>
    <?php $this->load->view("twitter"); ?>
<?php else: ?>   
    <?php $this->load->view("landingpage"); ?>
<?php endif; ?>
