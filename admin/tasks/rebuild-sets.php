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

$id = $alkaline->findID(@$_POST['image_id']);

if(empty($id)){
	$sets = $alkaline->getTable('sets');
	
	$set_ids = array();
	
	foreach($sets as $set){
		if($set['set_type'] == 'auto'){
			$set_ids[] = $set['set_id'];
		}
	}
	
	echo json_encode($set_ids);
}
else{
	$images = new Find('images');
	$images->sets(intval($id));
}

?>