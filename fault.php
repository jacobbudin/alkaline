<?php

require_once('config.php');

session_start();

@$message = $_SESSION['alkaline']['error']['message'];
@$number = $_SESSION['alkaline']['error']['number'];

define('TAB', 'Error');
define('TITLE', 'Alkaline Error');
require_once(PATH . ADMIN . 'includes/header.php');

echo '<p>';

if(!empty($message)){
	echo $message;
}
else{
	echo 'An unknown error has occured.';
}

if(!empty($number)){
	echo '(' . $number . ')';
}

echo '</p>';

require_once(PATH . ADMIN . 'includes/footer.php');

?>