<?php

class FacebookHandler extends Orbit{
	public $facebook;
	public $facebook_username;
	
	private $facebook_password;
	
	public function __construct(){
		parent::__construct();
		
		$this->facebook_active = $this->returnPref('facebook_active');
		$this->facebook_auto = $this->returnPref('facebook_auto');
		$this->facebook_name = $this->returnPref('facebook_name');
		$this->facebook_profile_id = $this->returnPref('facebook_profile_id');
		$this->facebook_album_id = $this->returnPref('facebook_album_id');
		$this->facebook_access_token = $this->returnPref('facebook_access_token');
		$this->facebook_format_image = $this->returnPref('facebook_format_image');
		$this->facebook_last_image_time = $this->returnPref('facebook_last_image_time');
		
		require_once('classes/facebook.php');
		
		$this->facebook_name = $this->returnPref('facebook_name');
		
		$config = array('appId' => '7c4c834772300f94f220af8c5198cd4a',
			'secret' => '3623ff780be9e6ebcc31eed8f2100888',
			'fileUpload' => true);
		
		$this->facebook = new Facebook($config);
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function orbit_config(){
		$params = array('access_token' => $this->facebook_access_token);
		$albums = $this->facebook->api('me/albums', $params);
		
		$albums_list = array();
		
		foreach($albums['data'] as $album){
			$albums_list[$album['id']] = $album['name'];
		}
		
		?>
		<p>Every time you publish an image, it will be uploaded to <a href="http://www.facebook.com/">Facebook</a>.</p>
		<?php
		if($this->facebook_active){
			$this->facebook_format_image = $this->makeHTMLSafe($this->facebook_format_image);
			?>
			<table>
				<tr>
					<td class="right"><label>Name:</label></td>
					<td><?php echo $this->facebook_name; ?> &#0160; <a href="<?php echo $this->locationFull(array('unlink' => 'facebook')); ?>"><button>Unlink from Facebook</button></a></td>
				</tr>
				<tr>
					<td class="right"><label for="facebook_album_id">Facebook album:</label></td>
					<td>
						<select id="facebook_album_id" name="facebook_album_id">
							<?php foreach ($albums_list as $id => $name): ?>
								<option value="<?php echo $id ?>" <?php if($id == $this->facebook_album_id){ echo 'selected="selected"'; } ?>><?php echo $name; ?></option>
							<?php endforeach ?>
						</select>
					</td>
				</tr>
				<tr>
					<td class="right"><label for="facebook_format_image">Image caption format:</label></td>
					<td>
						<textarea type="text" id="facebook_format_image" name="facebook_format_image" style="width: 30em;" class="code"><?php echo $this->facebook_format_image; ?></textarea><br />
						<p class="quiet">Your image will automatically be posted, use the text area above to write an optional caption. Use Canvas tags such as <code>{Image_Title}</code> and <code>{Image_URI}</code> above.</p>
					</td>
				</tr>
				<tr>
					<td class="right"><input type="checkbox" id="tumblr_auto" name="tumblr_auto" value="auto" <?php if($this->tumblr_auto == 'auto'){ echo 'checked="checked"'; } ?> /></td>
					<td><strong><label for="tumblr_auto">Enable automatic mode.</label></strong> When you publish, your Tumblog will be automatically updated.</td>
				</tr>
			</table>
			<?php
		}
		else{
			?>
			<table>
				<tr>
					<td class="right"><label>Title:</label></td>
					<td>
						<a href="<?php echo $this->locationFull(array('link' => 'facebook')); ?>"><button>Link to Facebook</button></a><br /><br />
						<span class="quiet">Note: Alkaline will be linked to whichever Facebook account you are currently logged into.</span>
					</td>
				</tr>
			</table>
			<?php
		}
	}
	
	public function orbit_config_load(){
		if(!empty($_GET['from'])){
			switch($_GET['from']){
				case 'facebook':
					/*
					$params = array('client_id' => '7c4c834772300f94f220af8c5198cd4a',
						'redirect_uri' => 'http://www.alkalineapp.com/callback/',
						'client_secret' => '3623ff780be9e6ebcc31eed8f2100888',
						'code' => $_GET['code']);
					
					$here = $this->facebook->api('oauth/access_token', $params);
					*/
					
					$access_token = $_REQUEST['access_token'];
					
					$params = array('access_token' => $access_token);
					$me = $this->facebook->api('me', $params);
					
					$this->facebook_active = true;
					$this->setPref('facebook_active', true);
					$this->setPref('facebook_name', $me['name']);
					$this->setPref('facebook_access_token', $access_token);
					
					$this->savePref();
					
					$this->addNote('You successfully linked your Facebook account.', 'success');
					
					header('Location: ' . $this->location());
					exit();
					
					break;
			}
		}
		
		
		if(!empty($_GET['link'])){
			switch($_GET['link']){
				case 'facebook':
					$facebook_oauth_token = $this->facebook->getAccessToken();
					
					$this->setPref('facebook_oauth_token', $facebook_oauth_token);
					$this->savePref();
					
					$params = array('next' => 'http://www.alkalineapp.com/callback/',
						'cancel_url' => 'http://www.alkalineapp.com/callback/',
						'req_perms' => 'user_photos, publish_stream, offline_access');
					
					header('Location: http://www.alkalineapp.com/callback/?' . http_build_query(
						array('to' => $this->facebook->getLoginUrl($params),
						'from' => $this->locationFull(array('from' => 'facebook'))))
					);
					
					exit();
			}
		}
		
		if(!empty($_GET['unlink'])){
			switch($_GET['unlink']){
				case 'facebook':
					$this->facebook_active = false;
					
					$this->setPref('facebook_token', '');
					$this->setPref('facebook_session', '');
					$this->savePref();
					
					$this->addNote('You successfully unlinked your Facebook account.', 'success');
					header('Location: ' . $this->location());
					exit();
					
					break;
			}
		}
		
		/*
		if(!empty($_GET['from'])){
			switch($_GET['from']){
				case 'facebook':
					$facebook_access_token = $this->facebook->getAccessToken($_GET['oauth_verifier']);
					
					$this->facebook_active = true;
					$this->setPref('facebook_active', true);
					
					$user = $this->facebook->get('account/verify_credentials');
					$this->setPref('facebook_screen_name', $user->screen_name);
					
					$this->setPref('facebook_oauth_token', $facebook_access_token['oauth_token']);
					$this->setPref('facebook_oauth_secret', $facebook_access_token['oauth_token_secret']);
					
					$this->savePref();
					
					$this->addNote('You successfully linked your Facebook account.', 'success');
					header('Location: ' . $this->location());
					exit();
					
					break;
			}
		}
		
		if(!empty($_GET['unlink'])){
			switch($_GET['unlink']){
				case 'facebook':
					$this->facebook_active = false;
					$this->setPref('facebook_active', false);
					$this->setPref('facebook_email', '');
					$this->setPref('facebook_password', '');
					$this->savePref();
					
					$this->addNote('You successfully unlinked your Facebook account.', 'success');
					header('Location: ' . $this->location());
					exit();
					
					break;
			}
		}
		*/
	}
	
	public function orbit_config_save(){
		$now = time();
		$this->setPref('facebook_last_image_time', $now);
		
		$this->setPref('facebook_auto', @$_POST['facebook_auto']);
		
		$this->setPref('facebook_album_id', @$_POST['facebook_album_id']);
		$this->setPref('facebook_format_image', @$_POST['facebook_format_image']);
		
		$this->savePref();
	}
	
	public function orbit_image($images, $override=false){
		if(($this->facebook_auto != 'auto') && ($override === false)){ return; }
		if(strpos($this->facebook_transmit, 'image') === false){ return; }
		if(count($images) < 1){ return; }
		
		$now = time();
		
		foreach($images as $image){
			$image_published = strtotime($image['image_published']);
			
			if(empty($image_published)){ continue; }
			if($image_published > $now){ continue; }
			if($override !== true){
				if($image_published <= $this->facebook_last_image_time){ continue; }
				if($image['image_privacy'] != 1){ continue; }
			}
			
			$this->storeTask(array($this, 'upload_image'), $image);
		}
		
		$this->setPref('facebook_last_image_time', $now);
		$this->savePref();
	}
	
	public function orbit_post($posts, $override=false){
		if(($this->facebook_auto != 'auto') && ($override === false)){ return; }
		if(strpos($this->facebook_transmit, 'post') === false){ return; }
		if(count($posts) < 1){ return; }
		
		$now = time();
		
		foreach($posts as $post){
			$post_published = strtotime($post['post_published']);
			
			if(empty($post_published)){ continue; }
			if($post_published > $now){ continue; }
			if($override !== true){
				if($post_published <= $this->facebook_last_post_time){ continue; }
			}
			
			$this->storeTask(array($this, 'upload_post'), $post);
		}
		
		$this->setPref('facebook_last_post_time', $now);
		$this->savePref();
	}
	
	public function upload_image($image){
		$canvas = new Canvas($this->facebook_format_image);
		$canvas->assignArray($image);
		$canvas->generate();
		
		$description = trim($canvas->template);
		
		$file = file_get_contents($image['image_file']);
		
		$params = array('access_token' => $this->facebook_access_token,
			'source' => '@' . $image['image_file'],
			'message' => $description);
		$photos = $this->facebook->api($this->facebook_album_id . '/photos', 'POST', $params);
	}
	
	public function upload_post($post){
		// Format post
		$canvas = new Canvas($this->facebook_format_post);
		$canvas->assignArray($post);
		$canvas->generate();
		
		// Reformat relative links
		$canvas->template = str_ireplace('href="/', 'href="' . LOCATION . '/', $canvas->template);
		$canvas->template = str_ireplace('href=\'/', 'href=\'' . LOCATION . '/', $canvas->template);
		
		$canvas->template = str_ireplace('src="/', 'src="' . LOCATION . '/', $canvas->template);
		$canvas->template = str_ireplace('src=\'/', 'src=\'' . LOCATION . '/', $canvas->template);
		
		$canvas->template = trim($canvas->template);
		
		// Send to Facebook
		$params = array('access_token' => $this->facebook_access_token,
			'subject' => $post['post_title'],
			'message' => $canvas->template);
		$photos = $this->facebook->api($this->facebook_album_id . '/notes', 'POST', $params);
	}
	
	public function orbit_send_html_image(){
		echo '<option value="facebook">Facebook</option>';
	}
	
	public function orbit_send_html_post(){
		echo '<option value="facebook">Facebook</option>';
	}
	
	public function orbit_send_facebook_image($images){
		return $this->orbit_image($images, true);
	}
	
	public function orbit_send_facebook_post($posts){
		return $this->orbit_post($posts, true);
	}
}

?>