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

$user->perm(true);

if(!empty($_GET['id'])){
	$set_id = $alkaline->findID($_GET['id']);
}

if(!empty($_GET['act'])){
	$set_act = $_GET['act'];
}

// SAVE CHANGES
if(!empty($_POST['set_id'])){
	$set_id = $alkaline->findID($_POST['set_id']);
	if(@$_POST['set_delete'] == 'delete'){
		$alkaline->deleteRow('sets', $set_id);
	}
	else{
		$set_title = trim($_POST['set_title']);
		
		if(!empty($_POST['set_title_url'])){
			$set_title_url = $alkaline->makeURL($_POST['set_title_url']);
		}
		else{
			$set_title_url = $alkaline->makeURL($set_title);
		}
		
		$fields = array('set_title' => $alkaline->makeUnicode($set_title),
			'set_title_url' => $set_title_url,
			'set_type' => $_POST['set_type'],
			'set_description' => $alkaline->makeUnicode($_POST['set_description']));
		
		if(isset($_POST['set_images'])){
			$fields['set_images'] = $_POST['set_images'];
		}
		
		$alkaline->updateRow($fields, 'sets', $set_id);
	}
	unset($set_id);
}
else{
	$alkaline->deleteEmptyRow('sets', array('set_title'));
}

// CREATE PILE
if($set_act == 'build'){
	$set_call = Find::recentMemory();
	if(!empty($set_call)){
		$fields = array('set_call' => serialize($set_call),
			'set_type' => 'auto');
	}
	else{
		$fields = array('set_type' => 'static');
	}
	$set_id = $alkaline->addRow($fields, 'sets');
	
	$images = new Find;
	$images->sets($set_id);
	$images->find();
	
	$set_images = @implode(', ', $images->image_ids);
	$set_image_count = $images->image_count;
	
	$fields = array('set_images' => $set_images,
		'set_image_count' => $set_image_count);
	$alkaline->updateRow($fields, 'sets', $set_id);
}

define('TAB', 'features');

// GET PILES TO VIEW OR PILE TO EDIT
if(empty($set_id)){
	$sets = $alkaline->getTable('sets');
	$set_count = @count($sets);
	
	define('TITLE', 'Alkaline Sets');
	require_once(PATH . ADMIN . 'includes/header.php');
	
	?>
	
	<div class="actions"><a href="<?php echo BASE . ADMIN . 'sets' . URL_ACT . 'build' . URL_RW; ?>">Build static set</a></div>

	<h1>Sets (<?php echo $set_count; ?>)</h1>
	
	<p>Sets are collections of images. You can build an automatic set that updates itself by <a href="<?php echo BASE . ADMIN . 'library' . URL_CAP; ?>">performing a search</a>.</p>
	
	<p>
		<input type="search" name="filter" placeholder="Filter" class="s" results="0" />
	</p>
	
	<table class="filter">
		<tr>
			<th style="width: 60%;">Title</th>
			<th class="center">Views</th>
			<th class="center">Images</th>
			<th>Last modified</th>
		</tr>
		<?php
	
		foreach($sets as $set){
			echo '<tr>';
				echo '<td><strong><a href="' . BASE . ADMIN . 'sets' . URL_ID . $set['set_id'] . URL_RW . '">' . $set['set_title'] . '</a></strong><br /><a href="' . BASE . 'set' . URL_ID . $set['set_title_url'] . URL_RW . '" class="nu">/' . $set['set_title_url'] . '</td>';
				echo '<td class="center">' . $set['set_views'] . '</td>';
				echo '<td class="center"><a href="' . BASE . ADMIN . 'search/sets/' . $set['set_id'] . '">' . $set['set_image_count'] . '</a></td>';
				echo '<td>' . $alkaline->formatTime($set['set_modified']) . '</td>';
			echo '</tr>';
		}
	
		?>
	</table>

	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}
else{
	// Get set
	$set = $alkaline->getRow('sets', $set_id);
	$set = $alkaline->makeHTMLSafe($set);
	
	// Update set
	$image_ids = new Find;
	$image_ids->sets($set_id);
	$image_ids->find();
	
	if(!empty($set['set_title'])){	
		define('TITLE', 'Alkaline Set: &#8220;' . $set['set_title']  . '&#8221;');
	}
	else{
		define('TITLE', 'Alkaline Set');
	}
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<div class="actions"><a href="<?php echo BASE . ADMIN . 'search' . URL_ACT . 'sets' . URL_AID . $set['set_id'] . URL_RW; ?>">View images (<?php echo $image_ids->image_count; ?>)</a> <a href="<?php echo BASE . 'set' . URL_ID . $set['set_id'] . URL_RW; ?>">Go to set</a></div>
	
	<h1>Set</h1>
	
	<form id="set" action="<?php echo BASE . ADMIN; ?>sets<?php echo URL_CAP; ?>" method="post">
		<table>
			<tr>
				<td class="right middle"><label for="set_title">Title:</label></td>
				<td><input type="text" id="set_title" name="set_title" value="<?php echo $set['set_title']; ?>" class="title" /></td>
			</tr>
			<tr>
				<td class="right pad"><label for="set_title_url">Custom URL:</label></td>
				<td class="quiet">
					<input type="text" id="set_title_url" name="set_title_url" value="<?php echo $set['set_title_url']; ?>" style="width: 300px;" /><br />
					Optional. Use only letters, numbers, underscores, and hyphens.
				</td>
			</tr>
			<tr>
				<td class="right pad"><label for="set_description">Description:</label></td>
				<td><textarea id="set_description" name="set_description"><?php echo $set['set_description']; ?></textarea></td>
			</tr>
			<tr>
				<td class="right"><label for="set_type">Type:</label></td>
				<td>
					<input type="radio" name="set_type" id="set_type_auto" value="auto" <?php if($set['set_type'] != 'static'){ echo 'checked="checked"'; } if(empty($set['set_call'])){ echo 'disabled="disabled"'; } ?> /> <label for="set_type_auto">Automatic</label> &#8212; Automatically include new images that meet the set&#8217;s criteria<br />
					<input type="radio" name="set_type" id="set_type_static" value="static" <?php if($set['set_type'] == 'static'){ echo 'checked="checked"'; }  ?> /> <label for="set_type_static">Static</label> &#8212; Only include the images originally selected
				</td>
			</tr>
			<?php if($set['set_type'] == 'static'){ ?>
				<tr>
					<td class="right"><label>Sort:</label></td>
					<td>
						<p>
							<span class="switch">&#9656;</span> <a href="#" class="show">Show set</a> <span class="quiet">(sort images by dragging and dropping)</span>
						</p>

						<div class="reveal" id="set_image_sort">
							<?php
						
							$images = new Image($set['set_images']);
							$images->getImgUrl('square');
						
							foreach($images->images as $image){
								echo '<img src="' . $image['image_src_square'] .'" alt="" class="frame" id="image-' . $image['image_id'] . '" />';
							}
						
							?><br /><br />
						</div>
						<input type="hidden" id="set_images" name="set_images" value="<?php echo $set['set_images']; ?>" />
					</td>
				</tr>
			<?php } ?>
			<tr>
				<td class="right"><input type="checkbox" id="set_delete" name="set_delete" value="delete" /></td>
				<td><strong><label for="set_delete">Delete this set.</label></strong> This action cannot be undone.</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="hidden" name="set_id" value="<?php echo $set['set_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo $alkaline->back(); ?>">cancel</a></td>
			</tr>
		</table>
	</form>

	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}

?>