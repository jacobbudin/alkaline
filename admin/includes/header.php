<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php echo (defined('TITLE') ? TITLE : 'Alkaline'); ?></title>
	<link rel="stylesheet" href="<?php echo BASE . CSS; ?>blueprint/screen.css" type="text/css" media="screen, projection" />
	<link rel="stylesheet" href="<?php echo BASE . CSS; ?>blueprint/print.css" type="text/css" media="print" />	
	<!--[if lt IE 8]><link rel="stylesheet" href="<?php echo BASE . CSS; ?>blueprint/ie.css" type="text/css" media="screen, projection" /><![endif]-->
	<link rel="stylesheet" href="<?php echo BASE . CSS; ?>alkaline.css" type="text/css" media="screen, projection" />
	<link rel="stylesheet" href="<?php echo BASE . CSS; ?>smoothness/jquery-ui-1.8.custom.css" type="text/css" media="screen, projection" />
	<!--[if IE]><script language="javascript" type="text/javascript" src="<?php echo BASE . JS; ?>jquery/excanvas.min.js"></script><![endif]-->
	<script src="<?php echo BASE . JS; ?>jquery/jquery-1.4.2.min.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery.json-2.2.min.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery-ui-1.8.custom.min.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery.flot.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery.html5_upload.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>alkaline.js" type="text/javascript"></script>
	<?php echo (defined('EMBED_CSS') ? '<style type="text/css">' . EMBED_CSS . '</style>' : ''); ?>
</head>
<body>
	<div id="header_holder">
		<div class="container">
			<div id="header" class="span-24 last">
				<div class="span-10 append-1">
					<a href="<?php echo BASE . ADMIN; ?>"><img src="/images/alkaline.png" alt="Alkaline" /></a>
				</div>
				<div id="user_panel" class="span-13 last">
					<img src="/images/icons/user.png" alt="" /> &#0160; <a href="<?php echo BASE . ADMIN; ?>preferences/" class="user">Jacob Budin</a>, Administrator <a href="<?php echo BASE . ADMIN; ?>logout/" class="logout">Log out</a>
				</div>
			</div>
		</div>
	</div>
	<div class="container">
		<div id="navigation" class="span-24 last">
			<ul>
				<li><a href="<?php echo BASE . ADMIN; ?>dashboard/"<?php if(@TAB == 'dashboard'){ echo ' class="selected"'; } ?>>Dashboard</a></li>
				<li><a href="<?php echo BASE . ADMIN; ?>library/"<?php if(@TAB == 'library'){ echo ' class="selected"'; } ?>>Library</a></li>
				<li><a href="<?php echo BASE . ADMIN; ?>features/"<?php if(@TAB == 'features'){ echo ' class="selected"'; } ?>>Features</a></li>
				<li><a href="<?php echo BASE . ADMIN; ?>settings/"<?php if(@TAB == 'settings'){ echo ' class="selected"'; } ?>>Settings</a></li>
				<li><a href="http://www.alkalineapp.com/help/" target="_new">Help</a></li>
			</ul>
		</div>
		<div id="content" class="span-24 last">
			<?php $alkaline->viewNotification(); ?>
			