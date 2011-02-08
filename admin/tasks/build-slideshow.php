<?php

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;

$image_ids = new Find;
$image_ids->privacy('public', true);
$image_ids->find();

$images = new Image($image_ids);
$images->getSizes('medium');
echo json_encode($images);

?>