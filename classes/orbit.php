<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalineapp.com/
*/

/**
 * @author Budin Ltd. <contact@budinltd.com>
 * @copyright Copyright (c) 2010-2011, Budin Ltd.
 * @version 1.0
 */

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
	
	/**
	 * Initiate Orbit object
	 *
	 * @param int|array $id Orbit IDs (otherwise all)
	 */
	public function __construct($id=null){
		parent::__construct();
		
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
					$extension['extension_file'] = parent::correctWinPath(PATH . EXTENSIONS . $extension['extension_folder'] . '/' . $extension['extension_file'] . '.php');
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
				$extensions = $_SESSION['alkaline']['extensions'];
				
				if(!empty($id)){
					$extension_ids = array();
					foreach($extensions as $extension){
						$extension_ids[] = $extension['extension_id'];
					}
					$extension_key = array_search($id, $extension_ids);
				}
				else{
					$class = get_class($this);
					
					$extension_classes = array();
					foreach($extensions as $extension){
						$extension_classes[] = $extension['extension_class'];
					}
					$extension_key = array_search($class, $extension_classes);
				}
				
				if($extension_key === false){
					return false;
				}
				
				$extension = $extensions[$extension_key];
			}
			
			foreach($extension as $key => $value){
				$key = preg_replace('#^extension\_#si', '', $key, 1);
				$this->$key = $value;
			}
			
			$this->uid = strval($this->uid);
			$this->file = parent::correctWinPath(PATH . EXTENSIONS . $this->folder . '/' . $this->file . '.php');
			if(!is_array($this->hooks)){
				$this->hooks = unserialize($this->hooks);
			}
			$this->preferences = unserialize($this->preferences);
		}
		return true;
	}
	
	/**
	 * Terminates object, saves extension data
	 *
	 * @return void
	 */
	public function __destruct(){
		// Save extension data
		$_SESSION['alkaline']['extensions'] = $this->extensions;
		
		parent::__destruct();
	}
	
	/**
	 * Set extension preference
	 *
	 * @param string $name 
	 * @param string $value 
	 * @return void
	 */
	public function setPref($name, $value){
		return $this->preferences[$name] = $value;
	}
	
	/**
	 * Read preference key and return value
	 *
	 * @param string $name 
	 * @param string $default 
	 * @return void
	 */
	public function returnPref($name, $default=null){
		return parent::returnForm($this->preferences, $name, $default);
	}
	
	/**
	 * Read preference key, return form data
	 *
	 * @param string $name 
	 * @param string $check 
	 * @return void
	 */
	public function readPref($name, $check=true){
		return parent::readForm($this->preferences, $name, $check);
	}
	
	/**
	 * Save preferences to database
	 *
	 * @return void
	 */
	public function savePref(){
		$query = $this->prepare('UPDATE extensions SET extension_preferences = :extension_preferences WHERE extension_uid = :extension_uid;');
		return $query->execute(array(':extension_preferences' => serialize($this->preferences), ':extension_uid' => $this->uid));
	}
	
	/**
	 * Current page for redirects
	 *
	 * @return void
	 */
	public function location(){
		return parent::location();
	}
	
	/**
	 * Reset preferences
	 *
	 * @return PDOStatement
	 */
	public function reset(){
		$query = $this->prepare('UPDATE extensions SET extension_preferences = "" WHERE extension_uid = :extension_uid;');
		return $this->execute(array(':extension_uid' => $this->uid));
	}
	
	/**
	 * Execute Orbit hook
	 *
	 * @param string $hook Hook name
	 * @return mixed Default value
	 */
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
		if($arguments[0]){
			$argument_return_type = $this->getType($arguments[0]);
		}
		
		// Find respective extensions, execute their code
		if(!empty($this->extensions)){
			foreach($this->extensions as $extension){
				if(@in_array($hook, $extension['extension_hooks'])){
					require_once($extension['extension_file']);
					$orbit = new $extension['extension_class']();
					$method = 'orbit_' . $hook;
					if(method_exists($orbit, $method)){
						$return = call_user_func_array(array($orbit, $method), $arguments);
						if(!empty($argument_return_type) and ($this->getType($return) == $argument_return_type)){
							$arguments = array_merge(array($return), array_splice($arguments, 1));
						}
					}
				}
			}
		}
		
		return $arguments[0];
	}
}

?>