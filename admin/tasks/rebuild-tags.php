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
	$image_ids = new Find('images');
	$image_ids->find();
	echo json_encode($image_ids->ids);
}
else{
	$images = new Image($_POST['image_id']);
	$images->getTags();
	
	$now = date('Y-m-d H:i:s');
	$image_tags = implode('; ', $images->images[0]['image_tags_array']);
	
	$query = $alkaline->prepare('UPDATE images SET image_modified = :image_modified, image_tags = :image_tags, image_tag_count = :image_tag_count WHERE image_id = :image_id;');
	
	$query->execute(array(':image_modified' => $now, ':image_tags' => $image_tags, ':image_tag_count' => count($images->images[0]['image_tags_array']), ':image_id' => $images->images[0]['image_id']));
}

?>