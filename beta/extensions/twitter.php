<?php

class Twitter extends Orbit{
	public $twitter;
	
	public function __construct(){
		parent::__construct('Twitter');
		$this->fetch('twitter.php');
		$this->twitter = new Twitter_Helper('alkalineapp', 'gkek573');
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	function photo_upload($photo_ids){
		$status = 'I just uploaded a photo.';
		echo $this->twitter->updateStatus($status);
	}
}

?>