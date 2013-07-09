<div id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModal" aria-hidden="true">
    <div class="modal-body"></div>
</div>
<div class="container-fluid">
    <fieldset>
            <legend>Friends and Followers</legend>
    
            <div class="row-fluid">
                <div class="span10 offset1">
                    <div class="devider"></div>
                    <div class="row-fluid controls-row">
                        <?php echo form_open("", array("class" => "form-inline")); ?>
                            <input type="hidden" name="section" value="add_friend" />
                            <div class="input-prepend">
                                <span class="add-on">@</span>
                                <input class="input-large" type="text" name="screenname" placeholder="Enter the Twitter screenname" />                                                                       
                            </div>                                  
                            <button class="btn btn-primary">Add user</button>
                            <div class="clearfix"></div>
                            <?php echo form_error("screenname"); ?>
                        <?php echo form_close(); ?>
                    </div>
                    <div class="devider"></div>
                    
                    <div class="row-fluid">
                        <a href="#" id="update_friends" class="pull-left span3 btn"><i class="icon-refresh"></i>&nbsp;Update friends</a>
                        <div class='offset3'></div>
                        <a href="#" id="update_followers" class="pull-right span3 offset3 btn"><i class="icon-refresh"></i>&nbsp;Update followers</a>  
                        <div class="clearfix"></div>
                    </div>
                    <div class="devider"></div>
                    
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="accordion" id="accordion2">
                                <div class="accordion-group">
                                    <div class="accordion-heading">
                                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseOne">Friends (<?php echo count($friends); ?>)</a>
                                    </div>
                                    <div id="collapseOne" class="accordion-body collapse">
                                        <div class="accordion-inner">
                                        <?php if($friends != false): ?>
                                            <?php foreach($friends as $friend): ?>
                                                <div class="row-fluid border-bottom">
                                                    <?php if($this->mod_twitter->friend_authorized($friend["twitter_id"]) == false): ?>
                                                        <a href="<?php echo site_url()."requests/send_authorization_request/".$friend["twitter_id"]; ?>/<?php echo date("His"); ?>.html" data-target="#myModal" data-toggle="modal" class="pull-right" title="Send authorization request">Authorize</a>
                                                        <?php else: ?>
                                                        <a href="<?php echo site_url()."requests/revoke_autorization/".sha1($this->mod_twitter->friend_authorized($friend["twitter_id"]).":".$friend["twitter_id"]); ?>/<?php echo date("His"); ?>.html" class="pull-right ajax-get" title="Revoke authorization">Revoke</a>
                                                    <?php endif; ?>
                                                    <div class="add-on">
                                                        <span class="text_black bold"><?php echo $friend["username"]; ?></span>&nbsp;<span class="text_gray"><?php echo $friend["name"]; ?></span>
                                                        <i class="icon-star"></i>
                                                    </div>                                                
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="accordion-group">
                                    <div class="accordion-heading">
                                        <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2" href="#collapseTwo">Followers (<?php echo count($followers); ?>)</a>
                                    </div>
                                    <div id="collapseTwo" class="accordion-body collapse in">
                                        <div class="accordion-inner">
                                        <?php if($followers != false): ?>
                                            <?php foreach($followers as $friend): ?>
                                                <div class="row-fluid border-bottom">
                                                    <?php if($this->mod_twitter->friend_authorized($friend["twitter_id"]) == false): ?>
                                                        <a href="<?php echo site_url()."requests/send_authorization_request/".$friend["twitter_id"]; ?>/<?php echo date("His"); ?>.html" data-target="#myModal" data-toggle="modal" class="pull-right" title="Send authorization request">Authorize</a>
                                                    <?php else: ?>
                                                        <a href="<?php echo site_url()."requests/revoke_autorization/".sha1($this->mod_twitter->friend_authorized($friend["twitter_id"]).":".$friend["twitter_id"]); ?>/<?php echo date("His"); ?>.html" class="pull-right ajax-get" title="Revoke authorization">Revoke</a>
                                                    <?php endif; ?>
                                                    <div class="add-on">
                                                        <span class="text_black bold">$<?php echo $friend["username"]; ?></span>&nbsp;<span class="text_gray"><?php echo $friend["name"]; ?></span>
                                                        <i class="icon-user"></i>
                                                    </div>                                                
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="offset1"></div>
            </div>            
    </fieldset>
</div>