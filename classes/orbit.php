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
	
	public function __construct($id=null){
		parent::__construct();
		
		// Secure original database connection
		$this->db_safe = $this->db;
		unset($this->db);
		
		// Start Orbit Engine
		if(!is_subclass_of($this, 'Orbit')){
			if(empty($id)){
				$query = $this->prepare('SELECT * FROM extensions WHERE extension_status > 0 ORDER BY extension_title ASC;');
			}
			else{
				$id = intval($id);
				$query = $this->prepare('SELECT * FROM extensions WHERE extension_id = ' . $id . ' AND extension_status > 0;');
			}
			$query->execute();
			$extensions = $query->fetchAll();

			$this->extensions = array();

			foreach($extensions as &$extension){
				$extension['extension_uid'] = strval($extension['extension_uid']);
				$extension['extension_file'] = PATH . EXTENSIONS . $extension['extension_file'] . '.php';
				$extension['extension_hooks'] = unserialize(stripslashes($extension['extension_hooks']));
			}
			
			$this->extensions = $extensions;
			$this->extension_count = count($this->extensions);
		}
		// Prepare Orbit-powered extension
		else{
			if(empty($id)){
				$query = $this->prepare('SELECT * FROM extensions WHERE extension_class = "' . get_class($this) . '" AND extension_status > 0;');
			}
			else{
				$id = intval($id);
				$query = $this->prepare('SELECT * FROM extensions WHERE extension_id = ' . $id . ' AND extension_status > 0;');
			}
			$query->execute();
			$extensions = $query->fetchAll();
			
			if(count($extensions) != 1){
				return false;
			}
			
			foreach($extensions[0] as $key => $value){
				$key = preg_replace('#^extension\_#si', '', $key, 1);
				$this->$key = $value;
			}
			
			$this->uid = strval($this->uid);
			$this->file = PATH . EXTENSIONS . strtolower($this->file) . '.php';
			$this->hooks = unserialize(stripslashes($this->hooks));
			$this->preferences = unserialize(stripslashes($this->preferences));
		}
		return true;
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	// DATABASE
	public function exec($query){
		$_SESSION['alkaline']['debug']['queries']++;
		return $this->db_safe->exec($query);
	}
	
	public function prepare($query){
		$_SESSION['alkaline']['debug']['queries']++;
		return $this->db_safe->prepare($query);
	}
	
	// Local require_once()
	protected function load($file){
		require_once(PATH . EXTENSIONS . $this->folder . '/' . $file);
	}
	
	// Set preference key
	public function setPref($name, $value){
		$this->preferences[$name] = $value;
		return true;
	}
	
	// Read preference key and return value
	public function readPref($name, $default=null){
		if(isset($this->preferences[$name])){
			return $this->preferences[$name];
		}
		if(isset($default)){
			return $default;
		}	
	}
	
	// Set preference key
	public function savePref(){
		return $this->exec('UPDATE extensions SET extension_preferences = "' . addslashes(serialize($this->preferences)) . '" WHERE extension_uid = "' . $this->uid . '";');
	}
	
	// Current page for redirects
	public function location(){
		return parent::location();
	}
	
	// Set preference key
	public function reset(){
		return $this->exec('UPDATE extensions SET extension_preferences = "" WHERE extension_uid = "' . $this->uid . '";');
	}
	
	// Execute extensions at hook
	public function hook($hook){
		// Configuration: maint_disable
		$safe_hooks = array('config', 'config_load', 'config_save');
		if(!in_array($hook, $safe_hooks)){
			if($this->returnConf('maint_disable')){
				return false;
			}
		}
		
		// Find arguments
		$arguments = func_get_args();
		
		// Find pass-by-default value
		$argument_pass_index = count($arguments) - 1;
		$argument_pass = $arguments[$argument_pass_index];
		
		// Remove non-arguments
		$arguments = array_slice($arguments, 1, count($arguments) - 2);
		$argument_count = count($arguments);
		
		// Add an empty argument for returns
		$arguments[] = '';
		
		// Find respective extensions, execute their code
		if(!empty($this->extensions)){
			foreach($this->extensions as $extension){
				if(@in_array($hook, $extension['extension_hooks'])){
					require_once($extension['extension_file']);
					$orbit = new $extension['extension_class']();
					$return = call_user_func_array(array($orbit, $hook), $arguments);
					if(!empty($return) and !is_bool($return)){
						$arguments = array_slice($arguments, 0, $argument_count);
						$arguments[] = $return;
					}
				}
			}
		}
		
		if(empty($arguments[$argument_count])){
			return $argument_pass;
		}
		
		return $arguments[$argument_count];
	}
}

?>