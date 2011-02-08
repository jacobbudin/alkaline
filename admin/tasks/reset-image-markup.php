<?php

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$markup = $alkaline->returnConf('image_markup_ext');

$query = $alkaline->prepare('SELECT image_id FROM images WHERE image_markup != :image_markup;');
$query->execute(array(':image_markup' => $markup));
$images = $query->fetchAll();

$image_ids = array();

foreach($images as $image){
	$image_ids[] = $image['image_id'];
}

if(count($image_ids) > 0){
	$query = $alkaline->prepare('UPDATE images SET image_description_raw = image_description, image_markup = :image_markup WHERE (image_id IN (' . implode(', ', $image_ids) . '));');
	$query->execute(array(':image_markup' => $markup));
}

?>