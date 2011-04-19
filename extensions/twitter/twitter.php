<?php

class Twitter extends Orbit{
	public $twitter;
	
	public $twitter_screen_name;
	
	private $twitter_oauth_token;
	private $twitter_oauth_secret;
	
	public function __construct(){
		parent::__construct();
		
		$this->twitter_active = $this->returnPref('twitter_active');
		$this->twitter_transmit = $this->returnPref('twitter_transmit');
		$this->twitter_format_image = $this->returnPref('twitter_format_image');
		$this->twitter_format_post = $this->returnPref('twitter_format_post');
		$this->twitter_last_image_id = $this->returnPref('twitter_last_image_id');
		$this->twitter_last_image_time = $this->returnPref('twitter_last_image_time');
		$this->twitter_last_post_id = $this->returnPref('twitter_last_post_id');
		$this->twitter_last_post_time = $this->returnPref('twitter_last_post_time');
		$this->twitter_uri_shortener = $this->returnPref('twitter_uri_shortener');
		
		// Bit.ly
		$this->twitter_bitly_username = $this->returnPref('twitter_bitly_username');
		$this->twitter_bitly_api_key = $this->returnPref('twitter_bitly_api_key');
		
		require_once('classes/twitteroauth.php');
		
		$this->twitter_screen_name = $this->returnPref('twitter_screen_name');
		$this->twitter_oauth_token = $this->returnPref('twitter_oauth_token');
		$this->twitter_oauth_secret = $this->returnPref('twitter_oauth_secret'); 
		
		if(!empty($this->twitter_oauth_token) and !empty($this->twitter_oauth_secret)){
			$this->twitter = new Twitter_TwitterOAuth('Ss0F1kxtvxkkmKGgvPx8w',
				't55gKYkDtn5uKo1enMyF1E00RwOec9aDzNo7TFhzZx4',
				$this->twitter_oauth_token,
				$this->twitter_oauth_secret);
		}
		else{
			$this->twitter = new Twitter_TwitterOAuth('Ss0F1kxtvxkkmKGgvPx8w',
				't55gKYkDtn5uKo1enMyF1E00RwOec9aDzNo7TFhzZx4');
		}
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function orbit_config(){
		?>
		<p>Every time you publish an image or post, your <a href="http://www.twitter.com/">Twitter</a> status will be updated. (If you publish multiple images or posts simultaneously, your status will only be updated once.)</p>
		<?php
		if($this->twitter_active){
			$this->twitter_format_image = $this->makeHTMLSafe($this->twitter_format_image);
			$this->twitter_format_post = $this->makeHTMLSafe($this->twitter_format_post);
			?>
			<table>
				<tr>
					<td class="right"><label>Username:</label></td>
					<td><a href="http://twitter.com/<?php echo $this->twitter_screen_name; ?>/"><button><?php echo $this->twitter_screen_name; ?></a> &#0160; <a href="<?php echo $this->locationFull(array('unlink' => 'twitter')); ?>">Unlink from Twitter</button></a></td>
				</tr>
				<tr>
					<td class="right middle"><label for="twitter_transmit">Transmit:</label></td>
					<td>
						<select name="twitter_transmit" id="twitter_transmit">
							<option value="images_posts" <?php echo $this->readPref('twitter_transmit', 'images_posts'); ?>>Images and posts</option>
							<option value="images" <?php echo $this->readPref('twitter_transmit', 'images'); ?>>Images only</option>
							<option value="posts" <?php echo $this->readPref('twitter_transmit', 'posts'); ?>>Posts only</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="right pad"><label for="twitter_format_image">Image format:</label></td>
					<td>
						<textarea type="text" id="twitter_format_image" name="twitter_format_image" style="width: 30em;" class="code"><?php echo $this->twitter_format_image; ?></textarea><br />
						<span class="quiet">Use Canvas tags such as <code>{Image_Title}</code> and <code>{Image_URI}</code> above.</span>
					</td>
				</tr>
				<tr>
					<td class="right pad"><label for="twitter_format_post">Post format:</label></td>
					<td>
						<textarea type="text" id="twitter_format_post" name="twitter_format_post" style="width: 30em;" class="code"><?php echo $this->twitter_format_post; ?></textarea><br />
						<span class="quiet">Use Canvas tags such as <code>{Post_Title}</code> and <code>{Post_URI}</code> above.</span>
					</td>
				</tr>
				<tr>
					<td class="right pad"><label for="twitter_uri_shortener">URL Shortener:</label></td>
					<td>
						<select id="twitter_uri_shortener" name="twitter_uri_shortener">
							<option value="">None</option>
							<option value="bitly" <?php echo $this->readPref('twitter_uri_shortener', 'bitly'); ?>>bit.ly</option>
							<option value="isgd" <?php echo $this->readPref('twitter_uri_shortener', 'isgd'); ?>>is.gd</option>
							<option value="tinyurl" <?php echo $this->readPref('twitter_uri_shortener', 'tinyurl'); ?>>TinyURL</option>
						</select>
						<table id="bitly" class="service">
							<tr>
								<td class="right middle"><label for="twitter_bitly_username">Bit.ly Username:</label></td>
								<td>
									<input id="twitter_bitly_username" name="twitter_bitly_username" value="<?php echo $this->twitter_bitly_username; ?>" class="m" />
								</td>
							</tr>
							<tr>
								<td class="right middle"><label for="twitter_bitly_api_key">Bit.ly API Key:</label></td>
								<td>
									<input id="twitter_bitly_api_key" name="twitter_bitly_api_key" value="<?php echo $this->twitter_bitly_api_key; ?>" class="m" />
								</td>
							</tr>
						</table>
					</td>
				</tr>
			</table>
			<script type="text/javascript">
				$(document).ready(function(){
					showHide();
					
					$('#twitter_uri_shortener').mouseup(function(){
						showHide();
					});
					
					function showHide(){
						service = $('#twitter_uri_shortener').val();
						$('table.service').hide();
						if($('table#' + service)){
							$('table#' + service).show();
						}
					}
				});
			</script>
			<?php
		}
		else{
			?>
			<table>
				<tr>
					<td class="right"><label>Username:</label></td>
					<td>
						<a href="<?php echo $this->locationFull(array('link' => 'twitter')); ?>"><button>Link to Twitter</button></a><br /><br />
						<span class="quiet">Note: Alkaline will be linked to whichever Twitter account you are currently logged into.</span>
					</td>
				</tr>
			</table>
			<?php
		}
	}
	
	public function orbit_config_load(){
		if(!empty($_GET['from'])){
			switch($_GET['from']){
				case 'twitter':
					$twitter_access_token = $this->twitter->getAccessToken($_GET['oauth_verifier']);
					
					$this->twitter_active = true;
					$this->setPref('twitter_active', true);
					
					$user = $this->twitter->get('account/verify_credentials');
					$this->setPref('twitter_screen_name', $user->screen_name);
					
					$this->setPref('twitter_oauth_token', $twitter_access_token['oauth_token']);
					$this->setPref('twitter_oauth_secret', $twitter_access_token['oauth_token_secret']);
					
					$this->savePref();
					
					$this->addNote('You successfully linked your Twitter account.', 'success');
					header('Location: ' . $this->location());
					exit();
					
					break;
			}
		}
		
		if(!empty($_GET['link'])){
			switch($_GET['link']){
				case 'twitter':
					$twitter_token = $this->twitter->getRequestToken($this->locationFull(array('from' => 'twitter')));
					$twitter_authorize_uri = $this->twitter->getAuthorizeURL($twitter_token['oauth_token']);
					
					$this->setPref('twitter_oauth_token', $twitter_token['oauth_token']);
					$this->setPref('twitter_oauth_secret', $twitter_token['oauth_token_secret']);
					$this->savePref();
					
					header('Location: ' . $twitter_authorize_uri);
					exit();
					
					break;
			}
		}
		
		if(!empty($_GET['unlink'])){
			switch($_GET['unlink']){
				case 'twitter':
					$this->twitter_active = false;
					$this->setPref('twitter_active', false);
					$this->setPref('twitter_screen_name', '');
					$this->setPref('twitter_oauth_token', '');
					$this->setPref('twitter_oauth_secret', '');
					$this->savePref();
					
					$this->addNote('You successfully unlinked your Twitter account.', 'success');
					header('Location: ' . $this->location());
					exit();
					
					break;
			}
		}
	}
	
	public function orbit_config_save(){
		$now = time();
		$this->setPref('twitter_last_image_time', $now);
		$this->setPref('twitter_last_post_time', $now);
		
		if(strpos($this->twitter_transmit, 'image')){
			if(empty($_POST['twitter_format_image'])){
				$this->addNote('You must format your tweet in order for the Twitter extension to work.', 'notice');
			}
			
			if(empty($this->twitter_last_image_id)){
				$image_ids = new Find('images');
				$image_ids->published();
				$image_ids->sort('image_published', 'DESC');
				$image_ids->privacy('public');
				$image_ids->find();
				$image = new Image($image_ids);
				$image->hook();
			}
		}
		
		if(strpos($this->twitter_transmit, 'post')){
			if(empty($_POST['twitter_format_post'])){
				$this->addNote('You must format your tweet in order for the Twitter extension to work.', 'notice');
			}
			
			if(empty($this->twitter_last_post_id)){
				$post_ids = new Find('posts');
				$post_ids->published();
				$post_ids->sort('post_published', 'DESC');
				$post_ids->find();
				$posts = new Post($post_ids);
				$posts->hook();
			}
		}
		
		$this->setPref('twitter_transmit', @$_POST['twitter_transmit']);
		$this->setPref('twitter_format_image', @$_POST['twitter_format_image']);
		$this->setPref('twitter_format_post', @$_POST['twitter_format_post']);
		$this->setPref('twitter_uri_shortener', @$_POST['twitter_uri_shortener']);
		
		// Bit.ly
		$this->setPref('twitter_bitly_username', @$_POST['twitter_bitly_username']);
		$this->setPref('twitter_bitly_api_key', @$_POST['twitter_bitly_api_key']);
		
		$this->savePref();
	}
	
	public function orbit_image($images){
		if(strpos($this->twitter_transmit, 'image') === false){ return; }
		
		if(count($images) < 1){ return; }
		
		$latest = 0;
		$now = time();
		
		foreach($images as $image){
			$image_published = strtotime($image['image_published']);
			
			if(empty($image_published)){ continue; }
			if($image_published > $now){ continue; }
			if($image_published <= $this->twitter_last_image_time){ continue; }
			if($image['image_privacy'] != 1){ continue; }
			
			if($image_published > $latest){
				$latest = $image_published;
				$latest_image = $image;
			}
		}
		
		if(empty($latest_image)){ return; }
		if($latest_image['image_id'] == $this->twitter_last_image_id){ return; }
		
		$this->setPref('twitter_last_image_time', $latest);
		$this->setPref('twitter_last_image_id', $latest_image['image_id']);
		$this->savePref();
		
		$latest_image['image_uri'] = $this->shortenURI($latest_image['image_uri'], $this->twitter_uri_shortener);
		
		$canvas = new Canvas($this->twitter_format_image);
		$canvas->assignArray($latest_image);
		$canvas->generate();
		
		$canvas->template = trim($canvas->template);
		
		$parameters = array('status' => $canvas->template);
		$this->twitter->post('statuses/update', $parameters);
	}
	
	public function orbit_post($posts){
		if(strpos($this->twitter_transmit, 'post') === false){ return; }
		
		if(count($posts) < 1){ return; }
		
		$latest = 0;
		$now = time();
		foreach($posts as $post){
			$post_published = strtotime($post['post_published']);
			
			if(empty($post_published)){ continue; }
			if($post_published > $now){ continue; }
			if($post_published <= $this->twitter_last_post_time){ continue; }
			
			if($post_published > $latest){
				$latest = $post_published;
				$latest_post = $post;
			}
		}
		
		if(empty($latest_post)){ return; }
		if($latest_post['post_id'] == $this->twitter_last_post_id){ return; }
		
		$this->setPref('twitter_last_post_time', $latest);
		$this->setPref('twitter_last_post_id', $latest_post['post_id']);
		$this->savePref();
		
		$latest_post['post_uri'] = $this->shortenURI($latest_post['post_uri'], $this->twitter_uri_shortener);
		
		$canvas = new Canvas($this->twitter_format_post);
		$canvas->assignArray($latest_post);
		$canvas->generate();
		
		$canvas->template = trim($canvas->template);
		
		$parameters = array('status' => $canvas->template);
		$this->twitter->post('statuses/update', $parameters);
	}
	
	public function shortenURI($uri, $service=null){
		if($service == 'tinyurl'){
			$uri = trim(file_get_contents('http://tinyurl.com/api-create.php?url=' . urlencode($uri)));
		}
		elseif($service == 'isgd'){
			$uri = trim(file_get_contents('http://is.gd/create.php?format=simple&url=' . urlencode($uri)));
		}
		elseif($service == 'bitly'){
			$json = file_get_contents('http://api.bit.ly/v3/shorten?login=' . $this->twitter_bitly_username . '&apiKey=' . $this->twitter_bitly_api_key  . '&longUrl=' . urlencode($uri) . '&format=json');
			$json = json_decode($json, true);
			$uri = $json['data']['url'];
		}
		
		return $uri;
	}
}

?>