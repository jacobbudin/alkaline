<?php

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

if(!empty($_POST['image_id'])){
	$alkaline->convertToIntegerArray($_POST['image_id']);
	$image = new Image($_POST['image_id']);
	$image->delete();
}

?>