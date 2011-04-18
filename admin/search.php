<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalineapp.com/
*/

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

Find::clearMemory();

if(!empty($_REQUEST['search_type'])){
	$table = $_REQUEST['search_type'];
}
else{
	$table = 'images';
}

$ids = new Find($table);
$ids->find();
$ids->saveMemory();

if($table == 'images'){
	header('Location: ' . LOCATION . BASE . ADMIN . 'results' . URL_CAP);
	exit();
}
else{
	header('Location: ' . LOCATION . BASE . ADMIN . $table . URL_ACT . 'results' . URL_RW);
	exit();
}

?>