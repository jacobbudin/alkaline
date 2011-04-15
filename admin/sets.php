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

$user->perm(true, 'sets');

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
	$image_ids = new Find('images');
	$set_call = $image_ids->recentMemory();
	if(!empty($set_call)){
		$fields = array('set_call' => serialize($set_call),
			'set_request' => serialize($_SESSION['alkaline']['search']['images']['request']),
			'set_type' => 'auto');
	}
	else{
		$fields = array('set_type' => 'static');
	}
	$set_id = $alkaline->addRow($fields, 'sets');
	
	$images = new Find('images');
	$images->sets($set_id);
	$images->find();
	
	$set_images = @implode(', ', $images->ids);
	$set_image_count = $images->count;
	
	$fields = array('set_images' => $set_images,
		'set_image_count' => $set_image_count);
	$alkaline->updateRow($fields, 'sets', $set_id);
}

define('TAB', 'features');

// GET PILES TO VIEW OR PILE TO EDIT
if(empty($set_id)){
	$sets = $alkaline->getTable('sets', null, null, null, 'set_modified DESC');
	$set_count = @count($sets);
	
	define('TITLE', 'Alkaline Sets');
	require_once(PATH . ADMIN . 'includes/header.php');
	
	?>
	
	<div class="actions"><a href="<?php echo BASE . ADMIN . 'sets' . URL_ACT . 'build' . URL_RW; ?>"><button>Build static set</button></a></div>

	<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/sets.png" alt="" /> Sets (<?php echo $set_count; ?>)</h1>
	
	<p>Sets are collections of images. You can build an automatic set that updates itself by <a href="<?php echo BASE . ADMIN . 'library' . URL_CAP; ?>">performing a search</a>.</p>
	
	<p>
		<input type="search" name="filter" placeholder="Filter" class="s" results="0" />
	</p>
	
	<table class="filter">
		<tr>
			<th>Title</th>
			<th class="center">Views</th>
			<th class="center">Images</th>
			<th>Created</th>
			<th>Last modified</th>
		</tr>
		<?php
	
		foreach($sets as $set){
			echo '<tr>';
				echo '<td><strong class="large"><a href="' . BASE . ADMIN . 'sets' . URL_ID . $set['set_id'] . URL_RW . '">' . $set['set_title'] . '</a></strong><br /><a href="' . BASE . 'set' . URL_ID . $set['set_title_url'] . URL_RW . '" class="nu quiet">' . $set['set_title_url'] . '</td>';
				echo '<td class="center">' . $set['set_views'] . '</td>';
				echo '<td class="center"><a href="' . BASE . ADMIN . 'search' . URL_ACT . 'sets' . URL_AID . $set['set_id'] . URL_RW . '">' . $set['set_image_count'] . '</a></td>';
				echo '<td>' . $alkaline->formatTime($set['set_created']) . '</td>';
				echo '<td>' . ucfirst($alkaline->formatRelTime($set['set_modified'])) . '</td>';
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
	$image_ids = new Find('images');
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
	
	<div class="actions"><a href="<?php echo BASE . ADMIN . 'search' . URL_ACT . 'sets' . URL_AID . $set['set_id'] . URL_RW; ?>"><button>View images (<?php echo $image_ids->count; ?>)</button></a> <a href="<?php echo BASE . 'set' . URL_ID . $set['set_id'] . URL_RW; ?>"><button>Launch set</button></a></div>
	
	<?php
	
	if(empty($set['set_title'])){
		echo '<h1><img src="' . BASE . ADMIN . 'images/icons/sets.png" alt="" /> New Set</h1>';
	}
	else{
		echo '<h1><img src="' . BASE . ADMIN . 'images/icons/sets.png" alt="" /> Set: ' . $set['set_title'] . '</h1>';
	}
	
	?>
	
	<form id="set" action="<?php echo BASE . ADMIN . 'sets' . URL_CAP; ?>" method="post">
		<div class="span-24 last">
			<div class="span-15 append-1">
				<input type="text" id="set_title" name="set_title" placeholder="Title" value="<?php echo $set['set_title']; ?>" class="title notempty" />
				<textarea id="set_description" name="set_description" placeholder="Description" style="height: 300px;" class="<?php if($user->returnPref('text_code')){ echo $user->returnPref('text_code_class'); } ?>"><?php echo $set['set_description']; ?></textarea>
			</div>
			<div class="span-8 last">
				<p>
					<label for="set_title_url">Custom URL:</label><br />
					<input type="text" id="set_title_url" name="set_title_url" value="<?php echo $set['set_title_url']; ?>" style="width: 300px;" /><br />
					<span class="quiet"><?php echo 'set' . URL_ID . $set['set_id']; ?>-<span id="set_title_url_link"></span></span>
				</p>
			
				<label for="set_type">Type:</label><br />
				<table>
					<tr>
						<td><input type="radio" name="set_type" id="set_type_auto" value="auto" <?php if($set['set_type'] != 'static'){ echo 'checked="checked"'; } if(empty($set['set_call'])){ echo 'disabled="disabled"'; } ?> /></td>
						<td>
							<label for="set_type_auto">Automatic</label> <span class="quiet">(search)</span><br />
							Automatically include new images that meet the set&#8217;s criteria
						</td>
					</tr>
					<tr>
						<td>
							<input type="radio" name="set_type" id="set_type_static" value="static" <?php if($set['set_type'] == 'static'){ echo 'checked="checked"'; }  ?> />
						</td>
						<td>
							<label for="set_type_static">Static</label> <span class="quiet">(handpicked)</span><br />
							Only include images selected at creation and those manually added since then</td>
					</tr>
				</table>
				
				<hr />
				<table>
					<tr>
						<td><input type="checkbox" id="set_delete" name="set_delete" value="delete" /></td>
						<td>
							<label for="set_delete">Delete this set.</label><br />
							This action cannot be undone.
						</td>
					</tr>
				</table>
			</div>
		</div>
		
		<p>
			<span class="switch">&#9656;</span> <a href="#" class="show">Show set&#8217;s images</a> <?php if($set['set_type'] == 'static'){ ?><span class="quiet">(sort images by dragging and dropping)</span><?php } ?>
		</p>

		<div class="reveal" <?php if($set['set_type'] == 'static'){ ?>id="set_image_sort"<?php } ?>>
			<?php
		
			$images = new Image($set['set_images']);
			$images->getSizes('square');
		
			foreach($images->images as $image){
				echo '<img src="' . $image['image_src_square'] .'" alt="" class="frame" id="image-' . $image['image_id'] . '" />';
			}
		
			?><br /><br />
		</div>
		<input type="hidden" id="set_images" name="set_images" value="<?php echo $set['set_images']; ?>" />
		
		<p>
			<input type="hidden" name="set_id" value="<?php echo $set['set_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo $alkaline->back(); ?>">cancel</a>
		</p>
	</form>

	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}

?>