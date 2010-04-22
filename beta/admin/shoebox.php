<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'user.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$photos = $alkaline->seekPhotos(PATH . SHOEBOX);

$photo_count = count($photos);

if($photo_count == 1){ $photo_count_read = 'There is 1 photo in your shoebox. Please wait while it is  processed&#8230;'; }
elseif($photo_count > 1){ $photo_count_read = 'There are ' . $photo_count . ' photos in your shoebox. Please wait while they are processed&#8230;'; }
else{
	$alkaline->addNotification('There are currently no photos in your shoebox.', 'notice');
	header('Location: http://' . DOMAIN . 'admin/dashboard/');
	exit();
}

define('TITLE', 'Alkaline Shoebox');
define('COLUMNS', '19');
define('WIDTH', '750');

require_once(PATH . ADMIN . 'includes/header.php');

?>
<div id="content" class="span-<?php echo COLUMNS - 2; ?> prepend-1 append-1 last">
	<h2>Shoebox</h2>
	
	<?php $alkaline->viewNotification(); ?>
	
	<p><?php echo $photo_count_read; ?></p>
	
	<?php
	
	if($photo_count > 0){
		foreach($photos as $photo){
			// new Import($photo);
		}
	}
	
	?>
</div>
<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>