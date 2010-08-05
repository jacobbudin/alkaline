<?php

class Markdown extends Orbit{
	public function __construct(){
		parent::__construct();
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function markup_markdown($page_text_raw){
		$this->load('markdown.php');
		$this->load('smartypants.php');
		
		$page_text = SmartyPants(Markdown($page_text_raw));
		
		return $page_text;
	}
	
	public function config(){
		?>
		For more information on Markdown, including its syntax, visit <a href="http://daringfireball.net/projects/markdown/">John Gruber&#8217;s Markdown page</a>.
		<?php
	}
	
	function page_markup_html(){
		echo '<option value="markdown">Markdown</option>';
	}
}

?>