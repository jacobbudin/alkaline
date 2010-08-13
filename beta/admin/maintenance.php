<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

define('TITLE', 'Alkaline Maintenance');
require_once(PATH . ADMIN . 'includes/header.php');
require_once(PATH . ADMIN . 'includes/settings.php');

?>

<h1>Maintenance</h1>

<p>Please let the task complete before closing your browser window. You will be automatically redirected to your dashboard when the task is complete.</p>
	
<div id="progress" class="span-17 last">
	
</div>

<h3>Thumbnails</h3>
		
<ul id="tasks">
	<li><a href="#rebuild-all">Rebuild all photo thumbnails</a></li>
	<li><a href="#delete-unused">Delete unclaimed photo thumbnails</a></li>
</ul>

<h3>System</h3>

<ul id="tasks">
	<li><a href="#delete-shoebox">Delete all files in shoebox</a></li>
</ul>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>