<?php

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'find.php');
require_once(PATH . CLASSES . 'import.php');
require_once(PATH . CLASSES . 'user.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

if(@!empty($_POST['message'])){
	@$alkaline->addNotification($_POST['message'], $_POST['type']);
}

?>