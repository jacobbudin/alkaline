<?php

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

if(@!empty($_POST['type'])){
	$user->view_type = $_POST['type'];
}

?>