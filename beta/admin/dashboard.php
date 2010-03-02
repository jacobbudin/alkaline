<?php

require_once('./../alkaline.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'user.php');

define('TITLE', 'Alkaline Dashboard');
define('COLUMNS', '19');
define('WIDTH', '750');

$user = new User;
$user->perm(true);

require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="content" class="span-<?php echo COLUMNS - 2; ?> prepend-1 append-1 last">
	<h2>Dashboard</h2>
	
	<div class="span-<?php echo COLUMNS - 2; ?> last">
		<div class="block">
			<h3>
				Statistics
			</h3>
		</div>
	</div>
	
	<div class="span-<?php echo ceil((COLUMNS/2)) - 2; ?> append-1">
		<h3><a href="shoebox/">Shoebox</a></h3>
		<p class="quiet">Add photos from your shoebox to your library.</p>
		
		<h3><a href="photos/">Photos</a></h3>
		<p class="quiet">Browse and edit the photos in your library.</p>
		
		<h3><a href="photos/">Rights</a></h3>
		<p class="quiet">Assign rights to your photos for sale or print.</p>
		
		<h3><a href="tags/">Tags</a></h3>
		<p class="quiet">View and edit tags associated with your library.</p>
		
		<h3><a href="piles/">Piles</a></h3>
		<p class="quiet">Create groups of photos using tags.</p>
		
		<h3><a href="narratives/">Narratives</a></h3>
		<p class="quiet">Compose pages and stories using your photos.</p>
	</div>
	<div class="span-<?php echo ceil((COLUMNS/2)) - 2; ?> last">
		<h3><a href="access/">Users &#0038; Guests</a></h3>
		<p class="quiet">Control access to your library.</p>
			
		<h3><a href="statistics/">Statistics</a></h3>
		<p class="quiet">View and track your library&#8217;s statistics.</p>
		
		<h3><a href="comments/">Comments</a></h3>
		<p class="quiet">Review and manage user comments.</p>
		
		<h3><a href="preferences/">Preferences</a></h3>
		<p class="quiet">Edit common preferences.</p>
		
		<h3><a href="themes/">Themes</a></h3>
		<p class="quiet">Manage the look and feel of your library.</p>
		
		<h3><a href="plugins/">Plug-ins</a></h3>
		<p class="quiet">Add, remove, and manage plug-ins.</p>
	</div>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>