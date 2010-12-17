<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

// Fix bad incoming links
if(!empty($_GET['id'])){
	$_GET['page'] = $_GET['id'];
}

if(@$_GET['act'] != 'bulk'){
	Find::clearMemory();
}

// SANITIZE INPUT
$_GET = array_map('strip_tags', $_GET);
$_POST = array_map('strip_tags', $_POST);

// Process actions
if(@$_POST['do'] == 'Do'){
	$act = $_POST['act'];
	$photo_ids = $alkaline->convertToIntegerArray($_POST['photo_ids']);
	
	if(count($photo_ids) > 0){	
		if($act == 'tag_add'){
			$tag_name = $_POST['act_tag_name'];
			$photos = new Photo($photo_ids);
			$tags = $photos->addTags(array($tag_name));
			if($tags !== false){
				$notification = 'You successfully added the tag &#8220;';
				if(!empty($tags[0])){
					$notification .= '<a href="' . BASE . ADMIN . 'search' . URL_ACT . 'tags' . URL_AID . @$tags[0] . URL_RW . '">' . $tag_name . '</a>';
				}
				else{
					$notification .= $tag_name;
				}
				$notification .= '&#8221;.';
				$alkaline->addNotification($notification, 'success');
			}
		}
		elseif($act == 'tag_remove'){
			$tag_name = $_POST['act_tag_name'];
			$photos = new Photo($photo_ids);
			$tags = $photos->removeTags(array($tag_name));
			if($tags !== false){
				$notification = 'You successfully removed the tag &#8220;';
				if(!empty($tags[0])){
					$notification .= '<a href="' . BASE . ADMIN . 'search' . URL_ACT . 'tags' . URL_AID . @$tags[0] . URL_RW . '">' . $tag_name . '</a>';
				}
				else{
					$notification .= $tag_name;
				}
				$notification .= '&#8221;.';
				$alkaline->addNotification($notification, 'success');
			}
		}
		elseif($act == 'pile_add'){
			$pile = $alkaline->getRow('piles', $_POST['act_pile_id']);
		
			if(!empty($pile['pile_photos'])){
				$pile_photos = explode(', ', $pile['pile_photos']);
				$pile_photos = array_merge($pile_photos, $photo_ids);
				$pile_photos = array_unique($pile_photos);
			}
			else{
				$pile_photos = $photo_ids;
			}
		
			$pile_photo_count = count($pile_photos);
			$pile_photos = implode(', ', $pile_photos);
		
			$fields = array('pile_photos' => $pile_photos,
				'pile_photo_count' => $pile_photo_count);
		
			$bool = $alkaline->updateRow($fields, 'piles', $_POST['act_pile_id']);
			if($bool === true){
				$alkaline->addNotification('You successfully added to the pile &#8220;<a href="' . BASE . ADMIN . 'search' . URL_ACT . 'piles' . URL_AID . @$pile['pile_id'] . URL_RW . '">' . $pile['pile_title'] . '</a>&#8221;.', 'success');
			}
		}
		elseif($act == 'pile_remove'){
			$pile = $alkaline->getRow('piles', $_POST['act_pile_id']);
		
			if(!empty($pile['pile_photos'])){
				$pile_photos = explode(', ', $pile['pile_photos']);
				foreach($photo_ids as $photo){
					$key = array_search($photo, $pile_photos, false);
					if($key !== false){
						unset($pile_photos[$key]);
					}
				}
				$pile_photos = array_merge($pile_photos);
				$pile_photos = array_unique($pile_photos);
			}
			else{
				$pile_photos = array();
			}
		
			$pile_photo_count = count($pile_photos);
			$pile_photos = implode(', ', $pile_photos);
		
			$fields = array('pile_photos' => $pile_photos,
				'pile_photo_count' => $pile_photo_count);
		
			$bool = $alkaline->updateRow($fields, 'piles', $_POST['act_pile_id']);
			if($bool === true){
				$alkaline->addNotification('You successfully removed from the pile &#8220;<a href="' . BASE . ADMIN . 'search' . URL_ACT . 'piles' . URL_AID . $pile['pile_id'] . URL_RW . '">' . $pile['pile_title'] . '</a>&#8221;.', 'success');
			}
		}
		elseif($act == 'right'){
			$right_id = intval($_POST['act_right_id']);
			if($right_id > 0){
				$photos = new Photo($photo_ids);
				$bool = $photos->updateFields(array('right_id' => $right_id));
				if($bool === true){
					$alkaline->addNotification('You successfully changed rights sets.', 'success');
				}
			}
		}
		elseif($act == 'privacy'){
			$privacy_id = intval($_POST['act_privacy_id']);
			if($privacy_id > 0){
				$photos = new Photo($photo_ids);
				$bool = $photos->updateFields(array('photo_privacy' => $privacy_id));
				if($bool === true){
					$alkaline->addNotification('You successfully changed privacy levels.', 'success');
				}
			}
		}
		elseif($act == 'publish'){
			$photos = new Photo($photo_ids);
			$now = date('Y-m-d H:i:s');
			$time = time();
			foreach($photos->photos as $photo){
				if(empty($photo['photo_published']) or (strtotime($photo['photo_published']) > $time)){
					$bool = $alkaline->updateRow(array('photo_published' => $now), 'photos', $photo['photo_id'], false);
				}
				else{
					$bool = true;
				}
			}
			if($bool === true){
				$alkaline->addNotification('The photos were succesfully published.', 'success');
			}
		}
	}
	
	$selected_photo_ids = $photo_ids;
}

