<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalinenapp.com/
*/

//
// MODIFY THE DEFINITIONS BELOW
//

// Server type
$server_type = '';

// Folder path
$path = '/var/www/vhosts/alkalineapp.com/beta/';

// URL base path (if installed at top-level of domain, use '/')
$base = '/';

// Database data source name (DSN including protocol)
$db_dsn = 'mysql:host=localhost;dbname=alkaline';

// Database type (DSN protocol)
$db_type = 'mysql';

// Database user username (leave empty for SQLite)
$db_user = 'alkaline';

// Database user password (leave empty for SQLite)
$db_pass = '212021fbyu';

// Database table prefix
$table_prefix = '';

// Alkaline subdirectory prefix
$folder_prefix = '';

// URL rewriting (supports Apache mod_rewrite, Microsoft IIS7 URL Rewrite 2, and compatible)
$url_rewrite = false;

// Global password salt
$salt = '';


//
// DO NOT MODIFY BELOW THIS LINE
//

// Valid extensions, separate by |
$img_ext = 'gif|jpg|jpeg|png|pdf|svg';

// Length, an integer in seconds, to remember a user's previous login
$user_remember = 1209600;

// Template extension
$temp_ext = '.html';

// Default query limit (can be overwritten)
$limit = 20;

// Date formatting
$date_format = 'M j, Y \a\t g:i a';

// Palette size
$palette_size = 8;

// Color tolerance (higher numbers varies colors more)
$color_tolerance = 60;


if($url_rewrite){
	define('URL_CAP', '/');
	define('URL_ID', '/');
	define('URL_ACT', '/');
	define('URL_AID', '/');
	define('URL_PAGE', '/');
	define('URL_RW', '/');
}
else{
	define('URL_CAP', '.php');
	define('URL_ID', '.php?id=');
	define('URL_ACT', '.php?act=');
	define('URL_PAGE', '.php?page=');
	define('URL_AID', '&id=');
	define('URL_RW', '');
}

define('SERVER_TYPE', $server_type);
define('PATH', $path);
define('BASE', $base);
define('DOMAIN', $_SERVER['SERVER_NAME']);
define('LOCATION', 'http://' . DOMAIN);
define('DB_DSN', $db_dsn);
define('DB_TYPE', $db_type);
@define('DB_USER', $db_user);
@define('DB_PASS', $db_pass);
define('TABLE_PREFIX', $table_prefix);
define('FOLDER_PREFIX', $folder_prefix);
define('SALT', $salt);
define('IMG_EXT', $img_ext);
define('USER_REMEMBER', $user_remember);
define('TEMP_EXT', $temp_ext);
define('LIMIT', $limit);
define('DATE_FORMAT', $date_format);
define('PALETTE_SIZE', $palette_size);
define('COLOR_TOLERANCE', $color_tolerance);

define('ADMIN', FOLDER_PREFIX . 'admin/');
define('CLASSES', FOLDER_PREFIX . 'classes/');
define('CSS', FOLDER_PREFIX . 'css/');
define('DB', FOLDER_PREFIX . 'db/');
define('EXTENSIONS', FOLDER_PREFIX . 'extensions/');
define('FUNCTIONS', FOLDER_PREFIX . 'functions/');
define('INCLUDES', FOLDER_PREFIX . 'includes/');
define('JS', FOLDER_PREFIX . 'js/');
define('IMAGES', ADMIN . 'images/');
define('INSTALL', FOLDER_PREFIX . 'install/');
define('PHOTOS', FOLDER_PREFIX . 'photos/');
define('SHOEBOX', FOLDER_PREFIX . 'shoebox/');
define('THEMES', FOLDER_PREFIX . 'themes/');

?>