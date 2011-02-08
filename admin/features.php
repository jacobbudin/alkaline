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

if(!empty($_GET['act']) and ($_GET['act'] != 'bulk')){
	Find::clearMemory();
}

// SANITIZE INPUT
$_GET = array_map('strip_tags', $_GET);
$_POST = array_map('strip_tags', $_POST);

// Process actions
if(!empty($_POST['do']) and ($_POST['do'] == 'Do')){
	$act = $_POST['act'];
	$image_ids = $alkaline->convertToIntegerArray($_POST['image_ids']);
	
	if(count($image_ids) > 0){	
		if($act == 'tag_add'){
			$tag_name = $_POST['act_tag_name'];
			$images = new Image($image_ids);
			$tags = $images->addTags(array($tag_name));
			if($tags !== false){
				$notification = 'You successfully added the tag &#8220;';
				if(!empty($tags[0])){
					$notification .= '<a href="' . BASE . ADMIN . 'search' . URL_ACT . 'tags' . URL_AID . @$tags[0] . URL_RW . '">' . $tag_name . '</a>';
				}
				else{
					$notification .= $tag_name;
				}
				$notification .= '&#8221;.';
				$alkaline->addNote($notification, 'success');
			}
		}
		elseif($act == 'tag_remove'){
			$tag_name = $_POST['act_tag_name'];
			$images = new Image($image_ids);
			$tags = $images->removeTags(array($tag_name));
			if($tags !== false){
				$notification = 'You successfully removed the tag &#8220;';
				if(!empty($tags[0])){
					$notification .= '<a href="' . BASE . ADMIN . 'search' . URL_ACT . 'tags' . URL_AID . @$tags[0] . URL_RW . '">' . $tag_name . '</a>';
				}
				else{
					$notification .= $tag_name;
				}
				$notification .= '&#8221;.';
				$alkaline->addNote($notification, 'success');
			}
		}
		elseif($act == 'pile_add'){
			$pile = $alkaline->getRow('piles', $_POST['act_pile_id']);
		
			if(!empty($pile['pile_images'])){
				$pile_images = explode(', ', $pile['pile_images']);
				$pile_images = array_merge($pile_images, $image_ids);
				$pile_images = array_unique($pile_images);
			}
			else{
				$pile_images = $image_ids;
			}
		
			$pile_image_count = count($pile_images);
			$pile_images = implode(', ', $pile_images);
		
			$fields = array('pile_images' => $pile_images,
				'pile_image_count' => $pile_image_count);
		
			$bool = $alkaline->updateRow($fields, 'piles', $_POST['act_pile_id']);
			if($bool === true){
				$alkaline->addNote('You successfully added to the pile &#8220;<a href="' . BASE . ADMIN . 'search' . URL_ACT . 'piles' . URL_AID . @$pile['pile_id'] . URL_RW . '">' . $pile['pile_title'] . '</a>&#8221;.', 'success');
			}
		}
		elseif($act == 'pile_remove'){
			$pile = $alkaline->getRow('piles', $_POST['act_pile_id']);
		
			if(!empty($pile['pile_images'])){
				$pile_images = explode(', ', $pile['pile_images']);
				foreach($image_ids as $image){
					$key = array_search($image, $pile_images, false);
					if($key !== false){
						unset($pile_images[$key]);
					}
				}
				$pile_images = array_merge($pile_images);
				$pile_images = array_unique($pile_images);
			}
			else{
				$pile_images = array();
			}
		
			$pile_image_count = count($pile_images);
			$pile_images = implode(', ', $pile_images);
		
			$fields = array('pile_images' => $pile_images,
				'pile_image_count' => $pile_image_count);
		
			$bool = $alkaline->updateRow($fields, 'piles', $_POST['act_pile_id']);
			if($bool === true){
				$alkaline->addNote('You successfully removed from the pile &#8220;<a href="' . BASE . ADMIN . 'search' . URL_ACT . 'piles' . URL_AID . $pile['pile_id'] . URL_RW . '">' . $pile['pile_title'] . '</a>&#8221;.', 'success');
			}
		}
		elseif($act == 'right'){
			$right_id = intval($_POST['act_right_id']);
			if($right_id > 0){
				$images = new Image($image_ids);
				$bool = $images->updateFields(array('right_id' => $right_id));
				if($bool === true){
					$alkaline->addNote('You successfully changed rights sets.', 'success');
				}
			}
		}
		elseif($act == 'privacy'){
			$privacy_id = intval($_POST['act_privacy_id']);
			if($privacy_id > 0){
				$images = new Image($image_ids);
				$bool = $images->updateFields(array('image_privacy' => $privacy_id));
				if($bool === true){
					$alkaline->addNote('You successfully changed privacy levels.', 'success');
				}
			}
		}
		elseif($act == 'publish'){
			$images = new Image($image_ids);
			$now = date('Y-m-d H:i:s');
			$time = time();
			foreach($images->images as $image){
				if(empty($image['image_published']) or (strtotime($image['image_published']) > $time)){
					$bool = $alkaline->updateRow(array('image_published' => $now), 'images', $image['image_id'], false);
				}
				else{
					$bool = true;
				}
			}
			if($bool === true){
				$alkaline->addNote('The images were successfully published.', 'success');
			}
		}
		elseif($act == 'delete'){
			if($alkaline->returnConf('bulk_delete')){
				$images = new Image($image_ids);
				$images->delete();
				$alkaline->addNote('The images were successfully deleted.', 'success');
			}
		}
	}
	
	$selected_image_ids = $image_ids;
}

