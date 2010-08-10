<?php

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

if(empty($_POST['photo_file'])){
	$photo_files = $alkaline->seekDirectory();
	echo json_encode($photo_files);
}
else{
	$photo = new Photo();
	$photo->import($_POST['photo_file']);
	$photo = array('id' => $photo->photo_ids[0], 'ext' => $photo->photos[0]['photo_ext']);
	echo json_encode($photo);
}

?>