// Preference: page_limit
if(!$max = $user->returnPref('page_limit')){
	$max = 100;
}

$photo_ids = new Find(@$_SESSION['alkaline']['search']['results']);
$photo_ids->page(null, $max);
$photo_ids->find();

$photos = new Photo($photo_ids->photo_ids);
$photos->getImgUrl('square');

define('TAB', 'features');
define('TITLE', 'Alkaline Features');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div class="span-24 last">
	<div class="span-18 colborder">
		<div class="actions">
			<a href="#select_all" id="select_all">Select all</a> <a href="#deselect_all" id="deselect_all">Deselect all</a>
		</div>
		
		<h1>Editor (<span id="photo_count_selected">0</span> of <?php echo number_format($photo_ids->photo_count); ?>)</h1>
		
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
				</select>
				<input type="text" class="s" id="act_tag_name" name="act_tag_name" />
				<?php echo $alkaline->showPiles('act_pile_id', true); ?>
				<?php echo $alkaline->showRights('act_right_id'); ?>
				<?php echo $alkaline->showPrivacy('act_privacy_id'); ?>
				<input type="hidden" name="photo_ids" id="photo_ids" value="" />
				<input name="do" type="submit" value="Do" />
			</p>
		</form>
		
		<p>
			<?php

			foreach($photos->photos as $photo){
				$selected = '';
				if(!empty($selected_photo_ids)){
					if(@in_array($photo['photo_id'], $selected_photo_ids)){
						$selected = '_selected';
					}
				}
				elseif(!empty($_SESSION['alkaline']['search']['results'])){
					$selected = '_selected';
				}
				?>
				<img src="<?php echo $photo['photo_src_square']; ?>" alt="" id="photo-<?php echo $photo['photo_id']; ?>" title="<?php echo $photo['photo_title']; ?>" class="frame<?php echo $selected; ?>" />
				<?php
			}
			?>
		</p>
		<?php
		if($photo_ids->page_count > 1){
			?>
			<p>
				<?php
				if(!empty($photo_ids->page_previous)){
					for($i = 1; $i <= $photo_ids->page_previous; ++$i){
						echo '<a href="' . BASE . ADMIN . 'features' . URL_PAGE . $i . URL_RW . '" class="page_no">' . number_format($i) . '</a>';
					}
				}
				?>
				<span class="page_no">Page <?php echo $photo_ids->page; ?> of <?php echo $photo_ids->page_count; ?></span>
				<?php
				if(!empty($photo_ids->page_next)){
					for($i = $photo_ids->page_next; $i <= $photo_ids->page_count; ++$i){
						echo '<a href="' . BASE . ADMIN . 'features' . URL_PAGE . $i . URL_RW . '" class="page_no">' . number_format($i) . '</a>';
					}
				}
				?>
			</p>
			<?php
		}
		?>
		<p class="quiet">
			<em>Tip: Hold down the Shift key to select a series of photos.</em>
		</p>
	</div>
	<div class="span-5 last">
		<h2><a href="<?php echo BASE . ADMIN; ?>tags<?php echo URL_CAP; ?>"><img src="/images/icons/tags.png" alt="" /> Tags &#9656;</a></h2>
		<h2><a href="<?php echo BASE . ADMIN; ?>piles<?php echo URL_CAP; ?>"><img src="/images/icons/piles.png" alt="" /> Piles &#9656;</a></h2>
		<h2><a href="<?php echo BASE . ADMIN; ?>comments<?php echo URL_CAP; ?>"><img src="/images/icons/comments.png" alt="" /> Comments &#9656;</a></h2>
		<h2><a href="<?php echo BASE . ADMIN; ?>pages<?php echo URL_CAP; ?>"><img src="/images/icons/pages.png" alt="" /> Pages &#9656;</a></h2>
		<h2><a href="<?php echo BASE . ADMIN; ?>rights<?php echo URL_CAP; ?>"><img src="/images/icons/rights.png" alt="" /> Rights &#9656;</a></h2>
	</div>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>