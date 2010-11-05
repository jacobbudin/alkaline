<?php

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$id = $alkaline->findID(@$_POST['photo_id']);

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
	$photos = new Find;
	$photos->pile(intval($id));
}

?>