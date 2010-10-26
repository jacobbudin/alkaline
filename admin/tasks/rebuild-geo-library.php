<?php

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

// Open cities JSON file
$cities = file_get_contents(PATH . INSTALL . 'cities.json');
$cities = explode("\n", $cities);

if(empty($_POST['photo_id'])){
	// Delete existing geo data, start from scratch
	$alkaline->exec('DELETE FROM cities;');
	$alkaline->exec('DELETE FROM countries;');
	
	// Load countries
	$countries = file_get_contents(PATH . INSTALL . 'countries.json');
	$countries = explode("\n", $countries);
	
	$query = $alkaline->prepare('INSERT INTO countries (country_id, country_code, country_name) VALUES (?, ?, ?);');
	
	foreach($countries as $country){
		$country = json_decode($country);
		$query->execute($country);
	}
	
	// Generate array of query blocks for cities
	$execute = array();
	$count = count($cities);
	for($i = 0; $i < $count; $i=$i+250){
		$execute[] = $i;
	}
	echo json_encode($execute);
}
else{
	// Insert blocks of cities
	$cities = @array_slice($cities, $_POST['photo_id'], 250);
	
	$query = $alkaline->prepare('INSERT INTO cities (city_id, city_name, city_state, country_code, city_name_raw, city_name_alt, city_pop, city_lat, city_long, city_class, city_code) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);');
	
	foreach($cities as $city){
		$city = json_decode($city);
		$city = array_map('utf8_encode', $city);
		$city = array_map('utf8_decode', $city);
		$query->execute($city);
	}
}

?>