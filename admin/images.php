<?php

/*
// Alkaline
// Copyright (c) 2010-2012 by Budin Ltd. Some rights reserved.
// http://www.alkalineapp.com/
*/

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;

// GET PHOTO
if($image_id = $alkaline->findID($_GET['id'])){
	header('Location: ' . LOCATION . BASE . ADMIN . 'image' . URL_ID . $image_id . URL_RW);
	exit();
}

header('Location: ' . BASE . ADMIN . 'library' . URL_CAP);
exit();

?>