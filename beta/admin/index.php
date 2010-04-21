<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'user.php');

$user = new User;

if($user->perm()){
	header('Location: http://' . DOMAIN . 'admin/dashboard/');
	exit();
}
else{
	header('Location: http://' . DOMAIN . 'admin/login/');
	exit();
}

?>