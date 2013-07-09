<section id="body">
    <div class="container-fluid">
        <div class="row-fluid">
            <fieldset>
                <legend>Contact us</legend>
            </fieldset>       
        </div>

        <div class="row-fluid">
            <p>
                If you have any questions about errors feel free to contact our support desk. For all other questions choose the correct category or choose other if you don't know.
            </p>
            <div class="devider"></div>
            <?php echo form_open(); ?>

                <div class="row-fluid">
                    <div class="span3">Name</div>
                    <input type="text" class="span6" name="name" placeholder="Your name" value="<?php echo set_value("name"); ?>" />
                    <?php echo form_error("name"); ?>
                </div>
                <div class="row-fluid">
                    <div class="span3">E-mail</div>
                    <input type="text" class="span6" name="email" placeholder="Your e-mail address" value="<?php echo set_value("email"); ?>" />
                    <?php echo form_error("email"); ?>
                </div>
                <div class="row-fluid">
                    <div class="span3">Question type</div>
                    <?php
                        $options = array("General" => "General", "Support" => "Support", "Sales" => "Sales", "Other" => "Other");
                        echo form_dropdown("type", $options, post("type"),"class='span6'");
                    ?>
                </div>
                <div class="row-fluid">
                    <div class="span3">Your question or comment</div>                    
                    <textarea rows="5" name="question" class="span6" placeholder="Tell us what you want to know"><?php echo set_value("question"); ?></textarea>
                    <?php echo form_error("question"); ?>
                </div>                
                <div class="row-fluid">
                    <div class="span3"></div>
                    <button type="submit" class="btn btn-success">Send it now</button>
                </div>
            <?php echo form_close(); ?>
            
        </div>
    </div>
</section>