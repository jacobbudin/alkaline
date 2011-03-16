<?php

class Markdown extends Orbit{
	public function __construct(){
		parent::__construct();
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function orbit_markup_markdown($page_text_raw){
		require_once('functions/markdown.php');
		require_once('functions/smartypants.php');
		
		$page_text = $page_text_raw;
		
		// Markdown
		$parser = new Markdown_Parser;
		$page_text = $parser->transform($page_text);
		
		// SmartyPants
		$parser = new Markdown_SmartyPants;
		$page_text = $parser->transform($page_text);
		
		return $page_text;
	}
	
	public function orbit_config(){
		?>
		<p>For more information on Markdown, including its syntax, visit <a href="http://daringfireball.net/projects/markdown/">John Gruber&#8217;s Markdown page</a>.</p>
		<?php
	}
	
	public function orbit_markup_html(){
		echo '<option value="markdown">Markdown</option>';
	}
}

?>