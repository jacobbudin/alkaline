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
	<link rel="stylesheet" href="<?php echo BASE . CSS; ?>smoothness/jquery-ui-1.8.9.custom.css" type="text/css" media="screen, projection" />
	<!--[if IE]><script language="javascript" type="text/javascript" src="<?php echo BASE . JS; ?>jquery/excanvas.min.js"></script><![endif]-->
	<link rel="shortcut icon" href="<?php echo BASE . IMAGES; ?>favicon.ico" />
	<script src="<?php echo BASE . JS; ?>jquery/jquery-1.5.min.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery-ui-1.8.9.custom.min.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery.json-2.2.min.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery.flot.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery.html5_upload.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery.ajaxq-0.0.1.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>alkaline.js" type="text/javascript"></script>
</head>
<body>
	<div id="header_holder">
		<div class="container">
			<div id="header" class="span-24 last">
				<div class="span-12 append-1">
					<a href="<?php echo BASE . ADMIN; ?>"><img src="<?php echo BASE . IMAGES; ?>shutter.png" alt="Alkaline" /></a>
				</div>
				<div id="panels" class="span-11 last">
					<?php
					if(!empty($user) and $user->perm()){
						?>
						<div id="user_panel" class="span-5 append-1">
							<strong><img src="<?php echo BASE . IMAGES; ?>icons/user.png" alt="" /> <?php echo $user->user['user_user']; ?></strong><br />
							<span class="small">
								<a href="<?php echo BASE . ADMIN . 'search' . URL_ACT . 'me' . URL_RW; ?>">My Photos</a> &#0183;
								<a href="<?php echo BASE . ADMIN; ?>preferences<?php echo URL_CAP; ?>">Preferences</a> &#0183;
								<a href="<?php echo BASE . ADMIN; ?>logout<?php echo URL_CAP; ?>">Logout</a>
							</span>
						</div>
						<div id="site_panel" class="span-5 last">
							<strong><img src="<?php echo BASE . IMAGES; ?>icons/home.png" alt="" /> <?php echo ((BASE == '/') ? DOMAIN : DOMAIN . BASE ); ?></strong><br />
							<span class="small"><a href="<?php echo BASE; ?>" target="<?php if($user->readPref('home_target')){ echo '_blank'; } ?>"><?php $title = $alkaline->returnConf('web_title'); echo (!empty($title) ? $title : 'Launch'); ?></a></span>
						</div>
						<?php
					}
					?>
				</div>
			</div>
			<div id="navigation" class="span-24 last">
				<ul>
					<?php

					if(@!defined('TAB') or (@TAB == 'dashboard') or (@TAB == 'library') or (@TAB == 'features') or (@TAB == 'settings')){
						?>
						<li><a href="<?php echo BASE . ADMIN; ?>dashboard<?php echo URL_CAP; ?>"<?php if(@TAB == 'dashboard'){ echo ' class="selected"'; } ?>>Dashboard</a></li>
						<li><a href="<?php echo BASE . ADMIN; ?>library<?php echo URL_CAP; ?>"<?php if(@TAB == 'library'){ echo ' class="selected"'; } ?>>Library</a></li>
						<li><a href="<?php echo BASE . ADMIN; ?>features<?php echo URL_CAP; ?>"<?php if(@TAB == 'features'){ echo ' class="selected"'; } ?>>Editor</a></li>
						<li><a href="<?php echo BASE . ADMIN; ?>settings<?php echo URL_CAP; ?>"<?php if(@TAB == 'settings'){ echo ' class="selected"'; } ?>>Settings</a></li>
						<li><a href="http://www.alkalineapp.com/guide/" target="_blank">Help</a></li>
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
		</div>
	</div>
	<div class="container">
		<div id="sub_navigation" class="span-24 last">
			<ul>
				<?php

				if(@TAB == 'dashboard'){
					?>
					<li><a href="<?php echo BASE . ADMIN; ?>statistics<?php echo URL_CAP; ?>"><img src="<?php echo BASE . IMAGES; ?>minis/stats.png" alt="" /> Statistics</a></li>
					<li><a href="<?php echo BASE . ADMIN; ?>preferences<?php echo URL_CAP; ?>"><img src="<?php echo BASE . IMAGES; ?>minis/preferences.png" alt="" /> Preferences</a></li>
					<?php
				}
				elseif(@TAB == 'library'){
					?>
					<li><a href="<?php echo BASE . ADMIN; ?>upload<?php echo URL_CAP; ?>"><img src="<?php echo BASE . IMAGES; ?>minis/upload.png" alt="" /> Upload</a></li>
					<li><a href="<?php echo BASE . ADMIN; ?>shoebox<?php echo URL_CAP; ?>"><img src="<?php echo BASE . IMAGES; ?>minis/shoebox.png" alt="" /> Shoebox</a></li>
					<?php
				}
				elseif(@TAB == 'features'){
					?>
					<li><a href="<?php echo BASE . ADMIN; ?>tags<?php echo URL_CAP; ?>"><img src="<?php echo BASE . IMAGES; ?>minis/tags.png" alt="" /> Tags</a></li>
					<li><a href="<?php echo BASE . ADMIN; ?>piles<?php echo URL_CAP; ?>"><img src="<?php echo BASE . IMAGES; ?>minis/piles.png" alt="" /> Piles</a></li>
					<li><a href="<?php echo BASE . ADMIN; ?>comments<?php echo URL_CAP; ?>"><img src="<?php echo BASE . IMAGES; ?>minis/comments.png" alt="" /> Comments</a></li>
					<li><a href="<?php echo BASE . ADMIN; ?>pages<?php echo URL_CAP; ?>"><img src="<?php echo BASE . IMAGES; ?>minis/pages.png" alt="" /> Pages</a></li>
					<li><a href="<?php echo BASE . ADMIN; ?>rights<?php echo URL_CAP; ?>"><img src="<?php echo BASE . IMAGES; ?>minis/rights.png" alt="" /> Rights</a></li>
					<?php
					
				}
				elseif(@TAB == 'settings'){
					?>
					<li><a href="<?php echo BASE . ADMIN; ?>users<?php echo URL_CAP; ?>"><img src="<?php echo BASE . IMAGES; ?>minis/users.png" alt="" /> Users</a></li>
					<li><a href="<?php echo BASE . ADMIN; ?>guests<?php echo URL_CAP; ?>"><img src="<?php echo BASE . IMAGES; ?>minis/guests.png" alt="" /> Guests</a></li>
					<li><a href="<?php echo BASE . ADMIN; ?>thumbnails<?php echo URL_CAP; ?>"><img src="<?php echo BASE . IMAGES; ?>minis/thumbnails.png" alt="" /> Thumbnails</a></li>
					<li><a href="<?php echo BASE . ADMIN; ?>themes<?php echo URL_CAP; ?>"><img src="<?php echo BASE . IMAGES; ?>minis/themes.png" alt="" /> Themes</a></li>
					<li><a href="<?php echo BASE . ADMIN; ?>extensions<?php echo URL_CAP; ?>"><img src="<?php echo BASE . IMAGES; ?>minis/extensions.png" alt="" /> Extensions</a></li>
					<li><a href="<?php echo BASE . ADMIN; ?>configuration<?php echo URL_CAP; ?>"><img src="<?php echo BASE . IMAGES; ?>minis/configuration.png" alt="" /> Configuration</a></li>
					<li><a href="<?php echo BASE . ADMIN; ?>maintenance<?php echo URL_CAP; ?>"><img src="<?php echo BASE . IMAGES; ?>minis/maintenance.png" alt="" /> Maintenance</a></li>
					<?php
				}
				
				
				?>
			</ul>
		</div>
		<div id="content" class="span-24 last">
			<?php if(!empty($alkaline)){ echo $alkaline->returnNotes(); } ?>
			