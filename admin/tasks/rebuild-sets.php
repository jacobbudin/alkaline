<?php

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
	$images = new Find;
	$images->sets(intval($id));
}

?>