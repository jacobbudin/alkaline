<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'user.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$tags = $alkaline->showTags(true);

define('TITLE', 'Alkaline Tags');
define('COLUMNS', '19');
define('WIDTH', '750');

require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="tags" class="container">
	<form id="tags_find" method="get">
		<input type="text" name="search" /> <input type="submit" value="Find" />
	</form>
	
	<h2>Tags <span class="small quiet">(<span id="count"><?php echo @$alkaline->tag_count; ?></span>)</span></h2>
	
	<hr />
	
	<p class="center">
		<?php echo $tags; ?>
	</p>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>