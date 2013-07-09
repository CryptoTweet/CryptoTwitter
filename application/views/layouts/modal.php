<?php
/**
 * Description of modal
 *
 * @author Victor Angelier <vangelier@hotmail.com>
 * @copyright (c) 2013, Victor Angelier
 * @link http://www.victorangelier.com Personal Website
 * @link http://www.twitter.com/digital_human
 */
@header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
@header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
@header("Cache-Control: post-check=0, pre-check=0", false);
@header("Pragma: no-cache"); // HTTP/1.0
@header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>Welcome to Encrypted Twitter</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <meta name="description" content="Encrypted Twitter project. Think about your privacy and join today! Registration is FREE and very easy!" />
    <link href="<?php echo site_url(); ?>css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo site_url(); ?>css/bootstrap-responsive.min.css" rel="stylesheet">
    <link href="<?php echo site_url(); ?>css/style.css?<?php echo date("His"); ?>" rel="stylesheet">
</head>
<body>
    <div id="wrapper">
        <div class="container-fluid" id="main"> 
            {yield}
        </div>
    </div>
    <script src="<?php echo site_url(); ?>js/jquery-1.10.1.min.js"></script>
    <script src="<?php echo site_url(); ?>js/bootstrap.min.js"></script>
    <script src="<?php echo site_url(); ?>js/bootstrap-scrollspy.js"></script>
    <script src="<?php echo site_url(); ?>js/holder.js"></script>
    <script src="<?php echo site_url(); ?>js/jquery.timer.js"></script>
    <script src="<?php echo site_url(); ?>js/script.js?<?php echo date("His"); ?>"></script>
    <script src="<?php echo site_url(); ?>js/dashboard.js?<?php echo date("His"); ?>"></script>
</body>
</html>