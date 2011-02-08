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
	$pile_id = $alkaline->findID($_GET['id']);
}

if(!empty($_GET['act'])){
	$pile_act = $_GET['act'];
}

// SAVE CHANGES
if(!empty($_POST['pile_id'])){
	$pile_id = $alkaline->findID($_POST['pile_id']);
	if(@$_POST['pile_delete'] == 'delete'){
		$alkaline->deleteRow('piles', $pile_id);
	}
	else{
		$pile_title = trim($_POST['pile_title']);
		
		if(!empty($_POST['pile_title_url'])){
			$pile_title_url = $alkaline->makeURL($_POST['pile_title_url']);
		}
		else{
			$pile_title_url = $alkaline->makeURL($pile_title);
		}
		
		$fields = array('pile_title' => $alkaline->makeUnicode($pile_title),
			'pile_title_url' => $pile_title_url,
			'pile_type' => $_POST['pile_type'],
			'pile_description' => $alkaline->makeUnicode($_POST['pile_description']));
		
		if(isset($_POST['pile_images'])){
			$fields['pile_images'] = $_POST['pile_images'];
		}
		
		$alkaline->updateRow($fields, 'piles', $pile_id);
	}
	unset($pile_id);
}
else{
	$alkaline->deleteEmptyRow('piles', array('pile_title'));
}

// CREATE PILE
if($pile_act == 'build'){
	$pile_call = Find::recentMemory();
	if(!empty($pile_call)){
		$fields = array('pile_call' => $pile_call,
			'pile_type' => 'auto');
	}
	else{
		$fields = array('pile_type' => 'static');
	}
	$pile_id = $alkaline->addRow($fields, 'piles');
	
	$images = new Find;
	$images->pile($pile_id);
	$images->find();
	
	$pile_images = @implode(', ', $images->image_ids);
	$pile_image_count = $images->image_count;
	
	$fields = array('pile_images' => $pile_images,
		'pile_image_count' => $pile_image_count);
	$alkaline->updateRow($fields, 'piles', $pile_id);
}

define('TAB', 'features');

// GET PILES TO VIEW OR PILE TO EDIT
if(empty($pile_id)){
	$piles = $alkaline->getTable('piles');
	$pile_count = @count($piles);
	
	define('TITLE', 'Alkaline Piles');
	require_once(PATH . ADMIN . 'includes/header.php');
	
	?>
	
	<div class="actions"><a href="<?php echo BASE . ADMIN . 'piles' . URL_ACT . 'build' . URL_RW; ?>">Build static pile</a></div>

	<h1>Piles (<?php echo $pile_count; ?>)</h1>
	
	<p>Piles are collections of images. You can build an automatic pile that updates itself by <a href="<?php echo BASE . ADMIN . 'library' . URL_CAP; ?>">performing a search</a>.</p>
	
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
	
		foreach($piles as $pile){
			echo '<tr>';
				echo '<td><strong><a href="' . BASE . ADMIN . 'piles' . URL_ID . $pile['pile_id'] . URL_RW . '">' . $pile['pile_title'] . '</a></strong><br /><a href="' . BASE . 'pile' . URL_ID . $pile['pile_title_url'] . URL_RW . '" class="nu">/' . $pile['pile_title_url'] . '</td>';
				echo '<td class="center">' . $pile['pile_views'] . '</td>';
				echo '<td class="center"><a href="' . BASE . ADMIN . 'search/piles/' . $pile['pile_id'] . '">' . $pile['pile_image_count'] . '</a></td>';
				echo '<td>' . $alkaline->formatTime($pile['pile_modified']) . '</td>';
			echo '</tr>';
		}
	
		?>
	</table>

	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}
else{
	// Get pile
	$pile = $alkaline->getRow('piles', $pile_id);
	$pile = $alkaline->makeHTMLSafe($pile);
	
	// Update pile
	$image_ids = new Find;
	$image_ids->pile($pile_id);
	$image_ids->find();
	
	if(!empty($pile['pile_title'])){	
		define('TITLE', 'Alkaline Pile: &#8220;' . $pile['pile_title']  . '&#8221;');
	}
	else{
		define('TITLE', 'Alkaline Pile');
	}
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<div class="actions"><a href="<?php echo BASE . ADMIN . 'search' . URL_ACT . 'piles' . URL_AID . $pile['pile_id'] . URL_RW; ?>">View images (<?php echo $image_ids->image_count; ?>)</a> <a href="<?php echo BASE . 'pile' . URL_ID . $pile['pile_id'] . URL_RW; ?>">Go to pile</a></div>
	
	<h1>Pile</h1>
	
	<form id="pile" action="<?php echo BASE . ADMIN; ?>piles<?php echo URL_CAP; ?>" method="post">
		<table>
			<tr>
				<td class="right middle"><label for="pile_title">Title:</label></td>
				<td><input type="text" id="pile_title" name="pile_title" value="<?php echo $pile['pile_title']; ?>" class="title" /></td>
			</tr>
			<tr>
				<td class="right pad"><label for="pile_title_url">Custom URL:</label></td>
				<td class="quiet">
					<input type="text" id="pile_title_url" name="pile_title_url" value="<?php echo $pile['pile_title_url']; ?>" style="width: 300px;" /><br />
					Optional. Use only letters, numbers, underscores, and hyphens.
				</td>
			</tr>
			<tr>
				<td class="right pad"><label for="pile_description">Description:</label></td>
				<td><textarea id="pile_description" name="pile_description"><?php echo $pile['pile_description']; ?></textarea></td>
			</tr>
			<tr>
				<td class="right"><label for="pile_type">Type:</label></td>
				<td>
					<input type="radio" name="pile_type" id="pile_type_auto" value="auto" <?php if($pile['pile_type'] != 'static'){ echo 'checked="checked"'; } if(empty($pile['pile_call'])){ echo 'disabled="disabled"'; } ?> /> <label for="pile_type_auto">Automatic</label> &#8212; Automatically include new images that meet the pile&#8217;s criteria<br />
					<input type="radio" name="pile_type" id="pile_type_static" value="static" <?php if($pile['pile_type'] == 'static'){ echo 'checked="checked"'; }  ?> /> <label for="pile_type_static">Static</label> &#8212; Only include the images originally selected
				</td>
			</tr>
			<?php if($pile['pile_type'] == 'static'){ ?>
				<tr>
					<td class="right"><label>Sort:</label></td>
					<td>
						<p>
							<span class="switch">&#9656;</span> <a href="#" class="show">Show pile</a> <span class="quiet">(sort images by dragging and dropping)</span>
						</p>

						<div class="reveal" id="pile_image_sort">
							<?php
						
							$images = new Image($pile['pile_images']);
							$images->getImgUrl('square');
						
							foreach($images->images as $image){
								echo '<img src="' . $image['image_src_square'] .'" alt="" class="frame" id="image-' . $image['image_id'] . '" />';
							}
						
							?><br /><br />
						</div>
						<input type="hidden" id="pile_images" name="pile_images" value="<?php echo $pile['pile_images']; ?>" />
					</td>
				</tr>
			<?php } ?>
			<tr>
				<td class="right"><input type="checkbox" id="pile_delete" name="pile_delete" value="delete" /></td>
				<td><strong><label for="pile_delete">Delete this pile.</label></strong> This action cannot be undone.</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="hidden" name="pile_id" value="<?php echo $pile['pile_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo $alkaline->back(); ?>">cancel</a></td>
			</tr>
		</table>
	</form>

	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}

?>