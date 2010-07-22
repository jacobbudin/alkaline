<?php

/*
// Alkaline
// Copyright (c) 2010 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalinenapp.com/
*/

function __autoload($class){
	$file = strtolower($class) . '.php';
	require_once(PATH . CLASSES . $file);
}

class Alkaline{
	const build = 166;
	const copyright = 'Powered by <a href="http://www.alkalineapp.com/">Alkaline</a>. Copyright &copy; 2010 by <a href="http://www.budinltd.com/">Budin Ltd.</a> All rights reserved.';
	const version = '1.0';
	
	public $js;
	
	public $db;
	protected $guest;
	protected $notifications;
	
	public $tag_count;
	
	public function __construct(){
		@header('Cache-Control: no-cache, must-revalidate');
		@header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
		
		// Begin a session, if one does not yet exist
		if(session_id() == ''){ session_start(); }
		
		// Load notifications variable from session
		if(!empty($_SESSION['notifications'])){ $this->notifications = $_SESSION['notifications']; }
		else{ $this->notifications = array(); }
		
		// Begin new JS injection
		$this->js = array();
		
		// Initiate database connection, if necessary
		$nodb_classes = array('Canvas');
		
		if(!in_array(get_class($this), $nodb_classes)){
			$this->db = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::ATTR_PERSISTENT => true));
		}
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
	
	// GUESTS
	// Authenticate guest
	public function access($key=null){
		// Error checking
		if(empty($key)){ return false; }
		
		$key = strip_tags($key);
		$query = $this->db->prepare('SELECT * FROM guests WHERE guest_key = "' . $key . '" LIMIT 0, 1;');
		$query->execute();
		$guest = $query->fetch();
		
		if(!$guest){
			return false;
		}
		
		$this->guest = $guest;
		$_SESSION['guest'] = $this->guest;
		
		return true;
	}
	
	// NOTIFICATIONS
	// Add notification
	public function addNotification($message, $type=null){
		$this->notifications[] = array('type' => $type, 'message' => $message);
	}
	
	// Check notifications
	public function isNotification(){
		$count = count($this->notifications);
		if($count > 0){
			// Determine unique types
			return $count;
		}
		else{
			return false;
		}
	}
	
	// View notification
	public function viewNotification($type=null){
		$count = count($this->notifications);
		if($count > 0){
			// Determine unique types
			$types = array();
			foreach($this->notifications as $notifications){
				$types[] = $notifications['type'];
			}
			$types = array_unique($types);
			
			echo '<ul>';

			// Produce HTML for display
			foreach($types as $type){
				echo '<li class="' . $type . '">';
				$messages = '';
				foreach($this->notifications as $notification){
					if($notifications['type'] == $type){
						$messages = $messages . ' ' . $notification['message'];
					}
				}
				$messages = ltrim($messages);
				echo $messages . '</li>';
			}
			
			echo '</ul>';

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
	
	// SEEK PHOTOS
	// Seek compatible photos
	public function seekPhotos($dir=null){
		// Error checking
		if(empty($dir)){
			return false;
		}
		
		$photos = array();
		
		self::seekDirectory($dir, $photos);
		
		return $photos;
	}
	
	// Seek directory
	private function seekDirectory($dir=null, &$photos){
		// Error checking
		if(empty($dir) or !isset($photos)){
			return false;
		}
		
		$ignore = array('.', '..');
		
		// Open listing
		$handle = opendir($dir);
		
		// Seek directory
		while($filename = readdir($handle)){
			if(!in_array($filename, $ignore)){ 
				// Recusively check directories
				if(is_dir($dir . '/' . $filename)){
					self::seekDirectory($dir . $filename . '/', $photos);
				}

				// Find files with proper extensions
				elseif(preg_match('([a-zA-Z0-9\-\_]+\.(' . IMG_EXT . '){1,1})', $filename)){
					$photos[] = $dir . $filename;
				}
			}
	    }
	
		// Close listing
		closedir($handle);
	}
	
	// Empty directory
	public function emptyDirectory($dir=null){
		// Error checking
		if(empty($dir)){
			return false;
		}
		
		$ignore = array('.', '..');
		
		// Open listing
		$handle = opendir($dir);
		
		// Seek directory
		while($filename = readdir($handle)){
			if(!in_array($filename, $ignore)){
				// Delete directories
				if(is_dir($dir . '/' . $filename)){
					self::emptyDirectory($dir . $filename . '/');
					@rmdir($dir . $filename . '/');
				}
				// Delete files
				else{
					@chmod($dir . $filename, 0777);
					@unlink($dir . $filename);
				}
			}
	    }
	
		// Close listing
		closedir($handle);
		
		return true;
	}
	
	// CONVERT TO ARRAY
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
	
	// CONVERT TO INTEGER ARRAY
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
	
	// FORMAT TIME
	// Make time more human-readable
	public function formatTime(&$time, $format){
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
	
	public function echoMonth($int){
		$int = intval($int);
		switch($int){
			case 1:
				return 'January';
				break;
			case 2:
				return 'February';
				break;
			case 3:
				return 'March';
				break;
			case 4:
				return 'April';
				break;
			case 5:
				return 'May';
				break;
			case 6:
				return 'June';
				break;
			case 7:
				return 'July';
				break;
			case 8:
				return 'August';
				break;
			case 9:
				return 'September';
				break;
			case 10:
				return 'October';
				break;
			case 11:
				return 'November';
				break;
			case 12:
				return 'December';
				break;	
		}
	}
	
	// SHOW TAGS
	// Display all tags
	public function showTags($cloud=false, $admin=false){
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
	
	// GET LIBRARY INFO
	public function getInfo(){
		// Tables for which to retrieve info
		$tables = array('photos' => 'photo_id', 'tags' => 'tag_id', 'comments' => 'comment_id', 'piles' => 'pile_id', 'pages' => 'page_id');
		
		$info = array();
		
		// Run helper function
		foreach($tables as $table => $selector){
			$info[$table] = self::countTable($table, $selector);
		}
		
		return $info;
	}
	
	function countTable($table, $selector){
		$query = $this->db->prepare('SELECT COUNT(' . $table . '.' . $selector . ') AS count FROM ' . $table . ';');
		$query->execute();
		$count = $query->fetch();
		
		$count = intval($count['count']);
		return $count;
	}
	
	// RECORD STATISTIC
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
	
	// FORM HANDLING
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
	
	// URL HANDLING
	// Find ID number from string
	public function findID($string){
		$matches = array();
		preg_match('/^([0-9]+)/s', $string, $matches);
		return @$matches[1];
	}
	
	// Make a URL-friendly string
	public function makeURL($string){
		$string = html_entity_decode($string, 1, 'UTF-8');
		$string = strtolower($string);
		$string = preg_replace('/([^a-zA-Z0-9]+)/s', '-', $string);
		$string = preg_replace('/^(\-)+/s', '', $string);
		$string = preg_replace('/(\-)+$/s', '', $string);
		return $string;
	}
}

?>