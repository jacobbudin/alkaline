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
			$comment_ids = $comment_ids->ids;
		}
		
		$this->comment_ids = parent::convertToIntegerArray($comment_ids);
		
		// Error checking
		$this->sql = ' WHERE (comments.comment_id IS NULL)';
		
		if(count($this->comment_ids) > 0){
			// Retrieve comments from database
			$this->sql = ' WHERE (comments.comment_id IN (' . implode(', ', $this->comment_ids) . '))';
			
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
	 * Format time
	 *
	 * @param string $format Format as in date();
	 * @return void
	 */
	public function formatTime($format=null){
		foreach($this->comments as &$comment){
			$comment['comment_created_format'] = parent::formatTime($comment['comment_created'], $format);
		}
		return true;
	}
}

?>