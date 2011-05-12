<?php

class Flickr extends Orbit{
	public $flickr;
	
	public $flickr_screen_name;
	
	private $flickr_oauth_token;
	private $flickr_oauth_secret;
	
	public function __construct(){
		parent::__construct();
		
		$this->flickr_active = $this->returnPref('flickr_active');
		
		require_once('classes/phpFlickr.php');
		
		$this->flickr_nsid = $this->returnPref('flickr_nsid');
		$this->flickr_token = $this->returnPref('flickr_token');
		$this->flickr_username = $this->returnPref('flickr_username');
		$this->flickr_last_image_time = $this->returnPref('flickr_last_image_time');
		$this->flickr_format_image = $this->returnPref('flickr_format_image');
		
		$this->flickr_screen_name = $this->returnPref('flickr_screen_name');
		$this->flickr_oauth_token = $this->returnPref('flickr_oauth_token');
		$this->flickr_oauth_secret = $this->returnPref('flickr_oauth_secret'); 
		
		$this->flickr = new phpFlickr('75fce2f6341c71a67ca64f3142630a01',
			'50a4da9acdbf0be0');		
		
		if(!empty($this->flickr_oauth_token) and !empty($this->flickr_oauth_secret)){
			$this->flickr->setToken($this->flickr_token);
		}
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function orbit_config(){
		?>
		<p>When you publish one or more images, the images will be uploaded to <a href="http://www.flickr.com/">Flickr</a>.</p>
		<?php
		if($this->flickr_active){
			$this->flickr_format_image = $this->makeHTMLSafe($this->flickr_format_image);
			?>
			<table>
				<tr>
					<td class="right"><label>Username:</label></td>
					<td><a href="http://flickr.com/photos/<?php echo $this->flickr_nsid; ?>/"><?php echo $this->flickr_username; ?></a> &#0160; <a href="<?php echo $this->locationFull(array('unlink' => 'flickr')); ?>"><button>Unlink from Flickr</button></a></td>
				</tr>
				<tr>
					<td class="right pad"><label for="flickr_format_image">Image description format:</label></td>
					<td>
						<textarea type="text" id="flickr_format_image" name="flickr_format_image" style="width: 30em;" class="code"><?php echo $this->flickr_format_image; ?></textarea><br />
						<span class="quiet">Use Canvas tags such as <code>{Image_Title}</code> and <code>{Image_URI}</code> above.</span>
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
						<a href="<?php echo $this->locationFull(array('link' => 'flickr')); ?>"><button>Link to Flickr</button></a><br /><br />
						<span class="quiet">Note: Alkaline will be linked to whichever Flickr account you are currently logged into.</span>
					</td>
				</tr>
			</table>
			<?php
		}
	}
	
	public function orbit_config_load(){
		if(!empty($_GET['from'])){
			switch($_GET['from']){
				case 'flickr':
					$result = $this->flickr->auth_getToken($_GET['frob']);
					$result2 = $this->flickr->auth_checkToken('75fce2f6341c71a67ca64f3142630a01', $result['token']);
					
					$this->flickr_active = true;
					$this->setPref('flickr_active', true);
					$this->setPref('flickr_username', $result2['user']['username']);
					$this->setPref('flickr_nsid', $result2['user']['nsid']);
					
					// $user = $this->flickr->get('account/verify_credentials');
					// $this->setPref('flickr_screen_name', $user->screen_name);
					
					$this->setPref('flickr_token', $result['token']);
					
					$this->savePref();
					
					$this->addNote('You successfully linked your Flickr account.', 'success');
					header('Location: ' . $this->location());
					exit();
					
					break;
			}
		}
		
		if(!empty($_GET['link'])){
			switch($_GET['link']){
				case 'flickr':
					$this->flickr->auth('write');
					break;
			}
		}
		
		if(!empty($_GET['unlink'])){
			switch($_GET['unlink']){
				case 'flickr':
					$this->flickr_active = false;
					$this->setPref('flickr_active', false);
					$this->setPref('flickr_screen_name', '');
					$this->setPref('flickr_oauth_token', '');
					$this->setPref('flickr_oauth_secret', '');
					$this->savePref();
					
					$this->addNote('You successfully unlinked your Flickr account.', 'success');
					header('Location: ' . $this->location());
					exit();
					
					break;
			}
		}
	}
	
	public function orbit_config_save(){
		$now = time();
		
		$this->setPref('flickr_last_image_time', $now);
		$this->setPref('flickr_format_image', @$_POST['flickr_format_image']);
		
		$this->savePref();
	}
	
	public function orbit_image($images){
		if(count($images) < 1){ return; }
		
		$now = time();
		
		if(!isset($image[0]['image_tags_array'])){
			$image_ids = array();
			
			foreach($images as $image){
				$image_ids[] = $image['image_id'];
			}
			
			$images = new Image($image_ids);
			$images->getTags();
			$images = $images->images;
		}
		
		foreach($images as $image){
			$image_published = strtotime($image['image_published']);
			
			if(empty($image_published)){ continue; }
			if($image_published > $now){ continue; }
			if($image_published <= $this->flickr_last_image_time){ continue; }
			if($image['image_privacy'] != 1){ continue; }
			
			$this->storeTask(array($this, 'upload'), $image);
		}
		
		$this->setPref('flickr_last_image_time', $now);
		$this->savePref();
	}
	
	public function upload($image){
		$canvas = new Canvas($this->flickr_format_image);
		$canvas->assignArray($image);
		$canvas->generate();
		
		$description = trim($canvas->template);
		
		$title = $image['image_title'];
		
		if(empty($title)){
			$title = '';
		}
		
		$tags = array();
		
		foreach($image['image_tags_array'] as $tag){
			$tags[] = '"' . str_replace('"', '\"', $tag) . '"';
		}
		
		$tags = implode(' ', $tags);
		
		$photo_id = $this->flickr->sync_upload($image['image_file'], $title, $description, $tags);
		
		if(!empty($image['image_taken'])){
			$this->flickr->photos_setDates($photo_id, null, $image['image_taken']);
		}
		
		if(!empty($image['image_geo_lat']) and !empty($image['image_geo_long'])){
			$this->flickr->photos_geo_setLocation($photo_id, $image['image_geo_lat'], $image['image_geo_long'], 11);
			echo $this->flickr->error_code;
		}
	}
}

?>