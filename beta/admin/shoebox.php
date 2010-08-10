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

?>
<div id="module" class="container">
	<h1>Shoebox</h1>
	<p>You have <?php $alkaline->echoCount($photo_count, 'photo'); ?> in your shoebox. Please wait&#8230;</p><br />
</div>

<div id="shoebox" class="container">
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