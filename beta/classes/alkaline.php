<?php

require_once('orbit.php');

class Alkaline{
	public $build = '1';
	public $version = 'Alpha (May 19)';
	public $js;
	
	protected $db;
	protected $notifications;
	
	public $tag_count;
	
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
	
	// TEMPORARY
	public function injectJS($name){
		$this->js[] = $name;
	}
	
	// TEMPORARY
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
			
			return $count;
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
	
	// Trim (remove whitespace) from values of an array
	public function trimValue(&$value){
		$value = trim($value);
	}

	// Convert a possible string or integer into an array
	public function convertToArray(&$input){
		if(is_string($input)){
			$find = strpos($input, ',');
			if($find === false){
				$input = array($input);
			}
			else{
				$input = explode(',', $input);
				$input = array_map('trim', $input);
			}
		}
		elseif(is_int($input)){
			$input = array($input);
		}
	}

	// Convert a possible string or integer into an array of integers
	public function convertToIntegerArray(&$input){
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
				$input = array_map('trim', $input);
			}
		}
	}
	
	// Make time more human-readable
	function formatTime(&$time, $format){
		if(!empty($time)){
			$time = str_replace('tonight', 'today', $time);
			if(empty($format)){
				$ampm = array(' am', ' pm');
				$ampm_correct = array(' a.m.', ' p.m.');
				$time = str_replace($ampm, $ampm_correct, date(DATE_FORMAT, @strtotime($time)));
			}
			else{
				$time = date($format, @strtotime($time));
			}
		}
	}
	
	// Retrieve all tags
	function allTags($cloud=false, $admin=false){
		$query = $this->db->prepare('SELECT tags.tag_name, tags.tag_id, photos.photo_id FROM tags, links, photos WHERE tags.tag_id = links.tag_id AND links.photo_id = photos.photo_id;');
		$query->execute();
		$tags = $query->fetchAll();
		
		$tag_names = array();
		$tag_counts = array();
		$tag_uniques = array();
		
		foreach($tags as $tag){
			$tag_names[] = $tag['tag_name'];
		}
		
		$tag_counts = array_count_values($tag_names);
		
		$tag_count_values = array_values($tag_counts);
		$tag_count_high = 0;
		
		foreach($tag_count_values as $value){
			if($value > $tag_count_high){
				$tag_count_high = $value;
			}
		}
		
		$tag_uniques = array_unique($tag_names);
		$this->tag_count = count($tag_uniques);
		
		$tags = array();
		
		foreach($tag_uniques as $tag){
			$tags[] = '<span style="font-size: ' . round(((($tag_counts[$tag] - 1) * 3) / $tag_count_high) + 1, 2)  . 'em;">' . $tag . '</span> <span class="small quiet">(' . $tag_counts[$tag] . ')</span>';
		}
		
		return implode(', ', $tags);
	}
	
	// Record a visitor to statistics
	public function recordStat($page_type=null){
		if(empty($_SESSION['duration_start']) or ((time() - @$_SESSION['duration_recent']) > 3600)){
			$duration = 0;
			$_SESSION['duration_start'] = time();
		}
		else{
			$duration = time() - $_SESSION['duration_start'];
		}
		
		$_SESSION['duration_recent'] = time();
		
		$referrer = (!empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : null;
		$page = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : null;
		$local = (stripos($referrer, LOCATION)) ? 1 : 0;
		
		$query = 'INSERT INTO stats (stat_session, stat_date, stat_duration, stat_referrer, stat_page, stat_page_type, stat_local) VALUES ("' . session_id() . '", "' . date('Y-m-d H:i:s') . '", "' . $duration . '", "' . $referrer . '", "' . $page . '", "' . $page_type . '", ' . $local . ');';
		$this->db->exec($query);
	}
	
	// Set form option
	public function setForm(&$array, $name, $unset=''){
		@$value = $_POST[$name];
		if(!isset($value)){
			$array[$name] = $unset;
		}
		elseif(empty($value)){
			$array[$name] = '';
		}
		elseif($value == 'on'){
			$array[$name] = 1;
		}
		else{
			$array[$name] = $value;
		}
	}
	
	// Retrieve form option
	public function readForm($array, $name, $check=true){
		@$value = $array[$name];
		if(!isset($value)){
			return false;
		}
		elseif($check === true){
			if($value == 1){
				return 'checked="checked"';
			}
		}
		else{
			return 'value="' . $value . '"';
		}
	}
	
	// Find ID number from string
	public function findID($string){
		$matches = array();
		preg_match('/([0-9]+)/s', $string, $matches);
		return @$matches[1];
	}
}

?>