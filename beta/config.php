<?php

// MODIFY THE DEFINITIONS BELOW

// Web site name
$site = 'Jacob Budin\'s Photos';

// Owner's name
$owner = 'Jacob Budin';

// Database data source name (DSN)
$db_dsn = 'mysql:host=localhost;dbname=alkaline';

// Database user username
$db_user = 'alkaline';

// Database user password
$db_pass = 'm902j2JK91kaO';

// Image extensions, separate by |
$img_ext = 'gif|GIF|jpg|JPG|jpeg|JPEG|png|PNG';

// Image resize quality, integer from 1 to 100, higher is better (80-95 recommended)
$img_qual = 85;

// Length, an integer in seconds, to remember a user's previous login
$user_remember = 1209600;

// Template extension
$temp_ext = '.html';

// Default query limit (can be overwritten)
$limit = 20;

// Current theme
$theme = 'basic';

// Time zone
$time_zone = 'America/New_York';

// Default user ID for shoebox uploads
$default_user_id = 1;

// Default view type (grid or list)
$default_view_type = 'grid';

// Date formatting
$date_format = 'M j, Y \a\t g:i a';


// DO NOT MODIFY BELOW THIS LINE

date_default_timezone_set($time_zone);

define('PATH', $_SERVER['DOCUMENT_ROOT'] . '/');
define('BASE', '/');
define('SERVER', $_SERVER['SERVER_NAME']);
define('DOMAIN', 'http://' . SERVER);

define('SITE', $site);
define('OWNER', $owner);
define('DB_DSN', $db_dsn);
define('DB_USER', $db_user);
define('DB_PASS', $db_pass);
define('IMG_EXT', $img_ext);
define('IMG_QUAL', $img_qual);
define('USER_REMEMBER', $user_remember);
define('TEMP_EXT', $temp_ext);
define('LIMIT', $limit);
define('THEME', $theme);
define('TIME_ZONE', $time_zone);
define('DEFAULT_USER_ID', $default_user_id);
define('DEFAULT_VIEW_TYPE', $default_view_type);
define('DATE_FORMAT', $date_format);

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