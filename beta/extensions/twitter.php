<?php

class Twitter extends Orbit{
	public $twitter;
	
	public function __construct(){
		parent::__construct('07a3f3d1b494c43417ff002ef659bebb687b75e4');
		
		$this->fetch('EpiCurl.php');
		$this->fetch('EpiOAuth.php');
		$this->fetch('EpiTwitter.php');
		
		$oauth_token = $this->readPref('oauth_token');
		$oauth_secret = $this->readPref('oauth_secret');
		$profile_image_url = $this->readPref('profile_image_url');
		$screen_name = $this->readPref('screen_name');
		
		$this->twitter = new EpiTwitter('Ss0F1kxtvxkkmKGgvPx8w',
			't55gKYkDtn5uKo1enMyF1E00RwOec9aDzNo7TFhzZx4',
			$oauth_token,
			$oauth_secret);
		
		if(empty($profile_image_url) or empty($screen_name)){
			$user = $this->twitter->get('/account/verify_credentials.json');
			$this->setPref('profile_image_url', $user->profile_image_url);
			$this->setPref('screen_name', $user->screen_name);
			$this->savePref();
		}
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	function photo_upload($photo_ids){
		$status = 'I just uploaded a photo.';
		$paramaters = array('status' => $status);
		// $this->twitter->post('/statuses/update.json', $paramaters);
	}
}

?>