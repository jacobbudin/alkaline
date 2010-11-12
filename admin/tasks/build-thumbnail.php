<?php

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

if(!empty($_SESSION['alkaline']['maintenance']['size_id'])){
	if(empty($_POST['photo_id'])){
		$photo_ids = new Find();
		$photo_ids->find();
		echo json_encode($photo_ids->photo_ids);
	}
	else{
		$photo = new Photo($_POST['photo_id']);
		$photo->sizePhoto(null, intval($_SESSION['alkaline']['maintenance']['size_id']));
	}
}

?>