<?php

class TextileHandler extends Orbit{
	public function __construct(){
		parent::__construct();
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	function markup_textile($page_text_raw){
		$this->load('classTextile.php');
		
		$textile = new Textile;
		$page_text = $textile->TextileThis($page_text_raw);
				
		return $page_text;
	}
	
	function page_markup_html(){
		echo '<option value="textile">Textile</option>';
	}
}

?>