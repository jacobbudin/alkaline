<?php

/*
// Alkaline
// Copyright (c) 2010-2012 by Budin Ltd. Some rights reserved.
// http://www.alkalineapp.com/
*/

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

if($user->deauth()){
	$alkaline->addNote('You successfully logged out.', 'success');
}

header('Location: ' . LOCATION . BASE . ADMIN . 'login' . URL_CAP);
exit();

?>