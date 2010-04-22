<?php

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'find.php');
require_once(PATH . CLASSES . 'import.php');
require_once(PATH . CLASSES . 'user.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

if(empty($_POST['photo_id'])){
	$photo_ids = new Find();
	$photo_ids->exec();
	echo json_encode($photo_ids->photo_ids);
}
else{
	$photo_id = intval($_POST['photo_id']);
	sleep(2);
	echo ' ';
}

?>