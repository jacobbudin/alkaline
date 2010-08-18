<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

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

$photos = $alkaline->seekDirectory(PATH . SHOEBOX);
$photo_count = count($photos);

if(!($photo_count > 0)){
	$alkaline->addNotification('There are no photos in your shoebox.', 'notice');
	header('Location: ' . BASE . ADMIN . 'dashboard/');
	exit();
}

define('TITLE', 'Alkaline Shoebox');
require_once(PATH . ADMIN . 'includes/header.php');
require_once(PATH . ADMIN . 'includes/library.php');

?>

<h1>Shoebox (<?php echo $photo_count; ?>)</h1>

<form action="" method="post">
	<div id="shoebox_photos">
		
	</div>

	<p id="progress">
	
	</p>

	<p>
		<input id="shoebox_photo_ids" type="hidden" name="photo_ids" value="" />
		<input id="shoebox_add" type="submit" value="Add photos" />
	</p>
</form>
	
<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>