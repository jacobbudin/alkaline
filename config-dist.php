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

// Redirect to installation wizard
$dir = explode('/', $_SERVER['PHP_SELF']);
$dir = array_splice($dir, 1, -1);
if(!empty($dir)){
	$dir = '/' . implode('/', $dir);
}
else{
	$dir = '';
}
$url = 'http://' . $_SERVER['SERVER_NAME'] . $dir . '/install/';

header('Location: ' . $url);
exit();

?>