<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'user.php');

$alkaline = new Alkaline;
$user = new User;

if($user->deauth()){
	$alkaline->addNotification('You successfully logged out.', 'success');
}

header('Location: ' . LOCATION . BASE . ADMIN . 'login/');
exit();

?>