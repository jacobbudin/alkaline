<?php

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$hint = strip_tags($_GET['term']);

$geo = new Geo();
$places = $geo->hint($hint);
echo json_encode($places);

?>