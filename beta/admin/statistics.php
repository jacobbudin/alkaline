<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'user.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

define('TITLE', 'Alkaline Statistics');
define('COLUMNS', '19');
define('WIDTH', '750');

require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="content" class="span-<?php echo COLUMNS - 2; ?> prepend-1 append-1 last">
	<h2>Statistics</h2>
	
	<?php $alkaline->viewNotification(); ?>
	
	<h3>Weekly</h3>
	
	<h3>Quarterly</h3>
	
	<h3>Historical</h3>
	
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>