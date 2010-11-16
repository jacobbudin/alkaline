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
			if(empty($_SESSION['alkaline']['extensions'])){
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
					$extension['extension_file'] = parent::correctWinPath(PATH . EXTENSIONS . $extension['extension_file'] . '.php');
					$extension['extension_hooks'] = unserialize($extension['extension_hooks']);
				}
			
				$_SESSION['alkaline']['extensions'] = $extensions;
			}
			
			$this->extensions = $_SESSION['alkaline']['extensions'];
			$this->extension_count = count($this->extensions);
		}
		// Prepare Orbit-powered extension
		else{
			if(empty($_SESSION['alkaline']['extensions'])){
				if(empty($id)){
					$query = $this->prepare('SELECT * FROM extensions WHERE extension_class = :extension_class AND extension_status > 0;');
					$query->execute(array(':extension_class' => get_class($this)));
				}
				else{
					$id = intval($id);
					$query = $this->prepare('SELECT * FROM extensions WHERE extension_id = ' . $id . ' AND extension_status > 0;');
					$query->execute();
				}
				$extensions = $query->fetchAll();
			
				if(count($extensions) != 1){
					return false;
				}
				
				$extension = $extensions[0];
			}
			else{
				$class = get_class($this);
				$extensions = $_SESSION['alkaline']['extensions'];
				foreach($extensions as $extension){
					if(!empty($id)){
						if($extension['extension_id'] == $id){
							$extension_key = key($extensions);
							break;
						}
					}
					elseif($extension['extension_class'] == $class){
						$extension_key = key($extensions);
						break;
					}
				}
				$extension = $extensions[$extension_key];
			}
			
			foreach($extension as $key => $value){
				$key = preg_replace('#^extension\_#si', '', $key, 1);
				$this->$key = $value;
			}
			
			$this->uid = strval($this->uid);
			$this->file = parent::correctWinPath(PATH . EXTENSIONS . strtolower($this->file) . '.php');
			if(!is_array($this->hooks)){
				$this->hooks = unserialize($this->hooks);
			}
			$this->preferences = unserialize($this->preferences);
		}
		return true;
	}
	
	public function __destruct(){
		// Close database connection
		$this->db_safe = null;
		
		// Save extension data
		$_SESSION['alkaline']['extensions'] = $this->extensions;
		
		parent::__destruct();
	}
	
	// DATABASE
	public function exec($query){
		$this->prequery($query);
		$response = $this->db_safe->exec($query);
		$this->postquery($query, $this->db_safe);
		
		return $response;
	}
	
	public function prepare($query){
		$this->prequery($query);
		$response = $this->db_safe->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
		$this->postquery($query, $this->db_safe);
		
		return $response;
	}
	
	// Local require_once()
	protected function load($file){
		require_once(parent::correctWinPath(PATH . EXTENSIONS . $this->folder . '/' . $file));
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
		$query = $this->prepare('UPDATE extensions SET extension_preferences = :extension_preferences WHERE extension_uid = :extension_uid;');
		return $query->execute(array(':extension_preferences' => serialize($this->preferences), ':extension_uid' => $this->uid));
	}
	
	// Current page for redirects
	public function location(){
		return parent::location();
	}
	
	// Set preference key
	public function reset(){
		$query = $this->prepare('UPDATE extensions SET extension_preferences = "" WHERE extension_uid = :extension_uid;');
		return $this->execute(array(':extension_uid' => $this->uid));
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