<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalineapp.com/
*/


require_once('../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;

// Import default SQL
$queries = file_get_contents(PATH . 'update/' . $alkaline->db_type . '.sql');
$queries = explode("\n", $queries);

foreach($queries as $query){
	$query = trim($query);
	if(!empty($query)){
		$alkaline->exec($query);
	}
}

$alkaline->addNote('You have successfully updated Alkaline. You should now remove the /upgrade directory.', 'success');

header('Location: ' . LOCATION . BASE . ADMIN);
exit();

?>