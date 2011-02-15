<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalineapp.com/
*/

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true, 'shoebox');

// PROCESS SUBMITTED IMAGES
if(!empty($_POST['image_ids'])){
	$image_ids = explode(',', $_POST['image_ids']);
	array_pop($image_ids);
	
	$alkaline->convertToIntegerArray($image_ids);
	
	foreach($image_ids as $image_id){
		$image = new Image($image_id);
		if(@$_POST['image-' . $image_id . '-delete'] == 'delete'){
			if($image->delete()){
				$alkaline->addNote('Your image has been deleted.', 'success');
			}

		}
		else{
			$fields = array('image_title' => $alkaline->makeUnicode(@$_POST['image-' . $image_id . '-title']),
				'image_description' => $alkaline->makeUnicode(@$_POST['image-' . $image_id . '-description']),
				'image_geo' => $alkaline->makeUnicode(@$_POST['image-' . $image_id . '-geo']),
				'image_published' => @$_POST['image-' . $image_id . '-published'],
				'image_privacy' => @$_POST['image-' . $image_id . '-privacy'],
				'right_id' => @$_POST['image-' . $image_id . '-id']);
			$image->updateFields($fields);
			$image->updateTags(json_decode(@$_POST['image-' . $image_id . '-tags_input']));
		}
	}
	
	$alkaline->addNote('Your shoebox has been processed.', 'success');
	
	header('Location: ' . BASE . ADMIN . 'library' . URL_CAP);
	exit();
}

// DETERMINE IF IMAGES IN SHOEBOX
$images = $alkaline->seekDirectory(PATH . SHOEBOX);
$image_count = count($images);

if(!($image_count > 0)){
	$alkaline->addNote('There are no images in your shoebox.', 'notice');
	header('Location: ' . BASE . ADMIN . 'library' . URL_CAP);
	exit();
}

define('TAB', 'library');
define('TITLE', 'Alkaline Shoebox');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<h1>Shoebox (<?php echo $image_count; ?>)</h1>

<form action="" method="post">
	<div id="privacy_html" class="none">
		<?php echo $alkaline->showPrivacy('image--privacy'); ?>
	</div>
	
	<div id="rights_html" class="none">
		<?php echo $alkaline->showRights('right--id'); ?>
	</div>
	
	<div id="shoebox_images">
		
	</div>

	<p id="progress">
	
	</p>

	<p>
		<input id="shoebox_image_ids" type="hidden" name="image_ids" value="" />
		<input id="shoebox_add" type="submit" value="Add images" />
	</p>
</form>
	
<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>