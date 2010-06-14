<?php

class Orbit extends Alkaline{
	public $id;
	public $uid;
	
	public $class;
	public $file;
	public $hooks;
	public $preferences;
	public $title;
	
	public $extensions;
	
	public $extension_count;
	
	private $db_safe;
	
	public function __construct($identifier=null){
		parent::__construct();
		
		$this->db_safe = $this->db;
		unset($this->db);
		
		if(empty($identifier)){
			$query = $this->db_safe->prepare('SELECT extension_id, extension_uid, extension_title, extension_class, extension_hooks, extension_preferences FROM extensions WHERE extension_status > 0 ORDER BY extension_build ASC, extension_id ASC;');
			$query->execute();
			$extensions = $query->fetchAll();

			$this->extensions = array();

			foreach($extensions as $extension){
				$extension_id = $extension['extension_id'];
				$extension_uid = strval($extension['extension_uid']);
				$extension_file = PATH . EXTENSIONS . strtolower($extension['extension_class']) . '.php';
				$extension_title = $extension['extension_title'];
				$extension_hooks = unserialize($extension['extension_hooks']);
				$this->extensions[] = array('extension_id' => $extension_id,
					'extension_uid' => $extension_uid,
					'extension_file' => $extension_file,
					'extension_title' => $extension_title,
					'extension_class' => $extension['extension_class'],
					'extension_hooks' => $extension_hooks);
			}
			
			$this->extension_count = count($this->extensions);
		}
		else{
			if(strlen($identifier) == 40){
				$this->uid = $identifier;
				$query = $this->db_safe->prepare('SELECT * FROM extensions WHERE extension_uid = "' . $this->uid . '" AND extension_status > 0;');
			}
			else{
				$this->id = $identifier;
				$query = $this->db_safe->prepare('SELECT * FROM extensions WHERE extension_id = "' . $this->id . '" AND extension_status > 0;');
			}
			$query->execute();
			$extensions = $query->fetchAll();
			
			if(count($extensions) != 1){
				return false;
			}
			
			foreach($extensions as $extension){
				$extension_id = $extension['extension_id'];
				$extension_uid = strval($extension['extension_uid']);
				$extension_file = PATH . EXTENSIONS . strtolower($extension['extension_class']) . '.php';
				$extension_title = $extension['extension_title'];
				$extension_hooks = unserialize($extension['extension_hooks']);
				$this->extensions[] = array('extension_id' => $extension_id,
					'extension_uid' => $extension_uid,
					'extension_file' => $extension_file,
					'extension_title' => $extension_title,
					'extension_class' => $extension['extension_class'],
					'extension_hooks' => $extension_hooks);
			}
			
			$this->id = $extensions[0]['extension_id'];
			$this->uid = strval($extensions[0]['extension_uid']);
			$this->title = $extensions[0]['extension_title'];
			$this->file = PATH . EXTENSIONS . strtolower($extensions[0]['extension_class']) . '.php';
			$this->class = $extensions[0]['extension_class'];
			$this->hooks = unserialize($extensions[0]['extension_hooks']);
			$this->preferences = unserialize($extensions[0]['extension_preferences']);
		}
		return true;
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	// Safe PDO->prepare()
	protected function prepare($sql){
		// Check for malicious SQL
		
		
		return $this->db_safe->prepare($sql);	
	}
	
	// Local require_once()
	protected function fetch($file){
		require_once(PATH . EXTENSIONS . $this->class . '/' . $file);
	}
	
	// Set preference key
	public function setPref($name, $value){
		$this->preferences[$name] = $value;
		return true;
	}
	
	// Read preference key and return value
	public function readPref($name){
		return @$this->preferences[$name];
	}
	
	// Set preference key
	public function savePref(){
		return $this->db_safe->exec('UPDATE extensions SET extension_preferences = "' . addslashes(serialize($this->preferences)) . '" WHERE extension_uid = "' . $this->uid . '";');
	}
	
	// Execute extensions at hook
	public function hook($hook){
		// Find arguments
		$arguments = func_get_args();
		$arguments = array_slice($arguments, 1);
		
		// Find respective extensions, execute their code
		if(!empty($this->extensions)){
			foreach($this->extensions as $extension){
				if(in_array($hook, $extension['extension_hooks'])){
					include($extension['extension_file']);
					$orbit = new $extension['extension_class']();
					call_user_func_array(array($orbit, $hook), $arguments);
				}
			}
		}
	}
}

?>