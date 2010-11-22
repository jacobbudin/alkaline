<?php

class Twitter extends Orbit{
	public $twitter;
	
	public $twitter_screen_name;
	
	private $twitter_oauth_token;
	private $twitter_oauth_secret;
	
	public function __construct(){
		parent::__construct();
		
		$this->twitter_active = $this->returnPref('twitter_active');
		$this->twitter_format = $this->returnPref('twitter_format');
		$this->twitter_url_shortener = $this->returnPref('twitter_url_shortener');
		
		require_once('classes/twitteroauth.php');
		
		$this->twitter_screen_name = $this->returnPref('twitter_screen_name');
		$this->twitter_oauth_token = $this->returnPref('twitter_oauth_token');
		$this->twitter_oauth_secret = $this->returnPref('twitter_oauth_secret'); 
		
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
		<p>Every time you publish a photo, your <a href="http://www.twitter.com/">Twitter</a> status will be updated. (If you publish multiple photos simultaneously, your status will only be updated once.)</p>
		<?php
		if($this->twitter_active){
			$this->twitter_format = $this->makeHTMLSafe($this->twitter_format);
			?>
			<table>
				<tr>
					<td class="right"><label>Username:</label></td>
					<td><a href="http://twitter.com/<?php echo $this->twitter_screen_name; ?>/"><?php echo $this->twitter_screen_name; ?></a> &#0160; <a href="<?php echo $this->locationFull('unlink=twitter'); ?>" class="button">Unlink from Twitter</a></td>
				</tr>
				<tr>
					<td class="right pad"><label for="twitter_format">Format:</label></td>
					<td>
						<input type="text" id="twitter_format" name="twitter_format" style="width: 30em;" value="<?php echo $this->twitter_format; ?>" /><br />
						<span class="quiet">Use the keywords %LINK, %PHOTO_TITLE, and %USER_NAME above.</span>
					</td>
				</tr>
				<tr>
					<td class="right pad"><label for="twitter_url_shortener">URL Shortener:</label></td>
					<td>
						<select id="twitter_url_shortener" name="twitter_url_shortener">
							<option value="">None</option>
							<option value="tinyurl" <?php echo $this->readPref('twitter_url_shortener', 'tinyurl'); ?>>TinyURL</option>
						</select>
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
						<a href="<?php echo $this->locationFull('link=twitter'); ?>" class="button">Link to Twitter</a><br /><br />
						<span class="quiet">Note: Alkaline will be linked to whichever Twitter account you are currently logged into.</span>
					</td>
				</tr>
			</table>
			<?php
		}
	}
	
	public function config_load(){
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
					
					$this->addNotification('You successfully linked your Twitter account.', 'success');
					header('Location: ' . $this->location());
					exit();
					
					break;
			}
		}
		
		if(!empty($_GET['link'])){
			switch($_GET['link']){
				case 'twitter':
					$twitter_token = $this->twitter->getRequestToken($this->locationFull('from=twitter'));
					$twitter_authorize_url = $this->twitter->getAuthorizeURL($twitter_token['oauth_token']);
					
					$this->setPref('twitter_oauth_token', $twitter_token['oauth_token']);
					$this->setPref('twitter_oauth_secret', $twitter_token['oauth_token_secret']);
					$this->savePref();
					
					header('Location: ' . $twitter_authorize_url);
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
					
					$this->addNotification('You successfully unlinked your Twitter account.', 'success');
					header('Location: ' . $this->location());
					exit();
					
					break;
			}
		}
	}
	
	public function config_save(){
		$this->setPref('twitter_format', @$_POST['twitter_format']);
		$this->setPref('twitter_url_shortener', @$_POST['twitter_url_shortener']);
		$this->savePref();
	}
	
	public function photo_publish($photo_ids){
		$status = 'I just uploaded a photo.';
		$paramaters = array('status' => $status);
		// $this->twitter->post('/statuses/update.json', $paramaters);
	}
}

?>