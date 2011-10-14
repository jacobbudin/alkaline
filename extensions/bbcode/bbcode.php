<?php

class BBCode extends Orbit{
	public function __construct(){
		parent::__construct();
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function orbit_markup_bbcode($page_text_raw){
		require_once('classes/BBCodeParser.php');
		
		$parser = new HTML_BBCodeParser();
		return $parser->qparse($page_text_raw);
		// return $page_text_raw;
	}
	
	public function orbit_config(){
		?>
		<p>For more information on BBCode, including its syntax, visit <a href=""></a>.</p>
		<?php
	}
	
	public function orbit_markup_html(){
		echo '<option value="bbcode">BBCode</option>';
	}
}

?>