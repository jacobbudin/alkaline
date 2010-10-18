<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="base" content="<?php echo LOCATION . BASE; ?>" />
	<meta name="folder_prefix" content="<?php echo FOLDER_PREFIX; ?>" />
	<title><?php echo (defined('TITLE') ? TITLE : 'Alkaline'); ?></title>
	<link rel="stylesheet" href="<?php echo BASE . CSS; ?>blueprint/screen.css" type="text/css" media="screen, projection" />
	<link rel="stylesheet" href="<?php echo BASE . CSS; ?>blueprint/print.css" type="text/css" media="print" />	
	<!--[if lt IE 8]><link rel="stylesheet" href="<?php echo BASE . CSS; ?>blueprint/ie.css" type="text/css" media="screen, projection" /><![endif]-->
	<link rel="stylesheet" href="<?php echo BASE . CSS; ?>alkaline.css" type="text/css" media="screen, projection" />
	<link rel="stylesheet" href="<?php echo BASE . CSS; ?>smoothness/jquery-ui-1.8.5.custom.css" type="text/css" media="screen, projection" />
	<!--[if IE]><script language="javascript" type="text/javascript" src="<?php echo BASE . JS; ?>jquery/excanvas.min.js"></script><![endif]-->
	<link rel="shortcut icon" href="<?php echo BASE . IMAGES; ?>favicon.ico" />
	<script src="<?php echo BASE . JS; ?>jquery/jquery-1.4.3.min.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery.json-2.2.min.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery-ui-1.8.5.custom.min.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery.flot.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery.html5_upload.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>alkaline.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>dashboard.js" type="text/javascript"></script>
</head>
<body>
	<div id="header_holder">
		<div class="container">
			<div id="header" class="span-24 last">
				<div class="span-10 append-1">
					<a href="<?php echo BASE . ADMIN; ?>"><img src="/images/alkaline.png" alt="Alkaline" /></a>
				</div>
				<div id="user_panel" class="span-13 last">
					<?php
					if(!empty($user) and $user->perm()){
						?>
						<img src="/images/icons/user.png" alt="" /> &#0160; <a href="<?php echo BASE . ADMIN; ?>preferences<?php echo URL_CAP; ?>" class="user"><?php echo $user->user['user_name']; ?></a>
						<?php
						
						$web_title = $alkaline->returnConf('web_title');
						if(!empty($web_title)){
							echo 'for <a href="' . BASE . '" target="_new">' . $web_title . '</a>';
						}
						
						?>
						<a href="<?php echo BASE . ADMIN; ?>logout<?php echo URL_CAP; ?>" class="logout">Log out</a>
						<?php
					}
					?>
				</div>
			</div>
		</div>
	</div>
	<div class="container">
		<div id="navigation" class="span-24 last">
			<ul>
				<?php
				
				if(@!defined('TAB') or (@TAB == 'dashboard') or (@TAB == 'library') or (@TAB == 'features') or (@TAB == 'settings')){
					?>
					<li><a href="<?php echo BASE . ADMIN; ?>dashboard<?php echo URL_CAP; ?>"<?php if(@TAB == 'dashboard'){ echo ' class="selected"'; } ?>>Dashboard</a></li>
					<li><a href="<?php echo BASE . ADMIN; ?>library<?php echo URL_CAP; ?>"<?php if(@TAB == 'library'){ echo ' class="selected"'; } ?>>Library</a></li>
					<li><a href="<?php echo BASE . ADMIN; ?>features<?php echo URL_CAP; ?>"<?php if(@TAB == 'features'){ echo ' class="selected"'; } ?>>Features</a></li>
					<li><a href="<?php echo BASE . ADMIN; ?>settings<?php echo URL_CAP; ?>"<?php if(@TAB == 'settings'){ echo ' class="selected"'; } ?>>Settings</a></li>
					<li><a href="http://www.alkalineapp.com/help/" target="_new">Help</a></li>
					<?php
				}
				else{
					?>
					<li><a href="" class="selected"><?php echo TAB; ?></a></li>
					<?php
				}
				
				?>
			</ul>
		</div>
		<div id="content" class="span-24 last">
			<?php if(!empty($alkaline)){ $alkaline->viewNotification(); } ?>
			