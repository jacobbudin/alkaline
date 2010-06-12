<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'orbit.php');
require_once(PATH . CLASSES . 'user.php');

$alkaline = new Alkaline;
$orbit = new Orbit;
$user = new User;

$user->perm(true);

define('TITLE', 'Alkaline Extensions');
define('COLUMNS', '19');
define('WIDTH', '750');

require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="content" class="span-<?php echo COLUMNS - 2; ?> prepend-1 append-1 last">
	<h2>Extensions</h2>
	
	<?php $alkaline->viewNotification(); ?>
	
	<h3>Available <span class="small quiet">(<span id="count"><?php echo @$orbit->extension_count; ?></span>)</span></h3>
	
	<hr />
	
	<?php
	
	foreach($orbit->extensions as $extension){
		echo '<p>' . $extension['extension_title'] . '</p>';
	}
	
	?>
	
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>