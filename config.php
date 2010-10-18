<?php

//
// MODIFY THE DEFINITIONS BELOW
//

// Server type
$server_type = 'unix';

// Database data source name (DSN)
$db_dsn = 'mysql:host=localhost;dbname=alkaline';
// $db_dsn = 'mysql:host=my01.winhost.com;dbname=mysql_14786_alkaline';
// $db_dsn = 'sqlite:/var/www/vhosts/alkalineapp.com/beta/assets/alkaline5.db';
// $db_dsn = 'pgsql:dbname=alkaline';
// $db_dsn = 'odbc:Driver=FreeTDS;Server=s03.winhost.com;Database=DB_14786_alkaline;Uid=DB_14786_alkaline_user;Pwd=m902j2JK91kaO;';
// $db_dsn = 'odbc:Driver=FreeTDS;Server=s03.winhost.com;Database=DB_14786_alkaline;Uid=DB_14786_alkaline_user;Pwd=m902j2JK91kaO;';

// If Microsoft SQL Server, use value "mssql"
$db_type = 'mysql';

// Database user username (not needed for SQLite or ODBC connections)
$db_user = 'alkaline';

// Database user password (not needed for SQLite or ODBC connections)
$db_pass = 'm902j2JK91kaO';

// Add a prefix to all database names
$table_prefix = '';

// Add a prefix to all primary directories
$folder_prefix = '';


//
// DO NOT MODIFY BELOW THIS LINE
//

// Valid extensions, separate by |
$img_ext = 'gif|GIF|jpg|JPG|jpeg|JPEG|png|PNG';

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

// URL rewriting (Apache mod_rewrite, Microsoft URL Rewrite 2, or compatible)
$url_rewrite = true;


if(empty($db_type)){
	$db_type = substr(DB_DSN, 0, strpos(DB_DSN, ':'));
}

if($url_rewrite){
	define('URL_CAP', '/');
	define('URL_ID', '/');
	define('URL_ACT', '/');
	define('URL_AID', '/');
	define('URL_RW', '/');
}
else{
	define('URL_CAP', '.php');
	define('URL_ID', '.php?id=');
	define('URL_ACT', '.php?act=');
	define('URL_AID', '&id=');
	define('URL_RW', '');
}

if($server_type == 'win'){
	define('PATH', $_SERVER['DOCUMENT_ROOT'] . '\\');
}
else{
	define('PATH', $_SERVER['DOCUMENT_ROOT'] . '/');
}

define('BASE', '/');
define('DOMAIN', $_SERVER['SERVER_NAME']);
define('LOCATION', 'http://' . DOMAIN);

define('SERVER_TYPE', $server_type);
define('DB_DSN', $db_dsn);
define('DB_TYPE', $db_type);
@define('DB_USER', $db_user);
@define('DB_PASS', $db_pass);
define('TABLE_PREFIX', $table_prefix);
define('FOLDER_PREFIX', $folder_prefix);
define('IMG_EXT', $img_ext);
define('USER_REMEMBER', $user_remember);
define('TEMP_EXT', $temp_ext);
define('LIMIT', $limit);
define('DATE_FORMAT', $date_format);
define('PALETTE_SIZE', $palette_size);
define('COLOR_TOLERANCE', $color_tolerance);

define('ADMIN', FOLDER_PREFIX . 'admin/');
define('ASSETS', FOLDER_PREFIX . 'assets/');
define('BLOCKS', FOLDER_PREFIX . 'blocks/');
define('CLASSES', FOLDER_PREFIX . 'classes/');
define('CSS', FOLDER_PREFIX . 'css/');
define('EXTENSIONS', FOLDER_PREFIX . 'extensions/');
define('FUNCTIONS', FOLDER_PREFIX . 'functions/');
define('JS', FOLDER_PREFIX . 'js/');
define('IMAGES', FOLDER_PREFIX . 'images/');
define('PHOTOS', FOLDER_PREFIX . 'photos/');
define('SHOEBOX', FOLDER_PREFIX . 'shoebox/');
define('THEMES', FOLDER_PREFIX . 'themes/');

?>