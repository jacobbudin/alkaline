<?php

require_once('./../alkaline.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'user.php');

$user = new User;
$user->deauth();

header('Location: http://' . DOMAIN . 'admin/login/');
exit();

?>