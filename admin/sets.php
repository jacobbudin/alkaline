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
		// Rebuild pile with new data
		$image_ids = new Find('images');
		$image_ids->find();
		$image_ids->saveMemory();
		
		$set_images = @implode(', ', $image_ids->ids);
		$set_image_count = $image_ids->count;
		
		$set_title = trim($_POST['set_title']);
		
		if(!empty($_POST['set_title_url'])){
			$set_title_url = $alkaline->makeURL($_POST['set_title_url']);
		}
		else{
			$set_title_url = $alkaline->makeURL($set_title);
		}
		
		$fields = array('set_call' => serialize($_SESSION['alkaline']['search']['images']['call']),
			'set_request' => serialize($_SESSION['alkaline']['search']['images']['request']),
			'set_title' => $alkaline->makeUnicode($set_title),
			'set_title_url' => $set_title_url,
			'set_type' => $_POST['set_type'],
			'set_description' => $alkaline->makeUnicode($_POST['set_description']),
			'set_images' => $set_images,
			'set_image_count' => $set_image_count);
		
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
	
	<div class="actions">
		<a href="<?php echo BASE . ADMIN . 'sets' . URL_ACT . 'build' . URL_RW; ?>"><button>Build set</button></a>
	</div>

	<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/sets.png" alt="" /> Sets (<?php echo $set_count; ?>)</h1>
	
	<p>Sets are collections of images. You can also build a set by <a href="<?php echo BASE . ADMIN . 'library' . URL_CAP; ?>">performing a search</a>.</p>
	
	<p>
		<input type="search" name="filter" placeholder="Filter" class="s" results="0" />
	</p>
	
	<table class="filter">
		<tr>
			<th>Title</th>
			<th class="center">Type</th>
			<th class="center">Views</th>
			<th class="center">Images</th>
			<th>Created</th>
			<th>Last modified</th>
		</tr>
		<?php
	
		foreach($sets as $set){
			echo '<tr class="ro">';
				echo '<td><strong class="large"><a href="' . BASE . ADMIN . 'sets' . URL_ID . $set['set_id'] . URL_RW . '">' . $set['set_title'] . '</a></strong><br /><a href="' . BASE . 'set' . URL_ID . $set['set_title_url'] . URL_RW . '" class="nu quiet">' . $set['set_title_url'] . '</td>';
				echo '<td class="center">' . ucwords($set['set_type']) . '</td>';
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
	$sets = new Set($set_id);
	$set = $sets->sets[0];
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
				
				<p class="quiet">
					Note: If you switch to static, and then return to automatic, you will lose any manual additions or removals performed.
				</p>
				
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
		
		
		<?php if($set['set_type'] == 'auto'){ $set_request = $set['set_request']; ?>
		<p class="slim">
			<span class="switch">&#9656;</span> <a href="#" class="show">Modify set&#8217;s criteria</a></span>
		</p>
		
		<div class="reveal">
			<table>
				<tr>
					<td class="right pad"><label for="search">Search:</label></td>
					<td class="quiet">
						<input type="text" id="search" name="q" class="l" value="<?php echo $set_request['q']; ?>" />
					</td>
				</tr>
				<tr>
					<td class="right pad"><label for="tags">Tags:</label></td>
					<td class="quiet">
						<input type="text" id="tags" name="tags" class="l" value="<?php echo $set_request['tags']; ?>" /><br />
						<em>Tip: Use the uppercase boolean operators AND, OR, and NOT.</em>
					</td>
				</tr>
				<tr>
					<td class="right pad"><label for="tags">EXIF metadata:</label></td>
					<td>
						<?php echo $alkaline->showEXIFNames('exif_name', $set_request['exif_name']); ?>
						<input type="text" id="exif_value" name="exif_value" class="s" value="<?php echo $set_request['exif_value']; ?>" /><br />
					</td>
				</tr>
				<tr>
					<td class="right middle"><label for="rights">Rights set:</label></td>
					<td class="quiet">
						<?php echo $alkaline->showRights('rights', $set_request['rights']); ?>
					</td>
				</tr>
				<tr>
					<td class="right middle"><label>Date taken:</label></td>
					<td class="quiet">
						between <input type="text" class="date s" name="taken_begin" value="<?php echo $set_request['taken_begin']; ?>" />
						and <input type="text" class="date s" name="taken_end" value="<?php echo $set_request['taken_end']; ?>" />
					</td>
				</tr>
				<tr>
					<td class="right middle"><label>Date uploaded:</label></td>
					<td class="quiet">
						between <input type="text" class="date s" name="uploaded_begin" value="<?php echo $set_request['uploaded_begin']; ?>" />
						and <input type="text" class="date s" name="uploaded_end" value="<?php echo $set_request['uploaded_end']; ?>" />
					</td>
				</tr>
				<tr>
					<td class="right middle"><label>Location:</label></td>
					<td class="quiet">
						within
						<select name="location_proximity">
							<option value="10" <?php echo $alkaline->readForm($set_request, 'location_proximity', '10'); ?>>10</option>
							<option value="25" <?php echo $alkaline->readForm($set_request, 'location_proximity', '25'); ?>>25</option>
							<option value="50" <?php echo $alkaline->readForm($set_request, 'location_proximity', '50'); ?>>50</option>
							<option value="100" <?php echo $alkaline->readForm($set_request, 'location_proximity', '100'); ?>>100</option>
							<option value="250" <?php echo $alkaline->readForm($set_request, 'location_proximity', '250'); ?>>250</option>
							<option value="500" <?php echo $alkaline->readForm($set_request, 'location_proximity', '500'); ?>>500</option>
							<option value="1000" <?php echo $alkaline->readForm($set_request, 'location_proximity', '1000'); ?>>1,000</option>
							<option value="2500" <?php echo $alkaline->readForm($set_request, 'location_proximity', '2500'); ?>>2,500</option>
						</select>
						miles of 
						<input type="text" name="location" class="image_geo m" value="<?php echo $set_request['location']; ?>" />
					</td>
				</tr>
				<tr>
					<td class="right middle"><label for="color">Dominant color:</label></td>
					<td>
						<select id="color" name="color">
							<option></option>
							<option value="blue" <?php echo $alkaline->readForm($set_request, 'color', 'blue'); ?>>Blue</option>
							<option value="red" <?php echo $alkaline->readForm($set_request, 'color', 'red'); ?>>Red</option>
							<option value="yellow" <?php echo $alkaline->readForm($set_request, 'color', 'yellow'); ?>>Yellow</option>
							<option value="green" <?php echo $alkaline->readForm($set_request, 'color', 'green'); ?>>Green</option>
							<option value="purple" <?php echo $alkaline->readForm($set_request, 'color', 'purple'); ?>>Purple</option>
							<option value="orange" <?php echo $alkaline->readForm($set_request, 'color', 'orange'); ?>>Orange</option>
							<option value="brown" <?php echo $alkaline->readForm($set_request, 'color', 'brown'); ?>>Brown</option>
							<option value="pink" <?php echo $alkaline->readForm($set_request, 'color', 'pink'); ?>>Pink</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="right middle"><label>Views:</label></td>
					<td>
						<select name="views_operator">
							<option value="greater" <?php echo $alkaline->readForm($set_request, 'views_operator', 'greater'); ?>>&#8805;</option>
							<option value="less" <?php echo $alkaline->readForm($set_request, 'views_operator', 'less'); ?>>&#8804;</option>
							<option value="equal" <?php echo $alkaline->readForm($set_request, 'views_operator', 'equal'); ?>>&#0061;</option>
						</select>
						<input type="text" name="views" class="xs" value="<?php echo $set_request['views']; ?>" />
					</td>
				</tr>
				<tr>
					<td class="right middle"><label for="orientation">Orientation:</label></td>
					<td class="quiet">
						<select id="orientation" name="orientation">
							<option value="" <?php echo $alkaline->readForm($set_request, 'orientation', ''); ?>>All</option>
							<option value="portrait" <?php echo $alkaline->readForm($set_request, 'orientation', 'portrait'); ?>>Portrait</option>
							<option value="landscape" <?php echo $alkaline->readForm($set_request, 'orientation', 'landscape'); ?>>Landscape</option>
							<option value="square" <?php echo $alkaline->readForm($set_request, 'orientation', 'square'); ?>>Square</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="right middle"><label for="privacy">Privacy level:</label></td>
					<td class="quiet">
						<select id="privacy" name="privacy">
							<option value="" <?php echo $alkaline->readForm($set_request, 'privacy', ''); ?>>All</option>
							<option value="public" <?php echo $alkaline->readForm($set_request, 'privacy', 'public'); ?>>Public</option>
							<option value="protected" <?php echo $alkaline->readForm($set_request, 'privacy', 'protected'); ?>>Protected</option>
							<option value="private" <?php echo $alkaline->readForm($set_request, 'privacy', 'private'); ?>>Private</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="right middle"><label for="published">Publication status:</label></td>
					<td class="quiet">
						<select id="published" name="published">
							<option value="" <?php echo $alkaline->readForm($set_request, 'published', ''); ?>>All</option>
							<option value="published" <?php echo $alkaline->readForm($set_request, 'published', 'published'); ?>>Published</option>
							<option value="unpublished" <?php echo $alkaline->readForm($set_request, 'published', 'unpublished'); ?>>Unpublished</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="right middle"><label>Sort results by:</label></td>
					<td>
						<select name="sort">
							<option value="taken" <?php echo $alkaline->readForm($set_request, 'sort', 'taken'); ?>>Date taken</option>
							<option value="updated" <?php echo $alkaline->readForm($set_request, 'sort', 'updated'); ?>>Date last updated</option>
							<option value="published" <?php echo $alkaline->readForm($set_request, 'sort', 'published'); ?>>Date published</option>
							<option value="uploaded" selected="selected" <?php echo $alkaline->readForm($set_request, 'sort', 'uploaded'); ?>>Date uploaded</option>
							<option value="title" <?php echo $alkaline->readForm($set_request, 'sort', 'title'); ?>>Title</option>
							<option value="views" <?php echo $alkaline->readForm($set_request, 'sort', 'views'); ?>>Views</option>
						</select>
						<select name="sort_direction">
							<option value="DESC" <?php echo $alkaline->readForm($set_request, 'sort_direction', 'DESC'); ?>>Descending</option>
							<option value="ASC" <?php echo $alkaline->readForm($set_request, 'sort_direction', 'ASC'); ?>>Ascending</option>
						</select>
					</td>
				</tr>
			</table>
		</div>
		<?php } ?>
		
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