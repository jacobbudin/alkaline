<?php

class Orbit extends Alkaline{
	public $sandbox;
	private $db_safe;
	
	public function __construct(){
		parent::__construct();
		$this->db_safe = $this->db;
		unset($this->db);
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	protected function prepare($sql){
		// Check for malicious SQL
		
		
		return $this->db_safe->prepare($sql);	
	}
}

?>