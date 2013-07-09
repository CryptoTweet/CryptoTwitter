<?php
/**
 * Description of read_authorization_request
 *
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright (c) 2013, Victor Angelier
 * @link http://www.victorangelier.com Personal Website
 * @link http://www.twitter.com/digital_human
 */
echo form_open();
?>
<section id="body">
    <fieldset>
        <legend>Please answer the following secret question.</legend>
        <div class="devider"></div>

        <div class="row-fluid">
            <label class="span12"><b>Secret question:</b></label>
        </div>
        <div class="row-fluid">
            <div class="span6 well"><?php echo $secret; ?></div>                
            <div class="offset6"></div>
        </div>

        <div class="row-fluid">
            <label class="span12"><b>Your answer:</b></label>
        </div>
        <div class="row-fluid">
            <input type="text" class="span6" name="answer" id="answer" placeholder="Your answer" />
            <?php echo form_error('answer'); ?>
        </div>
        <div class="row-fluid">            
            <button type="submit" class="btn btn-success span3">Answer now!</button>
            <div class="offset9">&nbsp;</div>
        </div>
        <input type="hidden" name="id" id="id" value="<?php echo $id; ?>" />
    </fieldset>
</section>
<?php echo form_close(); ?>