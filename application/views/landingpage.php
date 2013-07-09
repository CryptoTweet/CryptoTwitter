<div class="row-fluid welcome-row">
    <div class="span7 welcome">
        <div class="welcome-text">
            <h5>CryptoTwitter&trade; - You in control</h5>
            <p>It's FREE and registration through your Twitter&trade; account takes less then 1 minute!</p>
            <br />
            <a class="btn btn-success" href="<?php echo site_url()."account/register"; ?>" title="Register today!">Register for FREE here!</a>
            <br /><br />
        </div>
    </div>
    <div class="span5 well login">
        <?php echo form_open(site_url()."authenticate", array("class" => "nopadding nomargin")); ?>
        <div class="row-fluid">
            <input type="text" name="email" placeholder="E-mail" class="span12" /> 
            <?php echo form_error("email"); ?>
        </div>
        <div class="row-fluid">            
            <input type="password" name="password" placeholder="Password" class="span8 pull-left" />
            <button type="submit" class="btn btn-info span3 pull-right">Login</button>
            <div class="clearfix"></div>
            <?php echo form_error("password"); ?>
        </div>
        <div class="row-fluid">
            <p><a href="#"><small>Forgot your username or password?</small></a></p>
        </div>
        <?php echo form_close(); ?>
    </div>    
</div>