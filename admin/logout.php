<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalinenapp.com/
*/

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

if($user->deauth()){
	$alkaline->addNotification('You successfully logged out.', 'success');
}

header('Location: ' . LOCATION . BASE . ADMIN . 'login' . URL_CAP);
exit();

?>