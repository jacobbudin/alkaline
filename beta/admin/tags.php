<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$tag_id = $alkaline->findID(@$_GET['tag_id']);

if(!empty($tag_id)){
	header('Location: ' . LOCATION . BASE . ADMIN . 'search/tags/' . $tag_id);
	exit();
}

$tags = $alkaline->showTags(true);

define('TITLE', 'Alkaline Tags');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="module" class="container">
	<h1>Tags</h1>
	<p>Your library contains <?php echo @$alkaline->tag_count; ?> unique tags.</p>
</div>

<div id="tags" class="container center append-bottom">
	<?php echo @$tags; ?>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>