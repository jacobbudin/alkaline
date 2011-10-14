<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalineapp.com/
*/

/*
// Use the installation wizard (recommended), or replace this file with /install/config.php to manually edit the configuration
*/

// Auto-determinate
if($_SERVER['SCRIPT_FILENAME'][0] == '/'){
	define('SERVER_TYPE', '');
	$path = explode('/', __FILE__);
	$path = array_splice($path, 0, -1);
	$path = implode('/', $path);
	define('PATH', $path . '/');
}
else{
	define('SERVER_TYPE', 'win');
	$path = explode('\\', __FILE__);
	$path_new = array_splice($path, 1, -1);
	$path_new = implode('\\', $path_new);
	define('PATH', $path[0] . '\\' . $path_new . '\\');
}

preg_match_all('#(?:/)?(.*)/(?:.*)install#si', $_SERVER['SCRIPT_NAME'], $matches);
if(!empty($matches[1][0])){
	$dir = $matches[1][0] . '/';
}
else{
	$dir = '';
}

define('BASE', '/' . $dir);
define('FOLDER_PREFIX', '');

if(SERVER_TYPE == 'win'){
	define('ADMIN', FOLDER_PREFIX . 'admin\\');
	define('CACHE', FOLDER_PREFIX . 'cache\\');
	define('CLASSES', FOLDER_PREFIX . 'classes\\');
	define('DB', FOLDER_PREFIX . 'db\\');
	define('EXTENSIONS', FOLDER_PREFIX . 'extensions\\');
	define('FUNCTIONS', FOLDER_PREFIX . 'functions\\');
	define('INCLUDES', FOLDER_PREFIX . 'includes\\');
	define('JS', FOLDER_PREFIX . 'js\\');
	define('IMAGES', FOLDER_PREFIX . 'images\\');
	define('INSTALL', FOLDER_PREFIX . 'install\\');
	define('SHOEBOX', FOLDER_PREFIX . 'shoebox\\');
	define('THEMES', FOLDER_PREFIX . 'themes\\');
}
else{
	define('ADMIN', FOLDER_PREFIX . 'admin/');
	define('CACHE', FOLDER_PREFIX . 'cache/');
	define('CLASSES', FOLDER_PREFIX . 'classes/');
	define('DB', FOLDER_PREFIX . 'db/');
	define('EXTENSIONS', FOLDER_PREFIX . 'extensions/');
	define('FUNCTIONS', FOLDER_PREFIX . 'functions/');
	define('INCLUDES', FOLDER_PREFIX . 'includes/');
	define('JS', FOLDER_PREFIX . 'js/');
	define('IMAGES', FOLDER_PREFIX . 'images/');
	define('INSTALL', FOLDER_PREFIX . 'install/');
	define('SHOEBOX', FOLDER_PREFIX . 'shoebox/');
	define('THEMES', FOLDER_PREFIX . 'themes/');	
}

?>