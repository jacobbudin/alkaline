<?php

class AkismetHandler extends Orbit{
	private $akismet_api_key;
	public $akismet_spam_caught = 0;
	public $akismet_blog_url;
	
	public function __construct(){
		parent::__construct();
		
		$this->akismet_api_key = $this->returnPref('akismet_api_key');
		$this->akismet_spam_caught = $this->returnPref('akismet_spam_caught', 0);
		$this->akismet_blog_url = LOCATION . BASE;
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function comment_add($fields){
		$this->load('Akismet.php');
		
		if(empty($this->akismet_api_key)){ return; }
		
		$akismet = new Akismet($this->akismet_blog_url, $this->akismet_api_key);
		
		$akismet->setCommentAuthor($fields['comment_author_name']);
		$akismet->setCommentAuthorEmail($fields['comment_author_email']);
		$akismet->setCommentAuthorURL($fields['comment_author_url']);
		$akismet->setCommentContent($fields['comment_text']);

		if($akismet->isCommentSpam()){
			$fields['comment_status'] = -1;
			$this->setPref('akismet_spam_caught', ++$this->akismet_spam_caught);
			$this->savePref();
		}
		else{
			$fields['comment_status'] = 1;
		}
		
		return $fields;
	}
	
	public function config(){
		?>
		<p>To use this extension you will need an Akismet API key. If you do not have one, <a href="http://akismet.com/personal/">get one for free</a>.</p>
		
		<p>For more information on Akismet, visit <a href="http://akismet.com/">Akismet&#8217;s Web site</a>.</p>

		<table>
			<tr>
				<td class="right" style="padding-top: .75em;">Akismet API Key:</td>
				<td><input type="text" name="akismet_api_key" value="<?php echo $this->akismet_api_key; ?>" class="s" /></td>
			</tr>
			<tr>
				<td class="right">Lifetime spam count:</td>
				<td><?php echo $this->akismet_spam_caught; ?> comment<?php if($this->akismet_spam_caught != 1){ echo 's'; } ?></td>
			</tr>
		</table>
		<?php
	}
	
	public function config_save(){
		$this->setPref('akismet_api_key', $_POST['akismet_api_key']);
		$this->savePref();
	}
}

?>