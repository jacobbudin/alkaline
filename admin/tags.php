<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$tag_id = $alkaline->findID(@$_GET['tag_id']);

// Run search on specified tag
if(!empty($tag_id)){
	header('Location: ' . LOCATION . BASE . ADMIN . 'search' . URL_ACT . 'tags' . URL_AID . $tag_id);
	exit();
}

$tags = $alkaline->getTags();
$tag_count = count($tags);

define('TAB', 'features');
define('TITLE', 'Alkaline Tags');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<h1>Tags (<?php echo $tag_count; ?>)</h1>

<p id="tags" class="center">
	<?php
	
	$tags_html = array();
	
	foreach($tags as $tag){
		$tags_html[] = '<a href="' . BASE . ADMIN . 'search' . URL_ACT . 'tags' . URL_AID . $tag['id'] . '/" style="font-size: ' . $tag['size'] . 'em;">' . $tag['name'] . '</a></span> <span class="small quiet">(' . $tag['count'] . ')</span>';
	}
	
	echo implode($tags_html, ', ');
	
	?>
</p>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>