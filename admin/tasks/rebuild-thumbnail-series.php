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

$valid = false;

if(isset($_REQUEST['min'])){
	$_SESSION['alkaline']['maintenance']['series']['min'] = $_REQUEST['min'];
	$valid = true;
}
if(isset($_REQUEST['max'])){
	$_SESSION['alkaline']['maintenance']['series']['max'] = $_REQUEST['max'];
	$valid = true;
}

if(!empty($_REQUEST['series'])){
	if($valid == true){
		header('Location: ' . LOCATION . BASE . ADMIN . 'maintenance' . URL_CAP . '#rebuild-thumbnail-series');
		exit();
	}
	else{
		$alkaline->addNote('You must select a valid series when rebuilding thumbnails by series.', 'error');
		header('Location: ' . LOCATION . BASE . ADMIN . 'maintenance' . URL_CAP);
		exit();
	}
}

if(empty($_POST['image_id'])){
	$image_ids = range($_SESSION['alkaline']['maintenance']['series']['min'], $_SESSION['alkaline']['maintenance']['series']['max']);
	$image_ids = new Find('images', $image_ids);
	$image_ids->find();
	echo json_encode($image_ids->ids);
}
else{
	$image = new Image($_POST['image_id']);
	$image->deSizeImage();
	$image->sizeImage();
}

?>