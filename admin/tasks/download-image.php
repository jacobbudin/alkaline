<?php

/*
// Alkaline
// Copyright (c) 2010-2012 by Budin Ltd. Some rights reserved.
// http://www.alkalineapp.com/
*/

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

// Get image
$id = intval($_REQUEST['id']);
if(empty($id)){ exit(); }
$images = new Image($id);
$image = $images->images[0];

// Set browser headers
if(ini_get('zlib.output_compression')){ ini_set('zlib.output_compression', 'Off'); }
header('Pragma: public');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s',  filemtime($image['image_file'])) . ' GMT');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Cache-Control: private',false);
header('Content-Disposition: attachment; filename="' . $image['image_name'] . '";');
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . filesize($image['image_file']));
header('Content-Type: ' . $image['image_mime']);
header('Content-Description: File Transfer');
readfile($image['image_file']);
exit();

?>