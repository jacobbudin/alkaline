<?php

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$markup = $alkaline->returnConf('photo_markup_ext');

$query = $alkaline->prepare('SELECT photo_id FROM photos WHERE photo_markup != :photo_markup;');
$query->execute(array(':photo_markup' => $markup));
$photos = $query->fetchAll();

$photo_ids = array();

foreach($photos as $photo){
	$photo_ids[] = $photo['photo_id'];
}

if(count($photo_ids) > 0){
	$query = $alkaline->prepare('UPDATE photos SET photo_description_raw = photo_description, photo_markup = :photo_markup WHERE (photo_id IN (' . implode(', ', $photo_ids) . '));');
	$query->execute(array(':photo_markup' => $markup));
}

?>