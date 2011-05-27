<?php

class Dropbox extends Orbit{
	public $dropbox;
	
	public $dropbox_username;
	
	private $dropbox_oauth_token;
	private $dropbox_oauth_secret;
	
	public function __construct(){
		parent::__construct();
		
		$this->dropbox_active = $this->returnPref('dropbox_active');
		$this->dropbox_folder = $this->returnPref('dropbox_folder');
		$this->dropbox_accessed = $this->returnPref('dropbox_accessed');
		
		$this->dropbox_username = $this->returnPref('dropbox_username');
		$this->dropbox_oauth_token = $this->returnPref('dropbox_oauth_token');
		$this->dropbox_oauth_secret = $this->returnPref('dropbox_oauth_secret');
		
		require_once('classes/dropbox.php');
		$this->dropbox = new Dropbox_Dropbox('x3b5rc2yaew6ny9',
			'xqj58kjgyrhiqir');
					
		if(!empty($this->dropbox_oauth_token) and !empty($this->dropbox_oauth_secret)){
			$this->dropbox->setOAuthToken($this->dropbox_oauth_token);
			$this->dropbox->setOAuthTokenSecret($this->dropbox_oauth_secret);
		}
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function orbit_config(){
		?>
		<p>Alkaline can monitor a folder on your Dropbox account and automatically download new files to your Shoebox folder.</p>
		
		<?php
		if($this->dropbox_active){
			?>
			<table>
				<tr>
					<td class="right"><label>Username:</label></td>
					<td><a href="http://dropbox.com/<?php echo $this->dropbox_username; ?>/"><?php echo $this->dropbox_username; ?></a> &#0160; <a href="<?php echo $this->locationFull(array('unlink' => 'dropbox')); ?>"><button>Unlink from Dropbox</button></a></td>
				</tr>
				<tr>
					<td class="right pad"><label for="dropbox_folder">Folder:</label></td>
					<td>
						<input type="text" id="dropbox_folder" name="dropbox_folder" value="<?php echo $this->dropbox_folder; ?>" class="m" /><br />
						<span class="quiet">Alkaline will only import files from this folder. It is non-recursive.</span>
					</td>
				</tr>
			</table>
			<?php
		}
		else{
			?>
			<table>
				<tr>
					<td class="right"><label>Username:</label></td>
					<td>
						<a href="<?php echo $this->locationFull(array('link' => 'dropbox')); ?>"><button>Link to Dropbox</button></a><br /><br />
						<span class="quiet">Note: Alkaline will be linked to whichever Dropbox account you are currently logged into.</span>
					</td>
				</tr>
			</table>
			<?php
		}
	}
	
	public function orbit_config_load(){
		if(!empty($_GET['from'])){
			switch($_GET['from']){
				case 'dropbox':
					$dropbox_access_token = $this->dropbox->oAuthAccessToken($_GET['oauth_token']);
					
					var_dump($dropbox_access_token);
					
					$this->dropbox_active = true;
					$this->setPref('dropbox_active', true);
					
					$this->dropbox->setOAuthToken($dropbox_access_token['oauth_token']);
					$this->dropbox->setOAuthTokenSecret($dropbox_access_token['oauth_token_secret']);
					
					$user = $this->dropbox->accountInfo();
					$this->setPref('dropbox_username', $user['email']);
					
					$this->setPref('dropbox_oauth_token', $dropbox_access_token['oauth_token']);
					$this->setPref('dropbox_oauth_secret', $dropbox_access_token['oauth_token_secret']);
					
					$this->savePref();
					
					$this->addNote('You successfully linked your Dropbox account.', 'success');
					header('Location: ' . $this->location());
					exit();
					
					break;
			}
		}
		
		if(!empty($_GET['link'])){
			switch($_GET['link']){
				case 'dropbox':
					$dropbox_token = $this->dropbox->oAuthRequestToken();
					$dropbox_authorize_uri = $this->dropbox->oAuthAuthorize($dropbox_token['oauth_token'], $this->locationFull(array('from' => 'dropbox')));
					
					$this->setPref('dropbox_oauth_token', $dropbox_token['oauth_token']);
					$this->setPref('dropbox_oauth_secret', $dropbox_token['oauth_token_secret']);
					$this->savePref();
					
					header('Location: ' . $dropbox_authorize_uri);
					exit();
					
					break;
			}
		}
		
		if(!empty($_GET['unlink'])){
			switch($_GET['unlink']){
				case 'dropbox':
					$this->dropbox_active = false;
					$this->setPref('dropbox_active', false);
					$this->setPref('dropbox_username', '');
					$this->setPref('dropbox_oauth_token', '');
					$this->setPref('dropbox_oauth_secret', '');
					$this->savePref();
					
					$this->addNote('You successfully unlinked your Twitter account.', 'success');
					header('Location: ' . $this->location());
					exit();
					
					break;
			}
		}
	}
	
	public function orbit_config_save(){
		$this->setPref('dropbox_folder', $_POST['dropbox_folder']);
		$this->savePref();
	}
	
	public function orbit_shoebox(){
		if($this->dropbox_active != true){ return; }
		
		$folder = $this->dropbox->metadata($this->dropbox_folder);
		
		foreach($folder['contents'] as $file){
			if($file['is_dir'] === true){ continue; }
			if(strtotime($file['modified']) < $this->dropbox_accessed){ continue; }
			
			$this->storeTask(array($this, 'get'), $file['path']);
		}
		
		$this->setPref('dropbox_accessed', time());
		$this->savePref();
	}
	
	public function get($path){
		$fetch = $this->dropbox->filesGet($path);
		file_put_contents(PATH . SHOEBOX . $this->getFilename($path), base64_decode($fetch['data']));
	}
}

?>