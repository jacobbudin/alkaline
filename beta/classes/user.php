<?php

class User extends Alkaline{
	public $user;
	public $view_type;
	
	public function __construct(){
		parent::__construct();
		
		// Login user by session data
		if(!empty($_SESSION['user'])){
			$this->user = $_SESSION['user'];
			$this->view_type = $_SESSION['user_view_type'];
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
			$_SESSION['user'] = $this->user;
			$_SESSION['user_view_type'] = $this->view_type;
		}
		
		parent::__destruct();
	}
	
	// AUTHENTICATE (LOGIN)
	// Login user by username, password
	public function auth($username, $password, $remember=false){
		// Check database
		$query = $this->db->prepare('SELECT * FROM users WHERE user_user = "' . $username . '" AND user_pass = "' . sha1($password) . '";');
		$query->execute();
		$this->user = $query->fetchAll();
		
		if(!self::prep($remember)){
			return false;
		}
		
		return true;
	}
	
	// Login user by ID, key
	protected function authByCookie($user_id, $user_key, $remember=true){
		$query = $this->db->prepare('SELECT * FROM users WHERE user_id = "' . $user_id . '" AND user_key = "' . $user_key . '";');
		$query->execute();
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
		$this->user['user_permissions'] = explode(',', $this->user['user_permissions']);
		
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
		
		// Set default view type
		$this->view_type = DEFAULT_VIEW_TYPE;
		
		// Update database
		$this->db->exec('UPDATE users SET user_last_login = "' . date('Y-m-d H:i:s') . '", user_key = "' . $key . '" WHERE user_id = "' . $this->user['user_id'] . '"');
		
		return true;
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
	
	// Read preference key and return value
	public function readPref($name, $check=true){
		return parent::readForm($this->user['user_preferences'], $name, $check);
	}
	
	// UPDATE USER
	public function updateFields($array, $overwrite=true){
		// Verify each key has changed; if not, unset the key
		foreach($array as $key => $value){
			if($array[$key] == $this->user[$key]){
				unset($array[$key]);
			}
			if(!empty($this->user[$key]) and ($overwrite === false)){
				unset($array[$key]);
			}
		}
		
		// If no keys have changed, break
		if(count($array) == 0){
			continue;
		}
		
		$fields = array();
		
		// Prepare input
		foreach($array as $key => $value){
			$fields[] = $key . ' = "' . addslashes($value) . '"';
		}
		
		$sql = implode(', ', $fields);
		
		// Update table
		$this->db->exec('UPDATE users SET ' . $sql . ' WHERE user_id = ' . $this->user['user_id'] . ';');
	}
}

?>