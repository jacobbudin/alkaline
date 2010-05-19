<?php

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'photo.php');
require_once(PATH . CLASSES . 'user.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

if(!empty($_POST['photo_id'])){
	$alkaline->convertToIntegerArray($_POST['photo_id']);
	$photo = new Photo($_POST['photo_id']);
	$photo->delete();
}

?>