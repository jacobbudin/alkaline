<?php

class User extends Alkaline{
	public $user;
	
	public function __construct(){
		parent::__construct();
		
		// Login user by session data
		if(!empty($_SESSION['alkaline']['user'])){
			$this->user = $_SESSION['alkaline']['user'];
		}
		// Login user by ID, key
		elseif(!empty($_COOKIE['id']) and !empty($_COOKIE['key'])){
			$user_id = strip_tags($_COOKIE['id']);
			$user_key = strip_tags($_COOKIE['key']);
			self::authByCookie($user_id, $user_key);
		}
	}
	
	public function __destruct(){
		// Store user to session data
		if(self::perm() == true){
			$_SESSION['alkaline']['user'] = $this->user;
		}
		
		parent::__destruct();
	}
	
	// AUTHENTICATE (LOGIN)
	// Login user by username, password
	public function auth($username, $password, $remember=false){
		// Error checking
		if(empty($username) or empty($password)){
			return false;
		} 
		
		// Check database
		$query = $this->prepare('SELECT * FROM users WHERE user_user = :username AND user_pass = :password;');
		$query->execute(array(':username' => $username, ':password' => sha1($password)));
		$this->user = $query->fetchAll();
		
		if(!self::prep($remember)){
			return false;
		}
		
		return true;
	}
	
	// Login user by ID, key
	protected function authByCookie($user_id, $user_key, $remember=true){
		$query = $this->prepare('SELECT * FROM users WHERE user_id = :user_id AND user_key = :user_key;');
		$query->execute(array(':user_id' => $user_id, ':user_key' => $user_key));
		$this->user = $query->fetchAll();
		
		if(!self::prep($remember)){
			return false;
		}
		
		return true;
	}
	
	// Prepare user for functionality
	private function prep($remember=false){
		// If overlapping users exist, destroy object
		if(count($this->user) != 1){
			unset($this->user);
			return false;
		}
		
		// If user exists, store their row
		$this->user = $this->user[0];
		
		$key = '';
		
		// Store "remember me" data
		if($remember == true){
			$key = $this->user['user_id'] . $this->user['user_user'] . $this->user['user_pass'] . time();
			$key = sha1($key);
			setcookie('id', $this->user['user_id'], time()+USER_REMEMBER, '/');
			setcookie('key', $key, time()+USER_REMEMBER, '/');
		}
		
		// Destroy sensitive information from object
		unset($this->user['user_pass']);
		unset($this->user['user_key']);
		
		// Create arrays
		$this->user['user_permissions'] = unserialize($this->user['user_permissions']);
		$this->user['user_preferences'] = unserialize($this->user['user_preferences']);
		
		// Update database
		$fields = array('user_last_login' => date('Y-m-d H:i:s'), 'user_key' => $key);
		return $this->updateRow($fields, 'users', $this->user['user_id']);
	}
	
	// DEAUTHENTICATE (LOGOUT)
	// Logout user, destroy "remember me" data
	public function deauth(){
		unset($this->user);
		session_destroy();
		setcookie('id', '', time()-3600, '/');
		setcookie('key', '', time()-3600, '/');
		session_start();
		
		return true;
	}
	
	// PERMISSIONS
	// Verify user has permission to access module
	public function perm($required=false, $permission=null){
		if(empty($this->user)){
			if($required === true){
				header('Location: ' . LOCATION . BASE . ADMIN . 'login/');
				exit();
			}
			elseif($required === false){
				return false;
			}
		}
		else{
			if(empty($permission)){
				return true;
			}
			elseif(in_array($permission, $this->user['user_permissions'])){
				return true;
			}
			else{
				return false;
			}
		}
	}
	
	// PREFERENCES
	// Set preference key
	public function setPref($name, $unset=''){
		return parent::setForm($this->user['user_preferences'], $name, $unset);
	}
	
	// Read preference key and return value in HTML
	public function readPref($name, $check=true){
		return parent::readForm($this->user['user_preferences'], $name, $check);
	}
	
	// Read preference key and return value
	public function returnPref($name){
		return parent::returnForm($this->user['user_preferences'], $name);
	}
	
	// Save preferences
	public function savePref(){
		$fields = array('user_preferences' => serialize($this->user['user_preferences']));
		
		// Update database
		return $this->updateFields($fields);
	}
	
	// UPDATE USER
	public function updateFields($fields, $overwrite=true){
		// Verify each key has changed; if not, unset the key
		foreach($fields as $key => $value){
			if($fields[$key] == $this->user[$key]){
				unset($fields[$key]);
			}
			if(!empty($this->user[$key]) and ($overwrite === false)){
				unset($fields[$key]);
			}
		}
		
		// If no keys have changed, break
		if(count($fields) == 0){ return false; }
		
		// Update database
		return $this->updateRow($fields, 'users', $this->user['user_id']);
	}
}

?>