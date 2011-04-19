<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="base" content="<?php echo LOCATION . BASE; ?>" />
	<meta name="folder_prefix" content="<?php echo FOLDER_PREFIX; ?>" />
	<meta name="permissions" content="<?php if(!empty($user) and $user->perm() and !empty($user->user['user_permissions'])){ echo @implode(', ', $user->user['user_permissions']); } ?>" />
	<title><?php echo (defined('TITLE') ? TITLE : 'Alkaline'); ?></title>
	<link rel="stylesheet" href="<?php echo BASE . ADMIN; ?>css/jqtouch/jqtouch.min.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo BASE . ADMIN; ?>css/jqtouch/theme.min.css" type="text/css" />
	<link rel="stylesheet" href="<?php echo BASE . ADMIN; ?>css/alkaline_mobile.css" type="text/css" />
	<link rel="shortcut icon" href="<?php echo BASE . ADMIN; ?>images/favicon.ico" />
	<script src="<?php echo BASE . JS; ?>jquery/jquery-1.5.2.min.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jqtouch.min.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery.json-2.2.min.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery.ajaxq-0.0.1.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery.sparkline.min.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>alkaline_mobile.js" type="text/javascript"></script>
</head>
<body id="alkaline">