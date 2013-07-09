<section id="body">
    <?php echo form_open(); ?>
    <div class="container-fluid">
        <div class="page-header">
            <h4>Get verified!</h4>
        </div>

        <div class="row-fluid">
            <label class="span3">Enter your PIN:</label>
            <input type="text" name="pin" value="" class="input-small span3" />
            <?php echo form_error("pin"); ?>
            <div class="offset6"></div>
        </div>
        <div class="row-fluid">
            <label class="span3">&nbsp;</label>
            <input type="submit" class="btn btn-success span3" value="Verify now!" />
            <div class="offset6"></div>
        </div>

    </div>
    <?php echo form_close(); ?>
</section>