<?php

class SocialSync extends Orbit{
	public $twitter;
	
	private $twitter_oauth_token;
	private $twitter_oauth_secret;
	
	public function __construct(){
		parent::__construct();
		
		$this->load('twitteroauth.php');
		
		$this->twitter_active = $this->readPref('twitter_active');
		$this->facebook_active = $this->readPref('facebook_active');
		$this->tumblr_active = $this->readPref('tumblr_active');
		
		$this->twitter_oauth_token = $this->readPref('twitter_oauth_token');
		$this->twitter_oauth_secret = $this->readPref('twitter_oauth_secret'); 
		
		if(!empty($this->twitter_oauth_token) and !empty($this->twitter_oauth_secret)){
			$this->twitter = new TwitterOAuth('Ss0F1kxtvxkkmKGgvPx8w',
				't55gKYkDtn5uKo1enMyF1E00RwOec9aDzNo7TFhzZx4',
				$this->twitter_oauth_token,
				$this->twitter_oauth_secret);
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
		if(!empty($_GET['unlink'])){
			switch($_GET['unlink']){
				case 'twitter':
					$this->twitter_active = false;
					$this->setPref('twitter_active', false);
					$this->setPref('twitter_screen_name', '');
					$this->setPref('twitter_oauth_token', '');
					$this->setPref('twitter_oauth_secret', '');
					$this->savePref();
					break;
			}
		}
		
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
					break;
			}
		}
		
		$twitter_screen_name = $this->readPref('twitter_screen_name');
		
		?>
		
		<div class="span-23 last append-bottom">
			<div class="span-7 append-1">
				<img src="<?php echo BASE . EXTENSIONS; ?>socialsync/images/twitter.png" title="Twitter" />
		
				<p>Your Twitter status will be updated with the title and link of your last-uploaded photo.</p>
				
				<?php
				
				if($this->twitter_active){
					?>
					<p class="center"><strong><a href="http://twitter.com/<?php echo $twitter_screen_name; ?>/" class="wrapper"><?php echo $twitter_screen_name; ?></a></strong></p>
					<p class="center"><a href="?unlink=twitter" class="nu"><span class="button">&#0150;</span>Unlink from Twitter</a></p>
					<?php
				}
				else{
					?>
					<p class="center"><a href="?link=twitter" class="nu"><span class="button">&#0064;</span>Link to Twitter</a></p>
					<?php
				}
				?>
			</div>
			<div class="span-7 append-1">
				<img src="<?php echo BASE . EXTENSIONS; ?>socialsync/images/facebook.png" title="Facebook" />
		
				<p>Your Facebook status will be updated with the title and link of your last-uploaded photo.</p>

				<?php
				if($this->facebook_active){
					?>
					<p class="center"><strong><a href="http://facebook.com/<?php echo $facebook_screen_name; ?>/" class="wrapper"><?php echo $facebook_screen_name; ?></a></strong></p>
					<p class="center"><a href="?unlink=facebook" class="nu"><span class="button">&#0150;</span>Unlink from Facebook</a></p>
					<?php
				}
				else{
					?>
					<p class="center"><a href="?link=facebook" class="nu"><span class="button">&#0064;</span>Link to Facebook</a></p>
					<?php
				}
				?>
			</div>
			<div class="span-7 last">
				<img src="<?php echo BASE . EXTENSIONS; ?>socialsync/images/tumblr.png" title="Tumblr" />
		
				<p>Your Tumblog will be updated with an image of your last-uploaded photo.</p>

				<?php
				if($this->tumblr_active){
					?>
					<p class="center"><strong><a href="" class="wrapper"><?php echo $tumblr_screen_name; ?></a></strong></p>
					<p class="center"><a href="?unlink=tumblr" class="nu"><span class="button">&#0150;</span>Unlink from Tumblr</a></p>
					<?php
				}
				else{
					?>
					<p class="center"><a href="?link=tumblr" class="nu"><span class="button">&#0064;</span>Link to Tumblr</a></p>
					<?php
				}
				?>
			</div>
		</div>
		
		<p class="quiet">
			Icons by <a href="http://komodomedia.com/">Komodo Media, Rogie King</a>.
		</p>
		
		<?php
	}
	
	public function config_load(){
		if(!empty($_GET['link'])){
			switch($_GET['link']){
				case 'twitter':
					$twitter_token = $this->twitter->getRequestToken($this->location() . '?from=twitter');
					$twitter_authorize_url = $this->twitter->getAuthorizeURL($twitter_token['oauth_token']);
					
					$this->setPref('twitter_oauth_token', $twitter_token['oauth_token']);
					$this->setPref('twitter_oauth_secret', $twitter_token['oauth_token_secret']);
					$this->savePref();
					
					header('Location: ' . $twitter_authorize_url);
					exit();
					
					break;
			}
		}
	}
	
	public function photo_upload($photo_ids){
		$status = 'I just uploaded a photo.';
		$paramaters = array('status' => $status);
		// $this->twitter->post('/statuses/update.json', $paramaters);
	}
}

?>