<?php

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

if(!empty($_POST['photo_id'])){
	$alkaline->convertToIntegerArray($_POST['photo_id']);
	$photo = new Photo($_POST['photo_id']);
	$fields = array('photo_title' => $_POST['photo_title'],
		'photo_description' => $_POST['photo_description']);
	$photo->updateFields($fields);
}

?>