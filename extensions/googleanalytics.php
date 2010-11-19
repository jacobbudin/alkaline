<?php

class GoogleAnalytics extends Orbit{
	private $akismet_api_key;
	public $akismet_spam_caught = 0;
	public $akismet_blog_url;
	
	public function __construct(){
		parent::__construct();
		
		$this->ga_account_id = $this->returnPref('ga_account_id');
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function body_close(){
		if(!empty($this->ga_account_id)){
			?>
			<script type="text/javascript">

			  var _gaq = _gaq || [];
			  _gaq.push(['_setAccount', '<?php echo $this->ga_account_id; ?>']);
			  _gaq.push(['_trackPageview']);

			  (function() {
			    var ga = document.createElement('script');
			    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 
			        'http://www') + '.google-analytics.com/ga.js';
			    ga.setAttribute('async', 'true');
			    document.documentElement.firstChild.appendChild(ga);
			  })();

			</script>
			<?php
		}
	}
	
	public function config(){
		?>
		<p>To use this extension you will need a Google Analytics account. If you do not have one, <a href="http://www.google.com/analytics/sign_up.html">get one for free</a>. This extension does not affect Alkaline&#8217;s built-in statistics module.</p>
		
		<p>For more information on Google Analytics, visit <a href="http://www.google.com/analytics/">Google Analytics&#8217;s Web site</a>.</p>

		<table>
			<tr>
				<td class="right" style="padding-top: 1em;">Account Identifier (UA ID):</td>
				<td>
					<input type="text" name="ga_account_id" value="<?php echo $this->ga_account_id; ?>" /><br />
					<span class="quiet">Looks like: UA-12345678-1</span>
				</td>
			</tr>
		</table>
		<?php
	}
	
	public function config_save(){
		if(isset($_POST['ga_account_id'])){
			$this->setPref('ga_account_id', $_POST['ga_account_id']);
			$this->savePref();
		}
	}
}

?>