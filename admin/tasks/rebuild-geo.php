<?php

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$id = $alkaline->findID(@$_POST['image_id']);

// Require at least 128M memory
$mem = ini_get('memory_limit');
if(substr($mem, -1) == 'M'){
	if(substr($mem, 0, strlen($mem) - 1) < 128){
		if(!ini_set('memory_limit', '128M')){
			exit();
		}
	}
}

// Open cities JSON file
$cities = file_get_contents(PATH . DB . 'cities.json');
$cities = explode("\n", $cities);

if(!is_int($id)){
	// Generate array of query blocks for cities
	$execute = array();
	$count = count($cities);
	for($i = 0; $i < $count; $i=$i+1000){
		$execute[] = $i;
	}
	echo json_encode($execute);
}
else{
	if($id == 0){
		// Delete existing geo data, start from scratch
		$alkaline->exec('DELETE FROM cities;');
		$alkaline->exec('DELETE FROM countries;');
		
		// Load countries
		$countries = file_get_contents(PATH . DB . 'countries.json');
		$countries = explode("\n", $countries);

		$query = $alkaline->prepare('INSERT INTO countries (country_id, country_code, country_name) VALUES (?, ?, ?);');

		foreach($countries as $country){
			$country = json_decode($country);
			$country = @array_map('utf8_encode', $country);
			$country = @array_map('utf8_decode', $country);
			$query->execute($country);
		}
		
		$query->closeCursor();
	}
	
	// Insert blocks of cities
	$cities = @array_slice($cities, $id, 1000);
	
	$query = $alkaline->prepare('INSERT INTO cities (city_id, city_name, city_state, country_code, city_name_raw, city_name_alt, city_pop, city_lat, city_long, city_class, city_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);');
	
	foreach($cities as $city){
		$city = json_decode($city);
		$city = @array_map('utf8_encode', $city);
		$city = @array_map('utf8_decode', $city);
		$query->execute($city);
	}
	
	$query->closeCursor();
}

?>