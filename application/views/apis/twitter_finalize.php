<section id="body">
    <?php echo form_open(); ?>
    <div class="container-fluid">
        <div class="page-header">
            <h4>Verify your data</h4>
        </div>

        <div class="container-fluid">
            <div class="row-fluid">
                <label class="span3">Your name</label>
                <input type="text" class="input-large span6" name="name" value="<?php echo str_replace(array("-","_"), "",$userinfo->name); ?>" />
                <?php echo form_error("name"); ?>
            </div>
            <div class="row-fluid">
                <label class="span3">Your location</label>
                <input type="text" class="input-large span6" name="location" value="<?php echo str_replace(",", "", $userinfo->location); ?>" />
                <?php echo form_error("location"); ?>
            </div>
            <div class="row-fluid">
                <label class="span3">Personal website</label>
                <input type="text" class="input-large span6" name="personal_website" value="<?php echo $userinfo->url; ?>" />
                <?php echo form_error("personal_website"); ?>
            </div>
            <div class="row-fluid">
                <label class="span3">Personal description</label>
                <textarea class="span6" name="description"><?php echo $userinfo->description; ?></textarea>
                <?php echo form_error("description"); ?>
            </div>
            <div class="row-fluid">
                <label class="span3">Your e-mail</label>
                <input type="text" class="input-large span6" name="email" value="<?php echo post("email"); ?>" />
                <?php echo form_error("email"); ?>
            </div>
            <div class="row-fluid">
                <label class="span3">Your password</label>
                <input type="password" class="input-large span6" name="password" value="" />
                <?php echo form_error("password"); ?>
            </div>
            <div class="row-fluid">
                <label class="span3">Repeat your password</label>
                <input type="password" class="input-large span6" name="password_repeat" value="" />
                <?php echo form_error("password_repeat"); ?>
            </div>

            <input type="hidden" name="profile_image" value="<?php echo $userinfo->profile_image_url; ?>" />
            <input type="hidden" name="twitter_id" value="<?php echo $userinfo->id; ?>" />
            <div class="clearfix"></div>
            <div class="devider"></div>

            <div class="row-fluid">
                <label class="span3">&nbsp;</label>
                <input type="submit" value="Save &amp; continue" class="btn btn-success" />
            </div>
        </div>

    </div>
    <?php echo form_close(); ?>
</section>