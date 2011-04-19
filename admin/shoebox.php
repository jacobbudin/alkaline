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
$orbit = new Orbit;
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
			$image_description_raw = $alkaline->makeUnicode(@$_POST['image-' . $image_id . '-description-raw']);
			
			if($alkaline->returnConf('image_markup')){
				$image_markup_ext = $alkaline->returnConf('image_markup_ext');
				$image_description = $orbit->hook('markup_' . $image_markup_ext, $image_description_raw, $image_description);
			}
			else{
				$image_markup_ext = '';
				$image_description = $alkaline->nl2br($image_description_raw);
			}
			
			$fields = array('image_title' => $alkaline->makeUnicode(@$_POST['image-' . $image_id . '-title']),
				'image_description_raw' => $image_description_raw,
				'image_description' => $image_description,
				'image_geo' => $alkaline->makeUnicode(@$_POST['image-' . $image_id . '-geo']),
				'image_published' => @$_POST['image-' . $image_id . '-published'],
				'image_privacy' => @$_POST['image-' . $image_id . '-privacy'],
				'right_id' => @$_POST['right-' . $image_id . '-id']);
			$image->updateFields($fields);
			$image->updateTags(json_decode(@$_POST['image-' . $image_id . '-tags_input']));
		}
	}
	
	$alkaline->addNote('Your shoebox has been processed.', 'success');
	
	if($user->returnPref('shoe_to_bulk') === true){
		Find::clearMemory();

		$new_image_ids = new Find('images');
		$new_image_ids->_ids($image_ids);
		$new_image_ids->saveMemory();
		
		session_write_close();
		
		header('Location: ' . BASE . ADMIN . 'features' . URL_ACT . 'bulk' . URL_RW);
	}
	else{
		header('Location: ' . BASE . ADMIN . 'library' . URL_CAP);
	}
	exit();
}

// New posts
$files = $alkaline->seekDirectory(PATH . SHOEBOX, 'txt|mdown|md|markdown|textile');
$p_count = count($files);

foreach($files as $file){
	$post = new Post();
	$post->attachUser($user);
	$post->import($file);
}

// New images
$files = $alkaline->seekDirectory(PATH . SHOEBOX);
$i_count = count($files);

if(($i_count == 0) and ($p_count == 0)){
	$alkaline->addNote('There are no files in your shoebox.', 'error');
	header('Location: ' . BASE . ADMIN . 'upload' . URL_CAP);
	exit();
}
elseif(($i_count == 0) and ($p_count > 0)){
	$alkaline->addNote('You have successfully imported ' . $alkaline->returnFullCount($p_count, 'post') . '.', 'success');
	header('Location: ' . BASE . ADMIN . 'posts' . URL_CAP);
	exit();
}
else{
	$alkaline->addNote('You have also successfully imported ' . $alkaline->returnFullCount($p_count, 'post') . '.', 'success');
}

define('TAB', 'shoebox');
define('TITLE', 'Alkaline Shoebox');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/shoebox.png" alt="" /> Shoebox (<?php echo $i_count; ?>)</h1>

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
		<input id="shoebox_add" type="submit" value="Save changes" /> or <a href="<?php echo $alkaline->back(); ?>">cancel</a>
	</p>
</form>
	
<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>