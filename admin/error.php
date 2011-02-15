<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalineapp.com/
*/

require_once('../config.php');

@session_start();

define('TAB', 'Error');
define('TITLE', 'Alkaline Error');
require_once(PATH . ADMIN . 'includes/header.php');

if($_SESSION['alkaline']['errors']){
	$error_constants = array();
	foreach($_SESSION['alkaline']['errors'] as $error){
		$error_constants[] = $error['constant'];
	}
}

$key = array_search(E_USER_ERROR, $error_constants, true);

if($key !== false){
	$error = $_SESSION['alkaline']['errors'][$key];
	echo '<p>' . $error['message'];
	if(!empty($error['filename'])){
		echo ' (' . $error['filename'] . ', line ' . $error['line_number'] .')';
	} 
	echo '.</p>';
}
else{
	?>
	<p>A critical error has occurred.</p>
	<?php
}

require_once(PATH . ADMIN . 'includes/footer.php');

?>