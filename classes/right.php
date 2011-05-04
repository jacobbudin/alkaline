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

class Right extends Alkaline{
	public $images;
	public $right_ids;
	public $right_count = 0;
	public $rights;
	
	protected $sql;
	
	/**
	 * Initiate Right object
	 *
	 * @param array|int|string $right_ids Search rights (right IDs, right titles)
	 */
	public function __construct($right_ids=null){
		parent::__construct();
		
		// Reright right array
		$this->rights = array();
		
		// Input handling
		if(is_object($right_ids)){
			$last_modified = $right_ids->last_modified;
			$right_ids = $right_ids->ids;
		}
		
		$this->right_ids = parent::convertToIntegerArray($right_ids);
		
		// Error checking
		$this->sql = ' WHERE (rights.right_id IS NULL)';
		
		// Cache
		require_once('cache_lite/Lite.php');
		
		// Set a few options
		$options = array(
		    'cacheDir' => PATH . CACHE,
		    'lifeTime' => 3600
		);

		// Create a Cache_Lite object
		$cache = new Cache_Lite($options);
		
		if(($rights = $cache->get('rights:' . implode(',', $this->right_ids), 'rights')) && !empty($last_modified) && ($cache->lastModified() > $last_modified)){
			$this->rights = unserialize($rights);
		}
		else{
			if(count($this->right_ids) > 0){
				// Retrieve rights from database
				$this->sql = ' WHERE (rights.right_id IN (' . implode(', ', $this->right_ids) . '))';
			
				$query = $this->prepare('SELECT * FROM rights' . $this->sql . ';');
				$query->execute();
				$rights = $query->fetchAll();
		
				// Ensure rights array correlates to right_ids array
				foreach($this->right_ids as $right_id){
					foreach($rights as $right){
						if($right_id == $right['right_id']){
							$this->rights[] = $right;
						}
					}
				}
			}
			
			$cache->save(serialize($this->rights));
		}
		
		// Store right count as integer
		$this->right_count = count($this->rights);
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
		
		$this->rights = $orbit->hook('right', $this->rights, $this->rights);
	}
	
	/**
	 * Update rights
	 *
	 * @param array $fields Associate array of columns and fields
	 * @return void
	 */
	public function updateFields($fields){
		$ids = array();
		foreach($this->rights as $right){
			$ids[] = $right['right_id'];
		}
		return parent::updateRow($fields, 'rights', $ids);
	}
	
	/**
	 * Deletes rights
	 *
	 * @param bool Delete permanently (and therefore cannot be recovered)
	 * @return void
	 */
	public function delete($permanent=false){
		if($permanent === true){
			$this->deleteRow('rights', $this->right_ids);
		}
		else{
			$fields = array('right_deleted' => date('Y-m-d H:i:s'));
			$this->updateFields($fields);
		}
		
		return true;
	}
	
	/**
	 * Recover rights (and comments also deleted at same time)
	 * 
	 * @return bool
	 */
	public function recover(){
		$fields = array('right_deleted' => null);
		$this->updateFields($fields);
		
		return true;
	}
	
	/**
	 * Format time
	 *
	 * @param string $format Same format as date();
	 * @return void
	 */
	public function formatTime($format=null){
		foreach($this->rights as &$right){
			$right['right_created_format'] = parent::formatTime($right['right_created'], $format);
			$right['right_modified_format'] = parent::formatTime($right['right_modified'], $format);
		}
	}
	
	/**
	 * Merge with different right set
	 *
	 * @param null|int $right_id 
	 * @return void
	 */
	public function merge($right_id=null){
		$right_id = intval($right_id);
		
		if($right_id == 0){
			$query = $this->prepare('UPDATE images SET right_id = ? WHERE right_id = ' . implode(' OR right_id = ', $this->right_ids) . ';');
			$query->execute(array(null));
		}
		else{
			$query = $this->prepare('UPDATE images SET right_id = ? WHERE right_id = ' . implode(' OR right_id = ', $this->right_ids) . ';');
			$query->execute(array($right_id));
		}
		
		return true;
	}
}

?>