<?php
/**
 * Description of read_authorization_request
 *
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright (c) 2013, Victor Angelier
 * @link http://www.victorangelier.com Personal Website
 * @link http://www.twitter.com/digital_human
 */
?>
<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">Authorization request</h3>
</div>
<?php echo form_open(); ?>
<div class="modal-body">    
    <div class="row-fluid">
        <div class="span12">
            <label class="span12">Please enter a secret question only your friend <span class="text_black">$<?php echo $recipient["username"]; ?></span>&nbsp;<span class="text_gray"><?php echo $recipient["name"]; ?></span> can answer.</label>                
        </div>
    </div>
    <div class="row-fluid">
        <div class="span12">
            <label><b>Question:</b></label>
        </div>
    </div>
    <div class="row-fluid">
        <div class="span12">
            <input type="hidden" name="recipient" id="recipient" value="<?php echo $recipient["twitter_id"]; ?>" />
            <input type="text" class="span12" name="secret" id="secret" />
        </div>
    </div>    
    <div class="row-fluid">
        <div class="span12">
            <label><b>The only correct answer:</b></label>
        </div>
    </div>
    <div class="row-fluid">
        <div class="span12">                
            <input type="text" class="span12" name="answer" id="answer" placeholder="Your answer" />            
        </div>
    </div>
</div>
<div class="modal-footer">
    <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
    <button class="btn btn-primary" type="button" id="send_request">Send request</button>
</div>
<?php echo form_close(); ?>