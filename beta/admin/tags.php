<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$tags = $alkaline->showTags(true);

define('TITLE', 'Alkaline Tags');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="tags" class="container">
	
	<h2>Tags<?php echo ($alkaline->tag_count) ? ' <span id="tags_count" class="small quiet">(' . $alkaline->tag_count . ')</span>' : ''; ?></h2>
	
	<p>Add tags to your photos to easily find them later.</p>
	
	<hr />
	
	<p class="center">
		<?php echo $tags; ?>
	</p>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>