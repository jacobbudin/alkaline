<?php

class TextileHandler extends Orbit{
	public function __construct(){
		parent::__construct();
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function orbit_markup_textile($page_text_raw){
		require_once('classes/classTextile.php');
		
		$textile = new TextileHandler_Textile;
		$textile->Textile();
		$page_text = $textile->TextileThis($page_text_raw);
				
		return $page_text;
	}
	
	public function orbit_config(){
		?>
		<p>For more information on Textile, including its syntax, visit <a href="http://textile.thresholdstate.com/">Alex Shiels&#8217;s Textile page</a>.</p>
		<?php
	}
	
	public function orbit_markup_html(){
		echo '<option value="textile">Textile</option>';
	}
}

?>