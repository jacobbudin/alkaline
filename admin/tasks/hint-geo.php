<?php

/*
// Alkaline
// Copyright (c) 2010-2012 by Budin Ltd. Some rights reserved.
// http://www.alkalineapp.com/
*/

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;

$hint = strip_tags($_GET['term']);

$geo = new Geo;
$places = $geo->hint($hint);

echo json_encode($places);

?>