<?php

class Find extends Alkaline{
	public $photo_ids;
	public $photo_count;
	public $photo_count_result;
	public $page;
	public $page_count;
	public $page_limit;
	public $page_next;
	public $page_previous;
	protected $sql;
	protected $sql_conds;
	protected $sql_limit;
	protected $sql_sort;
	protected $sql_from;
	protected $sql_tables;
	protected $sql_order_by;
	protected $sql_where;
	
	public function __construct(){
		parent::__construct();
		
		// Store data to object
		$this->photo_ids = array();
		$this->sql = 'SELECT photos.photo_id';
		$this->sql_conds = array();
		$this->sql_limit = '';
		$this->sql_sort = array();
		$this->sql_from = '';
		$this->sql_tables = array('photos');
		$this->sql_order_by = '';
		$this->sql_where = '';
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	// FIND BY DATE UPLOADED
	public function findByUploaded($begin=null, $end=null){
		// Error checking
		if(empty($begin) and empty($end)){ return false; }
		
		// Set begin date
		if(!empty($begin)){
			if(is_int($begin)){ $begin = strval($begin); }
			if(strlen($begin) == 4){ $begin .= '-01-01'; }
			$begin = date('Y-m-d', strtotime($begin));
			$this->sql_conds[] = 'photos.photo_uploaded >= "' . $begin . '"';
		}
		
		// Set end date
		if(!empty($end)){
			if(is_int($end)){ $end = strval($end); }
			if(strlen($end) == 4){ $end .= '-01-01'; }
			$end = date('Y-m-d', strtotime($end));
			$this->sql_conds[] = 'photos.photo_uploaded <= "' . $end . '"';
		}
		
		return true;
	}
	
	// FIND BY VIEWS
	public function findByViews($min=null, $max=null){
		// Error checking
		if(empty($max) and empty($min)){ return false; }
		
		// Set maximum views
		if(!empty($max) and is_int($max)){
			$this->sql_conds[] = 'photos.photo_views <= ' . $max;
		}
		
		// Set minimum views
		if(!empty($min) and is_int($min)){
			$this->sql_conds[] = 'photos.photo_views >= ' . $min;
		}
		
		return true;
	}
	
	// FIND BY TAGS
	public function tags($tags=null){
		// Error checking
		if(empty($tags)){ return false; }
		
		parent::convertToArray($tags);
		
		// Add tables to query
		$this->sql_tables[] = 'links';
		$this->sql_tables[] = 'tags';
		
		// Set fields to search
		foreach($tags as $tag){
			$this->sql_conds[] = '(photos.photo_id = links.photo_id AND links.tag_id = tags.tag_id AND tags.tag_name = "' . $tag . '")';
		}
		
		return true;
	}
	
	// FIND BY SEARCH
	public function search($search=null){
		// Error checking
		if(empty($search)){ return false; }
		
		$search_lower = strtolower($search);
		
		// Set fields to search
		$sql = '(';
		$sql .= 'LOWER(photos.photo_title) LIKE "%' . $search_lower . '%" OR ';
		$sql .= 'LOWER(photos.photo_description) LIKE "%' . $search_lower . '%"';
		$sql .= ')';
		$this->sql_conds[] = $sql;
		
		return true;
	}
	
	// FIND BY PUBLISHED
	public function published($published=true){
		$now = date('Y-m-d H:i:s');
		if($published == true){
			$this->sql_conds[] = 'photos.photo_published < "' . $now . '"';
		}
		if($published == false){
			$this->sql_conds[] = '(photos.photo_published > "' . $now . '" OR photo_published = null)';
		}
	}
	
	// PAGINATE RESULTS
	public function page($page, $limit=LIMIT){
		// Store data to object
		$this->page = intval($page);
		$this->page_limit = intval($limit);
		
		// Error checking
		if(empty($page)){ return false; }
		if($page == 0){ return false; }
		
		// Set SQL limit
		$begin = ($page * $limit) - $limit;
		$this->sql_limit = ' LIMIT ' . $begin . ', ' . $limit;
		
		return true;
	}
	
	public function sort($column, $sort='ASC'){
		$this->sql_sort[] = $column . ' ' . $sort;
	}
	
	// EXECUTE QUERY
	public function exec(){
		// Prepare SQL conditions
		$this->sql_from = ' FROM ' . implode(', ', $this->sql_tables);
		
		if(count($this->sql_conds) > 0){
			$this->sql_where = ' WHERE ' . implode(' AND ', $this->sql_conds);
		}
		if(count($this->sql_sort) > 0){
			$this->sql_order_by = ' ORDER BY ' . implode(', ', $this->sql_sort);
		}
		
		// Prepare query without limit
		$this->sql .= $this->sql_from . $this->sql_where;
		
		// Execute query without limit
		$query = $this->db->prepare($this->sql);
		$query->execute();
		$photos = $query->fetchAll();
		
		// Determine number of photos
		$this->photo_count = count($photos);
		
		// Add order, limit
		$this->sql .= $this->sql_order_by . $this->sql_limit;
		
		// Execute query with order, limit
		$query = $this->db->prepare($this->sql);
		$query->execute();
		$photos = $query->fetchAll();
		
		// Grab photos.photo_ids of results
		$this->photo_ids = array();
		foreach($photos as $photo){
			$this->photo_ids[] = $photo['photo_id'];
		}
		
		// Count photos
		$this->photo_count_result = count($this->photo_ids);
		
		// Determine pagination
		if(!empty($this->page)){
			$this->page_count = ceil($this->photo_count / ($this->page * $this->page_limit));
			if($this->page < $this->page_count){
				$this->page_next = $this->page + 1;
			}
			if($this->page > 1){
				$this->page_previous = $this->page - 1;
			}
		}
		
		// Return photos.photo_ids
		return $this->photo_ids;
	}
}

?>