<?php

require_once('./../alkaline.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'notify.php');
require_once(PATH . CLASSES . 'user.php');

$notifications = new Notify;
$user = new User;

if($user->deauth()){
	$notifications->add('success', 'You have successfully logged out.');
}

header('Location: http://' . DOMAIN . 'admin/login/');
exit();

?>