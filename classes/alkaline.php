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
	const build = 402;
	const copyright = 'Powered by <a href="http://www.alkalineapp.com/">Alkaline</a>. Copyright &copy; 2010 by <a href="http://www.budinltd.com/">Budin Ltd.</a> All rights reserved.';
	const version = '1.0';
	
	public $db;
	public $db_type;
	public $configuration;
	public $tables = array('photos' => 'photo_id', 'tags' => 'tag_id', 'comments' => 'comment_id', 'piles' => 'pile_id', 'pages' => 'page_id', 'rights' => 'right_id', 'exifs' => 'exif_id', 'extensions' => 'extension_id', 'themes' => 'theme_id', 'sizes' => 'size_id', 'users' => 'user_id', 'guests' => 'guest_id');
	
	protected $addendum;
	protected $guest;
	protected $notifications;
	
	public function __construct(){
		@header('Cache-Control: no-cache, must-revalidate');
		@header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
		
		// Begin a session, if one does not yet exist
		if(session_id() == ''){ session_start(); }
		
		// Debug info
		if(get_class($this) == 'Alkaline'){
			$_SESSION['alkaline']['debug']['start_time'] = microtime(true);
			$_SESSION['alkaline']['debug']['queries'] = 0;
			$_SESSION['alkaline']['config'] = json_decode(@file_get_contents($this->correctWinPath(PATH . 'assets/config.json')), true);
			
			if(empty($_SESSION['alkaline']['config'])){
				$_SESSION['alkaline']['config'] = array();
			}
			
			if($timezone = $this->returnConf('web_timezone')){
				date_default_timezone_set($timezone);
			}
			else{
				date_default_timezone_set('GMT');
			}
		}
		
		// Initiate database connection, if necessary
		$nodb_classes = array('Canvas');
		
		if(!in_array(get_class($this), $nodb_classes)){
			if(defined('DB_TYPE') and defined('DB_DSN')){
				// Determine database type
				$this->db_type = DB_TYPE;
			
				if($this->db_type == 'mssql'){
					// $this->db = new PDO(DB_DSN);
				}
				elseif($this->db_type == 'mysql'){
					$this->db = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::ATTR_PERSISTENT => true, PDO::FETCH_ASSOC => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT));
				}
				elseif($this->db_type == 'pgsql'){
					$this->db = new PDO(DB_DSN, DB_USER, DB_PASS);
					$this->db->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);
				}
				elseif($this->db_type == 'sqlite'){
					$this->db = new PDO(DB_DSN, null, null, array(PDO::ATTR_PERSISTENT => true, PDO::FETCH_ASSOC => true, PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT));
				
					$this->db->sqliteCreateFunction('ACOS', 'acos', 1);
					$this->db->sqliteCreateFunction('COS', 'cos', 1);
					$this->db->sqliteCreateFunction('RADIANS', 'deg2rad', 1);
					$this->db->sqliteCreateFunction('SIN', 'sin', 1);
				}
			}
		}
	}
	
	public function __destruct(){
		// Close database connection
		$this->db = null;
	}
	
	// DATABASE
	public function exec($query){
		$this->prequery($query);
		$response = $this->db->exec($query);
		$this->postquery($query);
		
		return $response;
	}
	
	public function prepare($query){
		$this->prequery($query);
		$response = $this->db->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$this->postquery($query);
		
		return $response;
	}
	
	public function prequery(&$query){
		$_SESSION['alkaline']['debug']['queries']++;
		
		if($this->db_type == 'mssql'){
			/*
			preg_match('#GROUP BY (.*) ORDER BY#si', $query, $match);
			$find = @$match[0];
			if(!empty($find)){
				$replace = $find;
				$replace = str_replace('stat_day', 'DAY(stat_date)', $replace);
				$replace = str_replace('stat_month', 'MONTH(stat_date)', $replace);
				$replace = str_replace('stat_year', 'YEAR(stat_date)', $replace);
				$query = str_replace($find, $replace, $query);
			}
			
			if(preg_match('#SELECT (?:.*) LIMIT[[:space:]]+([0-9]+),[[:space:]]*([0-9]+)#si', $query, $match)){
				$query = preg_replace('#LIMIT[[:space:]]+([0-9]+),[[:space:]]*([0-9]+)#si', '', $query);
				$offset = @$match[1];
				$limit = @$match[2];
				preg_match('#FROM (.+?)(?:\s|,)#si', $query, $match);
				$table = @$match[1];
				$query = str_replace('SELECT ', 'SELECT TOP 999999999999999999 ROW_NUMBER() OVER (ORDER BY ' . $this->tables[$table]  . ' ASC) AS row_number,', $query);
				$query = 'SELECT * FROM (' . $query . ') AS temp WHERE temp.row_number > ' . $offset . ' AND temp.row_number <= ' . ($offset + $limit);
			}
			*/
		}
		elseif($this->db_type == 'pgsql'){
			$query = preg_replace('#LIMIT[[:space:]]+([0-9]+),[[:space:]]*([0-9]+)#si', 'LIMIT \2 OFFSET \1', $query);
			$query = str_replace('HOUR(', 'EXTRACT(HOUR FROM ', $query);
			$query = str_replace('DAY(', 'EXTRACT(DAY FROM ', $query);
			$query = str_replace('MONTH(', 'EXTRACT(MONTH FROM ', $query);
			$query = str_replace('YEAR(', 'EXTRACT(YEAR FROM ', $query);
		}
		elseif($this->db_type == 'sqlite'){
			$query = str_replace('HOUR(', 'strftime("%H",', $query);
			$query = str_replace('DAY(', 'strftime("%d",', $query);
			$query = str_replace('MONTH(', 'strftime("%m",', $query);
			$query = str_replace('YEAR(', 'strftime("%Y",', $query);
		}
	}
	
	public function postquery(&$query, $db=null){
		if(empty($db)){ $db = $this->db; }
		
		$error = $db->errorInfo();
		
		if(isset($error[2])){
			$code = $error[0];
			$message = $query . ' ' . ucfirst(preg_replace('#^Error\:[[:space:]]+#si', '', $error[2])) . ' (' . $code . ').';
			
			if(substr($code, 0, 2) == '00'){
				$this->report($message, $code);
			}
			else{
				$this->addNotification($message, 'error');
			}
		}
	}
	
	// REMOVE NULL FROM JSON
	public function removeNull($input){
		return str_replace(':null', ':""', $input);
	}
	
	// BOOMERANG
	// Receive updates from alkalineapp.com
	public function boomerang($request){
		$reply = self::removeNull(json_decode(file_get_contents('http://www.alkalineapp.com/boomerang/' . $request . '/'), true));
		return $reply;
	}	
	
	// GUESTS
	// Authenticate guest
	public function access($key=null){
		// Error checking
		if(empty($key)){ return false; }
		
		$key = strip_tags($key);
		$query = $this->prepare('SELECT * FROM guests WHERE guest_key = :guest_key LIMIT 0, 1;');
		$query->execute(array(':guest_key' => $key));
		$guest = $query->fetch();
		
		if(!$guest){
			return false;
		}
		
		$this->guest = $guest;
		$_SESSION['alkaline']['guest'] = $this->guest;
		
		return true;
	}
	
	// NOTIFICATIONS
	// Add notification
	public function addNotification($message, $type=null){
		$_SESSION['alkaline']['notifications'][] = array('type' => $type, 'message' => $message);
		return true;
	}
	
	// Check notifications
	public function isNotification(){
		$count = @count($_SESSION['alkaline']['notifications']);
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
		$count = @count($_SESSION['alkaline']['notifications']);
		
		if($count > 0){
			// Determine unique types
			$types = array();
			foreach($_SESSION['alkaline']['notifications'] as $notifications){
				$types[] = $notifications['type'];
			}
			$types = array_unique($types);
			
			// Produce HTML for display
			foreach($types as $type){
				echo '<p class="' . $type . '">';
				$messages = array();
				foreach($_SESSION['alkaline']['notifications'] as $notification){
					if($notification['type'] == $type){
						$messages[] = $notification['message'];
					}
				}
				echo implode(' ', $messages) . '</p>';
			}
			
			echo '<br />';

			// Dispose of messages
			unset($_SESSION['alkaline']['notifications']);
			
			return $count;
		}
		else{
			return false;
		}
	}
	
	// FILE HANDLING
	// Seek directory
	public function seekDirectory($dir=null, $ext=IMG_EXT){
		// Error checking
		if(empty($dir)){
			return false;
		}
		
		// Windows-friendly
		$dir = $this->correctWinPath($dir);
		
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
	public function countDirectory($dir=null){
		// Error checking
		if(empty($dir)){
			return false;
		}
		
		$files = self::seekDirectory($dir);
		$count = count($files);
		
		return $count;
	}
	
	// Get filename
	public function getFilename($file){
		$matches = array();
		
		// Windows cheat
		$file = str_replace('\\', '/', $file);
		
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
	
	// Check permissions
	public function checkPerm($file){
		return substr(sprintf('%o', @fileperms($file)), -4);
	}
	
	// Replace variable
	public function replaceVar($var, $replacement, $subject){
		return preg_replace('#^\s*' . str_replace('$', '\$', $var) . '\s*=(.*)$#mi', $replacement, $subject);
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
	public function formatTime($time, $format=null, $empty=false){
		// Error checking
		if(empty($time) or ($time == '0000-00-00 00:00:00')){
			return $empty;
		}
		if(empty($format)){
			$format = DATE_FORMAT;
		}
		
		$time = str_replace('tonight', 'today', $time);
		$time = @strtotime($time);
		$time = date($format, $time);
		
		$ampm = array(' am', ' pm');
		$ampm_correct = array(' a.m.', ' p.m.');
		
		$time = str_replace($ampm, $ampm_correct, $time);
		
		return $time;
	}
	
	// Turn time into relative
	public function formatRelTime($time, $format=null, $empty=false){
		// Error checking
		if(empty($time) or ($time == '0000-00-00 00:00:00')){
			return $empty;
		}
		if(empty($format)){
			$format = DATE_FORMAT;
		}
		
		$time = @strtotime($time);
		$seconds = time() - $time;
		
		switch($seconds){
			case($seconds < 3600):
				$minutes = intval($seconds / 60);
				if($minutes < 2){ $span = 'a minute ago'; }
				else{ $span = $minutes . ' minutes ago'; }
				break;
			case($seconds < 86400):
				$hours = intval($seconds / 3600);
				if($hours < 2){ $span = 'an hour ago'; }
				else{ $span = $hours . ' hours ago'; }
				break;
			case($seconds < 2419200):
				$days = intval($seconds / 86400);
				if($days < 2){ $span = 'yesterday'; }
				else{ $span = $days . ' days ago'; }
				break;
			case($seconds < 29030400):
				$months = intval($seconds / 2419200);
				if($months < 2){ $months = 'a month ago'; }
				else{ $span = $months . ' months ago'; }
				break;
			default:
				$span = date($format, $time);
				break;
		}
		return $span;
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
	
	// FORMAT STRINGS
	// Convert to Unicode (UTF-8)
	public function makeUnicode($string){
		return mb_detect_encoding($string, 'UTF-8') == 'UTF-8' ? $string : utf8_encode($string);
	}
	
	// Sanitize table, column names, other data
	public function sanitize($string){
		return preg_replace('#(?:(?![a-z0-9_\.-\s]).)*#si', '', $string);
	}
	
	// Make HTML-safe quotations
	public function makeHTMLSafe($input){
		if(is_string($input)){
			$input = self::makeHTMLSafeHelper($input);
		}
		if(is_array($input)){
			foreach($input as &$value){
				$value = self::makeHTMLSafe($value);
			}
		}
		
		return $input;
	}
	
	private function makeHTMLSafeHelper($string){
		$string = preg_replace('#\'#s', '&#0039;', $string);	
		$string = preg_replace('#\"#s', '&#0034;', $string);
		return $string;
	}
	
	// Reverse HTML-safe quotations
	public function reverseHTMLSafe($input){
		if(is_string($input)){
			$input = self::reverseHTMLSafeHelper($input);
		}
		if(is_array($input)){
			foreach($input as &$value){
				$value = self::reverseHTMLSafe($value);
			}
		}
		
		return $input;
	}
	
	private function reverseHTMLSafeHelper($string){
		$string = preg_replace('#\&\#0039\;#s', '\'', $string);	
		$string = preg_replace('#\&\#0034\;#s', '"', $string);
		return $string;
	}
	
	// SHOW TAGS
	// Display all tags
	public function getTags(){
		$query = $this->prepare('SELECT tags.tag_name, tags.tag_id, photos.photo_id FROM tags, links, photos WHERE tags.tag_id = links.tag_id AND links.photo_id = photos.photo_id;');
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
	
	// Display all blocks
	public function getBlocks(){
		$blocks = self::seekDirectory(PATH . BLOCKS, '.*');
		
		foreach($blocks as &$block){
			$block = self::getFilename($block);
		}
		
		return $blocks;
	}
	
	// PROCESS COMMENTS
	public function addComments(){
		// Configuration: comm_enabled
		if(!$this->returnConf('comm_enabled')){
			return false;
		}
		
		if(empty($_POST['comment_id'])){
			return false;
		}
		
		$id = self::findID($_POST['comment_id']);
		
		if($this->returnConf('comm_mod')){
			$comment_status = 0;
		}
		else{
			$comment_status = 1;
		}
		
		$comment_text = $this->makeUnicode(strip_tags($_POST['comment_' . $id .'_text']));
		
		$fields = array('photo_id' => $id,
			'comment_status' => $comment_status,
			'comment_text' => $comment_text,
			'comment_author_name' => $comment_text,
			'comment_author_url' => strip_tags($_POST['comment_' . $id .'_author_url']),
			'comment_author_email' => strip_tags($_POST['comment_' . $id .'_author_email']),
			'comment_author_ip' => $_SERVER['REMOTE_ADDR']);
		
		$orbit = new Orbit;
		$fields = $orbit->hook('comment_add', $fields, $fields);
		
		if(!$this->addRow($fields, 'comments')){
			return false;
		}
		
		if($this->returnConf('comm_email')){
			$this->email(0, 'New comment', 'A new comment has been submitted:' . "\r\n\n" . $comment_text);
		}
		
		$this->updateCount('comments', 'photos', 'photo_comment_count', $id);
		
		return true;
	}
	
	public function updateCount($count_table, $result_table, $result_field, $result_id){
		$result_id = intval($result_id);
		
		$count_table = $this->sanitize($count_table);
		
		$count_id_field = $this->tables[$count_table];
		$result_id_field = $this->tables[$result_table];
		
		// Get count
		$query = $this->prepare('SELECT COUNT(' . $count_id_field . ') AS count FROM ' . $count_table . ' WHERE ' . $result_id_field  . ' = :result_id;');
		
		if(!$query->execute(array(':result_id' => $result_id))){
			return false;
		}
		
		$counts = $query->fetchAll();
		$count = $counts[0]['count'];
		
		// Update row
		$query = $this->prepare('UPDATE ' . $result_table . ' SET ' . $result_field . ' = :count WHERE ' . $result_id_field . ' = ' . $result_id . ';');
		
		if(!$query->execute(array(':count' => $count))){
			return false;
		}
		
		return true;
	}
	
	// SHOW RIGHTS
	public function showRights($name, $right_id=null){
		if(empty($name)){
			return false;
		}
		
		$query = $this->prepare('SELECT right_id, right_title FROM rights;');
		$query->execute();
		$rights = $query->fetchAll();
		
		$html = '<select name="' . $name . '" id="' . $name . '"><option value=""></option>';
		
		foreach($rights as $right){
			$html .= '<option value="' . $right['right_id'] . '"';
			if($right['right_id'] == $right_id){
				$html .= ' selected="selected"';
			}
			$html .= '>' . $right['right_title'] . '</option>';
		}
		
		$html .= '</select>';
		
		return $html;
	}
	
	// SHOW PRIVACY
	public function showPrivacy($name, $privacy_id=1){
		if(empty($name)){
			return false;
		}
		
		$privacy_levels = array(1 => 'Public', 2 => 'Protected', 3 => 'Private');
		
		$html = '<select name="' . $name . '" id="' . $name . '">';
		
		foreach($privacy_levels as $privacy_level => $privacy_label){
			$html .= '<option value="' . $privacy_level . '"';
			if($privacy_level == $privacy_id){
				$html .= ' selected="selected"';
			}
			$html .= '>' . $privacy_label . '</option>';
		}
		
		$html .= '</select>';
		
		return $html;
	}
	
	// TABLE AND ROW FUNCTIONS
	public function getTable($table, $ids=null, $limit=null, $page=1, $order_by=null){
		if(empty($table)){
			return false;
		}
		if(!is_int($page) or ($page < 1)){
			$page = 1;
		}
		
		$table = $this->sanitize($table);
		
		$sql_params = array();
		
		$order_by_sql = '';
		$limit_sql = '';
		
		if(!empty($order_by)){
			$order_by = $this->sanitize($order_by);
			$order_by_sql = ' ORDER BY ' . $order_by;
		}
		
		if(!empty($limit)){
			$limit = intval($limit);
			$page = intval($page);
			$limit_sql = ' LIMIT ' . ($limit * ($page - 1)) . ', ' . $limit;
		}
		
		if(empty($ids)){
			$query = $this->prepare('SELECT * FROM ' . $table . $order_by_sql . $limit_sql . ';');
		}
		else{
			$ids = self::convertToIntegerArray($ids);
			$field = $this->tables[$table];
			
			$query = $this->prepare('SELECT * FROM ' . $table . ' WHERE ' . $field . ' = ' . implode(' OR ' . $field . ' = ', $ids) . $order_by_sql . $limit_sql . ';');
		}
		
		$query->execute($sql_params);
		$table = $query->fetchAll();
		return $table;
	}
	
	public function getRow($table, $id){
		$table = $this->getTable($table, $id);
		if(count($table) != 1){ return false; }
		return $table[0];
	}
	
	public function addRow($fields=null, $table){
		// Error checking
		if(empty($table) or (!is_array($fields) and isset($fields))){
			return false;
		}
		
		if(empty($fields)){
			$fields = array();
		}
		
		$table = $this->sanitize($table);
		
		// Add default fields
		switch($table){
			case 'comments':
				$fields['comment_created'] = date('Y-m-d H:i:s');
				break;
			case 'guests':
				$fields['guest_views'] = 0;
				$fields['guest_created'] = date('Y-m-d H:i:s');
				break;
			case 'rights':
				$fields['right_modified'] = date('Y-m-d H:i:s');
				break;
			case 'pages':
				$fields['page_views'] = 0;
				$fields['page_created'] = date('Y-m-d H:i:s');
				$fields['page_modified'] = date('Y-m-d H:i:s');
				break;
			case 'piles':
				$fields['pile_views'] = 0;
				$fields['pile_created'] = date('Y-m-d H:i:s');
				$fields['pile_modified'] = date('Y-m-d H:i:s');
				break;
			case 'users':
				$fields['user_created'] = date('Y-m-d H:i:s');
				break;
			default:
				break;
		}
		
		$field = $this->tables[$table];
		unset($fields[$field]);
		
		if(count($fields) > 0){
			$columns = array_keys($fields);
			$values = array_values($fields);
		
			$value_slots = array_fill(0, count($values), '?');
		
			// Add row to database
			$query = $this->prepare('INSERT INTO ' . $table . ' (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $value_slots) . ');');
		}
		else{
			$values = array();
			$query = $this->prepare('INSERT INTO ' . $table . ' (' . $this->tables[$table] . ') VALUES (NULL);');
		}
		
		if(!$query->execute($values)){
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
		
		$table = $this->sanitize($table);
		
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
		
		$columns = array_keys($fields);
		$values = array_values($fields);

		// Add row to database
		$query = $this->prepare('UPDATE ' . $table . ' SET ' . implode(' = ?, ', $columns) . ' = ? WHERE ' . $field . ' = ' . implode(' OR ' . $field . ' = ', $ids) . ';');
		if(!$query->execute($values)){
			return false;
		}
		
		return true;
	}
	
	public function deleteRow($table, $ids=null){
		if(empty($table) or empty($ids)){
			return false;
		}
		
		$table = $this->sanitize($table);
		
		$ids = self::convertToIntegerArray($ids);
		$field = $this->tables[$table];
		
		// Delete row
		$query = 'DELETE FROM ' . $table . ' WHERE ' . $field . ' = ' . implode(' OR ' . $field . ' = ', $ids) . ';';
		
		if(!$this->exec($query)){
			return false;
		}
		
		return true;
	}
	
	public function deleteEmptyRow($table, $fields){
		if(empty($table) or empty($fields)){
			return false;
		}
		
		$table = $this->sanitize($table);
		
		$fields = self::convertToArray($fields);
		
		$conditions = array();
		foreach($fields as $field){
			$conditions[] = '(' . $field . ' = ? OR ' . $field . ' IS NULL)';
		}
		
		$sql_params = array_fill(0, count($fields), null);
		
		// Delete empty rows
		$query = $this->prepare('DELETE FROM ' . $table . ' WHERE ' . implode(' AND ', $conditions) . ';');
		
		if(!$query->execute($sql_params)){
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
		unset($tables['exifs']);
		unset($tables['extensions']);
		unset($tables['themes']);
		unset($tables['sizes']);
		unset($tables['rights']);
		
		// Run helper function
		foreach($tables as $table => $selector){
			$info[] = array('table' => $table, 'count' => self::countTable($table));
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
	
	function countTable($table){
		$field = @$this->tables[$table];
		if(empty($field)){ return false; }
		
		$query = $this->prepare('SELECT COUNT(' . $table . '.' . $field . ') AS count FROM ' . $table . ';');
		$query->execute();
		$count = $query->fetch();
		
		$count = intval($count['count']);
		return $count;
	}
	
	function countTableNew($table){
		$table = $this->sanitize($table);
		if(empty($table)){ return false; }
		
		$field = $this->tables[$table];
		$query = $this->prepare('SELECT COUNT(' . $table . '.' . $field . ') AS count FROM ' . $table . ' WHERE ' . substr($table, 0, -1) . '_status = 0;');
		$query->execute();
		$count = $query->fetch();
		
		$count = intval($count['count']);
		return $count;
	}
	
	// RECORD STATISTIC
	// Record a visitor to statistics
	public function recordStat($page_type=null){
		if(!$this->returnConf('stat_enabled')){
			return false;
		}
		
		if(empty($_SESSION['alkaline']['duration_start']) or ((time() - @$_SESSION['alkaline']['duration_recent']) > 3600)){
			$duration = 0;
			$_SESSION['alkaline']['duration_start'] = time();
		}
		else{
			$duration = time() - $_SESSION['alkaline']['duration_start'];
		}
		
		$_SESSION['alkaline']['duration_recent'] = time();
		
		$referrer = (!empty($_SERVER['HTTP_REFERER'])) ? $_SERVER['HTTP_REFERER'] : null;
		$page = (!empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : null;
		$local = (stripos($referrer, LOCATION)) ? 1 : 0;
		
		$query = $this->prepare('INSERT INTO stats (stat_session, stat_date, stat_duration, stat_referrer, stat_page, stat_page_type, stat_local) VALUES (:stat_session, :stat_date, :stat_duration, :stat_referrer, :stat_page, :stat_page_type, :stat_local);');
		
		$query->execute(array(':stat_session' => session_id(), ':stat_date' => date('Y-m-d H:i:s'), ':stat_duration' => $duration, ':stat_referrer' => $referrer, ':stat_page' => $page, ':stat_page_type' => $page_type, ':stat_local' => $local));
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
		elseif($value == 'true'){
			$array[$name] = true;
		}
		else{
			$array[$name] = $value;
		}
	}
	
	// Retrieve form option (HTML)
	public function readForm($array, $name, $check=true){
		@$value = $array[$name];
		if(!isset($value)){
			return false;
		}
		elseif($check === true){
			if($value === true){
				return 'checked="checked"';
			}
		}
		elseif(!empty($check)){
			if($value == $check){
				return 'selected="selected"';
			}
		}
		else{
			return 'value="' . $value . '"';
		}
	}
	
	// Return form option
	public function returnForm($array, $name){
		@$value = $array[$name];
		if(!isset($value)){
			return false;
		}
		return $value;
	}
	
	// CONFIGURATION HANDLING
	// Set configuration key
	public function setConf($name, $unset=''){
		return self::setForm($_SESSION['alkaline']['config'], $name, $unset);
	}
	
	// Read configuration key and return value in HTML
	public function readConf($name, $check=true){
		return self::readForm($_SESSION['alkaline']['config'], $name, $check);
	}
	
	// Read configuration key and return value
	public function returnConf($name){
		return self::makeHTMLSafe(self::returnForm($_SESSION['alkaline']['config'], $name));
	}
	
	// Save configuration
	public function saveConf(){
		return file_put_contents($this->correctWinPath(PATH . 'assets/config.json'), json_encode(self::reverseHTMLSafe($_SESSION['alkaline']['config'])));
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
			echo $singular;
		}
		else{
			echo $plural;
		}
	}
	
	// Chose between singular and plural nouns
	public function echoFullCount($count, $singular, $plural=null){
		$count =  number_format($count) . ' ' . self::echoCount($count, $singular, $plural);
		return $count;
	}
	
	// Change path to Windows-friendly
	public function correctWinPath($path){
		if(SERVER_TYPE == 'win'){
			$path = str_replace('/', '\\', $path);
		}
		return $path;
	}
	
	// REDIRECT HANDLING
	// Current page for redirects
	public function location(){
		$location = LOCATION;
		$location .= preg_replace('#\?.*$#si', '', $_SERVER['REQUEST_URI']);
		return $location;
	}
	
	public function setCallback(){
		$_SESSION['alkaline']['callback'] = self::location();
	}
	
	public function callback($url=null){
		if(!empty($_SESSION['alkaline']['callback'])){
			header('Location: ' . $_SESSION['alkaline']['callback']);
		}
		elseif(!empty($url)){
			header('Location: ' . $url);
		}
		else{
			header('Location: ' . LOCATION . BASE . ADMIN . 'dashboard/');
		}
		exit();
	}
	
	// MAIL	
	protected function email($to=0, $subject, $message){
		if(empty($subject) or empty($message)){ return false; }
		
		if($to == 0){
			$to = $this->returnConf('web_email');
		}
		
		if(is_int($to) or preg_match('#[0-9]+#s', $to)){
			$query = $this->prepare('SELECT user_email FROM users WHERE user_id = ' . $to);
			$query->execute();
			$user = $query->fetch();
			$to = $user['user_email'];
		}
		
		$subject = 'Alkaline: ' . $subject;
		$message = $message . "\r\n\n" . '-- Alkaline';
		$headers = 'From: ' . $this->returnConf('web_email') . "\r\n" .
			'Reply-To: ' . $this->returnConf('web_email') . "\r\n" .
			'X-Mailer: PHP/' . phpversion();
		
		return mail($to, $subject, $message, $headers);
	}
	
	// DEBUG
	public function error($message, $number=null){
		$_SESSION['alkaline']['error']['message'] = $message;
		$_SESSION['alkaline']['error']['number'] = $number;
		header('Location: ' . LOCATION . BASE . 'fault' . URL_CAP);
		exit();
	}
	
	// Ouput debug info
	public function debug(){
		$_SESSION['alkaline']['debug']['execution_time'] = microtime(true) - $_SESSION['alkaline']['debug']['start_time'];
		return $_SESSION['alkaline']['debug'];
	}
	
	// Add report to log
	public function report($message, $number=null){
		// Format message
		$message = date('Y-m-d H:i:s') . "\t" . $message;
		if(!empty($number)){ $message .= ' (' . $number . ')'; }
		$message .= "\n";
		
		// Write message
		$handle = fopen($this->correctWinPath(PATH . ASSETS . 'log.txt'), 'a');
		if(@fwrite($handle, $message) === false){
			$this->error('Cannot write to report file.');
		}
		fclose($handle);
	}
}

?>