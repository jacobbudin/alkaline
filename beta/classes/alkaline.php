<?php

class Alkaline{
	public $js;
	protected $db;
	protected $notifications;
	protected $photos_cols;
	
	public function __construct(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
		
		// Begin a session, if one does not yet exist
		if(session_id() == ''){ session_start(); }
		
		// Load notifications variable from session
		if(!empty($_SESSION['notifications'])){ $this->notifications = $_SESSION['notifications']; }
		else{ $this->notifications = array(); }
		
		// Begin new JS injection
		$this->js = array();
		
		// Initiate database connection
		$this->db = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::ATTR_PERSISTENT => true));
	}
	
	public function __destruct(){
		// Save notifications variable to session
		$_SESSION['notifications'] = $this->notifications;
		
		// Close database connection
		$this->db = null;
	}
	
	public function injectJS($name){
		$this->js[] = $name;
	}
	
	public function dejectJS(){
		foreach($this->js as $js){
			echo '<script src="' . BASE . JS . $js . '.js" type="text/javascript"></script>';
		}
	}
	
	public function addNotification($message, $type=null){
		$this->notifications[] = array('type' => $type, 'message' => $message);
	}
	
	public function viewNotification($type=null){
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
			
			echo '<br />';

			// Dispose of messages
			unset($_SESSION['notifications']);
			unset($this->notifications);
			$this->notifications = array();
		}
		else{
			return false;
		}
	}
	
	// Seek compatible photos in a directory
	public function seekPhotos($dir){
		// Open shoebox directory
		if($handle = opendir($dir)){
			$photos = array();
			while($filename = readdir($handle)){
				// Find files with proper extensions
				if(preg_match('([a-zA-Z0-9\-\_]+\.(' . IMG_EXT . '){1,1})', $filename)){
					$photos[] = $dir . $filename;
				}
		    }
		    closedir($handle);
			return $photos;
		}
		else{ return false; }
	}
	
	// Find and store column fields into variables
	private function seekCols(){
		$query = $this->db->prepare('SELECT * FROM photos LIMIT 0,1;');
		$query->execute();
		$this->photos = $query->fetchAll();
		
		$this->photos_cols = array();
		foreach($this->photos as $photo){
			foreach($photo as $key => $value){
				$this->photos_cols[] = $key;
			}
		}
	}
	
	// Trim (that is, remove whitespace) from values of an array
	function trimValue(&$value){
		$value = trim($value);
	}

	// Convert a possible string or integer into an array
	function convertToArray(&$input){
		if(is_string($input)){
			$find = strpos($input, ',');
			if($find === false){
				$input = array($input);
			}
			else{
				$input = explode(',', $input);
				array_walk($input, 'trimValue');
			}
		}
	}

	// Convert a possible string or integer into an array of integers
	function convertToIntegerArray(&$input){
		if(is_int($input)){
			$input = array($input);
		}
		elseif(is_string($input)){
			$find = strpos($input, ',');
			if($find === false){
				$input = array(intval($input));
			}
			else{
				$input = explode(',', $input);
				array_walk($input, 'trimValue');
			}
		}
	}
}

?>