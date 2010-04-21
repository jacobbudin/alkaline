<?php

class Notify extends Alkaline{
	public $notifications;
	
	public function __construct(){
		parent::__construct();
		if(!empty($_SESSION['notifications'])){
			$this->notifications = $_SESSION['notifications'];
		}
		else{
			$this->notifications = array();
		}
	}
	
	public function __destruct(){
		$_SESSION['notifications'] = $this->notifications;
		parent::__destruct();
	}
	
	public function add($type, $message){
		$this->notifications[] = array('type' => $type, 'message' => $message);
	}
	
	public function view(){
		$count = count($this->notifications);
		if($count > 0){
			// Determine unique types
			$types = array();
			foreach($this->notifications as $notifications){
				$types[] = $notifications['type'];
			}
			$types = array_unique($types);

			// Produce HTML for display
			foreach($types as $type){
				echo '<p class="' . $type . '">';
				$messages = '';
				foreach($this->notifications as $notification){
					if($notifications['type'] == $type){
						$messages = $messages . ' ' . $notification['message'];
					}
				}
				$messages = ltrim($messages);
				echo $messages . '</p>';
			}

			// Dispose of messages
			unset($_SESSION['notifications']);
			unset($this->notifications);
			$this->notifications = array();
		}
		else{
			return false;
		}
	}
}

?>