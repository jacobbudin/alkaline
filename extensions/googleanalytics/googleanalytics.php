<?php

class GoogleAnalytics extends Orbit{
	private $ga_account_id;
	
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
		<p>To use this extension you will need a <a href="http://www.google.com/analytics/sign_up.html">Google Analytics account</a>. For more information on Google Analytics, visit <a href="http://www.google.com/analytics/">Google Analytics&#8217;s Web site</a>.</p>
		
		<p>This extension does not affect Alkaline&#8217;s built-in statistics module.</p>

		<table>
			<tr>
				<td class="right pad"><label for="ga_account_id">Account identifier (UA ID):</label></td>
				<td>
					<input type="text" id="ga_account_id" name="ga_account_id" value="<?php echo $this->ga_account_id; ?>" class="s" /><br />
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