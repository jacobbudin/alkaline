<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$alkaline->injectJS('maintenance');

define('TITLE', 'Alkaline Maintenance');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="module" class="container">
	<h1>Maintenance</h1>
	<p></p>
</div>

<div id="maintenance" class="container">
	<p>Please let the task complete before closing your browser window. You will be automatically redirected to your dashboard when the task is complete.</p>
	
	<div id="progress" class="span-17 last">
		
	</div>
	
	<h3>Thumbnails</h3>
	
	<ul id="tasks">
		<li><a href="#rebuild-all">Rebuild all photo thumbnails</a></li>
		<li><a href="#delete-unused">Delete unused photo thumnails</a></li>
	</ul>
	
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>