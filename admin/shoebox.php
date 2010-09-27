<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

// PROCESS SUBMITTED PHOTOS
if(!empty($_POST['photo_ids'])){
	$photo_ids = explode(',', $_POST['photo_ids']);
	array_pop($photo_ids);
	
	$alkaline->convertToIntegerArray($photo_ids);
	
	foreach($photo_ids as $photo_id){
		$photo = new Photo($photo_id);
		if(@$_POST['photo-' . $photo_id . '-delete'] == 'delete'){
			if($photo->delete()){
				$alkaline->addNotification('Your photo has been deleted.', 'success');
			}

		}
		else{
			$fields = array('photo_title' => $alkaline->makeUnicode(@$_POST['photo-' . $photo_id . '-title']),
				'photo_description' => $alkaline->makeUnicode(@$_POST['photo-' . $photo_id . '-description']),
				'photo_geo' => $alkaline->makeUnicode(@$_POST['photo-' . $photo_id . '-geo']),
				'photo_published' => @$_POST['photo-' . $photo_id . '-published'],
				'photo_privacy' => @$_POST['photo-' . $photo_id . '-privacy'],
				'right_id' => @$_POST['photo-' . $photo_id . '-id']);
			$photo->updateFields($fields);
			$photo->updateTags(json_decode(@$_POST['photo-' . $photo_id . '-tags_input']));
		}
	}
	
	$alkaline->addNotification('Your shoebox has been processed.', 'success');
	
	header('Location: ' . BASE . ADMIN . 'library/');
	exit();
}

// DETERMINE IF PHOTOS IN SHOEBOX
$photos = $alkaline->seekDirectory(PATH . SHOEBOX);
$photo_count = count($photos);

if(!($photo_count > 0)){
	$alkaline->addNotification('There are no photos in your shoebox.', 'notice');
	header('Location: ' . BASE . ADMIN . 'library/');
	exit();
}

define('TAB', 'library');
define('TITLE', 'Alkaline Shoebox');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<h1>Shoebox (<?php echo $photo_count; ?>)</h1>

<form action="" method="post">
	<div id="privacy_html" class="none">
		<?php echo $alkaline->showPrivacy('photo--privacy'); ?>
	</div>
	
	<div id="rights_html" class="none">
		<?php echo $alkaline->showRights('right--id'); ?>
	</div>
	
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