<?php

class Tumblr extends Orbit{
	public $tumblr;
	public $tumblr_username;
	
	private $tumblr_password;
	
	public function __construct(){
		parent::__construct();
		
		$this->tumblr_active = $this->returnPref('tumblr_active');
		$this->tumblr_transmit = $this->returnPref('tumblr_transmit');
		$this->tumblr_format_image = $this->returnPref('tumblr_format_image');
		$this->tumblr_format_post = $this->returnPref('tumblr_format_post');
		$this->tumblr_last_image_id = $this->returnPref('tumblr_last_image_id');
		$this->tumblr_last_image_time = $this->returnPref('tumblr_last_image_time');
		$this->tumblr_last_post_id = $this->returnPref('tumblr_last_post_id');
		$this->tumblr_last_post_time = $this->returnPref('tumblr_last_post_time');
		
		require_once('classes/TumblrAPI.php');
		
		$this->tumblr_email = $this->returnPref('tumblr_email');
		$this->tumblr_password = $this->returnPref('tumblr_password');
		
		if(!empty($this->tumblr_email) and !empty($this->tumblr_password)){
			ini_set('default_socket_timeout', 1);
			$this->tumblr = new TumblrAPI();
			ini_restore('default_socket_timeout');
			$this->tumblr->init($this->tumblr_email, $this->tumblr_password, 'Tumblr by Alkaline Labs');
			$this->tumblr->init_cache(60, PATH . CACHE);
		}
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function orbit_config(){
		?>
		<p>Every time you publish an image or post, your <a href="http://www.tumblr.com/">Tumblr</a> will be updated. (If you publish multiple images or posts simultaneously, your Tumblog will only be updated once.)</p>
		<?php
		if($this->tumblr_active){
			$this->tumblr_format_image = $this->makeHTMLSafe($this->tumblr_format_image);
			$this->tumblr_format_post = $this->makeHTMLSafe($this->tumblr_format_post);
			?>
			<table>
				<tr>
					<td class="right"><label>Email:</label></td>
					<td><?php echo $this->tumblr_email; ?></a> &#0160; <a href="<?php echo $this->locationFull(array('unlink' => 'tumblr')); ?>" class="button">Unlink from Tumblr</a></td>
				</tr>
				<tr>
					<td class="right middle"><label for="tumblr_transmit">Transmit:</label></td>
					<td>
						<select name="tumblr_transmit" id="tumblr_transmit">
							<option value="images_posts" <?php echo $this->readPref('tumblr_transmit', 'images_posts'); ?>>Images and posts</option>
							<option value="images" <?php echo $this->readPref('tumblr_transmit', 'images'); ?>>Images only</option>
							<option value="posts" <?php echo $this->readPref('tumblr_transmit', 'posts'); ?>>Posts only</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="right pad"><label for="tumblr_format_image">Image format:</label></td>
					<td>
						<textarea type="text" id="tumblr_format_image" name="tumblr_format_image" style="width: 30em;" class="code"><?php echo $this->tumblr_format_image; ?></textarea><br />
						<span class="quiet">Your image will automatically be posted, use the text area above to write an optional caption. Use Canvas tags such as <code>{Image_Title}</code> and <code>{Image_URI}</code> above.</span>
					</td>
				</tr>
				<tr>
					<td class="right pad"><label for="tumblr_format_post">Post format:</label></td>
					<td>
						<textarea type="text" id="tumblr_format_post" name="tumblr_format_post" style="width: 30em;" class="code"><?php echo $this->tumblr_format_post; ?></textarea><br />
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
					<td class="right middle"><label>Email:</label></td>
					<td>
						<input type="text" id="tumblr_email" name="tumblr_email" value="" class="m" />
					</td>
				</tr>
				<tr>
					<td class="right middle"><label>Password:</label></td>
					<td>
						<input type="password" id="tumblr_password" name="tumblr_password" value="" class="s" />
					</td>
				</tr>
			</table>
			<?php
		}
	}
	
	public function orbit_config_load(){
		/*
		if(!empty($_GET['from'])){
			switch($_GET['from']){
				case 'tumblr':
					$tumblr_access_token = $this->tumblr->getAccessToken($_GET['oauth_verifier']);
					
					$this->tumblr_active = true;
					$this->setPref('tumblr_active', true);
					
					$user = $this->tumblr->get('account/verify_credentials');
					$this->setPref('tumblr_screen_name', $user->screen_name);
					
					$this->setPref('tumblr_oauth_token', $tumblr_access_token['oauth_token']);
					$this->setPref('tumblr_oauth_secret', $tumblr_access_token['oauth_token_secret']);
					
					$this->savePref();
					
					$this->addNote('You successfully linked your Tumblr account.', 'success');
					header('Location: ' . $this->location());
					exit();
					
					break;
			}
		}
		*/
		
		if(!empty($_GET['unlink'])){
			switch($_GET['unlink']){
				case 'tumblr':
					$this->tumblr_active = false;
					$this->setPref('tumblr_active', false);
					$this->setPref('tumblr_email', '');
					$this->setPref('tumblr_password', '');
					$this->savePref();
					
					$this->addNote('You successfully unlinked your Tumblr account.', 'success');
					header('Location: ' . $this->location());
					exit();
					
					break;
			}
		}
	}
	
	public function orbit_config_save(){
		if(!empty($_POST['tumblr_email']) or !empty($_POST['tumblr_password'])){
			$this->tumblr = new TumblrAPI();
			$this->tumblr->init($_POST['tumblr_email'], $_POST['tumblr_password'], 'Tumblr by Alkaline Labs');
			$this->tumblr->init_cache(60, PATH . CACHE);
			$check = $this->tumblr->check('authenticate');
			
			if($check === true){
				$this->tumblr_active = true;
				$this->setPref('tumblr_active', true);
				$this->setPref('tumblr_email', $_POST['tumblr_email']);
				$this->setPref('tumblr_password', $_POST['tumblr_password']);
				$this->savePref();
				
				$this->addNote('You successfully linked your Tumblr account.', 'success');
			}
			else{
				$this->addNote('Your Tumblr account was not linked. Check your email address and password, and whether Tumblr&#8217;s Web site is currently available.', 'error');
			}
			
			header('Location: ' . $this->location());
			exit();
		}
		
		$now = time();
		$this->setPref('tumblr_last_image_time', $now);
		$this->setPref('tumblr_last_post_time', $now);
		
		if(strpos($this->tumblr_transmit, 'image')){
			if(empty($this->tumblr_last_image_id)){
				$image_ids = new Find;
				$image_ids->published();
				$image_ids->sort('image_published', 'DESC');
				$image_ids->privacy('public');
				$image_ids->find();
				$image = new Image($image_ids);
				$image->hook();
			}
		}
		
		if(strpos($this->tumblr_transmit, 'post')){			
			if(empty($this->tumblr_last_post_id)){
				$posts = new Post;
				$posts->published();
				$posts->sort('post_published', 'DESC');
				$posts->fetch();
				$posts->hook();
			}
		}
		
		$this->setPref('tumblr_transmit', @$_POST['tumblr_transmit']);
		$this->setPref('tumblr_format_image', @$_POST['tumblr_format_image']);
		$this->setPref('tumblr_format_post', @$_POST['tumblr_format_post']);
		
		$this->savePref();
	}
	
	public function orbit_image($images){
		if(strpos($this->tumblr_transmit, 'image') === false){ return; }
		
		if(count($images) < 1){ return; }
		
		// Seek array for new image
		$latest = 0;
		$now = time();
		
		foreach($images as $image){
			$image_published = strtotime($image['image_published']);
			
			if(empty($image_published)){ continue; }
			if($image_published > $now){ continue; }
			if($image_published <= $this->tumblr_last_image_time){ continue; }
			if($image['image_privacy'] != 1){ continue; }
			
			if($image_published > $latest){
				$latest = $image_published;
				$latest_image = $image;
			}
		}
		
		if(empty($latest_image)){ return; }
		if($latest_image['image_id'] == $this->tumblr_last_image_id){ return; }
		
		// Get image, tags
		$images = new Image($latest_image['image_id']);
		$latest_image_tags = $images->getTags();
		$latest_image = $images->images[0];
		
		// Format tags
		$latest_image_tags = '"' . implode('","') . '"';
		
		// Save this image as last
		$this->setPref('tumblr_last_image_time', $latest);
		$this->setPref('tumblr_last_image_id', $latest_image['image_id']);
		$this->savePref();
		
		// Format caption
		$canvas = new Canvas($this->tumblr_format_image);
		$canvas->assignArray($latest_image);
		$canvas->generate();
		
		// Reformat relative links
		$canvas->template = str_ireplace('href="/', 'href="' . LOCATION . '/', $canvas->template);
		$canvas->template = str_ireplace('href=\'/', 'href=\'' . LOCATION . '/', $canvas->template);

		$canvas->template = str_ireplace('src="/', 'src="' . LOCATION . '/', $canvas->template);
		$canvas->template = str_ireplace('src=\'/', 'src=\'' . LOCATION . '/', $canvas->template);
		
		$canvas->template = trim($canvas->template);
		
		// Send to Tumblr
		$parameters = array('type' => 'photo',
			'format' => 'html',
			'tags' => $latest_image_tags,
			'source' => LOCATION . $latest_image['image_src'],
			'caption' => $canvas->template,
			'click-through-url' => $latest_image['image_uri']);
		
		$this->tumblr->post($parameters);
	}
	
	public function orbit_post($posts){
		if(strpos($this->tumblr_transmit, 'post') === false){ return; }
		
		if(count($posts) < 1){ return; }
		
		// Seek array for new post
		$latest = 0;
		$now = time();
		
		foreach($posts as $post){
			$post_published = strtotime($post['post_published']);
			
			if(empty($post_published)){ continue; }
			if($post_published > $now){ continue; }
			if($post_published <= $this->tumblr_last_post_time){ continue; }
			
			if($post_published > $latest){
				$latest = $post_published;
				$latest_post = $post;
			}
		}
		
		// Save this post as last
		if($latest_post['post_id'] == $this->tumblr_last_post_id){ return; }
		if(empty($latest_post)){ return; }
		
		$this->setPref('tumblr_last_post_time', $latest);
		$this->setPref('tumblr_last_post_id', $latest_post['post_id']);
		$this->savePref();
		
		// Format post
		$canvas = new Canvas($this->tumblr_format_post);
		$canvas->assignArray($latest_post);
		$canvas->generate();
		
		// Reformat relative links
		$canvas->template = str_ireplace('href="/', 'href="' . LOCATION . '/', $canvas->template);
		$canvas->template = str_ireplace('href=\'/', 'href=\'' . LOCATION . '/', $canvas->template);
		
		$canvas->template = str_ireplace('src="/', 'src="' . LOCATION . '/', $canvas->template);
		$canvas->template = str_ireplace('src=\'/', 'src=\'' . LOCATION . '/', $canvas->template);
		
		$canvas->template = trim($canvas->template);
		
		// Send to Tumblr
		$parameters = array('type' => 'regular',
			'format' => 'html',
			'title' => $latest_post['post_title'],
			'body' => $canvas->template);

		$this->tumblr->post($parameters);
	}
}

?>