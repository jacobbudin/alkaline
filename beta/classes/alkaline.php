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
	const build = 173;
	const copyright = 'Powered by <a href="http://www.alkalineapp.com/">Alkaline</a>. Copyright &copy; 2010 by <a href="http://www.budinltd.com/">Budin Ltd.</a> All rights reserved.';
	const version = '1.0';
	
	public $js;
	public $tables = array('photos' => 'photo_id', 'tags' => 'tag_id', 'comments' => 'comment_id', 'piles' => 'pile_id', 'pages' => 'page_id', 'rights' => 'right_id', 'extensions' => 'extension_id', 'themes' => 'theme_id');
	
	public $db;
	protected $guest;
	protected $notifications;
	
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
	
	// FILE HANDLING
	// Seek directory
	private function seekDirectory($dir=null, $ext=IMG_EXT){
		// Error checking
		if(empty($dir)){
			$dir = PATH . SHOEBOX;
		}
		
		$files = array();
		$ignore = array('.', '..');
		
		// Open listing
		$handle = opendir($dir);
		
		// Seek directory
		while($filename = readdir($handle)){
			if(!in_array($filename, $ignore)){ 
				// Recusively check directories
				/*
				if(is_dir($dir . '/' . $filename)){
					self::seekDirectory($dir . $filename . '/', $files);
				}
				*/

				// Find files with proper extensions
				if(preg_match('([a-zA-Z0-9\-\_]+\.(' . $ext . '){1,1})', $filename)){
					$files[] = $dir . $filename;
				}
			}
	    }
	
		// Close listing
		closedir($handle);
		
		return $files;
	}
	
	// Count compatible photos in shoebox
	public function countDirectory($dir = null){
		// Error checking
		if(empty($dir)){
			$dir = PATH . SHOEBOX;
		}
		
		$files = self::seekDirectory($dir);
		$count = count($files);
		
		return $count;
	}
	
	// Get filename
	public function getFilename($file){
		$matches = array();
		preg_match('#^(.*/)?(?:$|(.+?)(?:(\.[^.]*$)|$))#si', $file, $matches);
		if(count($matches) < 1){
			return false;
		}
		$filename = $matches[2] . $matches[3];
		return $filename;
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
					chmod($dir . $filename, 0777);
					unlink($dir . $filename);
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
		return $input;
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
		return $input;
	}
	
	// CONVERT INTEGER-LIKE STRINGS TO INTEGERS
	// Convert a possible string or integer into an array of integers
	public function makeStringInt(&$input){
		if(!is_string($input)){
			break;
		}
		if(preg_match('#^[0-9]+$#s', $input)){
			$input = intval($input);
		}
		return $input;
	}
	
	// FORMAT TIME
	// Make time more human-readable
	public function formatTime($time, $format=null){
		// Error checking
		if(empty($time)){
			return false;
		}
		
		$time = str_replace('tonight', 'today', $time);
		
		$ampm = array(' am', ' pm');
		$ampm_correct = array(' a.m.', ' p.m.');
		
		if(empty($format)){
			$time = date(DATE_FORMAT, @strtotime($time));
		}
		else{
			$time = date($format, @strtotime($time));
		}
		
		$time = str_replace($ampm, $ampm_correct, $time);
		
		return $time;
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
	public function getTags(){
		$query = $this->db->prepare('SELECT tags.tag_name, tags.tag_id, photos.photo_id FROM tags, links, photos WHERE tags.tag_id = links.tag_id AND links.photo_id = photos.photo_id;');
		$query->execute();
		$tags = $query->fetchAll();
		
		$tag_ids = array();
		$tag_names = array();
		$tag_counts = array();
		$tag_uniques = array();
		
		foreach($tags as $tag){
			$tag_names[] = $tag['tag_name'];
			$tag_ids[$tag['tag_name']] = $tag['tag_id'];
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
		natsort($tag_uniques);
		
		$tags = array();
		
		foreach($tag_uniques as $tag){
			$tags[] = array('id' => $tag_ids[$tag],
				'size' => round(((($tag_counts[$tag] - 1) * 3) / $tag_count_high) + 1, 2),
				'name' => $tag,
				'count' => $tag_counts[$tag]);
		}
		
		return $tags;
	}
	
	// PROCESS COMMENTS
	public function addComments(){
		if(empty($_POST['comment_id'])){
			return false;
		}
		
		$id = self::findID($_POST['comment_id']);
		
		$fields = array('photo_id' => $id,
			'comment_text' => strip_tags($_POST['comment_' . $id .'_text']),
			'comment_author_name' => strip_tags($_POST['comment_' . $id .'_author_name']),
			'comment_author_url' => strip_tags($_POST['comment_' . $id .'_author_url']),
			'comment_author_email' => strip_tags($_POST['comment_' . $id .'_author_email']),
			'comment_author_ip' => $_SERVER['REMOTE_ADDR']);
		
		$orbit = new Orbit;
		$fields = $orbit->hook('comment_add', $fields, $fields);
		
		if($this->addRow($fields, 'comments')){
			return false;
		}
		
		$this->updateCount('comments', 'photos', 'photo_comment_count', $id);
		
		return true;
	}
	
	public function updateCount($count_table, $result_table, $result_field, $result_id){
		$result_id = intval($result_id);
		
		$count_id_field = $this->tables[$count_table];
		$result_id_field = $this->tables[$result_table];
		
		// Get count
		$query = $this->db->prepare('SELECT COUNT(' . $count_id_field . ') AS count FROM ' . $count_table . ' WHERE ' . $result_id_field . ' = ' . $result_id .';');
		
		if(!$query->execute()){
			return false;
		}
		
		$counts = $query->fetchAll();
		$count = $counts[0]['count'];
		
		// Update row
		$query = 'UPDATE ' . $result_table . ' SET ' . $result_field . ' = ' . $count . ' WHERE ' . $result_id_field . ' = ' . $result_id . ';';
		
		if(!$this->db->exec($query)){
			return false;
		}
		
		return true;
	}
	
	// SHOW RIGHTS
	public function showRights($name){
		if(empty($name)){
			return false;
		}
		
		$query = $this->db->prepare('SELECT right_id, right_title FROM rights;');
		$query->execute();
		$rights = $query->fetchAll();
		
		$html = '<select name="' . $name . '" id="' . $name . '"><option value="">All</option>';
		
		foreach($rights as $right){
			$html .= '<option value="' . $right['right_id'] . '">' . $right['right_title'] . '</option>';
		}
		
		$html .= '</select>';
		
		return $html;
	}
	
	public function getTable($table=null, $ids=null, $limit=null, $page=1, $order_by=null){
		if(empty($table)){
			return false;
		}
		
		$order_by_sql = '';
		$limit_sql = '';
		
		if(!empty($order_by)){
			$order_by_sql = ' ORDER BY ' . $order_by;
		}
		
		if(!empty($limit)){
			$limit = intval($limit);
			$page = intval($page);
			$limit_sql = ' LIMIT ' . (($limit * ($page - 1)) - $limit);
		}
		
		if(empty($ids)){
			$query = $this->db->prepare('SELECT * FROM ' . $table . $order_by_sql . $limit_sql . ';');
		}
		else{
			$ids = self::convertToIntegerArray($ids);
			$field = $this->tables[$table];
			
			$query = $this->db->prepare('SELECT * FROM ' . $table . ' WHERE ' . $field . ' = ' . implode(' OR ' . $field . ' = ', $ids) . $order_by_sql . $limit_sql . ';');
		}
		
		$query->execute();
		$table = $query->fetchAll();
		
		return $table;
	}
	
	public function addRow($fields=null, $table){
		// Error checking
		if(empty($table) or (!is_array($fields) and isset($fields))){
			return false;
		}
		
		if(empty($fields)){
			$fields = array();
		}
		
		// Clean up input for database insertion
		foreach($fields as $key => &$value){
			$value = addslashes($value);
		}
		
		// Add default fields
		switch($table){
			case 'comments':
				$fields['comment_created'] = date('Y-m-d H:i:s');
				break;
			case 'piles':
				$fields['pile_created'] = date('Y-m-d H:i:s');
				$fields['pile_modified'] = date('Y-m-d H:i:s');
				break;
			case 'pages':
				$fields['page_created'] = date('Y-m-d H:i:s');
				$fields['page_modified'] = date('Y-m-d H:i:s');
				break;
		}
		
		// Split up fields array for insertion
		$columns = array_keys($fields);
		$values = array_values($fields);
		
		// Add row to database
		$query = 'INSERT INTO ' . $table . ' (' . implode(', ', $columns) . ') VALUES ("' . implode('", "', $values) . '");';
		
		if(!$this->db->exec($query)){
			return false;
		}
		
		// Return ID
		$id = intval($this->db->lastInsertId());
		return $id;
	}
	
	public function updateRow($fields, $table, $ids=null, $default=true){
		// Error checking
		if(empty($fields) or empty($table) or !is_array($fields)){
			return false;
		}
		
		$ids = self::convertToIntegerArray($ids);
		$field = $this->tables[$table];
		
		// Add default fields
		if($default == true){
			switch($table){
				case 'piles':
					$fields['pile_modified'] = date('Y-m-d H:i:s');
					break;
				case 'pages':
					$fields['page_modified'] = date('Y-m-d H:i:s');
					break;
			}
		}
		
		// Convert array of database fields to SQL-friendly string
		$fields_combined = array();
		
		foreach($fields as $key => $value){
			$fields_combined[] = $key . ' = "' . addslashes($value) . '"';
		}
		
		$fields_sql = implode(', ', $fields_combined);
		
		// Update row
		$query = 'UPDATE ' . $table . ' SET ' . $fields_sql . ' WHERE ' . $field . ' = ' . implode(' OR ' . $field . ' = ', $ids) . ';';
		
		if(!$this->db->exec($query)){
			return false;
		}
		
		return true;
	}
	
	public function deleteRow($table, $ids=null){
		if(empty($table) or empty($ids)){
			return false;
		}
		
		$ids = self::convertToIntegerArray($ids);
		$field = $this->tables[$table];
		
		// Delete row
		$query = 'DELETE FROM ' . $table . ' WHERE ' . $field . ' = ' . implode(' OR ' . $field . ' = ', $ids) . ';';
		
		if(!$this->db->exec($query)){
			return false;
		}
		
		return true;
	}
	
	// GET LIBRARY INFO
	public function getInfo(){
		$info = array();
		
		// Get tables
		$tables = $this->tables;
		
		// Exclude tables
		unset($tables['rights']);
		
		// Run helper function
		foreach($tables as $table => $selector){
			$info[] = array('table' => $table, 'count' => self::countTable($table, $selector));
		}
		
		foreach($info as &$table){
			if($table['count'] == 1){
				$table['display'] = preg_replace('#s$#si', '', $table['table']);
			}
			else{
				$table['display'] = $table['table'];
			}
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
		if(preg_match('#^([0-9]+)#s', $string, $matches)){
			$match = intval($matches[1]);
		}
		else{
			return false;
		}
		return $match;
	}
	
	// Make a URL-friendly string
	public function makeURL($string){
		$string = html_entity_decode($string, 1, 'UTF-8');
		$string = strtolower($string);
		$string = preg_replace('#([^a-zA-Z0-9]+)#s', '-', $string);
		$string = preg_replace('#^(\-)+#s', '', $string);
		$string = preg_replace('#(\-)+$#s', '', $string);
		return $string;
	}
	
	// Minimize non-unique elements of a URL
	public function minimizeURL($url){
		$url = preg_replace('#^http\:\/\/www\.#s', '', $url);
		$url = preg_replace('#^http\:\/\/#s', '', $url);
		$url = preg_replace('#^www\.#s', '', $url);
		$url = preg_replace('#\/$#s', '', $url);
		return $url;
	}
	
	// Trim long strings
	public function fitString($string, $length=50){
		if(strlen($string) > $length){
			$string = substr($string, 0, $length - 3) . '&#0133;';
		}
		return $string;
	}
	
	// Chose between singular and plural nouns
	public function echoCount($count, $singular, $plural=null){
		if(empty($plural)){
			$plural = $singular . 's';
		}
		
		if($count == 1){
			echo $count . ' ' . $singular;
		}
		else{
			echo $count . ' ' . $plural;
		}
	}
}

?>