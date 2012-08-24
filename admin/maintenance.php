<?php

/*
// Alkaline
// Copyright (c) 2010-2012 by Budin Ltd. Some rights reserved.
// http://www.alkalineapp.com/
*/

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true, 'maintenance');

define('TAB', 'settings');
define('TITLE', 'Alkaline Maintenance');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/maintenance.png" alt="" /> Maintenance</h1>

<div id="progress">
</div>

<div id="tasks">
	<p>Maintenance tasks correct the behavior or improve of the performance of your Alkaline installation. You do not need to perform them if you are not experiencing any problems.<p>
		
	<p class="notice">Backup your Web site (and its database) before performing maintenance. Most of these actions cannot be undone.</p><br />
	
	<div class="span-24 last">
		<div class="span-10 append-2">
			<h3>Files</h3>
		
			<ul class="tasks">
				<li>
					<strong><a href="#rebuild-thumbnails">Rebuild all image thumbnails</a></strong><br />
					Individual thumbnail sizes can be rebuilt on <a href="<?php echo BASE . ADMIN . 'thumbnails' . URL_CAP; ?>">their respective pages</a>, necessary for fixing corrupt or missing image files (resource intensive)
				</li>
				<ul>
					<li>
						<strong>Rebuild thumbnail series</strong><br />
						<form action="<?php echo BASE . ADMIN . 'tasks/rebuild-thumbnail-series.php'; ?>" method="post">
							From image ID <input type="text" name="min" placeholder="0" style="width: 5em" /> to <input type="text" name="max" placeholder="100" style="width: 5em" />
							<input type="hidden" name="series" value="true" />
							<input type="submit" value="Rebuild series" />
						</form>
					</li>
				</ul>
				<li>
					<strong><a href="#reorganize-library">Reorganize image library</a></strong><br />
					Move files, as necessary, according to the current <a href="<?php echo BASE . ADMIN . 'configuration' . URL_CAP; ?>">hierarchical directory mode</a> setting
				</li>
				<li>
					<strong><a href="#delete-unused-thumbnails">Delete unclaimed image thumbnails</a></strong><br />
					Delete stray thumbnails that are no longer part of your Alkaline installation, increasing available disk space
				</li>
				<li>
					<strong><a href="#build-items">Build items table</a></strong><br />
					For Sphinx users only, builds items table for document ID purposes
				</li>
				<li>
					<strong><a href="#delete-shoebox">Delete all files in shoebox</a></strong><br />
					Delete all files from your shoebox folder, removing troublesome files that could not be imported
				</li>
				<li>
					<strong><a href="#delete-cache">Delete cached files</a></strong><br />
					Delete cached files that may have become outdated
				</li>
				<li>
					<strong><a href="#delete-tasks">Delete cached tasks</a></strong><br />
					Delete unperformed tasks relating to installed extensions
				</li>
			</ul>
		</div>
		<div class="span-10 last">	
			<h3>Database</h3>
	
			<ul>
				<li>
					<strong><a href="#update-counts">Update counts</a></strong><br />
					Recount various count fields that may have become inaccurate&#8212;particularly if you manually edit the database tables
				</li>
				<li>
					<strong><a href="#rebuild-tags">Rebuild image tags</a></strong><br />
					Rebuild tag fields that may have gone astray
				</li>
				<li>
					<strong><a href="#rebuild-sets">Rebuild set catalog</a></strong><br />
					Reassess each set&#8217;s contents using its original user-selected criteria, ensuring each set is up-to-date
				</li>
				<li>
					<strong><a href="#rebuild-geo">Rebuild geographic library</a></strong><br />
					Regenerates the built-in database of cities and nations, necessary for fixing corrupt or missing locations
				</li>
				<li>
					<strong><a href="#delete-orphaned-tags">Delete orphaned tags</a></strong><br />
					Removes tags that are no longer linked to any images, increasing available space
				</li>
				<li>
					<strong><a href="#delete-old-versions">Delete old versions</a></strong><br />
					Removes versions of minor changes older than 2 weeks, and versions of moderate changes older than 6 months
				</li>
				<li>
					<strong><a href="#reset-markup">Reset markup</a></strong><br />
					Resets markup on all images, posts, sets, etc. where not equal to the current markup language (not recommended)
				</li>
			</ul>
		</div>
	</div>
</div>

<p>Please let the task complete before closing your browser window; you will automatically be redirected to your dashboard when the task is complete.</p>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>