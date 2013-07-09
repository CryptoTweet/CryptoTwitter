<?php
/**
 * Description of public_profile
 *
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright (c) 2013, Victor Angelier
 * @link http://www.victorangelier.com Personal Website
 * @link http://www.twitter.com/digital_human
 */
?>
<section id="body">
    <div class="container">
        <div class="row-fluid">
            <fieldset>
                <legend><?php echo $user["name"]; ?>'s public profile</legend>
            </fieldset>

            <div class="row-fluid">
                <div class="span12">
                    <div class="row-fluid">
                        <div class="span3">
                            <img class="span12" alt="<?php echo $user["name"]; ?> profile image" title="<?php echo $user["name"]; ?> profile image" src="<?php echo $user["profile_image"]; ?>" />
                        </div>
                        <div class="span9">
                            <div class="devider"></div>
                            <div class="row-fluid">
                                <div class="span6 profile_name"><strong><?php echo $user["name"]; ?></strong></div>
                                <div class="span4"><?php echo $user["location"]; ?></div>
                            </div>
                            <div class="row-fluid">
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
                            <div class="devider"></div>
                            <hr class="nomargin nopadding" />
                            <div class="devider"></div>
                            <div class="row-fluid">
                                <?php echo $user["personal_website"]; ?>
                            </div>
                            <div class="row-fluid">
                                <div class="devider"></div>
                                <?php echo $user["description"]; ?>
                            </div>
                        </div>
                    </div>                    
                </div>
            </div>            
            <div class="devider"></div>
            
            <h4>ETweets</h4>
            <?php if($this->session->userdata("uid") < 1 || $this->session->userdata("auth") != true): ?>
            <div class="row-fluid">
                <div class="devider"></div>
                <p>To be able to read ETweets from <?php echo $user["name"]; ?> you need to be authorized. Please create an account, login, and ask for authorization.</p>
            </div>
            <div class="row-fluid">
                <p>Join now! Its free!</p>
                <a class="btn btn-success" href="<?php echo site_url()."account/register"; ?>" title="Register today!">Register for FREE here!</a>
            </div>
            <?php else: ?>
            <div class="row-fluid">
                <div class="devider"></div>
                <?php if($tweets == false): ?>
                        <?php echo $user["name"]; ?> has no messages yet, or you are not authorized to read them.
                <?php else: ?>                    
                <?php foreach($tweets as $tweet): ?>
                        <div class="row-fluid">
                            <div class="tweet span8 well">
                                <div class="row-fluid">
                                    <div class="pull-left">                                                
                                        <img class="avatar" height="50" width="50" src="<?php echo strip_slashes($tweet["avatar"]); ?>" />
                                    </div>
                                    <div class="span10">
                                        <div class="row-fluid">
                                            <span class="pull-right time"><?php echo $tweet['timespan']; ?></span>
                                            <span class="text_black bold"><a href="<?php echo site_url(); ?>$<?php echo $tweet["screenname"]; ?>"><?php echo $tweet["name"]; ?></a></span>
                                            $<?php echo $tweet['screenname']; ?>
                                            <div class="clearfix"></div>
                                        </div>
                                        <div class="row-fluid">
                                            <div class="span12"><p><?php echo parse_tweet($tweet['text']); ?></p></div>
                                        </div>
                                    </div>                                            
                                </div>
                                <div class="row-fluid">
                                    <div class="etweet-tools">
                                        <div class="dropdown pull-right">
                                            <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-th-list"></i>More</a>
                                            <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu">
                                            <?php
                                            if(!isset($tweet["send"]) && intval($tweet["twitter_sender_id"]) > 0):
                                                echo '<li><a href="#" data-id="'.$tweet["id"].'" class="reply"><i class="icon-share-alt"></i>&nbsp;<small>Reply</small></a></li>';
                                                if(!isset($tweet['retweet']) || $tweet["retweet"] == 0):
                                                    if($this->mod_twitter->already_retweeted($tweet['hash']) == false){
                                                        echo '<li><a href="#" data-id="'.$tweet["id"].'" class="retweet"><i class="icon-retweet"></i>&nbsp;<small>Retweet</small></a></li>';
                                                    }
                                                endif;
                                            elseif($tweet["send"] == 1):
                                                echo  '<li><a href="#" data-id="'.$tweet["id"].'" class="delete"><i class="icon-fire"></i>&nbsp;<small>Delete</small></a></li>';
                                            endif;
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>                                            
                <?php endforeach; ?>
                <?php endif; ?>
                <div class="devider"></div>
            <?php endif; ?>
            </div>
            <div class="row-fluid">
                <div class="devider"></div>
                <br />
            </div>     

        </div>
    </div>
</section>