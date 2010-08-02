<?php

class Markdown extends Orbit{
	public function __construct(){
		parent::__construct('07a3f3d1b494c43417ff002ef659bebb687b75g1');
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	function markup_markdown($page_text_raw){
		$this->load('markdown.php');
		$this->load('smartypants.php');
		
		$page_text = SmartyPants(Markdown($page_text_raw));
		
		return $page_text;
	}
	
	function page_markup(){
		echo '<option value="markdown">Markdown</option>';
	}
}

?>