<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="base" content="<?php echo LOCATION . BASE; ?>" />
	<meta name="folder_prefix" content="<?php echo FOLDER_PREFIX; ?>" />
	<meta name="permissions" content="<?php if(!empty($user) and $user->perm()){ echo @implode(', ', $user->user['user_permissions']); } ?>" />
	<title><?php echo (defined('TITLE') ? TITLE : 'Alkaline'); ?></title>
	<link rel="stylesheet" href="<?php echo BASE . ADMIN; ?>css/blueprint/screen.css" type="text/css" media="screen, projection" />
	<link rel="stylesheet" href="<?php echo BASE . ADMIN; ?>css/blueprint/print.css" type="text/css" media="print" />	
	<!--[if lt IE 8]><link rel="stylesheet" href="<?php echo BASE . ADMIN; ?>css/blueprint/ie.css" type="text/css" media="screen, projection" /><![endif]-->
	<link rel="stylesheet" href="<?php echo BASE . ADMIN; ?>css/alkaline.css" type="text/css" media="screen, projection" />
	<link rel="stylesheet" href="<?php echo BASE . ADMIN; ?>css/smoothness/jquery-ui-1.8.11.custom.css" type="text/css" media="screen, projection" />
	<!--[if IE]><script language="javascript" type="text/javascript" src="<?php echo BASE . JS; ?>jquery/excanvas.min.js"></script><![endif]-->
	<link rel="shortcut icon" href="<?php echo BASE . ADMIN; ?>images/favicon.ico" />
	<script src="<?php echo BASE . JS; ?>jquery/jquery-1.5.2.min.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery-ui-1.8.11.custom.min.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery.json-2.2.min.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery.flot.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery.html5_upload.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery.ajaxq-0.0.1.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery.caret-range-1.0.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>alkaline.js" type="text/javascript"></script>
