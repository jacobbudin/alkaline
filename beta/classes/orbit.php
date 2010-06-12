<?php

class Orbit extends Alkaline{
	public $class;
	public $uid;
	public $extensions;
	public $extension_count;
	public $preferences;
	public $sandbox;
	private $db_safe;
	
	public function __construct($uid=null){
		parent::__construct();
		
		$this->uid = $uid;
		$this->db_safe = $this->db;
		unset($this->db);
		
		if(empty($uid)){
			$query = $this->db_safe->prepare('SELECT extension_uid, extension_title, extension_class, extension_hooks, extension_preferences FROM extensions WHERE extension_status > 0 ORDER BY extension_build ASC, extension_id ASC;');
			$query->execute();
			$extensions = $query->fetchAll();

			$this->extensions = array();

			foreach($extensions as $extension){
				$extension_uid = strval($extension['extension_uid']);
				$extension_file = PATH . EXTENSIONS . strtolower($extension['extension_class']) . '.php';
				$extension_title = $extension['extension_title'];
				$extension_hooks = unserialize($extension['extension_hooks']);
				$this->extensions[$extension_uid] = array('extension_file' => $extension_file,
					'extension_title' => $extension_title,
					'extension_class' => $extension['extension_class'],
					'extension_hooks' => $extension_hooks);
			}
			
			$this->extension_count = count($this->extensions);
		}
		else{
			$query = $this->db_safe->prepare('SELECT extension_class, extension_preferences FROM extensions WHERE extension_uid = "' . $this->uid . '" AND extension_status > 0;');
			$query->execute();
			$extensions = $query->fetchAll();
			
			if(count($extensions) != 1){
				return false;
			}
			
			$this->class = $extensions[0]['extension_class'];
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
		foreach($this->extensions as $extension){
			if(in_array($hook, $extension['extension_hooks'])){
				include($extension['extension_file']);
				$orbit = new $extension['extension_class']();
				call_user_func_array(array($orbit, $hook), $arguments);
			}
		}
	}
}

?>