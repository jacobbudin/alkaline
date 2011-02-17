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

class Set extends Alkaline{
	public $set_ids;
	public $set_count = 0;
	public $sets;
	
	/**
	 * Initiate Set object
	 *
	 * @param array|int|string $set Search sets (set IDs, set titles)
	 */
	public function __construct($set=null){
		parent::__construct();
		
		if(!empty($set)){
			$sql_params = array();
			
			if(is_int($set)){
				$set_id = $set;
				$query = $this->prepare('SELECT * FROM sets WHERE set_id = ' . $set_id . ';');
			}
			elseif(is_string($set)){
				$query = $this->prepare('SELECT * FROM sets WHERE (LOWER(set_title_url) LIKE :set_title_url);');
				$sql_params[':set_title_url'] = '%' . strtolower($set) . '%';
			}
			elseif(is_array($set)){
				$set_ids = $this->convertToIntegerArray($set);
				$query = $this->prepare('SELECT * FROM sets WHERE set_id = ' . implode(' OR set_id = ', $set_ids) . ';');
			}
			
			if(!empty($query)){
				$query->execute($sql_params);
				$this->sets = $query->fetchAll();
				
				$this->set_count = count($this->sets);
			}
		}
		
		// Attach additional fields
		for($i = 0; $i < $this->set_count; ++$i){
			if(empty($this->sets[$i]['set_title_url']) or (URL_RW != '/')){
				$this->sets[$i]['set_uri_rel'] = BASE . 'set' . URL_ID . $this->sets[$i]['set_id'] . URL_RW;
			}
			else{
				$this->sets[$i]['set_uri_rel'] = BASE . 'set' . URL_ID . $this->sets[$i]['set_id'] . '-' . $this->sets[$i]['set_title_url'] . URL_RW;
			}
			
			$this->sets[$i]['set_uri'] = LOCATION . $this->sets[$i]['set_uri_rel'];
		}
	}
	
	public function __destruct(){
		parent::__destruct();
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
		
		$this->sets = $orbit->hook('set', $this->sets, $this->sets);
	}
	
	public function create(){
		
	}
	
	/**
	 * Fetch all pages
	 *
	 * @return void
	 */
	public function fetchAll(){
		$query = $this->prepare('SELECT set_id FROM sets;');
		$query->execute();
		$sets = $query->fetchAll();
		
		$set_ids = array();
		
		foreach($sets as $set){
			$set_ids[] = $set['set_id'];
		}
		
		// Reconstruct
		self::__construct($set_ids);
	}
	
	public function search($search=null){
		
	}
	
	/**
	 * Update pages
	 *
	 * @param array $fields Associate array of columns and fields
	 * @return void
	 */
	public function update($fields){
		$ids = array();
		foreach($this->sets as $set){
			$ids[] = $set['set_id'];
		}
		return parent::updateRow($fields, 'sets', $ids);
	}
	
	/**
	 * Format time
	 *
	 * @param string $format Same format as date();
	 * @return void
	 */
	public function formatTime($format=null){
		foreach($this->sets as &$set){
			$set['set_created_format'] = parent::formatTime($set['set_created'], $format);
			$set['set_modified_format'] = parent::formatTime($set['set_modified'], $format);
		}
	}
}

?>