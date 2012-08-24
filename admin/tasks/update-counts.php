<?php

/*
// Alkaline
// Copyright (c) 2010-2012 by Budin Ltd. Some rights reserved.
// http://www.alkalineapp.com/
*/

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

if(empty($_POST['image_id'])){
	$image_ids = new Find('images');
	$image_ids->find();
	echo json_encode($image_ids->ids);
}
else{
	$alkaline->updateCount('comments', 'images', 'image_comment_count', $_POST['image_id']);
}

?>