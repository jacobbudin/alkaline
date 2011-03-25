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

$image_ids = new Find('images');
$image_ids->find();
$image_ids->saveMemory();

header('Location: ' . LOCATION . BASE . ADMIN . 'results' . URL_CAP);
exit();

?>