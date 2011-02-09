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
	public $page;
	public $page_begin;
	public $page_count;
	public $page_limit;
	public $page_limit_first;
	public $page_next;
	public $page_previous;
	public $posts;
	public $post_ids;
	public $post_count;
	public $post_count_result;
	protected $sql;
	protected $sql_conds;
	protected $sql_limit;
	protected $sql_sorts;
	protected $sql_from;
	protected $sql_tables;
	protected $sql_group_by;
	protected $sql_having;
	protected $sql_having_fields;
	protected $sql_params;
	protected $sql_order_by;
	protected $sql_where;
	
	/**
	 * Initiate Post object
	 *
	 * @param array|int|string $post Search posts (post IDs, post titles)
	 */
	public function __construct($posts_ids=null){
		parent::__construct();
		
		// Store data to object
		$this->comments = array();
		$this->comment_ids = array();
		$this->page = 1;
		$this->page_limit = LIMIT;
		$this->image_ids = array();
		$this->sql = 'SELECT *';
		$this->sql_conds = array();
		$this->sql_limit = '';
		$this->sql_sorts = array();
		$this->sql_from = '';
		$this->sql_tables = array('posts');
		$this->sql_join = '';
		$this->sql_join_type = '';
		$this->sql_join_tables = array();
		$this->sql_join_on = array();
		$this->sql_group_by = ' GROUP BY posts.post_id';
		$this->sql_having = '';
		$this->sql_injection = '';
		$this->sql_having_fields = array();
		$this->sql_params = array();
		$this->sql_order_by = '';
		$this->sql_where = '';
		
		if(!empty($comment_ids)){
			$comment_ids = parent::convertToIntegerArray($comment_ids);
			$this->sql_conds[] = 'posts.post_id IN (' . implode(', ', $posts_ids) . ')';
		}
		
		if(!empty($_REQUEST['q'])){
			$this->search($_REQUEST['q']);
		}
		if(!empty($_REQUEST['created_begin']) or !empty($_REQUEST['created_end'])){
			$this->created($_REQUEST['created_begin'], $_REQUEST['created_end']);
		}
		if(!empty($_REQUEST['status'])){
			$this->status($_REQUEST['status']);
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
	 * Find by search (text, author)
	 *
	 * @param string $search Search string
	 * @return bool True if successful
	 */
	public function search($search=null){
		// Error checking
		if(empty($search)){ return false; }
		
		// Prepare input
		$search_lower = strtolower($search);
		$search_lower = preg_replace('#\s#', '%', $search_lower);
		$search_lower = '%' . $search_lower . '%';
		
		$query = $this->prepare('SELECT posts.post_id FROM posts WHERE (LOWER(post_text) LIKE :post_text) OR (LOWER(post_title) LIKE :post_title);');
		$query->execute(array(':post_text' => $search_lower, ':post_title' => $search_lower));
		$posts = $query->fetchAll();
		
		$post_ids = array();
		
		foreach($posts as $post){
			$post_ids[] = $post['post_id'];
		}
		
		if(count($post_ids)){
			$this->sql_conds[] = 'posts.post_id IN (' . implode(', ', $post_ids) . ')';
		}
		else{
			$this->sql_conds[] = 'posts.post_id IN (NULL)';
		}
		
		return true;
	}
	
	/**
	 * Find by publish status
	 *
	 * @param bool $published
	 * @return void
	 */
	public function published($published=true){
		$now = date('Y-m-d H:i:s');
		
		if($published == true){
			$this->sql_conds[] = 'posts.post_published < :post_published';
			$this->sql_params[':post_published'] = $now;
		}
		if($published == false){
			$this->sql_conds[] = '(posts.post_published > :post_published OR post_published IS NULL)';
			$this->sql_params[':post_published'] = $now;
		}
	}

	/**
	 * Find by date created
	 *
	 * @param string $begin Date begin 
	 * @param string $end Date end
	 * @return bool True if successful
	 */
	public function created($begin=null, $end=null){
		// Error checking
		if(empty($begin) and empty($end)){ return false; }
		
		// Set begin date
		if(!empty($begin)){
			if(is_int($begin)){ $begin = strval($begin); }
			if(strlen($begin) == 4){ $begin .= '-01-01'; }
			$begin = date('Y-m-d', strtotime($begin));
			$this->sql_conds[] = 'posts.post_created >= :post_created_begin';
			$this->sql_params[':post_created_begin'] = $begin . ' 00:00:00';
		}
		
		// Set end date
		if(!empty($end)){
			if(is_int($end)){ $end = strval($end); }
			if(strlen($end) == 4){ $end .= '-01-01'; }
			$end = date('Y-m-d', strtotime($end));
			$this->sql_conds[] = 'posts.post_created <= :post_created_end';
			$this->sql_params[':post_created_end'] = $end . ' 23:59:59"';
		}
		
		return true;
	}
	
	/**
	 * Find by post association
	 *
	 * @param int|array $post_ids Post IDs
	 * @return bool True if successful
	 */
	public function post($post_ids=null){
		// Error checking
		if(empty($post_ids)){ return false; }
		
		$post_ids = parent::convertToIntegerArray($post_ids);
		
		$this->sql_conds[] = 'posts.post_id IN (' . implode(', ', $post_ids) . ')';
		
		return true;
	}
	
	/**
	 * Sort results
	 *
	 * @param string $column Comment table column
	 * @param string $sort Sort order (ASC or DESC)
	 * @return void
	 */
	public function sort($column, $sort='ASC'){
		// Error checking
		if(empty($column)){ return false; }
		
		$column = $this->sanitize($column);
		
		$column = strtolower($column);
		$sort = strtoupper($sort);
		
		// More error checking
		if(($sort != 'ASC') and ($sort != 'DESC')){
			return false;
		}
		
		// Set column, sort
		$this->sql_sorts[] = $column . ' ' . $sort;
		
		return true;
	}
	
	/**
	 * Find by field not null
	 *
	 * @param string $field Table field
	 * @return bool True if successful
	 */
	public function notnull($field){
		if(empty($field)){ return false; }
		
		$field = $this->sanitize($field);
		
		$this->sql_conds[] = $field . ' IS NOT NULL';
		
		return true;
	}
	
	/**
	 * Paginate results
	 *
	 * @param int $page Page number
	 * @param int $limit Number of images per page
	 * @param int $first Number of images on the first page (if different)
	 * @return bool True if successful
	 */
	public function page($page, $limit=null, $first=null){
		// Error checking
		if(empty($page)){
			if(!empty($_GET['page'])){ $page = intval($_GET['page']); }
			else{ $page = 1; }
		}
		else{
			$page = intval($page);
		}
		if($page < 1){ return false; }
		if(empty($limit)){ $limit = LIMIT; }
		if(empty($first)){ $first = $limit; }
		
		// Store data to object
		$this->page = $page;
		$this->page_limit = intval($limit);
		$this->page_limit_first = intval($first);
		
		// Set SQL limit
		if($page == 1){ $this->page_limit_curent = $this->page_limit_first; }
		else{ $this->page_limit_curent = $this->page_limit; }
		
		$this->page_begin = (($page - 1) * $this->page_limit_curent) - $this->page_limit_curent + $this->page_limit_first;
		$this->sql_limit = ' LIMIT ' . $this->page_begin . ', ' . $this->page_limit_curent;
		
		return true;
	}
	
	/**
	 * Execute Comment class to determine class variables
	 *
	 * @return void
	 */
	public function fetch(){
		// Prepare SQL
		$this->sql_from = ' FROM ' . implode(', ', $this->sql_tables);

		if(count($this->sql_conds) > 0){
			$this->sql_where = ' WHERE ' . implode(' AND ', $this->sql_conds);
		}
		
		if(count($this->sql_sorts) > 0){
			$this->sql_order_by = ' ORDER BY ' . implode(', ', $this->sql_sorts);
			if(($this->db_type == 'pgsql') or ($this->db_type == 'mssql')){
				$sql_sorts = str_ireplace(' ASC', '', $this->sql_sorts);
				$sql_sorts = str_ireplace(' DESC', '', $this->sql_sorts);
				$this->sql_group_by .= ', ' . implode(', ', $sql_sorts);
			}
		}
		else{
			$this->sql_order_by = ' ORDER BY posts.post_created DESC';
			if(($this->db_type == 'pgsql') or ($this->db_type == 'mssql')){
				$this->sql_group_by .= ', posts.post_created';
			}
		}
		
		if((count($this->sql_join_on) > 0) and (count($this->sql_join_tables) > 0) and (!empty($this->sql_join_type))){
			$this->sql_join = ' ' . $this->sql_join_type . ' ' . implode(', ', $this->sql_join_tables) . ' ON ' . implode(', ', $this->sql_join_on);
		}
		
		if(count($this->sql_having_fields) > 0){
			$this->sql_having = ' HAVING ' . implode(', ', $this->sql_having_fields);
		}

		// Prepare query without limit
		$this->sql .= $this->sql_from . $this->sql_join . $this->sql_where . $this->sql_group_by . $this->sql_having . $this->sql_order_by;
		
		// Execute query without limit
		$query = $this->prepare($this->sql);
		$query->execute($this->sql_params);
		$posts = $query->fetchAll();
		
		// Determine number of posts
		$this->post_count = count($posts);
		
		// Determine pagination
		// Determine pagination
		if(!empty($this->page)){
			$this->page_count = ceil(($this->post_count - $this->page_limit_first) / $this->page_limit) + 1;
			if($this->page < $this->page_count){
				$this->page_next = $this->page + 1;
			}
			if($this->page > 1){
				$this->page_previous = $this->page - 1;
			}
		}
		
		// Add order, limit
		$this->sql .= $this->sql_limit;

		// Execute query with order, limit
		$query = $this->prepare($this->sql);
		$query->execute($this->sql_params);
		$this->posts = $query->fetchAll();
		
		// Grab posts.post_ids of results
		$this->post_ids = array();
		
		foreach($posts as $post){
			$this->post_ids[] = intval($post['post_id']);
		}
		
		// Attach additional fields
		for($i = 0; $i < $this->post_count; ++$i){
			if(empty($this->posts[$i]['post_title_url'])){
				$this->posts[$i]['post_uri'] = LOCATION . BASE . 'post' . URL_ID . $this->posts[$i]['post_id'] . URL_RW;
			}
			else{
				$this->posts[$i]['post_uri'] = LOCATION . BASE . 'post' . URL_ID . $this->posts[$i]['post_id'] . '-' . $this->posts[$i]['post_title_url'] . URL_RW;
			}
		}
		
		// Count posts
		$this->post_count_result = count($this->posts);
		
		// Return posts.post_ids
		return $this->posts;
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
	 * Format time
	 *
	 * @param string $format Same format as date();
	 * @return void
	 */
	public function formatTime($format=null){
		foreach($this->posts as &$post){
			$post['post_created'] = parent::formatTime($post['post_created'], $format);
			$post['post_modified'] = parent::formatTime($post['post_modified'], $format);
			$post['post_published'] = parent::formatTime($post['post_published'], $format);
		}
	}
}

?>