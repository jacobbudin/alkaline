<?php

class Twitter extends Orbit{
	public $twitter;
	
	public $twitter_screen_name;
	
	private $twitter_oauth_token;
	private $twitter_oauth_secret;
	
	public function __construct(){
		parent::__construct();
		
		$this->twitter_active = $this->readPref('twitter_active');
		
		$this->load('twitteroauth.php');
		
		$this->twitter_screen_name = $this->readPref('twitter_screen_name');
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
		?>
		<p>Every time you publish a photo, your Twitter status will be updated with the photo&#8217;s title and a shortened link to its photo page.</p>
		<?php
		if($this->twitter_active){
			?>
			<table>
				<tr>
					<td class="right">Username:</td>
					<td><a href="http://twitter.com/<?php echo $this->twitter_screen_name; ?>/"><strong><?php echo $this->twitter_screen_name; ?></strong></a> &#0160; <a href="?unlink=twitter" class="nu"><span class="button">&#0150;</span>Unlink from Twitter</a></td>
				</tr>
			</table>

			<?php
		}
		else{
			?>
			<table>
				<tr>
					<td class="right">Username:</td>
					<td>
						<a href="?link=twitter" class="nu"><span class="button">&#0064;</span>Link to Twitter</a><br /><br />
						<span class="quiet">Note: Alkaline will be linked to whichever Twitter account you are currently logged into.</span>
					</td>
				</tr>
			</table>
			<?php
		}
	}
	
	public function config_load(){
		if(!empty($_GET['link'])){
			switch($_GET['link']){
				case 'tumblr':
					$tumblr_token = $this->tumblr->getRequestToken($this->location() . '?from=tumblr');
					$tumblr_authorize_url = $this->tumblr->getAuthorizeURL($tumblr_token['oauth_token']);
					
					$this->setPref('tumblr_oauth_token', $tumblr_token['oauth_token']);
					$this->setPref('tumblr_oauth_secret', $tumblr_token['oauth_token_secret']);
					$this->savePref();
					
					header('Location: ' . $tumblr_authorize_url);
					exit();
					
					break;
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
					
					header('Location: ' . $this->location());
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
					
					header('Location: ' . $this->location());
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