<?php

class Twitter extends Orbit{
	public $twitter;
	
	private $oauth_token;
	private $oauth_secret;
	
	public function __construct(){
		parent::__construct('07a3f3d1b494c43417ff002ef659bebb687b75e4');
		
		$this->fetch('EpiCurl.php');
		$this->fetch('EpiOAuth.php');
		$this->fetch('EpiTwitter.php');
		
		$this->oauth_token = $this->readPref('oauth_token');
		$this->oauth_secret = $this->readPref('oauth_secret');
		
		if(!empty($this->oauth_token) and !empty($this->oauth_secret)){
			$this->twitter = new EpiTwitter('Ss0F1kxtvxkkmKGgvPx8w',
				't55gKYkDtn5uKo1enMyF1E00RwOec9aDzNo7TFhzZx4',
				$this->oauth_token,
				$this->oauth_secret);
		}
		else{
			$this->twitter = new EpiTwitter('Ss0F1kxtvxkkmKGgvPx8w',
				't55gKYkDtn5uKo1enMyF1E00RwOec9aDzNo7TFhzZx4');
		}
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function config(){
		$profile_image_url = $this->readPref('profile_image_url');
		$screen_name = $this->readPref('screen_name');
		
		if(empty($profile_image_url) or empty($screen_name)){
			$user = $this->twitter->get('/account/verify_credentials.json');
			$this->setPref('profile_image_url', $user->profile_image_url);
			$this->setPref('screen_name', $user->screen_name);
			$this->savePref();
		}
		
		$url = $this->twitter->getAuthorizeUrl(null, array('oauth_callback' => LOCATION . BASE . EXTENSIONS));
		
		?>
		<h4>Current Account</h4>
		
		<p>
			<img src="<?php echo $profile_image_url; ?>" alt="" height="48" width="48" /><br />
			<a href="http://twitter.com/<?php echo $screen_name; ?>/"><?php echo $screen_name; ?></a>
		</p>
		<br />
		
		<h4>Authorize</h4>
		<p><a href="<?php echo @$url; ?>">Add or change your linked Twitter account</p>
		<?php
	}
	
	public function photo_upload($photo_ids){
		$status = 'I just uploaded a photo.';
		$paramaters = array('status' => $status);
		// $this->twitter->post('/statuses/update.json', $paramaters);
	}
}

?>