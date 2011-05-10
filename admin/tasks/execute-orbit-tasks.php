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
	$count = $_SESSION['alkaline']['tasks'];
	
	for($i=1; $i <= $count; $i++){
		$tasks[] = $i;
	}
	
	echo $alkaline->removeNull(json_encode($tasks));
}
else{
	$prbit = new Orbit;
	$prbit->executeTask($_POST['image_id']);
}

?>