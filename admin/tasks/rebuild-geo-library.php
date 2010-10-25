<?php

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

// Open Geo SQL file
$queries = file_get_contents(PATH . ASSETS . 'geo.sql');
$queries = explode("\n", $queries);

if(empty($_POST['photo_id'])){
	$alkaline->exec('DELETE FROM cities;');
	$alkaline->exec('DELETE FROM countries;');
	
	/*
	// Import default SQL
	$queries = file_get_contents(PATH . ASSETS . $alkaline->db_type . '.sql');
	$queries = explode("\n", $queries);
	
	foreach($queries as $query){
		$query = trim($query);
		if(!empty($query) and (strstr($query, 'cities') or strstr($query, 'countries'))){
			$alkaline->exec($query . ';');
		}
	}
	*/
	
	// Generate array of query blocks
	$execute = array();
	$count = count($queries);
	for($i = 0; $i < $count; $i=$i+100){
		$execute[] = $i;
	}
	echo json_encode($execute);
}
else{
	// Execute a block of queries
	$queries = @array_slice($queries, $_POST['photo_id'], 100);
	foreach($queries as $query){
		if($alkaline->db_type != 'sqlite'){
			$query = str_replace('\'\'', '\\\'', $query);
		}
		$query = trim($query);
		if(!empty($query)){
			$alkaline->exec($query);
		}
	}
}

?>