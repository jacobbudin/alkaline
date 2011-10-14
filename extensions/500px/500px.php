<?php

class Five extends Orbit{
	public $five;
	public $five_username;
	
	private $five_password;
	
	public function __construct(){
		parent::__construct();
		
		$this->five_active = $this->returnPref('five_active');
		
		$this->five_format_image = $this->returnPref('five_format_image');
		$this->five_last_image_id = $this->returnPref('five_last_image_id');
		$this->five_last_image_time = $this->returnPref('five_last_image_time');
		
		require_once('classes/500pxAPI.php');
		
		$this->five_name = $this->returnPref('five_name');
		$this->five_oauth_token = $this->returnPref('five_oauth_token');
		$this->five_oauth_secret = $this->returnPref('five_oauth_secret');
		
		/*
		if(!empty($this->five_email) and !empty($this->five_password)){
			ini_set('default_socket_timeout', 1);
			$this->five = new TumblrAPI();
			ini_restore('default_socket_timeout');
			$this->five->init($this->five_email, $this->five_password, 'Tumblr by Alkaline Labs');
			$this->five->init_cache(60, PATH . CACHE);
		}
		*/
		
		if(!empty($this->five_oauth_token) and !empty($this->five_oauth_secret)){
			ini_set('default_socket_timeout', 1);
			$this->five = new Five_FiveAPI('ol2NtEYlEjbs19qU4yEg1KkJaPbdnZSbARyem2rG',
				'F3yhjN8T9wcahHbV6Nrq5AHZISunhlgISpKghZmd',
				$this->five_oauth_token,
				$this->five_oauth_secret);
			ini_restore('default_socket_timeout');
		}
		else{
			ini_set('default_socket_timeout', 1);
			$this->five = new Five_FiveAPI('ol2NtEYlEjbs19qU4yEg1KkJaPbdnZSbARyem2rG',
				'F3yhjN8T9wcahHbV6Nrq5AHZISunhlgISpKghZmd');
			ini_restore('default_socket_timeout');
		}
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function orbit_config(){
		
		?>
		<p>Let Alkaline update your <a href="http://www.500px.com/">500px</a>.</p>
		<?php
		if($this->five_active){
			$this->five_format_image = $this->makeHTMLSafe($this->five_format_image);
			?>
			<table>
				<tr>
					<td class="right"><label>Username:</label></td>
					<td><a href="http://500px.com/<?php echo $this->five_name; ?>"><?php echo $this->five_name; ?></a> &#0160; <a href="<?php echo $this->locationFull(array('unlink' => 'five')); ?>"><button>Unlink from 500px</button></a></td>
				</tr>
				<tr>
					<td class="right"><label for="five_format_image">Image format:</label></td>
					<td>
						<textarea type="text" id="five_format_image" name="five_format_image" style="width: 30em;" class="code"><?php echo $this->five_format_image; ?></textarea><br />
						<p class="quiet">Your image will automatically be posted, you can use the text area above to write an optional caption. Use Canvas tags such as <code>{Image_Title}</code> and <code>{Image_URI}</code> above.</p>
					</td>
				</tr>
			</table>
			<?php
		}
		else{
			?>
			<table>
				<tr>
					<td class="right"><label>Name:</label></td>
					<td>
						<a href="<?php echo $this->locationFull(array('link' => 'five')); ?>"><button>Link to 500px</button></a><br /><br />
						<span class="quiet">Note: Alkaline will be linked to whichever 500px account you are currently logged into.</span>
					</td>
				</tr>
			</table>
			<?php
		}
	}
	
	public function orbit_config_load(){
		if(!empty($_GET['from'])){
			switch($_GET['from']){
				case 'five':
					$five_access_token = $this->five->getAccessToken($_GET['oauth_verifier']);
					
					$this->five_active = true;
					$this->setPref('five_active', true);
					
					$response = $this->five->get('users');
					$this->setPref('five_name', $response->user->username);
					
					$this->setPref('five_oauth_token', $five_access_token['oauth_token']);
					$this->setPref('five_oauth_secret', $five_access_token['oauth_token_secret']);
					
					$this->savePref();
					
					$this->addNote('You successfully linked your 500px account.', 'success');
					header('Location: ' . $this->location());
					exit();
					
					break;
			}
		}
		
		if(!empty($_GET['link'])){
			switch($_GET['link']){
				case 'five':
					$five_token = $this->five->getRequestToken($this->locationFull(array('from' => 'five')));
					$five_authorize_uri = $this->five->getAuthorizeURL($five_token['oauth_token']);
					
					$this->setPref('five_oauth_token', $five_token['oauth_token']);
					$this->setPref('five_oauth_secret', $five_token['oauth_token_secret']);
					$this->savePref();
					
					header('Location: ' . $five_authorize_uri);
					exit();
					
					break;
			}
		}
		
		if(!empty($_GET['unlink'])){
			switch($_GET['unlink']){
				case 'five':
					$this->five_active = false;
					$this->setPref('five_active', false);
					$this->setPref('five_name', '');
					$this->setPref('five_oauth_token', '');
					$this->setPref('five_oauth_secret', '');
					$this->savePref();
					
					$this->addNote('You successfully unlinked your 500px account.', 'success');
					header('Location: ' . $this->location());
					exit();
					
					break;
			}
		}
		
		/*
		if(!empty($_GET['from'])){
			switch($_GET['from']){
				case 'five':
					$five_access_token = $this->five->getAccessToken($_GET['oauth_verifier']);
					
					$this->five_active = true;
					$this->setPref('five_active', true);
					
					$user = $this->five->get('account/verify_credentials');
					$this->setPref('five_screen_name', $user->screen_name);
					
					$this->setPref('five_oauth_token', $five_access_token['oauth_token']);
					$this->setPref('five_oauth_secret', $five_access_token['oauth_token_secret']);
					
					$this->savePref();
					
					$this->addNote('You successfully linked your Tumblr account.', 'success');
					header('Location: ' . $this->location());
					exit();
					
					break;
			}
		}
		
		if(!empty($_GET['unlink'])){
			switch($_GET['unlink']){
				case 'five':
					$this->five_active = false;
					$this->setPref('five_active', false);
					$this->setPref('five_email', '');
					$this->setPref('five_password', '');
					$this->savePref();
					
					$this->addNote('You successfully unlinked your Tumblr account.', 'success');
					header('Location: ' . $this->location());
					exit();
					
					break;
			}
		}
		*/
	}
	
	public function orbit_config_save(){
		$now = time();
		$this->setPref('five_last_image_time', $now);
		
		$this->setPref('five_format_image', @$_POST['five_format_image']);
		
		$this->savePref();
	}
	
	public function orbit_image($images, $override=false){
		if($override === false){ return; }
		if(count($images) < 1){ return; }
		
		$now = time();
		
		foreach($images as $image){
			$image_published = strtotime($image['image_published']);
			
			if(empty($image_published)){ continue; }
			if($image_published > $now){ continue; }
			if($override !== true){
				if($image_published <= $this->five_last_image_time){ continue; }
				if($image['image_privacy'] != 1){ continue; }
			}
			
			$image['500px_category_id'] = $_POST['500px_category_id'];
			
			$this->storeTask(array($this, 'upload_image'), $image);
		}
		
		$this->setPref('five_last_image_time', $now);
		$this->savePref();
	}
	
	public function upload_image($image){
		// Format caption
		$canvas = new Canvas($this->five_format_image);
		$canvas->assignArray($image);
		$canvas->generate();
		
		// Reformat relative links
		$canvas->template = str_ireplace('href="/', 'href="' . LOCATION . '/', $canvas->template);
		$canvas->template = str_ireplace('href=\'/', 'href=\'' . LOCATION . '/', $canvas->template);

		$canvas->template = str_ireplace('src="/', 'src="' . LOCATION . '/', $canvas->template);
		$canvas->template = str_ireplace('src=\'/', 'src=\'' . LOCATION . '/', $canvas->template);
		
		$canvas->template = trim($canvas->template);
		
		// Send to 500px
		$parameters = array('category' => intval($image['500px_category_id']),
			'description' => $canvas->template,
			'name' => $image['image_title']);
		
		$response = $this->five->post('photos', $parameters);
		
		var_dump($response);
		
		$file = file_get_contents($image['image_file']);
		
		$parameters = array('consumer_key' => 'ol2NtEYlEjbs19qU4yEg1KkJaPbdnZSbARyem2rG',
			'access_key' => $this->five_oauth_token,
			'photo_id' => $response->photo->id,
			'upload_key' => $response->upload_key,
			'file' => $file);
		
		$response = $this->five->post('upload', $parameters);
		
		var_dump($response);
	}
	
	public function orbit_send_html_image(){
		echo '<option value="five">500px</option>
			<script type="text/javascript">
				$("#image_send_service").change(function() {
					var value = $(this).val();
					if(value == "five"){
						$(this).after(\'&#0160;<select id="500px_category_id" name="500px_category_id"><option value="10">Abstract</option><option value="11">Animals</option><option value="5">Black and White</option><option value="1">Celebrities</option><option value="9">City and Architecture</option><option value="15">Commercial</option><option value="16">Concert</option><option value="20">Family</option><option value="14">Fashion</option><option value="2">Film</option><option value="24">Fine Art</option><option value="23">Food</option><option value="3">Journalism</option><option value="8">Landscapes</option><option value="12">Macro</option><option value="18">Nature</option><option value="4">Nude</option><option value="7">People</option><option value="19">Performing Arts</option><option value="17">Sport</option><option value="6">Still Life</option><option value="21">Street</option><option value="13">Travel</option><option value="22">Underwater</option></select>\');
					}
				});
			</script>';
	}
	
	public function orbit_send_five_image($images){
		return $this->orbit_image($images, true);
	}
}

?>