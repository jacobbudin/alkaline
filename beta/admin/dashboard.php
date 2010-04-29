<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'user.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

define('TITLE', 'Alkaline Dashboard');
define('COLUMNS', '19');
define('WIDTH', '750');

require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="content" class="span-<?php echo COLUMNS - 2; ?> prepend-1 append-1 last">
	<h2>Dashboard</h2>
	
	<?php $alkaline->viewNotification(); ?>
	
	<div class="span-<?php echo COLUMNS - 2; ?> last">
		<div class="block">
			<h3>Overview</h3>
		</div>
	</div>
	
	<div class="span-<?php echo ceil((COLUMNS/2)) - 2; ?> append-1">
		<div class="span-1">
			<img src="/images/icons/shoebox.png" alt="" />
		</div>
		<div class="span-7 last">
			<h3><a href="<?php echo BASE . ADMIN ?>shoebox/">Shoebox</a></h3>
			<p class="quiet">Add photos from your shoebox to your library.</p>
		</div>

		<div class="span-1">
			<img src="/images/icons/photos.png" alt="" />
		</div>
		<div class="span-7 last">
			<h3><a href="<?php echo BASE . ADMIN ?>photos/">Photos</a></h3>
			<p class="quiet">Browse and edit the photos in your library.</p>
		</div>

		<div class="span-1">
			<img src="/images/icons/rights.png" alt="" />
		</div>
		<div class="span-7 last">
			<h3><a href="<?php echo BASE . ADMIN ?>photos/">Rights</a></h3>
			<p class="quiet">Assign rights to your photos for sale or print.</p>
		</div>

		<div class="span-1">
			<img src="/images/icons/tags.png" alt="" />
		</div>
		<div class="span-7 last">
			<h3><a href="<?php echo BASE . ADMIN ?>tags/">Tags</a></h3>
			<p class="quiet">View and edit tags associated with your library.</p>
		</div>

		<div class="span-1">
			<img src="/images/icons/piles.png" alt="" />
		</div>
		<div class="span-7 last">
			<h3><a href="<?php echo BASE . ADMIN ?>piles/">Piles</a></h3>
			<p class="quiet">Create groups of photos using tags.</p>
		</div>

		<div class="span-1">
			<img src="/images/icons/narratives.png" alt="" />
		</div>
		<div class="span-7 last">
			<h3><a href="<?php echo BASE . ADMIN ?>narratives/">Narratives</a></h3>
			<p class="quiet">Compose pages and stories using your photos.</p>
		</div>
	</div>
	<div class="span-<?php echo ceil((COLUMNS/2)) - 2; ?> last">
		<div class="span-1">
			<img src="/images/icons/access.png" alt="" />
		</div>
		<div class="span-7 last">
			<h3><a href="<?php echo BASE . ADMIN ?>access/">Users &#0038; Guests</a></h3>
			<p class="quiet">Control access to your library.</p>
		</div>

		<div class="span-1">
			<img src="/images/icons/statistics.png" alt="" />
		</div>
		<div class="span-7 last">
			<h3><a href="<?php echo BASE . ADMIN ?>statistics/">Statistics</a></h3>
			<p class="quiet">View and track your library&#8217;s statistics.</p>
		</div>

		<div class="span-1">
			<img src="/images/icons/comments.png" alt="" />
		</div>
		<div class="span-7 last">
			<h3><a href="<?php echo BASE . ADMIN ?>comments/">Comments</a></h3>
			<p class="quiet">Review and manage user comments.</p>
		</div>

		<div class="span-1">
			<img src="/images/icons/preferences.png" alt="" />
		</div>
		<div class="span-7 last">
			<h3><a href="<?php echo BASE . ADMIN ?>preferences/">Preferences</a></h3>
			<p class="quiet">Edit common preferences.</p>
		</div>

		<div class="span-1">
			<img src="/images/icons/themes.png" alt="" />
		</div>
		<div class="span-7 last">
			<h3><a href="<?php echo BASE . ADMIN ?>themes/">Themes</a></h3>
			<p class="quiet">Manage the look and feel of your library.</p>
		</div>

		<div class="span-1">
			<img src="/images/icons/extensions.png" alt="" />
		</div>
		<div class="span-7 last">
			<h3><a href="<?php echo BASE . ADMIN ?>extensions/">Extensions</a></h3>
			<p class="quiet">Add, remove, and manage extensions.</p>
		</div>
		
		<div class="span-1">
			<img src="/images/icons/maintenance.png" alt="" />
		</div>
		<div class="span-7 last">
			<h3><a href="<?php echo BASE . ADMIN ?>maintenance/">Maintenance</a></h3>
			<p class="quiet">Repair and correct your library.</p>
		</div>
	</div>
	
	<p><strong>Having problems with Alkaline?</strong> <a href="<?php echo BASE . ADMIN ?>diagnostics/">Send us a diagnostic report.</a></p>
	
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>