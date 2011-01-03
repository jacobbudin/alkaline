<?php

class Pile extends Alkaline{
	public $pile_id;
	public $pile_count;
	public $piles;
	
	public function __construct($pile=null){
		parent::__construct();
		
		if(!empty($pile)){
			$sql_params = array();
			if(is_int($pile)){
				$pile_id = $pile;
				$query = $this->prepare('SELECT * FROM piles WHERE pile_id = ' . $pile_id . ';');
			}
			elseif(is_string($pile)){
				$query = $this->prepare('SELECT * FROM piles WHERE (LOWER(pile_title_url) LIKE :pile_title_url);');
				$sql_params[':pile_title_url'] = '%' . strtolower($pile) . '%';
			}
			elseif(is_array($pile)){
				$pile_ids = convertToIntegerArray($pile);
				$query = $this->prepare('SELECT * FROM piles WHERE pile_id = ' . implode(' OR pile_id = ', $pile_ids) . ';');
			}
			
			if(!empty($query)){
				$query->execute($sql_params);
				$this->piles = $query->fetchAll();
				
				$this->pile_count = count($this->piles);
			}
		}
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	// Perform object Orbit hook
	public function hook($orbit=null){
		if(!is_object($orbit)){
			$orbit = new Orbit;
		}
		
		$this->piles = $orbit->hook('pile', $this->piles, $this->piles);
		return true;
	}
	
	public function create(){
		
	}
	
	public function fetchAll(){
		$query = $this->prepare('SELECT * FROM piles;');
		$query->execute();
		$this->piles = $query->fetchAll();
		
		$this->pile_count = count($this->piles);
	}
	
	public function search($search=null){
		
	}
	
	public function update($fields){
		$ids = array();
		foreach($this->piles as $pile){
			$ids[] = $pile['pile_id'];
		}
		return parent::updateRow($fields, 'piles', $ids);
	}
}

?>