</head>
<body id="alkaline">
	<div id="header_holder">
		<div class="container">
			<div id="header" class="span-24 last">
				<div class="span-6 append-1">
					<a href="<?php echo BASE . ADMIN; ?>"><img src="<?php echo BASE . ADMIN; ?>images/shutter.png" alt="Alkaline" /></a>
				</div>
				<div id="panels" class="span-17 last">
					<?php
					if(!empty($user) and $user->perm()){
						?>
						<div id="search_panel" class="span-5 append-1">
							<form action="<?php echo BASE . ADMIN . 'search' . URL_CAP; ?>" method="post">
								<input type="search" name="q" results="10" /><br /><a href="<?php echo BASE . ADMIN . 'library' . URL_CAP; ?>#advanced" class="advanced_link">Advanced Search</a>
							</form>
						</div>
						<div id="user_panel" class="span-5 append-1">
							<strong><img src="<?php echo BASE . ADMIN; ?>images/icons/user.png" alt="" /> <?php echo $user->user['user_user']; ?></strong><br />
							<span class="small">
								<a href="<?php echo BASE . ADMIN . 'search' . URL_ACT . 'me' . URL_RW; ?>">My Uploads</a> &#0183;
								<a href="<?php echo BASE . ADMIN . 'preferences' . URL_CAP; ?>">Preferences</a> &#0183;
								<a href="<?php echo BASE . ADMIN . 'logout' . URL_CAP; ?>">Logout</a>
							</span>
						</div>
						<div id="site_panel" class="span-5 last">
							<strong><img src="<?php echo BASE . ADMIN; ?>images/icons/home.png" alt="" /> <?php echo ((BASE == '/') ? $alkaline->minimizeURL(DOMAIN) : $alkaline->minimizeURL(DOMAIN . BASE) ); ?></strong><br />
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

					if(@!defined('TAB') or (@TAB == 'dashboard') or (@TAB == 'library') or (@TAB == 'posts') or (@TAB == 'comments') or (@TAB == 'features') or (@TAB == 'settings')){
						?>
						<li id="tab_dashboard"><a href="<?php echo BASE . ADMIN; ?>dashboard<?php echo URL_CAP; ?>"<?php if(@TAB == 'dashboard'){ echo ' class="selected"'; } ?>>Dashboard</a></li>
						<li id="tab_library"><a href="<?php echo BASE . ADMIN; ?>library<?php echo URL_CAP; ?>"<?php if(@TAB == 'library'){ echo ' class="selected"'; } ?>>Library</a></li>
						<li id="tab_posts"><a href="<?php echo BASE . ADMIN; ?>posts<?php echo URL_CAP; ?>"<?php if(@TAB == 'posts'){ echo ' class="selected"'; } ?>>Posts</a></li>
						<li id="tab_comments"><a href="<?php echo BASE . ADMIN; ?>comments<?php echo URL_CAP; ?>"<?php if(@TAB == 'comments'){ echo ' class="selected"'; } ?>>Comments</a></li>
						<li id="tab_features"><a href="<?php echo BASE . ADMIN; ?>features<?php echo URL_CAP; ?>"<?php if(@TAB == 'features'){ echo ' class="selected"'; } ?>>Editor</a></li>
						<li id="tab_settings"><a href="<?php echo BASE . ADMIN; ?>settings<?php echo URL_CAP; ?>"<?php if(@TAB == 'settings'){ echo ' class="selected"'; } ?>>Settings</a></li>
						<li id="tab_help"><a href="http://www.alkalineapp.com/guide/" target="_blank">Help</a></li>
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
					<li id="sub_statistics"><a href="<?php echo BASE . ADMIN; ?>statistics<?php echo URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/stats.png" alt="" /> Statistics</a></li>
					<li id="sub_preferences"><a href="<?php echo BASE . ADMIN; ?>preferences<?php echo URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/preferences.png" alt="" /> Preferences</a></li>
					<?php
				}
				elseif(@TAB == 'library'){
					?>
					<li id="sub_upload"><a href="<?php echo BASE . ADMIN; ?>upload<?php echo URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/upload.png" alt="" /> Upload</a></li>
					<li id="sub_shoebox"><a href="<?php echo BASE . ADMIN; ?>shoebox<?php echo URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/shoebox.png" alt="" /> Shoebox</a></li>
					<?php
				}
				elseif(@TAB == 'posts'){
					?>
					
					<?php
				}
				elseif(@TAB == 'comments'){
					?>
					
					<?php
				}
				elseif(@TAB == 'features'){
					?>
					<li id="sub_tags"><a href="<?php echo BASE . ADMIN; ?>tags<?php echo URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/tags.png" alt="" /> Tags</a></li>
					<li id="sub_sets"><a href="<?php echo BASE . ADMIN; ?>sets<?php echo URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/sets.png" alt="" /> Sets</a></li>
					<li id="sub_pages"><a href="<?php echo BASE . ADMIN; ?>pages<?php echo URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/pages.png" alt="" /> Pages</a></li>
					<li id="sub_rights"><a href="<?php echo BASE . ADMIN; ?>rights<?php echo URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/rights.png" alt="" /> Rights</a></li>
					<?php
					
				}
				elseif(@TAB == 'settings'){
					?>
					<li id="sub_users"><a href="<?php echo BASE . ADMIN; ?>users<?php echo URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/users.png" alt="" /> Users</a></li>
					<li id="sub_guests"><a href="<?php echo BASE . ADMIN; ?>guests<?php echo URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/guests.png" alt="" /> Guests</a></li>
					<li id="sub_thumbnails"><a href="<?php echo BASE . ADMIN; ?>thumbnails<?php echo URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/thumbnails.png" alt="" /> Thumbnails</a></li>
					<li id="sub_themes"><a href="<?php echo BASE . ADMIN; ?>themes<?php echo URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/themes.png" alt="" /> Themes</a></li>
					<li id="sub_extensions"><a href="<?php echo BASE . ADMIN; ?>extensions<?php echo URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/extensions.png" alt="" /> Extensions</a></li>
					<li id="sub_configuration"><a href="<?php echo BASE . ADMIN; ?>configuration<?php echo URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/configuration.png" alt="" /> Configuration</a></li>
					<li id="sub_maintenance"><a href="<?php echo BASE . ADMIN; ?>maintenance<?php echo URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/maintenance.png" alt="" /> Maintenance</a></li>
					<?php
				}
				
				
				?>
			</ul>
		</div>
		<div id="content" class="span-24 last">
			<?php if(!empty($alkaline)){ echo $alkaline->returnNotes(); } ?>
			