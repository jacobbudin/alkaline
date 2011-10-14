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

if(!empty($_POST['uri'])){
	$citation = $alkaline->loadCitation($_POST['uri'], $_POST['field'], $_POST['field_id']);
	if($citation != false){
		echo $alkaline->removeNull(json_encode($citation));
	}
}

?>