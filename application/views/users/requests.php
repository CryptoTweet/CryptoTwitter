<?php 
    //Helper for timespans
    $this->load->helper('date');
?>
<div class="container-fluid">
    <fieldset>
            <legend>Requests</legend>
                    
            <?php if($requests != false): ?>
                <?php foreach($requests as $r): ?>

                <div class="row-fluid">
                    <span class="text_black span4">$<?php echo $r["username"]; ?>&nbsp;<span class="text_gray"><?php echo $r["name"]; ?></span></span>
                    <span class="span8"><?php echo timespan(strtotime($r["datetime"]),time()); ?></span>
                </div>
                <div class="devider"></div>

                <?php endforeach; ?>
            <?php endif; ?>
                    
    </fieldset>
</div>