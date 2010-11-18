<?php

class TextileHandler extends Orbit{
	public function __construct(){
		parent::__construct();
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function markup_textile($page_text_raw){
		$this->load('classTextile.php');
		
		$textile = new Textile;
		$page_text = $textile->TextileThis($page_text_raw);
				
		return $page_text;
	}
	
	public function config(){
		?>
		<p>For more information on Textile, including its syntax, visit <a href="http://textile.thresholdstate.com/">Alex Shiels&#8217;s Textile page</a>.</p>
		<?php
	}
	
	public function markup_html(){
		echo '<option value="textile">Textile</option>';
	}
}

?>