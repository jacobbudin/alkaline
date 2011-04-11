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

class Post extends Alkaline{
	public $comments;
	public $posts = array();
	public $post_ids;
	public $post_count = 0;
	public $post_count_result = 0;
	public $user;
	protected $sql;
	
	/**
	 * Initiates post object
	 *
	 * @param array|int|string $post_ids post IDs (use Find class to locate them)
	 */
	public function __construct($post_ids=null){
		parent::__construct();
		
		// Reset post array
		$this->posts = array();
		
		// Input handling
		if(is_object($post_ids)){
			$post_ids = $post_ids->ids;
		}
		
		$this->post_ids = parent::convertToIntegerArray($post_ids);
		
		// Error checking
		$this->sql = ' WHERE (posts.post_id IS NULL)';
		
		if(count($this->post_ids) > 0){
			// Retrieve posts from database
			$this->sql = ' WHERE (posts.post_id IN (' . implode(', ', $this->post_ids) . '))';
			
			$query = $this->prepare('SELECT * FROM posts' . $this->sql . ';');
			$query->execute();
			$posts = $query->fetchAll();
		
			// Ensure posts array correlates to post_ids array
			foreach($this->post_ids as $post_id){
				foreach($posts as $post){
					if($post_id == $post['post_id']){
						$this->posts[] = $post;
					}
				}
			}
		
			// Store post count as integer
			$this->post_count = count($this->posts);
		
			// Attach additional fields
			for($i = 0; $i < $this->post_count; ++$i){
				$title_url = $this->makeURL($this->posts[$i]['post_title']);
				if(empty($title_url) or (URL_RW != '/')){
					$this->posts[$i]['post_uri_rel'] = BASE . 'post' . URL_ID . $this->posts[$i]['post_id'] . URL_RW;
				}
				else{
					$this->posts[$i]['post_uri_rel'] = BASE . 'post' . URL_ID . $this->posts[$i]['post_id'] . '-' . $title_url . URL_RW;
				}
				
				$this->posts[$i]['post_uri'] = LOCATION . $this->posts[$i]['post_uri_rel'];
				
				if($this->returnConf('comm_enabled') != true){
					$this->posts[$i]['post_comment_disabled'] = 1;
				}
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
		
		$this->posts = $orbit->hook('post', $this->posts, $this->posts);
	}
	
	/**
	 * Increase post_views field by 1
	 *
	 * @return void
	 */
	public function updateViews(){
		for($i = 0; $i < $this->post_count; ++$i){
			$this->posts[$i]['post_views']++;
			$this->exec('UPDATE posts SET post_views = ' . $this->posts[$i]['post_views'] . ' WHERE post_id = ' . $this->posts[$i]['post_id'] . ';');
		}
	}
	
	/**
	 * Attribute actions to user
	 *
	 * @param User $user User object
	 * @return void
	 */
	public function attachUser($user){
		$this->user = $user->user;
	}
	
	/**
	 * Deletes posts
	 *
	 * @return void
	 */
	public function delete(){
		$ids = array();
		for($i = 0; $i < $this->post_count; ++$i){
			$ids[] = $this->posts[$i]['post_id'];
		}
		
		$this->deleteRow('posts', $ids);
	}
	
	/**
	 * Update post table
	 *
	 * @param array $array Associative array of columns and fields
	 * @param bool $overwrite 
	 * @return void
	 */
	public function updateFields($array, $overwrite=true){
		// Error checking
		if(!is_array($array)){
			return false;
		}
		
		$array_original = $array;
		
		for($i = 0; $i < $this->post_count; ++$i){
			$array = $array_original;
			
			// Verify each key has changed; if not, unset the key
			foreach($array as $key => $value){
				if($array[$key] == $this->posts[$i][$key]){
					unset($array[$key]);
				}
				if(!empty($this->posts[$i][$key]) and ($overwrite === false)){
					unset($array[$key]);
				}
			}
			
			// If no keys have changed, break
			if(count($array) == 0){
				continue;
			}
			
			$fields = array();
			
			// Prepare input
			foreach($array as $key => $value){
				if($key == 'post_published'){
					if(empty($value)){
						$fields[$key] = null;
					}
					elseif(strtolower($value) == 'now'){
						$value = date('Y-m-d H:i:s');
						$fields[$key] = $value;
					}
					else{
						$value = str_ireplace(' on ', ', ', $value);
						$value = str_ireplace(' at ', ', ', $value);
						$value = strtotime($value);
						if($value !== false){
							$value = date('Y-m-d H:i:s', $value);
						}
						else{
							$this->addNote('The post&#8217;s publish date could not be determined, and was left unpublished.', 'error');
							$value = '';
						}
						$fields[$key] = $value;
					}
				}
				elseif($key == 'post_geo'){
					$geo = new Geo($value);
					if(!empty($geo->city)){
						if($geo->city['country_name'] == 'United States'){
							$fields['post_geo'] = $geo->city['city_name'] . ', ' . $geo->city['city_state'] .', ' . $geo->city['country_name'];
						}
						else{
							$fields['post_geo'] = $geo->city['city_name'] . ', ' . $geo->city['country_name'];
						}
					}
					elseif(!empty($geo->raw)){
						$fields['post_geo'] = ucwords($geo->raw);
					}
					else{
						$fields['post_geo'] = '';
					}
					
					if(!empty($geo->lat) and !empty($geo->long)){
						$fields['post_geo_lat'] = $geo->lat;
						$fields['post_geo_long'] = $geo->long;
					}
				}
				else{
					$fields[$key] = $value;
				}
			}
			
			// Set post_modified field to now
			$fields['post_modified'] = date('Y-m-d H:i:s');
			
			$columns = array_keys($fields);
			$values = array_values($fields);
			
			// Add row to database
			$query = $this->prepare('UPDATE posts SET ' . implode(' = ?, ', $columns) . ' = ? WHERE post_id = ' . $this->posts[$i]['post_id'] . ';');
			if(!$query->execute($values)){
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Format time
	 *
	 * @param string $format Same format as date();
	 * @return void
	 */
	public function formatTime($format=null){
		foreach($this->posts as &$post){
			$post['post_created_format'] = parent::formatTime($post['post_created'], $format);
			$post['post_modified_format'] = parent::formatTime($post['post_modified'], $format);
			$post['post_published_format'] = parent::formatTime($post['post_published'], $format);
		}
	}
	
	
	/**
	 * Add string notation to particular sequence, good for CSS columns
	 *
	 * @param string $label String notation
	 * @param int $frequency 
	 * @param bool $start_first True if first post should be selected and begin sequence
	 * @return void
	 */
	public function addSequence($label, $frequency, $start_first=false){
		if($start_first === false){
			$i = 1;
		}
		else{
			$i = $frequency;
		}
		
		// Store post comment fields
		foreach($this->posts as &$post){
			if($i == $frequency){
				if(empty($post['post_sequence'])){
					$post['post_sequence'] = $label;
				}
				else{
					$post['post_sequence'] .= ' ' . $label;
				}
				$i = 1;
			}
			else{
				$i++;
			}
		}
		
		return true;
	}
	
	/**
	 * Get word and numerical sequencing of posts
	 *
	 * @param int $start First number on page
	 * @param bool $asc Sequence order (false if DESC)
	 * @return void
	 */
	public function getSeries($start=null, $asc=true){
		if(!isset($start)){
			$start = 1;
		}
		else{
			$start = intval($start);
		}
		
		if($asc === true){
			$values = range($start, $start+$this->post_count);
		}
		else{
			$values = range($start, $start-$this->post_count);
		}
		
		for($i = 0; $i < $this->post_count; ++$i){
			$this->posts[$i]['post_numeric'] = $values[$i];
			$this->posts[$i]['post_alpha'] = ucwords($this->numberToWords($values[$i]));
		}
	}
	
	/**
	 * Get comments data, append comment <input> HTML data
	 *
	 * @param bool Published (true) or all (false)
	 * @return array Associative array of comments
	 */
	public function getComments($published=true){
		if($published == true){
			$query = $this->prepare('SELECT * FROM comments, posts' . $this->sql . ' AND comments.post_id = posts.post_id AND comments.comment_status > 0;');
		}
		else{
			$query = $this->prepare('SELECT * FROM comments, posts' . $this->sql . ' AND comments.post_id = posts.post_id;');
		}
		$query->execute();
		$this->comments = $query->fetchAll();
		
		foreach($this->comments as &$comment){
			if(!empty($comment['comment_author_avatar'])){
				$comment['comment_author_avatar'] = '<img src="' . $comment['comment_author_avatar'] . '" alt="" />';
			}
			$comment['comment_created'] = parent::formatTime($comment['comment_created']);
		}
		
		// Store post comment fields
		for($i = 0; $i < $this->post_count; ++$i){
			$this->posts[$i]['post_comment_text'] = '<textarea id="comment_' . $this->posts[$i]['post_id'] . '_text" name="comment_' . $this->posts[$i]['post_id'] . '_text" class="comment_text"></textarea>';
			
			$this->posts[$i]['post_comment_author_name'] = '<input type="text" id="comment_' . $this->posts[$i]['post_id'] . '_author_name" name="comment_' . $this->posts[$i]['post_id'] . '_author_name" class="comment_author_name" />';
			
			$this->posts[$i]['post_comment_author_email'] = '<input type="text" id="comment_' . $this->posts[$i]['post_id'] . '_author_email" name="comment_' . $this->posts[$i]['post_id'] . '_author_email" class="comment_author_email" />';
			
			$this->posts[$i]['post_comment_author_uri'] = '<input type="text" id="comment_' . $this->posts[$i]['post_id'] . '_author_uri" name="comment_' . $this->posts[$i]['post_id'] . '_author_uri" class="comment_author_uri" />';
		
			$this->posts[$i]['post_comment_submit'] = '<input type="hidden" name="post_id" value="' . $this->posts[$i]['post_id'] . '" /><input type="submit" id="" name="" class="comment_submit" value="Submit comment" />';
		}
		
		return $this->comments;
	}
	
}

?>