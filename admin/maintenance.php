<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalinenapp.com/
*/

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

<div id="progress">
</div>

<div id="tasks">
	<p>Maintenance tasks correct the behavior or improve of the performance of your Alkaline installation. You do not need to perform them if you are not experiencing any problems.<p>
	
	<h3>Files</h3>
		
	<ul class="tasks">
		<li>
			<strong><a href="#rebuild-thumbnails">Rebuild all photo thumbnails</a></strong><br />
			Individual thumbnail sizes can be rebuilt on <a href="<?php echo BASE . ADMIN . 'thumbnails' . URL_CAP; ?>">their respective pages</a>, necessary for fixing corrupt or missing image files
		</li>
		<li>
			<strong><a href="#delete-unused">Delete unclaimed photo thumbnails</a></strong><br />
			Delete stray thumbnails that are no longer part of your Alkaline installation, increasing available space
		</li>
		<li>
			<strong><a href="#delete-shoebox">Delete all files in Shoebox</a></strong><br />
			Delete all files from your Shoebox folder, increasing available space
		</li>
	</ul>
	
	<h3>Database</h3>
	
	<ul>
		<li>
			<strong><a href="#rebuild-piles">Rebuild pile catalog</a></strong><br />
			Reasses each pile&#8217;s contents using its original user-selected criteria, ensuring each pile is up-to-date
		</li>
		<li>
			<strong><a href="#rebuild-geo">Rebuild geographic library</a></strong><br />
			Regenerates the built-in database of cities and nations, necessary for fixing corrupt or missing locations
		</li>
		<li>
			<strong><a href="#delete-orphaned-tags">Delete orphaned tags</a></strong><br />
			Removes tags that are no longer linked to any photos, increasing available space
		</li>
		<li>
			<strong><a href="#reset-photo-markup">Reset photo markup</a></strong>
		</li>
		<li>
			<strong><a href="#reset-comment-markup">Reset comment markup</a></strong>
		</li>
	</ul>
</div>

<p>Please let the task complete before closing your browser window; you will automatically be redirected to your dashboard when the task is complete.</p>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>