<?php
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
	<title><?php echo (!isset($this->title) ? "Welcome to Encrypted Twitter" : $this->title); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Encrypted Twitter project. Think about your privacy and join today! Registration is FREE and very easy!" />
    <link href="<?php echo site_url(); ?>css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo site_url(); ?>css/bootstrap-responsive.min.css" rel="stylesheet">
    <link href="<?php echo site_url(); ?>css/style.css?<?php echo date("His"); ?>" rel="stylesheet">
</head>
<body>
    <div id="wrapper">
        <header>
            <?php $this->load->view("default/menu"); ?>
            <?php if(($message = $this->session->flashdata("message")) != ""): ?>
            <div class="container-fluid">
                <div class="alert">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <strong>Notification!</strong> <?php echo $this->session->flashdata("message"); ?>
                </div>
            </div>
            <?php endif; ?>
        </header>