// Preference: page_limit
if(!$max = $user->returnPref('page_limit')){
	$max = 100;
}

$image_ids = new Find();
$image_ids->memory();
$image_ids->page(null, $max);
$image_ids->find();

$images = new Image($image_ids->image_ids);
$images->getImgUrl('square');
$images->hook();

define('TAB', 'features');
define('TITLE', 'Alkaline Features');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div class="span-24 last">
	<div class="span-18 colborder">
		<div class="actions">
			<a href="#select_all" id="select_all">Select all</a> <a href="#deselect_all" id="deselect_all">Deselect all</a> <a href="<?php echo BASE . ADMIN . 'features' . URL_ACT . 'clear' . URL_RW; ?>">Show all</a>
		</div>
		
		<h1>Editor (<span id="image_count_selected">0</span> of <?php echo number_format($image_ids->image_count); ?>)</h1>
		
		<form action="" method="post">
			<p>
				<select name="act" id="act">
					<option value="tag_add">Add tag</option>
					<option value="tag_remove">Remove tag</option>
					<option value="pile_add">Add to static pile</option>
					<option value="pile_remove">Remove from static pile</option>
					<option value="right">Switch to rights set</option>
					<option value="privacy">Switch to privacy level</option>
					<option value="publish">Publish now</option>
					<?php if($alkaline->returnConf('bulk_delete')){ echo '<option value="delete">Delete</option>'; } ?>
				</select>
				<input type="text" class="s" id="act_tag_name" name="act_tag_name" />
				<?php echo $alkaline->showPiles('act_pile_id', true); ?>
				<?php echo $alkaline->showRights('act_right_id'); ?>
				<?php echo $alkaline->showPrivacy('act_privacy_id'); ?>
				<input type="hidden" name="image_ids" id="image_ids" value="" />
				<input name="do" type="submit" value="Do" />
			</p>
		</form>
		
		<p>
			<?php

			foreach($images->images as $image){
				$selected = '';
				if(!empty($selected_image_ids)){
					if(@in_array($image['image_id'], $selected_image_ids)){
						$selected = '_selected';
					}
				}
				elseif(!empty($_SESSION['alkaline']['search']['results'])){
					$selected = '_selected';
				}
				?>
				<img src="<?php echo $image['image_src_square']; ?>" alt="" id="image-<?php echo $image['image_id']; ?>" title="<?php echo $image['image_title']; ?>" class="frame<?php echo $selected; ?>" />
				<?php
			}
			?>
		</p>
		<?php
		if($image_ids->page_count > 1){
			?>
			<p>
				<?php
				if(!empty($image_ids->page_previous)){
					for($i = 1; $i <= $image_ids->page_previous; ++$i){
						echo '<a href="' . BASE . ADMIN . 'features' . URL_PAGE . $i . URL_RW . '" class="page_no">' . number_format($i) . '</a>';
					}
				}
				?>
				<span class="page_no">Page <?php echo $image_ids->page; ?> of <?php echo $image_ids->page_count; ?></span>
				<?php
				if(!empty($image_ids->page_next)){
					for($i = $image_ids->page_next; $i <= $image_ids->page_count; ++$i){
						echo '<a href="' . BASE . ADMIN . 'features' . URL_PAGE . $i . URL_RW . '" class="page_no">' . number_format($i) . '</a>';
					}
				}
				?>
			</p>
			<?php
		}
		?>
		<p class="quiet">
			<em>Tip: Hold down the Shift key to select a series of images.</em>
		</p>
	</div>
	<div class="span-5 last">
		<h2><a href="<?php echo BASE . ADMIN; ?>tags<?php echo URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/icons/tags.png" alt="" /> Tags &#9656;</a></h2>
		<h2><a href="<?php echo BASE . ADMIN; ?>piles<?php echo URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/icons/piles.png" alt="" /> Piles &#9656;</a></h2>
		<h2><a href="<?php echo BASE . ADMIN; ?>comments<?php echo URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/icons/comments.png" alt="" /> Comments &#9656;</a></h2>
		<h2><a href="<?php echo BASE . ADMIN; ?>pages<?php echo URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/icons/pages.png" alt="" /> Pages &#9656;</a></h2>
		<h2><a href="<?php echo BASE . ADMIN; ?>rights<?php echo URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/icons/rights.png" alt="" /> Rights &#9656;</a></h2>
	</div>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>