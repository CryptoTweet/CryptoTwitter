<?php
/**
 * Description of profile
 *
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright (c) 2013, Victor Angelier
 * @link http://www.victorangelier.com Personal Website
 * @link http://www.twitter.com/digital_human
 */
?>
<?php echo form_open(); ?>
<div class="container-fluid">
    <fieldset>
        <legend>Profile</legend>
        <input type="hidden" name="section" value="profile" />

            <div class="row-fluid">        
                <label class="span3">Your name</label>
                <input type="text" class="input-large span6" name="name" value="<?php echo $userinfo["name"]; ?>" />
                <?php echo form_error("name"); ?>
                <div class="offset3"></div>
            </div>
            <div class="row-fluid">
                <label class="span3">Your location</label>
                <input type="text" class="input-large span6" name="location" value="<?php echo $userinfo["location"]; ?>" />
                <?php echo form_error("location"); ?>
                <div class="offset3"></div>
            </div>
            <div class="row-fluid">
                <label class="span3">Your e-mail</label>
                <input type="text" class="input-large span6" name="email" value="<?php echo $userinfo["email"]; ?>" />
                <?php echo form_error("email"); ?>
                <div class="offset3"></div>
            </div>
            <div class="row-fluid">
                <label class="span3">Personal website</label>
                <input type="text" class="input-large span6" name="personal_website" value="<?php echo $userinfo["personal_website"]; ?>" />
                <?php echo form_error("personal_website"); ?>
                <div class="offset3"></div>
            </div>
            <div class="row-fluid">
                <label class="span3">Personal description</label>
                <textarea class="span6" name="description"><?php echo $userinfo["description"]; ?></textarea>
                <?php echo form_error("description"); ?>
                <div class="offset3"></div>
            </div>
            <div class="devider"></div>
            <div class="row-fluid">
                <label class="span10">To change your password, just enter a new one below</label>
                <div class="offset2"></div>
            </div>
            <div class="row-fluid">
                <label class="span3">Your password</label>
                <input type="password" class="input-large span6" name="password" value="<?php echo post("password"); ?>" autocomplete="off" />
                <?php echo form_error("password"); ?>
                <div class="offset3"></div>
            </div>
            <div class="row-fluid">
                <label class="span3">Repeat your password</label>
                <input type="password" class="input-large span6" name="password_repeat" value="<?php echo post("password_repeat"); ?>" autocomplete="off" />
                <?php echo form_error("password_repeat"); ?>
                <div class="offset3"></div>
            </div>
            <div class="clearfix"></div>
            <div class="devider"></div>
            <div class="row-fluid">
                <label class="span2">&nbsp;</label>
                <input type="submit" value="Save &amp; continue" class="btn btn-success span3" />
                <div class="offset7"></div>
            </div>

    </fieldset>    
</div>
<?php echo form_close(); ?>