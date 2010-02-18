<?php

class Find extends Alkaline{
	public $photo_ids;
	protected $sql;
	private $sql_conds;
	protected $sql_limit;
	protected $sql_where;
	
	public function __construct(){
		parent::__construct();
		$this->photo_ids = array();
		$this->sql = 'SELECT photo_id FROM photos';
		$this->sql_conds = array();
		$this->sql_limit = '';
		$this->sql_where = '';
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function findByUploaded($begin=null, $end=null){
		if(empty($begin) and empty($end)){ return false; }
		if(!empty($begin)){
			if(is_int($begin)){ $begin = strval($begin); }
			if(strlen($begin) == 4){ $begin .= '-01-01'; }
			$begin = date('Y-m-d', strtotime($begin));
			$this->sql_conds[] = 'photos.photo_uploaded >= "' . $begin . '"';
		}
		if(!empty($end)){
			if(is_int($end)){ $end = strval($end); }
			if(strlen($end) == 4){ $end .= '-01-01'; }
			$end = date('Y-m-d', strtotime($end));
			$this->sql_conds[] = 'photos.photo_uploaded <= "' . $end . '"';
		}
	}
	
	public function findByViews($min=null, $max=null){
		if(empty($max) and empty($min)){ return false; }
		if(!empty($max) and is_int($max)){
			$this->sql_conds[] = 'photos.photo_views <= ' . $max;
		}
		if(!empty($min) and is_int($min)){
			$this->sql_conds[] = 'photos.photo_views >= ' . $min;
		}
	}
	
	public function search($query=null){
		if(empty($query)){ return false; }
		$sql = '(';
		$sql .= 'LOWER(photos.photo_title) LIKE "%' . strtolower($query) . '%" OR ';
		$sql .= 'LOWER(photos.photo_description) LIKE "%' . strtolower($query) . '%"';
		$sql .= ')';
		$this->sql_conds[] = $sql;
	}
	
	public function page($page, $limit=LIMIT){
		if(!empty($page)){
			$begin = ($page * $limit) - $limit;
			$this->sql_limit = ' LIMIT ' . $begin . ', ' . $limit;
			return true;
		}
		else{ return false; }
	}
	
	public function exec(){
		if(count($this->sql_conds) > 0){
			$this->sql_where = ' WHERE ' . implode(' AND ', $this->sql_conds);
		}
		$this->sql = $this->sql . $this->sql_where . $this->sql_limit;
		$query = $this->db->prepare($this->sql);
		$query->execute();
		$this->photos = $query->fetchAll();
		
		$this->photo_ids = array();
		foreach($this->photos as $photo){
			$this->photo_ids[] = $photo['photo_id'];
		}
		
		if(count($this->photo_ids) > 0){
			return $this->photo_ids;
		}
		else{ return false; }
	}
}

?>