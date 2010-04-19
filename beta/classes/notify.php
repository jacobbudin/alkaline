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
		$notifications[] = array('type' => $type, 'message' => $message);
	}
	
	public function view(){
		$count = count($notifications);
		if($count > 0){
			
			// Determine unique types
			$types = array();
			foreach($_SESSION['messages'] as $message){
				$types[] = $message['type'];
			}
			$types = array_unique($types);

			// Produce HTML for display
			foreach($types as $type){
				echo '<div class="' . $type . '">';
				$messages = '';
				foreach($_SESSION['messages'] as $message){
					if($message['type'] == $type){
						$messages = $messages . ' ' . $message['message'];
					}
				}
				$messages = ltrim($messages);
				echo $messages . '</div>';
			}

			// Dispose of messages
			$this->notifications = array();
			
			return $count;
		}
		else{
			return false;
		}
	}
}

?>