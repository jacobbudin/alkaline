<?php

class Orbit extends Alkaline{
	public $sandbox;
	public $class;
	private $db_safe;
	
	public function __construct($class){
		// Error checking
		if(empty($class)){ return false; }
		
		parent::__construct();
		
		$this->class = strtolower($class);
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
	
	protected function fetch($file){
		require_once(PATH . EXTENSIONS . $this->class . '/' . $file);
	}
}

?>