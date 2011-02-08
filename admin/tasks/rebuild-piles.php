<?php

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$id = $alkaline->findID(@$_POST['image_id']);

if(empty($id)){
	$piles = $alkaline->getTable('piles');
	
	$pile_ids = array();
	
	foreach($piles as $pile){
		if($pile['pile_type'] == 'auto'){
			$pile_ids[] = $pile['pile_id'];
		}
	}
	
	echo json_encode($pile_ids);
}
else{
	$images = new Find;
	$images->pile(intval($id));
}

?>