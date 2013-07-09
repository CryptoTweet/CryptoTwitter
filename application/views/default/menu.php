<?php
$menu = array(
    "home"  =>  false,
    "dashboard" =>  false,
    "register"  =>  false,
    "twitter_login" =>  false,
    "whatis"    =>  false,
    "how"   =>  false,
    "contact" => false
);

if(uri_string() == "/" || uri_string() == ""){
    $menu["home"] = true;
}elseif(uri_string() == "users/dashboard"){
    $menu["dashboard"] = true;
}elseif(uri_string() == "twitter_login"){
    $menu["register"] = true;
}elseif(uri_string() == "about/what_is_cryptotwitter"){
    $menu["whatis"] = true;
}elseif(uri_string() == "about/how_does_cryptotwitter_work"){
    $menu["how"] = true;
}elseif(uri_string() == "contact"){
    $menu["contact"] = true;
}
?>
<div class="container-fluid">
    <div class="row-fluid">
         <div class="navbar navbar-inverse nomargin">
                <div class="navbar-inner">
                    <div class="container-fluid">
                         <!-- .btn-navbar is used as the toggle for collapsed navbar content -->
                        <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </a>
                         
                        <a class="brand" href="<?php echo site_url(); ?>" title="CryptoTwitter - Encrypted Twitter">CryptoTwitter&trade;</a>
                        <div class="nav-collapse collapse">
                            <ul class="nav">
                                <?php if($this->user != null): ?>
                                <li class="<?php echo ($menu["home"] == true ? "active" : ""); ?>"><a href="<?php echo site_url(); ?>">Home</a></li>
                                <?php else: ?>
                                <li class="<?php echo ($menu["home"] == true ? "active" : ""); ?>"><a href="<?php echo site_url(); ?>">Home</a></li>
                                <?php endif; ?>
                                <?php if(($user = $this->mod_twitter->check_user_exists()) === FALSE): ?>
                                <li class="<?php echo ($menu["register"] == true ? "active" : ""); ?>"><a href="<?php echo site_url(); ?>account/register" title="Register with Twitter">Register with Twitter</a></li>
                                <?php else: ?>
                                <li class="<?php echo ($menu["dashboard"] == true ? "active" : ""); ?>"><a href="<?php echo site_url(); ?>users/dashboard" title="Dashboard">Dashboard</a></li>
                                <?php endif; ?>
                                <li class="<?php echo ($menu["whatis"] == true ? "active" : ""); ?>"><a href="<?php echo site_url(); ?>about/what_is_cryptotwitter" title="What is CryptoTwitter?">What is CryptoTwitter?</a></li>
                                <li class="<?php echo ($menu["how"] == true ? "active" : ""); ?>"><a href="<?php echo site_url(); ?>about/how_does_cryptotwitter_work" title="How does it work?">How does it work?</a></li>                                                
                               <li class="<?php echo ($menu["contact"] == true ? "active" : ""); ?>"><a href="<?php echo site_url(); ?>contact" title="Contact">Contact</a></li>
                            </ul>
                        </div>
                        
                        <ul class="nav pull-right">
                            <?php if(($user = $this->mod_twitter->check_user_exists()) !== FALSE): ?>
                            <li><a href="<?php echo site_url(); ?>users/dashboard"><?php echo $user['name']; ?></a></li>
                            <li><a href="<?php echo site_url(); ?>logoff">Logoff</a></li>
                            <?php else: ?>
                            <li class="dropdown">
                               <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                Login <b class="caret"></b>
                               </a>
                               <div class="dropdown-menu">
                                   <?php echo form_open(site_url()."authenticate", "class='form-inline'"); ?>
                                   <div class="row-fluid">
                                       <div class="span12">
                                           <input type="text" name="email" placeholder="E-mail" class="span12" /> 
                                       </div>
                                   </div>
                                   <div class="devider"></div>
                                   <div class="row-fluid">
                                       <div class="span12">
                                           <input type="password" name="password" placeholder="Password" class="span12" />
                                       </div>
                                   </div>
                                   <div class="row-fluid">
                                       <div class="span12">                                                                                
                                            <input type="submit" value="Login" class="btn btn-info" />
                                       </div>
                                   </div>
                                   <?php echo form_close(); ?>
                                </div>
                            </div>
                            <?php endif; ?>                            
                        </ul>                        
                    </div>
               </div>
         </div>
            <ul class="breadcrumb">
                <li><a href="<?php echo site_url(); ?>">Home</a> <span class="divider">/</span></li>
                <?php $old = ""; $count = 0; foreach($this->uri->segment_array() as $part): ?>
                <?php if(substr(trim(uri_string()), 0, 1) == "$"){ $part = "Public profile"; } ?>
                    <li><a href="<?php echo site_url().($old == "" ? "" : $old."/").$part; $old = $part; ?>"><?php echo ucfirst(str_replace("_", " ", $part)); ?></a> <span class="divider">/</span></li>                
                <?php                    
                    $count++;
                    if($count == 2){
                        break;
                    }                    
                    endforeach; 
                ?>
            </ul>
    </div>


