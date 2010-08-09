<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$tag_id = $alkaline->findID(@$_GET['tag_id']);

// Run search on specified tag
if(!empty($tag_id)){
	header('Location: ' . LOCATION . BASE . ADMIN . 'search/tags/' . $tag_id);
	exit();
}

$tags = $alkaline->getTags();
$tag_count = count($tags);

define('TITLE', 'Alkaline Tags');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="module" class="container">
	<h1>Tags</h1>
	<p>Your library contains <?php $alkaline->echoCount($tag_count, 'unique tag'); ?>.</p>
</div>

<div id="tags" class="container center append-bottom">
	<?php
	
	$tags_html = array();
	
	foreach($tags as $tag){
		$tags_html[] = '<a href="' . BASE . ADMIN . 'tags/' . $tag['id'] . '" style="font-size: ' . $tag['size'] . 'em;">' . $tag['name'] . '</a></span> <span class="small quiet">(<a href="' . BASE . ADMIN . 'search/tags/' . $tag['id'] . '">' . $tag['count'] . '</a>)</span>';
	}
	
	echo implode($tags_html, ', ');
	
	?>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>