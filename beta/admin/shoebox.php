<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'photo.php');
require_once(PATH . CLASSES . 'user.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

if(!empty($_POST['photo_ids'])){
	$photo_ids = explode(',', $_POST['photo_ids']);
	array_pop($photo_ids);
	$alkaline->convertToIntegerArray($photo_ids);
	foreach($photo_ids as $photo_id){
		$photo = new Photo($photo_id);
		$fields = array('photo_title' => $_POST['photo-' . $photo_id . '-title'],
			'photo_description' => $_POST['photo-' . $photo_id . '-description']);
		$photo->updateFields($fields);
	}
	$alkaline->addNotification('Your shoebox has been successfully processed.', 'success');
	header('Location: ' . BASE . ADMIN . 'dashboard/');
	exit();
}

$photos = $alkaline->seekPhotos(PATH . SHOEBOX);

$photo_count = count($photos);

if($photo_count == 1){ $photo_count_read = 'There is 1 photo in your shoebox. Please wait while it is  processed&#8230;'; }
elseif($photo_count > 1){ $photo_count_read = 'There are ' . $photo_count . ' photos in your shoebox. Please wait while they are processed&#8230;'; }
else{
	$alkaline->addNotification('There are currently no photos in your shoebox.', 'notice');
	header('Location: ' . BASE . ADMIN . 'dashboard/');
	exit();
}

define('TITLE', 'Alkaline Shoebox');
define('COLUMNS', '19');
define('WIDTH', '750');

$alkaline->injectJS('shoebox');

require_once(PATH . ADMIN . 'includes/header.php');

?>
<div id="content" class="span-<?php echo COLUMNS - 2; ?> prepend-1 append-1 last">
	<h2>Shoebox</h2>
	
	<?php $alkaline->viewNotification(); ?>
	
	<p><?php echo @$photo_count_read; ?></p>
	
	<form action="" method="post">
		<div id="photos">
			
		</div>
	
		<div id="progress" class="span-17 last">
		
		</div>
	
		<p class="center">
			<br />
			<input id="photo_ids" type="hidden" name="photo_ids" value="" />
			<input id="add" type="submit" value="Add photos" />
		</p>
	</form>
	
</div>
<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>