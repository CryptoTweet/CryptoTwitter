<?php
/**
 * Description of message
 *
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright (c) 2013, Victor Angelier
 * @link http://www.victorangelier.com Personal Website
 * @link http://www.twitter.com/digital_human
 */
?>
    <fieldset>
        <?php if(isset($message) && is_array($message)): ?>
            <legend>Message</legend>
        <?php else: ?>
            <legend>Messages</legend>
        <?php endif; ?>
    
        <div class="row-fluid">
            <div class="span8 offset2">

                <?php if(isset($message) && is_array($message)): ?>
                        <div class="row-fluid">
                            <div class="span12">
                                <div class="well tweet <?php echo (isset($message["private"]) && $message["private"] == 1 ? "border-orange" : ""); ?>">
                                    <img class="pull-left avatar" height="50" width="50" src="<?php echo strip_slashes($message["sender"]["profile_image"]); ?>" />
                                    <span class="pull-right time"><?php echo $message['datetime']; ?></span>
                                    <label><?php echo $message["sender"]['name']; ?> <span>$<?php echo $message['sender']['username']; ?></span></label>
                                    <p><?php echo $message['tweet_text']; ?></p>
                                </div>
                            </div>
                            <div class="devider"></div>
                        </div>                
                <?php endif; ?>
                
                <?php if($tweets == false): ?>
                        <label>No messages yet</label>
                <?php else: ?>
                <?php foreach($tweets as $tweet): ?>
                        <div class="row-fluid">
                            <div class="span12 well etweet<?php echo ((isset($tweet["private"]) && $tweet["private"] == 1) ? " border-orange" : ""); ?>">
                                <div class="row-fluid">
                                    <div class="tweet">
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
                
            </div>
            <div class="offset2"></div>
        </div>
    </fieldset>
