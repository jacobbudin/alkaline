<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'user.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

define('TITLE', 'Alkaline Tags');
define('COLUMNS', '19');
define('WIDTH', '750');

require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="content" class="span-<?php echo COLUMNS - 2; ?> prepend-1 append-1 last">
	<h2>Tags</h2>
	
	<?php $alkaline->viewNotification(); ?>
	
	<h3>Search</h3>

	<form id="search" method="get">
		<input type="text" name="search" style="width: 30%; font-size: .9em; margin-left: 0;" /> <input type="submit" value="Search" />
	</form><br />
	
	<h3>Cloud <span class="small quiet">(<span id="count"><?php echo @$tags_count; ?></span>)</span></h3>
	
	<hr />
	
	<p class="center">
		
	</p>
	
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>