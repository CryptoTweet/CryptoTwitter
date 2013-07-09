<?php
/**
 * Description of dashboard
 *
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright (c) 2013, Victor Angelier
 * @link http://www.victorangelier.com Personal Website
 * @link http://www.twitter.com/digital_human
 */
?>
<section id="body">
    <div class="container-fluid">
        <div class="row-fluid">
            <ul class="nav nav-tabs" id="dashboard">
                <li class="active"><a href="#messages" data-toggle="tab">Messages</a></li>
                <li><a href="#friends" data-toggle="tab">Friends (<?php echo $count; ?>)</a></li>
                <li><a href="#profile" data-toggle="tab">Profile</a></li>
                <li><a href="#requests" data-toggle="tab">Requests</a></li>
                <li><a href="#settings" data-toggle="tab">Settings</a></li>
            </ul>
        </div>
    </div>
    <div class="tab-content">
        <div class="tab-pane active" id="messages">
            <?php $this->load->view("users/message"); ?>
        </div>
        <div class="tab-pane" id="friends">
            <?php $this->load->view("users/friends"); ?>
        </div>
        <div class="tab-pane" id="profile">
            <?php $this->load->view("users/profile"); ?>
        </div>
        <div class="tab-pane" id="requests">
            <?php $this->load->view("users/requests"); ?>
        </div>
        <div class="tab-pane" id="settings">
            <?php $this->load->view("users/settings"); ?>
        </div>
    </div>
</section>