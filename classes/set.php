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
	
	protected $sql;
	
	/**
	 * Initiate Set object
	 *
	 * @param array|int|string $set_ids Search sets (set IDs, set titles)
	 */
	public function __construct($set_ids=null){
		parent::__construct();
		
		// Reset set array
		$this->sets = array();
		
		// Input handling
		if(is_object($set_ids)){
			$set_ids = $set_ids->ids;
		}
		
		$this->set_ids = parent::convertToIntegerArray($set_ids);
		
		// Error checking
		$this->sql = ' WHERE (sets.set_id IS NULL)';
		
		if(count($this->set_ids) > 0){
			// Retrieve sets from database
			$this->sql = ' WHERE (sets.set_id IN (' . implode(', ', $this->set_ids) . '))';
			
			$query = $this->prepare('SELECT * FROM sets' . $this->sql . ';');
			$query->execute();
			$sets = $query->fetchAll();
		
			// Ensure sets array correlates to set_ids array
			foreach($this->set_ids as $set_id){
				foreach($sets as $set){
					if($set_id == $set['set_id']){
						$this->sets[] = $set;
					}
				}
			}
		
			// Store set count as integer
			$this->set_count = count($this->sets);
		
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
	
	/**
	 * Update sets
	 *
	 * @param array $fields Associate array of columns and fields
	 * @return void
	 */
	public function updateFields($fields){
		$ids = array();
		foreach($this->sets as $set){
			$ids[] = $set['set_id'];
		}
		return parent::updateRow($fields, 'sets', $ids);
	}
	
	/**
	 * Increase set_views field by 1
	 *
	 * @return void
	 */
	public function updateViews(){
		for($i = 0; $i < $this->set_count; ++$i){
			$this->sets[$i]['set_views']++;
			$this->exec('UPDATE sets SET set_views = ' . $this->sets[$i]['set_views'] . ' WHERE set_id = ' . $this->sets[$i]['set_id'] . ';');
		}
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