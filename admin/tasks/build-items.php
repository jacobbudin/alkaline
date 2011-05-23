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

$user->perm(true, 'maintenance');

$id = $alkaline->findID(@$_POST['image_id']);

if(empty($id)){
	$ids = array();
	
	$alkaline->exec('DELETE FROM items WHERE item_id IS NOT NULL');
	
	foreach($alkaline->tables_index as $key => $value){
		$ids[] = ++$key;
	}
	
	echo json_encode($ids);
}
else{
	$ids = new Find($alkaline->tables_index[--$id]);
	$ids->find();
	
	foreach($ids->ids as $item_id){
		$fields = array('item_table' => $alkaline->tables_index[$id],
			'item_table_id' => $item_id);
		$alkaline->addRow($fields, 'items');
	}
}

?>