<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalineapp.com/
*/

/**
 * @author Budin Ltd. <contact@budinltd.com>
 * @copyright Copyright (c) 2010-2011, Budin Ltd.
 * @version 1.0
 */

class Geo extends Alkaline{
	public $city;
	public $lat;
	public $long;
	public $raw;
	public $states;
	public $states_abbrev;
	public $states_full;
	
	protected $sql;
	protected $sql_conds;
	protected $sql_limit;
	protected $sql_sort;
	protected $sql_from;
	protected $sql_params;
	protected $sql_tables;
	protected $sql_order_by;
	protected $sql_where;
	
	/**
	 * Initiate Geo class
	 *
	 * @param string $geo Search string 
	 * @param int $radius Search radius (in miles)
	 */
	public function __construct($geo=null, $radius=1){
		parent::__construct();
		
		// Store data to object
		$this->states = array('AL' => 'Alabama',
			'AK' => 'Alaska',
			'AS' => 'American Samoa',
			'AZ' => 'Arizona',
			'AR' => 'Arkansas',
			'CA' => 'California',
			'CO' => 'Colorado',
			'CT' => 'Connecticut',
			'DE' => 'Delaware',
			'DC' => 'District of Columbia',
			'FM' => 'Federated States of Micronesia',
			'FL' => 'Florida',
			'GA' => 'Georgia',
			'GU' => 'Guam',
			'HI' => 'Hawaii',
			'ID' => 'Idaho',
			'IL' => 'Illinois',
			'IN' => 'Indiana',
			'IA' => 'Iowa',
			'KS' => 'Kansas',
			'KY' => 'Kentucky',
			'LA' => 'Louisiana',
			'ME' => 'Maine',
			'MH' => 'Marshall Islands',
			'MD' => 'Maryland',
			'MA' => 'Massachusetts',
			'MI' => 'Michigan',
			'MN' => 'Minnesota',
			'MS' => 'Mississippi',
			'MO' => 'Missouri',
			'MT' => 'Montana',
			'NE' => 'Nebraska',
			'NV' => 'Nevada',
			'NH' => 'New Hampshire',
			'NJ' => 'New Jersey',
			'NM' => 'New Mexico',
			'NY' => 'New York',
			'NC' => 'North Carolina',
			'ND' => 'North Dakota',
			'MP' => 'Northern Mariana Islands',
			'OH' => 'Ohio',
			'OK' => 'Oklahoma',
			'OR' => 'Oregon',
			'PW' => 'Palau',
			'PA' => 'Pennsylvania',
			'PR' => 'Puerto Rico',
			'RI' => 'Rhode Island',
			'SC' => 'South Carolina',
			'SD' => 'South Dakota',
			'TN' => 'Tennessee',
			'TX' => 'Texas',
			'UT' => 'Utah',
			'VT' => 'Vermont',
			'VI' => 'Virgin Islands',
			'VA' => 'Virginia',
			'WA' => 'Washington',
			'WV' => 'West Virginia',
			'WI' => 'Wisconsin',
			'WY' => 'Wyoming');
		
		$this->sql = 'SELECT *';
		$this->sql_conds = array();
		$this->sql_limit = '';
		$this->sql_sort = array();
		$this->sql_from = '';
		$this->sql_params = array();
		$this->sql_tables = array('cities', 'countries');
		$this->sql_order_by = '';
		$this->sql_where = '';
		
		if(empty($geo)){
			return false;
		}
		
		// Convert integer-like strings into integers
		if(preg_match('/^[0-9]+$/', $geo)){
			$geo = intval($geo);
		}
		
		// Lookup integer in cities table, city_id field
		if(is_int($geo)){
			$type = 'id';
			$this->sql_conds[] = '(cities.city_id = :city_id)';
			$this->sql_params[':city_id'] = $geo;
		}
		
		// Are these coordinates?
		elseif(preg_match('#^[^A-Z]+$#si', $geo)){
			// Must have latitude and longitude
			if(!preg_match('#[0-9-]+\s*,\s*[0-9-]+#si', $geo)){ return false; }
			
			$geo = preg_replace('#[^0-9-,.]+#', '', $geo);
			
			$type = 'coord';
			$coord = explode(',', $geo);
			
			$lat = floatval(trim($coord[0]));
			$long = floatval(trim($coord[1]));
			
			$this->lat = $lat;
			$this->long = $long;
			
			// Haversine formula
			$this->sql .= ', (3959 * acos(cos(radians(' . $lat . ')) * cos(radians(city_lat)) * cos(radians(city_long) - radians(' . $long . ')) + sin(radians(' . $lat . ')) * sin(radians(city_lat)))) AS distance';
			$this->sql_conds[] = '(city_lat <= ' . ceil($lat + $radius) . ')';
			$this->sql_conds[] = '(city_lat >= ' . ceil($lat - $radius) . ')';
			$this->sql_conds[] = '(city_long <= ' . ceil($long + $radius) . ')';
			$this->sql_conds[] = '(city_long >= ' . ceil($long - $radius) . ')';
			$this->sql_sort[] = 'distance';
		}
		
		// Lookup city in cities table
		elseif(is_string($geo)){
			if(preg_match('#([0-9-,.]+)\s*,\s*([0-9-,.]+)#si', $geo, $matches)){
				$this->lat = $matches[1];
				$this->long = $matches[2];
				$geo = preg_replace('#\(?([0-9-,.]+)\s*,\s*([0-9-,.]+)\)?#si', '', $geo);
			}
			
			$type = 'name';
			
			$geo = trim($geo);
			$this->raw = $geo;
			
			if(strpos($geo, ',') === false){
				$geo_city = $geo;
			}
			elseif(preg_match('/([^\,]+)\,([^\,]+)\,([^\,]*)/', $geo, $matches)){
				$geo_city = trim($matches[1]);
				$geo_state = self::convertAbbrev(trim($matches[2]));
				$geo_country = self::convertAbbrev(trim($matches[3]));
				if($geo_country != 'United States'){
					unset($geo_state);
				}
			}
			elseif(preg_match('/([^\,]+)\,([^\,]+)/', $geo, $matches)){
				$geo_city = trim($matches[1]);
				$geo_unknown = self::convertAbbrev(trim($matches[2]));
				
				if(array_key_exists(strtoupper($geo_unknown), $this->states)){
					$geo_state = $geo_unknown;
				}
				elseif($geo_state_abbrev = array_search(ucfirst($geo_unknown), $this->states, false)){
					$geo_state = $geo_state_abbrev;
				}
				else{
					$geo_country = $geo_unknown;
				}
				
			}
			else{
				return false;
			}

			// Set fields to search
			if(!empty($geo_city)){
				$geo_city_lower = strtolower($geo_city);
				$this->sql_conds[] = '(LOWER(cities.city_name) LIKE :city_lower OR LOWER(cities.city_name_raw) LIKE :city_lower OR LOWER(cities.city_name_alt) = :city_lower)';
				$this->sql_params[':city_lower'] = '%' . $geo_city_lower . '%';
			}
			if(!empty($geo_state)){
				$geo_state_lower = strtolower($geo_state);
				$this->sql_conds[] = '(LOWER(cities.city_state) LIKE :state_lower)';
				$this->sql_params[':state_lower'] = '%' . $geo_state_lower . '%';
			}
			if(!empty($geo_country)){
				$geo_country_lower = strtolower($geo_country);
				$this->sql_conds[] = '(LOWER(countries.country_name) LIKE :country_lower)';
				$this->sql_params[':country_lower'] = '%' . $geo_country_lower . '%';
			}
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
		
		// Execute query
		$query = $this->prepare($this->sql);
		$query->execute($this->sql_params);
		$cities = $query->fetchAll();
		
		// If no results
		if(empty($cities[0])){
			
			// If coordinate searching, expand radius
			if(($type == 'coord') and ($radius < 1000000)){
				self::__construct($geo, $radius*5);
			}
			
			// Otherwise, give up
			return false;
		}
		
		$this->city = $cities[0];
		
		if(empty($this->lat) or empty($this->long)){
			$this->lat = $this->city['city_lat'];
			$this->long = $this->city['city_long'];
		}
		
		if(!array_key_exists(strtoupper($this->city['city_state']), $this->states)){
			$this->city['city_state'] = '';
		}
		
		return true;
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	/**
	 * Return an formatted Geo string
	 *
	 * @return string Formatted string
	 */
	public function __toString(){
		$str = '';
		if(!empty($this->city)){
	        $str .= $this->city['city_name'] . ', ';
			$str .= (!empty($this->city['city_state'])) ? $this->city['city_state'] . ', ' : null;
			$str .= $this->city['country_name'] . ' ';
		}
		elseif(!empty($this->raw)){
			$str .= $this->raw . ' ';
		}
		if(!empty($this->lat) and !empty($this->long)){
			$str .= '(' . $this->lat . ', ' . $this->long . ')';
		}
		return trim($str);
    }

	/**
	 * Perform Orbit hook
	 *
	 * @param Orbit $orbit 
	 * @return void
	 */
	public function hook($orbit=null){
		if(!is_object($orbit)){
			$orbit = new Orbit;
		}
		
		$this->city = $orbit->hook('geo', $this->city, $this->city);
	}
	
	/**
	 * List cities in internal database by search, for suggestions
	 *
	 * @param string $hint Search string
	 * @return array
	 */
	public function hint($hint){
		$hint_lower = strtolower($hint);
		
		$sql = 'SELECT cities.city_name, cities.city_state, countries.country_name, cities.city_pop FROM cities, countries WHERE ((LOWER(cities.city_name) LIKE :hint_lower OR LOWER(cities.city_name_raw) LIKE :hint_lower OR LOWER(cities.city_name_alt) LIKE :hint_lower)) AND cities.country_code = countries.country_code ORDER BY cities.city_pop DESC';
		
		$query = $this->prepare($sql);
		$query->execute(array(':hint_lower' => $hint_lower . '%'));
		$cities = $query->fetchAll();
		
		$cities_list = array();
		
		foreach($cities as &$city){
			if(!array_key_exists(strtoupper($city['city_state']), $this->states)){
				$city['city_state'] = '';
			}
			
			$string = $city['city_name'] . ', ';
			if(!empty($city['city_state'])){
				$string .= $city['city_state'] . ', ';
			}
			$string .= $city['country_name'];
			
			$cities_list[] = $string;
		}
		
		return $cities_list;
	}
	
	/**
	 * Convert common abbreviations
	 *
	 * @param string $var 
	 * @return string
	 */
	protected function convertAbbrev($var){
		$countries_abbrev = array('USA', 'US', 'America', 'UK', 'UAE', 'Holland', 'The');
		$countries = array('United States', 'United States', 'United States', 'United Kingdom', 'United Arab Emirates', 'Netherlands', '');
		$var = str_ireplace($countries_abbrev, $countries, $var);
		return $var;
	}
	
}

?>