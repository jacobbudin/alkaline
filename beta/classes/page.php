<?php

class Page extends Alkaline{
	public $page_id;
	public $page;
	
	public function __construct($page=null){
		parent::__construct();
		
		if(!empty($page)){	
			if(is_int($page)){
				$page_id = $page;
				$query = $this->db->prepare('SELECT * FROM pages WHERE page_id = ' . $page_id . ';');
			}
			elseif(is_string($page)){
				$page_title = $page;
				$query = $this->db->prepare('SELECT * FROM pages WHERE LOWER(page_title) LIKE "%' . strtolower($page_title) . '%;');
			}
			elseif(is_array($page)){
				$page_ids = convertToIntegerArray($page);
				$query = $this->db->prepare('SELECT * FROM pages WHERE page_id = ' . implode(' OR page_id = ', $page_ids) . ';');
			}
			
			if(!empty($query)){
				$query->execute();
				$this->pages = $query->fetchAll();
			}
		}
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function count(){
		$count = @count($this->pages);
		if(!is_int($count)){ $count = 0; }
		return $count;
	}
	
	public function create(){
		
	}
	
	public function fetchAll(){
		$query = $this->db->prepare('SELECT * FROM pages;');
		$query->execute();
		$this->pages = $query->fetchAll();
	}
	
	public function search($search=null){
		
	}
	
	public function update($fields){
		$ids = array();
		foreach($this->pages as $page){
			$ids[] = $page['page_id'];
		}
		return parent::updateRow($fields, 'pages', $ids);
	}
}

?>