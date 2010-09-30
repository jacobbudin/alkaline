<?php

// MODIFY THE DEFINITIONS BELOW

// Web site name
$site = 'Jacob Budin';

// Owner's name
$owner = 'Jacob Budin';

// Database data source name (DSN)
// $db_dsn = 'mysql:host=localhost;dbname=alkaline';
$db_dsn = 'sqlite:/var/www/vhosts/alkalineapp.com/beta/assets/alkaline5.db';

// Database user username
$db_user = 'alkaline';

// Database user password
$db_pass = 'm902j2JK91kaO';

// Image extensions, separate by |
$img_ext = 'gif|GIF|jpg|JPG|jpeg|JPEG|png|PNG';

// Length, an integer in seconds, to remember a user's previous login
$user_remember = 1209600;

// Template extension
$temp_ext = '.html';

// Default query limit (can be overwritten)
$limit = 20;

// Current theme
$theme = 'basic';

// Default user ID for shoebox uploads
$default_user_id = 1;

// Date formatting
$date_format = 'M j, Y \a\t g:i a';

// Palette size
$palette_size = 8;

// Color tolerance (higher numbers varies colors more)
$color_tolerance = 60;

// Watermark margin (pixels)
$watermark_margin = 10;

// Watermark transparency (percentage)
$watermark_transparency = 100;

// DO NOT MODIFY BELOW THIS LINE

define('PATH', $_SERVER['DOCUMENT_ROOT'] . '/');
define('BASE', '/');
define('DOMAIN', $_SERVER['SERVER_NAME']);
define('LOCATION', 'http://' . DOMAIN);

define('SITE', $site);
define('OWNER', $owner);
define('DB_DSN', $db_dsn);
define('DB_USER', $db_user);
define('DB_PASS', $db_pass);
define('IMG_EXT', $img_ext);
define('USER_REMEMBER', $user_remember);
define('TEMP_EXT', $temp_ext);
define('LIMIT', $limit);
define('THEME', $theme);
define('DEFAULT_USER_ID', $default_user_id);
define('DATE_FORMAT', $date_format);
define('PALETTE_SIZE', $palette_size);
define('COLOR_TOLERANCE', $color_tolerance);
define('WATERMARK_MARGIN', $watermark_margin);
define('WATERMARK_TRANSPARENCY', $watermark_transparency);

define('ADMIN', 'admin/');
define('ASSETS', 'assets/');
define('BLOCKS', 'blocks/');
define('CLASSES', 'classes/');
define('CSS', 'css/');
define('EXTENSIONS', 'extensions/');
define('FUNCTIONS', 'functions/');
define('JS', 'js/');
define('IMAGES', 'images/');
define('PHOTOS', 'photos/');
define('SHOEBOX', 'shoebox/');
define('THEMES', 'themes/');

?>