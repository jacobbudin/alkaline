<?php

session_start();

$message = $_SESSION['alkaline']['error']['message'];
$number = $_SESSION['alkaline']['error']['number'];

if(!empty($message)){
	echo $message;
}

if(!empty($number)){
	echo '(' . $number . ')';
}
	

?>