<?php

class Facebook extends Orbit{
	public $facebook;
	public $facebook_username;
	
	private $facebook_password;
	
	public function __construct(){
		parent::__construct();
		
		$this->facebook_active = $this->returnPref('facebook_active');
		$this->facebook_transmit = $this->returnPref('facebook_transmit');
		$this->facebook_format_image = $this->returnPref('facebook_format_image');
		$this->facebook_format_post = $this->returnPref('facebook_format_post');
		$this->facebook_last_image_time = $this->returnPref('facebook_last_image_time');
		$this->facebook_last_post_time = $this->returnPref('facebook_last_post_time');
		
		require_once('classes/FacebookAPI.php');
		
		$this->facebook_name = $this->returnPref('facebook_name');
		$this->facebook_oauth_token = $this->returnPref('facebook_oauth_token');
		$this->facebook_oauth_secret = $this->returnPref('facebook_oauth_secret');
		
		/*
		if(!empty($this->facebook_email) and !empty($this->facebook_password)){
			ini_set('default_socket_timeout', 1);
			$this->facebook = new FacebookAPI();
			ini_restore('default_socket_timeout');
			$this->facebook->init($this->facebook_email, $this->facebook_password, 'Facebook by Alkaline Labs');
			$this->facebook->init_cache(60, PATH . CACHE);
		}
		*/
		
		if(!empty($this->facebook_oauth_token) and !empty($this->facebook_oauth_secret)){
			ini_set('default_socket_timeout', 1);
			$this->facebook = new Facebook_FacebookAPI('4P6gWXvDKLeuRy0hTLdMXxADrclI2QMbNQXPa3O78jeap7005S',
				'Kx8nJZplPAtEH7bgXBItlQMW9CsAsCDhIELU4ktXKQ1Cf7Akc9',
				$this->facebook_oauth_token,
				$this->facebook_oauth_secret);
			ini_restore('default_socket_timeout');
		}
		else{
			ini_set('default_socket_timeout', 1);
			$this->facebook = new Facebook_FacebookAPI('4P6gWXvDKLeuRy0hTLdMXxADrclI2QMbNQXPa3O78jeap7005S',
				'Kx8nJZplPAtEH7bgXBItlQMW9CsAsCDhIELU4ktXKQ1Cf7Akc9');
			ini_restore('default_socket_timeout');
		}
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function orbit_config(){
		?>
		<p>Every time you publish an image or post, your <a href="http://www.facebook.com/">Facebook</a> will be updated. (If you publish multiple images or posts simultaneously, your Tumblelog will only be updated once.)</p>
		<?php
		if($this->facebook_active){
			$this->facebook_format_image = $this->makeHTMLSafe($this->facebook_format_image);
			$this->facebook_format_post = $this->makeHTMLSafe($this->facebook_format_post);
			?>
			<table>
				<tr>
					<td class="right"><label>Name:</label></td>
					<td><a href="http://<?php echo $this->facebook_name; ?>.facebook.com/"><?php echo $this->facebook_name; ?></a> &#0160; <button><a href="<?php echo $this->locationFull(array('unlink' => 'facebook')); ?>">Unlink from Facebook</button></a></td>
				</tr>
				<tr>
					<td class="right middle"><label for="facebook_transmit">Transmit:</label></td>
					<td>
						<select name="facebook_transmit" id="facebook_transmit">
							<option value="images_posts" <?php echo $this->readPref('facebook_transmit', 'images_posts'); ?>>Images and posts</option>
							<option value="images" <?php echo $this->readPref('facebook_transmit', 'images'); ?>>Images only</option>
							<option value="posts" <?php echo $this->readPref('facebook_transmit', 'posts'); ?>>Posts only</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="right pad"><label for="facebook_format_image">Image format:</label></td>
					<td>
						<textarea type="text" id="facebook_format_image" name="facebook_format_image" style="width: 30em;" class="code"><?php echo $this->facebook_format_image; ?></textarea><br />
						<span class="quiet">Your image will automatically be posted, use the text area above to write an optional caption. Use Canvas tags such as <code>{Image_Title}</code> and <code>{Image_URI}</code> above.</span>
					</td>
				</tr>
				<tr>
					<td class="right pad"><label for="facebook_format_post">Post format:</label></td>
					<td>
						<textarea type="text" id="facebook_format_post" name="facebook_format_post" style="width: 30em;" class="code"><?php echo $this->facebook_format_post; ?></textarea><br />
						<span class="quiet">Your title will automatically be posted, use the text area above to write an optional body text (or summary). Use Canvas tags such as <code>{Post_Title}</code> and <code>{Post_URI}</code> above.</span>
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
					$facebook_access_token = $this->facebook->getAccessToken($_GET['oauth_verifier']);
					
					$this->facebook_active = true;
					$this->setPref('facebook_active', true);
					
					$user = $this->facebook->post('authenticate');
					$xml = simplexml_load_string($user);
					$this->setPref('facebook_name', (string)$xml->tumblelog['name']);
					
					$this->setPref('facebook_oauth_token', $facebook_access_token['oauth_token']);
					$this->setPref('facebook_oauth_secret', $facebook_access_token['oauth_token_secret']);
					
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
					$facebook_token = $this->facebook->getRequestToken($this->locationFull(array('from' => 'facebook')));
					$facebook_authorize_uri = $this->facebook->getAuthorizeURL($facebook_token['oauth_token']);
					
					$this->setPref('facebook_oauth_token', $facebook_token['oauth_token']);
					$this->setPref('facebook_oauth_secret', $facebook_token['oauth_token_secret']);
					$this->savePref();
					
					header('Location: ' . $facebook_authorize_uri);
					exit();
					
					break;
			}
		}
		
		if(!empty($_GET['unlink'])){
			switch($_GET['unlink']){
				case 'facebook':
					$this->facebook_active = false;
					$this->setPref('facebook_active', false);
					$this->setPref('facebook_name', '');
					$this->setPref('facebook_oauth_token', '');
					$this->setPref('facebook_oauth_secret', '');
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
		$this->setPref('facebook_last_post_time', $now);
		
		if(strpos($this->facebook_transmit, 'image')){
			if(empty($this->facebook_last_image_id)){
				$image_ids = new Find('images');
				$image_ids->published();
				$image_ids->sort('image_published', 'DESC');
				$image_ids->privacy('public');
				$image_ids->find();
				$image = new Image($image_ids);
				$image->hook();
			}
		}
		
		if(strpos($this->facebook_transmit, 'post')){			
			if(empty($this->facebook_last_post_id)){
				$post_ids = new Find('posts');
				$post_ids->published();
				$post_ids->sort('post_published', 'DESC');
				$post_ids->find();
				$posts = new Post($post_ids);
				$posts->hook();
			}
		}
		
		$this->setPref('facebook_transmit', @$_POST['facebook_transmit']);
		$this->setPref('facebook_format_image', @$_POST['facebook_format_image']);
		$this->setPref('facebook_format_post', @$_POST['facebook_format_post']);
		
		$this->savePref();
	}
	
	public function orbit_image($images){
		if(strpos($this->facebook_transmit, 'image') === false){ return; }
		
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
		
		$this->facebook->post('write', $parameters);
	}
	
	public function orbit_post($posts){
		if(strpos($this->facebook_transmit, 'post') === false){ return; }
		
		if(count($posts) < 1){ return; }
		
		// Seek array for new post
		$latest = 0;
		$now = time();
		
		foreach($posts as $post){
			$post_published = strtotime($post['post_published']);
			
			if(empty($post_published)){ continue; }
			if($post_published > $now){ continue; }
			if($post_published <= $this->facebook_last_post_time){ continue; }
			
			if($post_published > $latest){
				$latest = $post_published;
				$latest_post = $post;
			}
		}
		
		// Save this post as last
		if(empty($latest_post)){ return; }
		if($latest_post['post_id'] == $this->facebook_last_post_id){ return; }
		
		$this->setPref('facebook_last_post_time', $latest);
		$this->setPref('facebook_last_post_id', $latest_post['post_id']);
		$this->savePref();
		
		// Format post
		$canvas = new Canvas($this->facebook_format_post);
		$canvas->assignArray($latest_post);
		$canvas->generate();
		
		// Reformat relative links
		$canvas->template = str_ireplace('href="/', 'href="' . LOCATION . '/', $canvas->template);
		$canvas->template = str_ireplace('href=\'/', 'href=\'' . LOCATION . '/', $canvas->template);
		
		$canvas->template = str_ireplace('src="/', 'src="' . LOCATION . '/', $canvas->template);
		$canvas->template = str_ireplace('src=\'/', 'src=\'' . LOCATION . '/', $canvas->template);
		
		$canvas->template = trim($canvas->template);
		
		// Send to Facebook
		$parameters = array('type' => 'regular',
			'format' => 'html',
			'title' => $latest_post['post_title'],
			'body' => $canvas->template);

		$this->facebook->post('write', $parameters);
	}
}

?>