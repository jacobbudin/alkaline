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
	
	// $alkaline->exec('DELETE FROM items WHERE item_id IS NOT NULL');
	
	foreach($alkaline->tables_index as $key => $value){
		$ids[] = ++$key;
	}
	
	echo json_encode($ids);
}
else{
	$table = $alkaline->tables_index[--$id];
	
	$ids = new Find($table);
	$ids->find();
	
	$query = $alkaline->prepare('SELECT item_table_id FROM items WHERE item_table = :item_table;');
	$query->execute(array(':item_table' => $table));
	$items = $query->fetchAll();
	
	$item_table_ids = array();
	
	foreach($items as $item){
		$item_table_ids[] = $item['item_table_id'];
	}
	
	foreach($ids->ids as $item_id){
		if(in_array($item_id, $item_table_ids)){ continue; }
		
		$fields = array('item_table' => $alkaline->tables_index[$id],
			'item_table_id' => $item_id);
		$alkaline->addRow($fields, 'items');
	}
}

?>