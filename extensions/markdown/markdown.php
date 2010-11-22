<?php

class Markdown extends Orbit{
	public function __construct(){
		parent::__construct();
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function markup_markdown($page_text_raw){
		require_once('functions/markdown.php');
		require_once('functions/smartypants.php');
		
		$page_text = SmartyPants(Markdown($page_text_raw));
		
		return $page_text;
	}
	
	public function config(){
		?>
		<p>For more information on Markdown, including its syntax, visit <a href="http://daringfireball.net/projects/markdown/">John Gruber&#8217;s Markdown page</a>.</p>
		<?php
	}
	
	public function markup_html(){
		echo '<option value="markdown">Markdown</option>';
	}
}

?>