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

require_once(PATH . ADMIN . 'includes/header.php');

?>
<div id="shoebox" class="container">
	<h2>Shoebox</h2>
	
	<p><?php echo $photo_count_read; ?></p><br />
	
	<h3>Imported <span class="small quiet">(<span id="shoebox_import_count"><?php echo $photo_count; ?></span>)</span></h3>
	
	<form action="" method="post">
		<div id="shoebox_photos">
			
		</div>
	
		<p id="shoebox_progress" class="span-23 last">
		
		</p>
	
		<p>
			<input id="shoebox_photo_ids" type="hidden" name="photo_ids" value="" />
			<input id="shoebox_add" type="submit" value="Add photos" />
		</p>
	</form>
	
</div>
<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>