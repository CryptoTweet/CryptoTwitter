    <div class="row-fluid">
        
            <div class="span12">            
                <?php if($this->session->userdata("oauth_token") != "" && intval($this->session->userdata("auth")) == 1): ?>
                    <?php echo form_open(); ?>
                        <div class="row-fluid">

                            <div class="span5 well">
                                <div class="row-fluid">
                                    <div class="span12">
                                        <div class="row-fluid">
                                            <div class="pull-left">
                                                <img class="avatar" height="20" width="20" src="<?php echo stripslashes($this->user["profile_image"]);?>" />
                                            </div>
                                            <div class="span10">
                                                <div class="row-fluid">
                                                    <span>&nbsp;<a class="text_black bold" href="<?php echo site_url()."$".$this->user['username']; ?>">$<?php echo $this->user['username']; ?></a></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row-fluid stats visible-desktop">
                                            <ul class="userstats">
                                                <li class="span4">
                                                    <a href="#"><strong><?php echo (isset($etweet_count) ? $etweet_count : "0"); ?></strong></a><br />
                                                    ETweets
                                                </li>
                                                <li class="span4">
                                                    <a href="#"><strong><?php echo (isset($authorized_count) ? $authorized_count : "0"); ?></strong></a><br />
                                                    Authorized
                                                </li>
                                                <li class="span4">
                                                    <a href="#"><strong><?php echo (isset($friends_count) ? $friends_count : "0"); ?></strong></a><br />
                                                    Friends
                                                </li>
                                            </ul>
                                        </div>                                        
                                    </div>
                                </div>
                                <div class="devider"></div>
                                <div class="row-fluid">
                                    <div class="span12">
                                        <label class="checkbox pull-left" title="Mention on Twitter&trade;">
                                            <input type="checkbox" id="tweet_also" value="1" title="Mention on Twitter&trade;" checked="checked" /> Mention                                        
                                        </label>
                                        <small class="pull-right" title="Update friendlist">&nbsp;<i class="icon-refresh"></i>&nbsp;Update friendlist</small>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                                <hr class="nomargin nopadding" /><br />
                                <div class="row-fluid">
                                    <div class="span12">
                                        <select id="recipient"></select>&nbsp;<a href="#" class=""></a>                                    
                                    </div>                                
                                </div>
                                
                                <div class="row-fluid">                                
                                    <textarea class="span12" maxlength="130" id="tweet" placeholder="Compose new ETweet"></textarea>                                
                                </div>
                                <div class="row-fluid">
                                    <button type="button" id="send_tweet" class="btn btn-info span4">ETweet!</button>
                                    <div class="offset8 text-align-right"><span id="charcount">130</span></div>
                                </div>
                                <div class="devider"></div>
                                <div class="devider"></div>
                            </div> 

                            <div class="span7 well" id="tweetfeed">                            
                                Loading ETweets, give me a second...
                            </div>

                        </div>
                    <?php echo form_close(); ?>
                <?php endif; ?>
            </div>
        
    </div>
