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
	
	public function findByDateUploaded($start=null, $finish=null){
		if(empty($start) and empty($finish)){ return false; }
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