<?php

class FacebookHandler extends Orbit{
	public $facebook;
	public $facebook_username;
	
	private $facebook_password;
	
	public function __construct(){
		parent::__construct();
		
		$this->facebook_active = $this->returnPref('facebook_active');
		$this->facebook_format_image = $this->returnPref('facebook_format_image');
		$this->facebook_last_image_time = $this->returnPref('facebook_last_image_time');
		
		require_once('classes/facebook.php');
		
		$this->facebook_name = $this->returnPref('facebook_name');
		
		$config = array('appId' => '7c4c834772300f94f220af8c5198cd4a',
			'secret' => '3623ff780be9e6ebcc31eed8f2100888');
		
		$this->facebook = new Facebook($config);
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function orbit_config(){
		?>
		<p>Every time you publish an image, it will be uploaded to <a href="http://www.facebook.com/">Facebook</a>.</p>
		<?php
		if($this->facebook_active){
			$this->facebook_format_image = $this->makeHTMLSafe($this->facebook_format_image);
			?>
			<table>
				<tr>
					<td class="right"><label>Name:</label></td>
					<td><a href="http://<?php echo $this->facebook_name; ?>.facebook.com/"><?php echo $this->facebook_name; ?></a> &#0160; <button><a href="<?php echo $this->locationFull(array('unlink' => 'facebook')); ?>">Unlink from Facebook</button></a></td>
				</tr>
				<tr>
					<td class="right pad"><label for="facebook_format_image">Image format:</label></td>
					<td>
						<textarea type="text" id="facebook_format_image" name="facebook_format_image" style="width: 30em;" class="code"><?php echo $this->facebook_format_image; ?></textarea><br />
						<span class="quiet">Your image will automatically be posted, use the text area above to write an optional caption. Use Canvas tags such as <code>{Image_Title}</code> and <code>{Image_URI}</code> above.</span>
					</td>
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
					var_dump($_REQUEST);
					/*
					$tumblr_access_token = $this->tumblr->getAccessToken($_GET['oauth_verifier']);
					
					$this->tumblr_active = true;
					$this->setPref('tumblr_active', true);
					
					$user = $this->tumblr->post('authenticate');
					$xml = simplexml_load_string($user);
					$this->setPref('tumblr_name', (string)$xml->tumblelog['name']);
					
					$this->setPref('tumblr_oauth_token', $tumblr_access_token['oauth_token']);
					$this->setPref('tumblr_oauth_secret', $tumblr_access_token['oauth_token_secret']);
					
					$this->savePref();
					
					$this->addNote('You successfully linked your Tumblr account.', 'success');
					*/
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
					
					$params = array('next' => 'http://www.alkalineapp.com/',
						'cancel' => 'http://www.alkalineapp.com/');
					
					header('Location: ' . $this->facebook->getLoginUrl($params));
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
		
		if(empty($this->facebook_last_image_id)){
			$image_ids = new Find('images');
			$image_ids->published();
			$image_ids->sort('image_published', 'DESC');
			$image_ids->privacy('public');
			$image_ids->find();
			$image = new Image($image_ids);
			$image->hook();
		}
		
		$this->savePref();
	}
	
	public function orbit_image($images){
		if(count($images) < 1){ return; }
		
		// Seek array for new image
		$latest = 0;
		$now = time();
		
		foreach($images as $image){
			$image_published = strtotime($image['image_published']);
			
			if(empty($image_published)){ continue; }
			if($image_published > $now){ continue; }
			if($image_published <= $this->facebook_last_image_time){ continue; }
			if($image['image_privacy'] != 1){ continue; }
			
			if($image_published > $latest){
				$latest = $image_published;
				$latest_image = $image;
			}
		}
		
		if(empty($latest_image)){ return; }
		if($latest_image['image_id'] == $this->facebook_last_image_id){ return; }
		
		// Get image, tags
		$images = new Image($latest_image['image_id']);
		$tags = $images->getTags();
		$latest_image = $images->images[0];
		
		$latest_image_tags = array();
		
		foreach($tags as $tag){
			$latest_image_tags[] = $tag['tag_name'];
		}
		
		// Format tags
		$latest_image_tags = '"' . implode('","', $latest_image_tags) . '"';
		
		// Save this image as last
		$this->setPref('facebook_last_image_time', $latest);
		$this->setPref('facebook_last_image_id', $latest_image['image_id']);
		$this->savePref();
		
		// Format caption
		$canvas = new Canvas($this->facebook_format_image);
		$canvas->assignArray($latest_image);
		$canvas->generate();
		
		// Reformat relative links
		$canvas->template = str_ireplace('href="/', 'href="' . LOCATION . '/', $canvas->template);
		$canvas->template = str_ireplace('href=\'/', 'href=\'' . LOCATION . '/', $canvas->template);

		$canvas->template = str_ireplace('src="/', 'src="' . LOCATION . '/', $canvas->template);
		$canvas->template = str_ireplace('src=\'/', 'src=\'' . LOCATION . '/', $canvas->template);
		
		$canvas->template = trim($canvas->template);
		
		// Send to Facebook
		$parameters = array('type' => 'photo',
			'format' => 'html',
			'tags' => $latest_image_tags,
			'source' => LOCATION . $latest_image['image_src'],
			'caption' => $canvas->template,
			'click-through-url' => $latest_image['image_uri']);
		
		// $this->facebook->post('write', $parameters);
	}
}

?>