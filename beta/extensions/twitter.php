<?php

class Twitter extends Orbit{
	public $twitter;
	
	private $oauth_token;
	private $oauth_secret;
	
	public function __construct(){
		parent::__construct('07a3f3d1b494c43417ff002ef659bebb687b75e4');
		
		$this->fetch('twitteroauth.php');
		
		$this->oauth_token = $this->readPref('oauth_token');
		$this->oauth_secret = $this->readPref('oauth_secret'); 
		
		if(!empty($this->oauth_token) and !empty($this->oauth_secret)){
			$this->twitter = new TwitterOAuth('Ss0F1kxtvxkkmKGgvPx8w',
				't55gKYkDtn5uKo1enMyF1E00RwOec9aDzNo7TFhzZx4',
				$this->oauth_token,
				$this->oauth_secret);
		}
		else{
			$this->twitter = new TwitterOAuth('Ss0F1kxtvxkkmKGgvPx8w',
				't55gKYkDtn5uKo1enMyF1E00RwOec9aDzNo7TFhzZx4');
		}
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function config(){
		$profile_image_url = $this->readPref('profile_image_url');
		$screen_name = $this->readPref('screen_name');
		
		if(@$_GET['new'] == 1){
			$access_token = $this->twitter->getAccessToken($_GET['oauth_verifier']);
			var_dump($access_token);
			$this->setPref('oauth_token', $access_token['oauth_token']);
			$this->setPref('oauth_secret', $access_token['oauth_token_secret']);
			$this->savePref();
			echo 'a:' . $access_token['oauth_token'];
			echo 'b:' . $access_token['oauth_token_secret'];
			echo 'SUCCESS!';
		}
		
		if(empty($profile_image_url) or empty($screen_name)){
			$user = $this->twitter->get('/account/verify_credentials.json');
			$this->setPref('profile_image_url', $user->profile_image_url);
			$this->setPref('screen_name', $user->screen_name);
			$this->savePref();
		}
		
		
		$token = $this->twitter->getRequestToken(LOCATION . $_SERVER['REQUEST_URI'] . '&new=1');
		$url = $this->twitter->getAuthorizeURL($token['oauth_token']);
		
		$this->setPref('oauth_token', $token['oauth_token']);
		$this->setPref('oauth_secret', $token['oauth_token_secret']);
		$this->savePref();
		
		?>
		<h4>Current Account</h4>
		
		<p>
			<img src="<?php echo $profile_image_url; ?>" alt="" height="48" width="48" /><br />
			<a href="http://twitter.com/<?php echo $screen_name; ?>/"><?php echo $screen_name; ?></a>
		</p>
		<br />
		
		<h4>Authorize</h4>
		<p>Add or change your linked Twitter account:</p>
		<a href="<?php echo $url; ?>"><img src="<?php echo BASE . EXTENSIONS; ?>twitter/images/signin.png" alt="Sign in with Twitter"/></a><br />
		<?php
	}
	
	public function photo_upload($photo_ids){
		$status = 'I just uploaded a photo.';
		$paramaters = array('status' => $status);
		// $this->twitter->post('/statuses/update.json', $paramaters);
	}
}

?>