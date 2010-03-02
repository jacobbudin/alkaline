<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php if(defined('TITLE')){ echo TITLE; } else{ echo 'Alkaline'; } ?></title>
	<link rel="stylesheet" href="/css/blueprint/screen.css" type="text/css" media="screen, projection" />
	<link rel="stylesheet" href="/css/blueprint/print.css" type="text/css" media="print" />	
	<!--[if lt IE 8]><link rel="stylesheet" href="/css/blueprint/ie.css" type="text/css" media="screen, projection" /><![endif]-->
	<link rel="stylesheet" href="/css/alkaline.css" type="text/css" media="screen, projection" />
	<style type="text/css">
		.container { width: <?php if(defined('WIDTH')){ echo WIDTH; } else{ echo '750'; } ?>px; }	
		#content { background-image: url(<?php echo BASE . 'images/block-'; if(defined('WIDTH')){ echo WIDTH; } else{ echo '750'; } ?>.png); }
	</style>
</head>
<body>
	<div class="container">
		<div id="header" class="span-<?php echo COLUMNS; ?> last">
			<p style="float: left;"><a href="<?php echo BASE . ADMIN; ?>"><img src="<?php echo BASE . IMAGES; ?>shutter.png" alt="" /></a></p>
			<?php
			if($user->perm()){
			?>
				<div id="user">You are logged in as: <?php echo $user->user['user_name']; ?>. &#0160; <a href="<?php echo BASE . 'admin/logout/'; ?>" class="button_red">Logout</a></div>
			<?php
			}
			?>
		</div>