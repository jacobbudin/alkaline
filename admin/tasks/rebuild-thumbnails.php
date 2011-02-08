<?php

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

if(empty($_POST['image_id'])){
	$image_ids = new Find();
	$image_ids->find();
	echo json_encode($image_ids->image_ids);
}
else{
	$image = new Image($_POST['image_id']);
	$image->deSizeImage();
	$image->sizeImage();
}

?>