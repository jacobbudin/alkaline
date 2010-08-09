<?php

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'photo.php');
require_once(PATH . CLASSES . 'user.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

if(empty($_POST['photo_file'])){
	$photo_files = $alkaline->seekDirectory(PATH . SHOEBOX);
	echo json_encode($photo_files);
}
else{
	$photo = new Photo($_POST['photo_file']);
	$photo = array('id' => $photo->photo_ids[0], 'ext' => $photo->photos[0]['photo_ext']);
	echo json_encode($photo);
}

?>