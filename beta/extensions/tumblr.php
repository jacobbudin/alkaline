<?php

class Tumblr extends Orbit{
	public function __construct(){
		parent::__construct();
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	function photo_upload($photo_ids){
		$query = $this->prepare('SELECT extension_uid, extension_class, extension_hooks, extension_preferences FROM extensions WHERE extension_status > 0 ORDER BY extension_build ASC, extension_id ASC;');
		$query->execute();
		$extensions = $query->fetchAll();
		// var_dump($extensions);
	}
}

?>