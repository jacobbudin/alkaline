<?php

require_once('config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$alkaline->access($_GET['key']);

header('Location: ' . LOCATION . BASE);
exit();

?>