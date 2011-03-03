<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalineapp.com/
*/

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
	$alkaline->updateCount('comments', 'images', 'image_comment_count', $_POST['image_id']);
}

?>