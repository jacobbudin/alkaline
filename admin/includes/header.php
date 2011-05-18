<?php if(!empty($user) and $user->perm()){ $badges = $alkaline->getBadges(); } ?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="base" content="<?php echo LOCATION . BASE; ?>" />
	<meta name="folder_prefix" content="<?php echo FOLDER_PREFIX; ?>" />
	<meta name="permissions" content="<?php if(!empty($user) and $user->perm() and !empty($user->user['user_permissions'])){ echo @implode(', ', $user->user['user_permissions']); } ?>" />
	<title><?php echo (defined('TITLE') ? TITLE : 'Alkaline'); ?></title>
	<link rel="stylesheet" href="<?php echo BASE . ADMIN; ?>css/blueprint/screen.css" type="text/css" media="screen, projection" />
	<link rel="stylesheet" href="<?php echo BASE . ADMIN; ?>css/blueprint/print.css" type="text/css" media="print" />	
	<!--[if lt IE 8]><link rel="stylesheet" href="<?php echo BASE . ADMIN; ?>css/blueprint/ie.css" type="text/css" media="screen, projection" /><![endif]-->
	<link rel="stylesheet" href="<?php echo BASE . ADMIN; ?>css/formalize.css" type="text/css" media="screen, projection" />
	<link rel="stylesheet" href="<?php echo BASE . ADMIN; ?>css/jquery-ui/jquery-ui-1.8.7.custom.css" type="text/css" media="screen, projection" />
	<link rel="stylesheet" href="<?php echo BASE . ADMIN; ?>css/alkaline.css" type="text/css" media="screen, projection" />
	<!--[if IE]><script language="javascript" type="text/javascript" src="<?php echo BASE . JS; ?>jquery/excanvas.min.js"></script><![endif]-->
	<link rel="shortcut icon" href="<?php echo BASE . ADMIN; ?>images/favicon.ico" />
	<script src="<?php echo BASE . JS; ?>jquery/jquery-1.5.2.min.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery-ui-1.8.12.custom.min.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery.json-2.2.min.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery.flot.min.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery.html5_upload.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery.ajaxq-0.0.1.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery.caret-range-1.0.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery.formalize.min.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery.tiptip.min.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>alkaline.js?965" type="text/javascript"></script>
