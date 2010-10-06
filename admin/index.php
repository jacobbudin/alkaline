<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

if($user->perm()){
	header('Location: ' . LOCATION . BASE . ADMIN . 'dashboard' . URL_CAP);
	exit();
}
else{
	header('Location: ' . LOCATION . BASE . ADMIN . 'login' . URL_CAP);
	exit();
}

?>