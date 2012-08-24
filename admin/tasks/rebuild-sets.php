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

$id = $alkaline->findID(@$_POST['image_id']);

if(empty($id)){
	$sets = new Find('sets');
	$sets->find();
	
	echo json_encode($sets->ids);
}
else{
	$set = new Set(intval($id));
	$set->rebuild();
}

?>