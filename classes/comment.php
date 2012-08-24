<?php

/*
// Alkaline
// Copyright (c) 2010-2012 by Budin Ltd. Some rights reserved.
// http://www.alkalineapp.com/
*/

/**
 * @author Budin Ltd. <contact@budinltd.com>
 * @copyright Copyright (c) 2010-2012, Budin Ltd.
 * @version 1.0
 */

class Comment extends Alkaline{
	public $comments;
	public $comment_ids;
	public $comment_count = 0;
	public $image_ids = array();
	public $post_ids = array();

	protected $sql;
	
	/**
	 * Initiate Comment class
	 *
	 * @param string|int|array $comment_ids Limit results to select comment IDs
	 */
	public function __construct($comment_ids=null){
		parent::__construct();
		
		// Recomment comment array
		$this->comments = array();
		
		// Input handling
		if(is_object($comment_ids)){
			$last_modified = $comment_ids->last_modified;
			$comment_ids = $comment_ids->ids;
		}
		
		$this->comment_ids = parent::convertToIntegerArray($comment_ids);
		
		// Error checking
		$this->sql = ' WHERE (comments.comment_id IS NULL)';
		if(count($this->comment_ids) > 0){
			$this->sql = ' WHERE (comments.comment_id IN (' . implode(', ', $this->comment_ids) . '))';
		}
		
		// Cache
		require_once('cache_lite/Lite.php');
		
		// Set a few options
		$options = array(
		    'cacheDir' => PATH . CACHE,
		    'lifeTime' => 3600
		);

		// Create a Cache_Lite object
		$cache = new Cache_Lite($options);
		
		if(($comments = $cache->get('comments:' . implode(',', $this->comment_ids), 'comments')) && !empty($last_modified) && ($cache->lastModified() > $last_modified)){
			$this->comments = unserialize($comments);
		}
		else{
			if(count($this->comment_ids) > 0){
				$query = $this->prepare('SELECT * FROM comments' . $this->sql . ';');
				$query->execute();
				$comments = $query->fetchAll();
		
				// Ensure comments array correlates to comment_ids array
				foreach($this->comment_ids as $comment_id){
					foreach($comments as $comment){
						if($comment_id == $comment['comment_id']){
							$this->comments[] = $comment;
						}
					}
				}
			}
			
			$cache->save(serialize($this->comments));
		}
		
		// Store comment count as integer
		$this->comment_count = count($this->comments);
		
		// Attach additional fields
		for($i = 0; $i < $this->comment_count; ++$i){
			if($this->comments[$i]['image_id'] != 0){
				$this->image_ids[] = $this->comments[$i]['image_id'];
			}
			if($this->comments[$i]['post_id'] != 0){
				$this->post_ids[] = $this->comments[$i]['post_id'];
			}
		}
	
		$this->image_ids = array_unique($this->image_ids, SORT_NUMERIC);
		$this->image_ids = array_values($this->image_ids);

		$this->post_ids = array_unique($this->post_ids, SORT_NUMERIC);
		$this->post_ids = array_values($this->post_ids);
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
		
		$this->comments = $orbit->hook('comment', $this->comments, $this->comments);
	}
	
	/**
	 * Deletes comments
	 *
	 * @param bool Delete permanently (and therefore cannot be recovered)
	 * @return void
	 */
	public function delete($permanent=false){
		if($permanent === true){
			$this->deleteRow('comments', $this->comment_ids);
		}
		else{
			$fields = array('comment_deleted' => date('Y-m-d H:i:s'));
			$this->updateFields($fields);
		}
		
		return true;
	}
	
	/**
	 * Recover comments (and comments also deleted at same time)
	 * 
	 * @return bool
	 */
	public function recover(){
		$fields = array('comment_deleted' => null);
		$this->updateFields($fields);
		
		return true;
	}
	
	/**
	 * Update comment fields
	 *
	 * @param string $fields Associative array of columns and fields
	 * @return PDOStatement
	 */
	public function updateFields($fields){
		$ids = array();
		foreach($this->comments as $comment){
			$ids[] = $comment['comment_id'];
		}
		return parent::updateRow($fields, 'comments', $ids);
	}
	
	/**
	 * Format time
	 *
	 * @param string $format Format as in date();
	 * @return void
	 */
	public function formatTime($format=null){
		foreach($this->comments as &$comment){
			$comment['comment_created_format'] = parent::formatTime($comment['comment_created'], $format);
			$comment['comment_modified_format'] = parent::formatTime($comment['comment_modified'], $format);
		}
		return true;
	}
	
	/**
	 * Get word and numerical sequencing of comments
	 *
	 * @param int $start First number on page
	 * @param bool $asc Sequence order (false if DESC)
	 * @return void
	 */
	public function getSeries($start=null, $asc=true){
		if(!is_numeric($start)){
			$start = 1;
		}
		else{
			$start = intval($start);
		}
		
		if($asc === true){
			$values = range($start, $start+$this->comment_count);
		}
		else{
			$values = range($start, $start-$this->comment_count);
		}
		
		for($i = 0; $i < $this->comment_count; ++$i){
			$this->comments[$i]['comment_numeric'] = $values[$i];
			$this->comments[$i]['comment_alpha'] = ucwords($this->numberToWords($values[$i]));
		}
	}
	
	/**
	 * Add string notation to particular sequence, good for CSS columns
	 *
	 * @param string $label String notation
	 * @param int $frequency 
	 * @param bool $start_first True if first comment should be selected and begin sequence
	 * @return void
	 */
	public function addSequence($label, $frequency, $start_first=false){
		if($start_first === false){
			$i = 1;
		}
		else{
			$i = $frequency;
		}
		
		// Store comment comment fields
		foreach($this->comments as &$comment){
			if($i == $frequency){
				if(empty($comment['comment_sequence'])){
					$comment['comment_sequence'] = $label;
				}
				else{
					$comment['comment_sequence'] .= ' ' . $label;
				}
				$i = 1;
			}
			else{
				$i++;
			}
		}
		
		return true;
	}
}

?>