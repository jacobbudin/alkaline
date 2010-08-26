<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

define('TAB', 'settings');
define('TITLE', 'Alkaline Maintenance');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<h1>Maintenance</h1>

<p>Please let the task complete before closing your browser window. You will be automatically redirected to your dashboard when the task is complete.</p>

<div id="progress">
</div>

<div id="tasks">
	<h3>Thumbnails</h3>
		
	<ul>
		<li><a href="#rebuild-all">Rebuild all photo thumbnails</a></li>
		<li><a href="#delete-unused">Delete unclaimed photo thumbnails</a></li>
	</ul>

	<h3>System</h3>

	<ul>
		<li><a href="#delete-shoebox">Delete all files in shoebox</a></li>
	</ul>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>