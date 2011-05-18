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
$orbit = new Orbit;

$user->perm(true, 'features');

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
		elseif($act == 'send'){
			if(!empty($_POST['act_send'])){
				$act_send = $_POST['act_send'];
				$images = new Image($image_ids);
				
				$orbit->hook('send_' . $act_send, $images);
			}
		}
		elseif($act == 'set_add'){
			$set = $alkaline->getRow('sets', $_POST['act_set_id']);
		
			if(!empty($set['set_images'])){
				$set_images = explode(', ', $set['set_images']);
				$set_images = array_merge($set_images, $image_ids);
				$set_images = array_unique($set_images);
			}
			else{
				$set_images = $image_ids;
			}
		
			$set_image_count = count($set_images);
			$set_images = implode(', ', $set_images);
		
			$fields = array('set_images' => $set_images,
				'set_image_count' => $set_image_count);
		
			$bool = $alkaline->updateRow($fields, 'sets', $_POST['act_set_id']);
			if($bool === true){
				$alkaline->addNote('You successfully added to the set &#8220;<a href="' . BASE . ADMIN . 'search' . URL_ACT . 'sets' . URL_AID . @$set['set_id'] . URL_RW . '">' . $set['set_title'] . '</a>&#8221;.', 'success');
			}
		}
		elseif($act == 'set_remove'){
			$set = $alkaline->getRow('sets', $_POST['act_set_id']);
		
			if(!empty($set['set_images'])){
				$set_images = explode(', ', $set['set_images']);
				foreach($image_ids as $image){
					$key = array_search($image, $set_images, false);
					if($key !== false){
						unset($set_images[$key]);
					}
				}
				$set_images = array_merge($set_images);
				$set_images = array_unique($set_images);
			}
			else{
				$set_images = array();
			}
		
			$set_image_count = count($set_images);
			$set_images = implode(', ', $set_images);
		
			$fields = array('set_images' => $set_images,
				'set_image_count' => $set_image_count);
		
			$bool = $alkaline->updateRow($fields, 'sets', $_POST['act_set_id']);
			if($bool === true){
				$alkaline->addNote('You successfully removed from the set &#8220;<a href="' . BASE . ADMIN . 'search' . URL_ACT . 'sets' . URL_AID . $set['set_id'] . URL_RW . '">' . $set['set_title'] . '</a>&#8221;.', 'success');
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
		elseif($act == 'geo'){
			$geo = $_POST['act_geo'];
			
			$images = new Image($image_ids);
			$bool = $images->updateFields(array('image_geo' => $geo));
			if($bool === true){
				$alkaline->addNote('You successfully set the location.', 'success');
			}
		}
		elseif($act == 'publish'){
			$publish = $_POST['act_publish'];
			
			$images = new Image($image_ids);
			$bool = $images->updateFields(array('image_published' => $publish));
			if($bool === true){
				$alkaline->addNote('You successfully set the publication date.', 'success');
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

$image_ids = new Find('images');
$is_memory = $image_ids->memory();
$image_ids->page(null, $max);
$image_ids->find();

$images = new Image($image_ids);
$images->getSizes('square');
$images->hook();

define('TAB', 'features');
define('TITLE', 'Alkaline Features');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div class="span-24 last">
	<?php
	
	if($user->perm(false, 'editor')){
		?>
		<div class="actions">
			<a href="#select_all" id="select_all"><button>Select all</button></a>
			<a href="#deselect_all" id="deselect_all"><button>Deselect all</button></a>
			<?php if($is_memory === true){ ?>
				<a href="<?php echo BASE . ADMIN . 'features' . URL_ACT . 'clear' . URL_RW; ?>"><button>Clear all</button></a>
			<?php } ?>
		</div>
	
		<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/editor.png" alt="" /> Editor (<span id="image_count_selected">0</span> of <?php echo number_format($image_ids->count); ?>)</h1>
	
		<form action="" method="post">
			<p>
				<select name="act" id="act">
					<option value="tag_add">Add tag</option>
					<option value="tag_remove">Remove tag</option>
					<option value="send">Send to</option>
					<option value="set_add">Add to static set</option>
					<option value="set_remove">Remove from static set</option>
					<option value="right">Switch to rights set</option>
					<option value="privacy">Switch to privacy level</option>
					<option value="geo">Set location</option>
					<option value="publish">Publish on</option>
					<?php if($alkaline->returnConf('bulk_delete')){ echo '<option value="delete">Delete</option>'; } ?>
				</select>
				<input type="text" class="s image_tag" id="act_tag_name" name="act_tag_name" />
				<input type="text" class="s image_geo" id="act_geo" name="act_geo" />
				<input type="text" class="s" id="act_publish" name="act_publish" />
				<?php echo $alkaline->showSets('act_set_id', true); ?>
				<?php echo $alkaline->showRights('act_right_id'); ?>
				<?php echo $alkaline->showPrivacy('act_privacy_id'); ?>
				<select id="act_send" name="act_send">
					<?php $orbit->hook('send_html'); ?>
				</select>
				<input type="hidden" name="image_ids" id="image_ids" value="" />
				<input type="submit" id="act_do" name="do" value="Do" />
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
				elseif(!empty($_SESSION['alkaline']['search']['images']['results'])){
					$selected = '_selected';
				}
				?>
				<img src="<?php echo $image['image_src_square']; ?>" alt="" id="image-<?php echo $image['image_id']; ?>" title="<?php echo $image['image_title']; ?>" class="frame_fade<?php echo $selected; ?> tip" />
				<?php
			}
			?>
		</p>
		<?php if($image_ids->page_count > 1){ ?>
			<p>
				<?php
				if(!empty($image_ids->page_previous)){
					for($i = 1; $i <= $image_ids->page_previous; ++$i){
						$page_uri = 'page_' . $i . '_uri';
						echo '<a href="' . $image_ids->$page_uri  .'" class="page_no">' . number_format($i) . '</a>';
					}
				}
				?>
				<span class="page_no">Page <?php echo $image_ids->page; ?> of <?php echo $image_ids->page_count; ?></span>
				<?php
				if(!empty($image_ids->page_next)){
					for($i = $image_ids->page_next; $i <= $image_ids->page_count; ++$i){
						$page_uri = 'page_' . $i . '_uri';
						echo '<a href="' . $image_ids->$page_uri  .'" class="page_no">' . number_format($i) . '</a>';
					}
				}
				?>
			</p>
		<?php } ?>
		
		<p class="quiet">
			<em>Tip: Hold down the Shift key to select a series of images.</em>
		</p>
	<?php
	
	}
	else{
		?>
		<h1>Editor</h1>
		
		<p>You do not have permission to access this module.</p>
		<?php
	}
	
	?>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>