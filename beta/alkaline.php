<?php

// MODIFY THE DEFINITIONS BELOW

// Database data source name (DSN)
$db_dsn = 'mysql:host=localhost;dbname=alkaline';

// Database username
$db_user = 'alkaline';

// Database password
$db_pass = 'm902j2JK91kaO';

// Image extensions, separate by |
$img_ext = 'gif|GIF|jpg|JPG|jpeg|JPEG|png|PNG';

// Image resize quality, integer from 1 to 100 (80-90 recommended)
$img_qual = 85;

// Template extension
$temp_ext = '.html';

// Default limit (determines maximum number of photos per page, can be overwritten)
$limit = 20;

// Current theme
$theme = 'basic';


// DO NOT MODIFY BELOW THIS LINE

$path = $_SERVER['DOCUMENT_ROOT'] . '/';
define('PATH', $path);

// $base = substr($_SERVER['SCRIPT_NAME'], 0, strrpos($_SERVER['SCRIPT_NAME'], '/') + 1);
define('BASE', '/');

define('DB_DSN', $db_dsn);
define('DB_USER', $db_user);
define('DB_PASS', $db_pass);
define('IMG_EXT', $img_ext);
define('IMG_QUAL', $img_qual);
define('TEMP_EXT', $temp_ext);
define('LIMIT', $limit);
define('THEME', $theme);

define('ADMIN', 'admin/');
define('CLASSES', 'classes/');
define('CSS', 'css/');
define('FUNCTIONS', 'functions/');
define('JS', 'js/');
define('IMAGES', 'images/');
define('PHOTOS', 'photos/');
define('SHOEBOX', 'shoebox/');
define('THEMES', 'themes/');

?>