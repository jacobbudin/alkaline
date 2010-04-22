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

require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="content" class="span-<?php echo COLUMNS - 2; ?> prepend-1 append-1 last">
	<h2>Maintenance</h2>
	
	<?php $alkaline->viewNotification(); ?>
	
	<p>Maintenance tasks repair or correct your library. You do not need to execute any of these tasks if you are not experiencing problems. Please let the task complete before closing your browser window. You will be redirected to your dashboard when the task is complete.</p>
	
	<div id="progress">
		
	</div>
	
	<div id="tasks">
		<h4>Thumbnails</h4>
		<ul>
			<li><a class="task" href="#">Rebuild all photo thumbnails</a></li>
			<li><a class="task" href="#">Rebuild only absent photo thumbnails</a></li>
		</ul><br />
	
		<h4>Statistics</h4>
		<ul>
			<li><a class="task" href="#">Recalulate library tallies</a></li>
			<li><a class="task" href="#">Reset library statistics</a></li>
		</ul>
	</div>
	
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>