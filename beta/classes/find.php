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
	private $sql_conds;
	protected $sql_limit;
	protected $sql_where;
	
	public function __construct(){
		parent::__construct();
		
		// Store data to object
		$this->photo_ids = array();
		$this->sql = 'SELECT photo_id FROM photos';
		$this->sql_conds = array();
		$this->sql_limit = '';
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
	
	// FIND BY SEARCH
	public function search($query=null){
		// Error checking
		if(empty($query)){ return false; }
		
		// Set fields to search
		$sql = '(';
		$sql .= 'LOWER(photos.photo_title) LIKE "%' . strtolower($query) . '%" OR ';
		$sql .= 'LOWER(photos.photo_description) LIKE "%' . strtolower($query) . '%"';
		$sql .= ')';
		$this->sql_conds[] = $sql;
		
		return true;
	}
	
	// PAGINATE RESULTS
	public function page($page, $limit=LIMIT){
		// Store data to object
		$this->page = intval($page);
		$this->page_limit = intval($limit);
		
		// Error checking
		if(empty($page)){ return false; }
		if($page == 0){ return false; }
		
		// 
		
		// Set SQL limit
		$begin = ($page * $limit) - $limit;
		$this->sql_limit = ' LIMIT ' . $begin . ', ' . $limit;
		
		return true;
	}
	
	// EXECUTE QUERY
	public function exec(){
		// Prepare SQL conditions
		if(count($this->sql_conds) > 0){
			$this->sql_where = ' WHERE ' . implode(' AND ', $this->sql_conds);
		}
		
		// Prepare query without limit
		$this->sql = $this->sql . $this->sql_where;
		
		// Execute query without limit
		$query = $this->db->prepare($this->sql);
		$query->execute();
		$photos = $query->fetchAll();
		
		// Determine number of photos
		$this->photo_count = count($photos);
		
		// Add limit
		$this->sql .= $this->sql_limit;
		
		// Execute query with limit
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
			$this->page_count = ceil($this->photo_count / $this->page);
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