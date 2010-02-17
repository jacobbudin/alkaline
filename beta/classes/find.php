<?php

class Find extends Alkaline{
	public function __construct(){
		parent::__construct();
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function findByDateUploaded($start=null, $finish=null, $limit=LIMIT){
		if(empty($start) and empty($finish)){ return false; }
	}
	
	public function findByViews($mix=null, $max=null, $limit=LIMIT){
		if(empty($max) and empty($min)){ return false; }
	}
	
	public function search($query=null, $limit=LIMIT){
		if(empty($query)){ return false; }
		
	}
}

?>