</head>
<body id="alkaline">
	<div id="header_holder">
		<div class="container">
			<div id="userbar" class="span-24 right">
				<?php
				if(!empty($user) and $user->perm()){
					?>
					<div>
						<strong>
							<img src="<?php echo BASE . ADMIN; ?>images/icons/home_top.png" alt="" />
							<?php $title = $alkaline->returnConf('web_title'); echo (!empty($title) ? $title : ''); ?>
						</strong> &#0160;
						<a href="<?php echo BASE; ?>" target="<?php if($user->readPref('home_target')){ echo '_blank'; } ?>">Launch</a>
					</div>
					
					<div>
						<strong>
							<img src="<?php echo BASE . ADMIN; ?>images/icons/user_top.png" alt="" />
							<?php echo $user->user['user_user']; ?>
						</strong> &#0160;
						<a href="<?php echo BASE . ADMIN . 'search' . URL_ACT . 'me' . URL_RW; ?>">My Images</a>,
						<a href="<?php echo BASE . ADMIN . 'posts' . URL_ACT . 'me' . URL_RW; ?>">Posts</a>,
						<a href="<?php echo BASE . ADMIN . 'comments' . URL_ACT . 'me' . URL_RW; ?>">Comments</a> &#0160;
						<a href="<?php echo BASE . ADMIN . 'preferences' . URL_CAP; ?>">Preferences</a> &#0160;
						<a href="<?php echo BASE . ADMIN . 'logout' . URL_CAP; ?>">Logout</a>
					</div>
					<?php
				}
				?>
			</div>
			<div id="header" class="span-24 last">
				<div class="span-6 append-1">
					<a href="<?php echo BASE . ADMIN; ?>"><img src="<?php echo BASE . ADMIN; ?>images/shutter.png" alt="Alkaline" /></a>
				</div>
				<div id="panels" class="span-17 last">
					<?php
					if(!empty($user) and $user->perm()){
						?>
						<div id="search_panel" class="span-17 append-1">
							<form action="<?php echo BASE . ADMIN . 'search' . URL_CAP; ?>" method="post">
								<select name="search_type" id="search_type">
									<option value="images">Images</option>
									<option value="posts">Posts</option>
									<option value="comments">Comments</option>
								</select>
								<input type="search" name="q" results="10" />
								<input type="submit" value="Search" />
							</form>
							<!-- <a href="<?php echo BASE . ADMIN . 'library' . URL_CAP; ?>#advanced" class="advanced_link">Advanced Search</a> -->
						</div>
						<?php
					}
					?>
				</div>
			</div>
			<div id="navigation" class="span-24 last">
				<ul>
					<?php

					if(@!defined('TAB') or (@TAB == 'dashboard') or (@TAB == 'upload') or (@TAB == 'shoebox') or (@TAB == 'library') or (@TAB == 'posts') or (@TAB == 'comments') or (@TAB == 'features') or (@TAB == 'settings')){
						?>
						<li id="tab_dashboard">
							<a href="<?php echo BASE . ADMIN . 'dashboard' . URL_CAP; ?>"<?php if(@TAB == 'dashboard'){ echo ' class="selected"'; } ?>>Dashboard &#9662;</a>
							<ul>
								<li id="sub_statistics"><a href="<?php echo BASE . ADMIN; ?>statistics<?php echo URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/stats.png" alt="" /> Statistics</a></li>
								<li id="sub_preferences"><a href="<?php echo BASE . ADMIN; ?>preferences<?php echo URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/preferences.png" alt="" /> Preferences</a></li>
							</ul>
						</li>
						<?php if(($badges['images'] > 0) or ($badges['posts'] > 0)){ ?>
						<li id="tab_shoebox" class="red">
							<a href="<?php echo BASE . ADMIN . 'shoebox' . URL_CAP; ?>"<?php if(@TAB == 'shoebox'){ echo ' class="selected"'; } ?>>Shoebox</a>
						</li>
						<?php } else{ ?>
						<li id="tab_upload">
							<a href="<?php echo BASE . ADMIN . 'upload' . URL_CAP; ?>"<?php if(@TAB == 'upload'){ echo ' class="selected"'; } ?>>Upload</a>
						</li>
						<?php } ?>
						<li id="tab_library">
							<a href="<?php echo BASE . ADMIN . 'library' . URL_CAP; ?>"<?php if(@TAB == 'library'){ echo ' class="selected"'; } ?>>Images</a>
							<ol>
								<?php
								if($badges['images'] > 0){
									echo '<li><a href="' . BASE . ADMIN . 'shoebox' . URL_CAP . '">' . $badges['images'] . '</a></li>';
								}
								?>
							</ol>
						</li>
						<li id="tab_posts">
							<a href="<?php echo BASE . ADMIN . 'posts' . URL_CAP; ?>"<?php if(@TAB == 'posts'){ echo ' class="selected"'; } ?>>Posts</a>
							<ol>
								<?php
								if($badges['posts'] > 0){
									echo '<li><a href="' . BASE . ADMIN . 'shoebox' . URL_CAP . '">' . $badges['posts'] . '</a></li>';
								}
								?>
							</ol>
						</li>
						<li id="tab_comments">
							<a href="<?php echo BASE . ADMIN . 'comments' . URL_CAP; ?>"<?php if(@TAB == 'comments'){ echo ' class="selected"'; } ?>>Comments</a>
							<ol>
								<?php
								if($badges['comments'] > 0){
									echo '<li><a href="' . BASE . ADMIN . 'comments' . URL_ACT . 'new' .  URL_RW . '">' . $badges['comments'] . '</a></li>';
								}
								?>
							</ol>
						</li>
						<li id="tab_features">
							<a href="<?php echo BASE . ADMIN . 'features' . URL_CAP; ?>"<?php if(@TAB == 'features'){ echo ' class="selected"'; } ?>>Editor <span>&#9662;</span></a>
							<ul>
								<li id="sub_tags"><a href="<?php echo BASE . ADMIN . 'tags' . URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/tags.png" alt="" /> Tags</a></li>
								<li id="sub_sets"><a href="<?php echo BASE . ADMIN. 'sets' . URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/sets.png" alt="" /> Sets</a></li>
								<li id="sub_pages"><a href="<?php echo BASE . ADMIN . 'pages' . URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/pages.png" alt="" /> Pages</a></li>
								<li id="sub_rights"><a href="<?php echo BASE . ADMIN . 'rights' . URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/rights.png" alt="" /> Rights</a></li>
							</ul>
						</li>
						<li id="tab_settings">
							<a href="<?php echo BASE . ADMIN . 'settings' . URL_CAP; ?>"<?php if(@TAB == 'settings'){ echo ' class="selected"'; } ?>>Settings <span>&#9662;</span></a>
							<ul>
								<li id="sub_users"><a href="<?php echo BASE . ADMIN . 'users' . URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/users.png" alt="" /> Users</a></li>
								<li id="sub_guests"><a href="<?php echo BASE . ADMIN . 'guests' . URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/guests.png" alt="" /> Guests</a></li>
								<li id="sub_thumbnails"><a href="<?php echo BASE . ADMIN . 'thumbnails' . URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/thumbnails.png" alt="" /> Thumbnails</a></li>
								<li id="sub_themes"><a href="<?php echo BASE . ADMIN . 'themes' . URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/themes.png" alt="" /> Themes</a></li>
								<li id="sub_extensions"><a href="<?php echo BASE . ADMIN . 'extensions' . URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/extensions.png" alt="" /> Extensions</a></li>
								<li id="sub_configuration"><a href="<?php echo BASE . ADMIN . 'configuration' . URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/configuration.png" alt="" /> Configuration</a></li>
								<li id="sub_maintenance"><a href="<?php echo BASE . ADMIN . 'maintenance' . URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/minis/maintenance.png" alt="" /> Maintenance</a></li>
							</ul>
						</li>
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
		<div id="content" class="span-24 last">
			<?php if(!empty($alkaline)){ echo $alkaline->returnNotes(); } ?>