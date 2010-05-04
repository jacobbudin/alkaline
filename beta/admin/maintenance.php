<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'user.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

define('TITLE', 'Alkaline Maintenance');
define('COLUMNS', '19');
define('WIDTH', '750');

$alkaline->injectJS('maintenance');

require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="content" class="span-<?php echo COLUMNS - 2; ?> prepend-1 append-1 last">
	<h2>Maintenance</h2>
	
	<?php $alkaline->viewNotification(); ?>
	
	<p>Maintenance tasks repair or correct your library. You do not need to execute any of these tasks if you are not experiencing problems. Please let the task complete before closing your browser window. You will be redirected to your dashboard when the task is complete.</p>
	
	<div id="progress">
		
	</div>
	
	<div id="tasks">
		<h4><a id="rebuild-all" class="task" href="#">Rebuild photo thumbnails</a></h4>
	</div>
	
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>