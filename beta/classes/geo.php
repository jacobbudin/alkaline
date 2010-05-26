<?php

class Geo extends Alkaline{
	public $city;
	protected $sql;
	protected $sql_conds;
	protected $sql_limit;
	protected $sql_sort;
	protected $sql_from;
	protected $sql_tables;
	protected $sql_order_by;
	protected $sql_where;
	
	public function __construct($geo){
		parent::__construct();
		
		// Store data to object
		$this->photo_ids = array();
		$this->sql = 'SELECT *';
		$this->sql_conds = array();
		$this->sql_limit = '';
		$this->sql_sort = array();
		$this->sql_from = '';
		$this->sql_tables = array('cities', 'countries');
		$this->sql_order_by = '';
		$this->sql_where = '';
		
		// Convert integer-like strings into integers
		if(preg_match('/^[0-9]+$/', $geo)){
			$geo = intval($geo);
		}
		
		// Lookup integer in cities table, city_id field
		if(is_int($geo)){
			$this->sql_conds[] = '(cities.city_id = ' . $geo . ')';
		}
		
		// Are these coordinates?
		elseif(preg_match('/^[^A-Z]+$/i', $geo)){
			$geo = explode(',', $geo);
			$lat = trim($geo[0]);
			$long = trim($geo[1]);
			$this->sql .= ', (3959 * acos(cos(radians(' . $lat . ')) * cos(radians(city_lat)) * cos(radians(city_long) - radians(' . $long . ')) + sin(radians(' . $lat . ')) * sin(radians(city_lat)))) AS distance';
			$this->sql_conds[] = '(city_lat < ' . ($lat + 5) . ')';
			$this->sql_conds[] = '(city_lat > ' . ($lat - 5) . ')';
			$this->sql_conds[] = '(city_long < ' . ($long + 5) . ')';
			$this->sql_conds[] = '(city_long > ' . ($long - 5) . ')';
			$this->sql_sort[] = 'distance';
		}
		
		// Lookup city in cities table
		elseif(is_string($geo)){
			$geo_lower = strtolower($geo);

			// Set fields to search
			$this->sql_conds[] = '(LOWER(cities.city_name) LIKE "%' . $geo_lower . '%" OR LOWER(cities.city_name_raw) LIKE "%' . $geo_lower . '%" OR LOWER(cities.city_name_alt) = "%' . $geo_lower . '%")';
		}
		
		else{
			return false;
		}
		
		// Tie cities table to countries table
		$this->sql_conds[] = '(cities.country_code = countries.country_code)';
		
		// Order by the most likely of matches
		$this->sql_sort[] = 'cities.city_pop DESC';
		
		// Select only the most likely
		$this->sql_limit = ' LIMIT 0,1';
		
		// Prepare SQL conditions
		$this->sql_from = ' FROM ' . implode(', ', $this->sql_tables);
		
		if(count($this->sql_conds) > 0){
			$this->sql_where = ' WHERE ' . implode(' AND ', $this->sql_conds);
		}
		if(count($this->sql_sort) > 0){
			$this->sql_order_by = ' ORDER BY ' . implode(', ', $this->sql_sort);
		}
		
		// Prepare query
		$this->sql .= $this->sql_from . $this->sql_where . $this->sql_order_by . $this->sql_limit;
		
		echo $this->sql . '<br />';
		
		// Execute query
		$query = $this->db->prepare($this->sql);
		$query->execute();
		$cities = $query->fetchAll();
		
		// If nothing found...
		if(empty($cities[0])){
			return false;
		}
		
		$this->city = $cities[0];
		
		return true;
	}
	
}

?>