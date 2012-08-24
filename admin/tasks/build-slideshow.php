<?php

/*
// Alkaline
// Copyright (c) 2010-2012 by Budin Ltd. Some rights reserved.
// http://www.alkalineapp.com/
*/

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;

$image_ids = new Find('images');
$image_ids->privacy('public', true);
$image_ids->find();

$images = new Image($image_ids);
$images->getSizes('medium');
echo json_encode($images);

?>