<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

if(!empty($_GET['id'])){
	$_GET['page'] = $_GET['id'];
}

// Process actions
if(@$_POST['do'] == 'Do'){
	$act = $_POST['act'];
	$photo_ids = $alkaline->convertToIntegerArray($_POST['photo_ids']);
	
	if(count($photo_ids) > 0){	
		if($act == 'tag_add'){
			$photos = new Photo($photo_ids);
			$photos->addTags(array($_POST['act_tag_name']));
		}
		elseif($act == 'tag_remove'){
			$photos = new Photo($photo_ids);
			$photos->removeTags(array($_POST['act_tag_name']));
		
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
		
			$alkaline->updateRow($fields, 'piles', $_POST['act_pile_id']);
		}
		elseif($act == 'right'){
			$right_id = intval($_POST['act_right_id']);
			if($right_id > 0){
				$photos = new Photo($photo_ids);
				$photos->updateFields(array('right_id' => $right_id));
			}
		}
		elseif($act == 'privacy'){
			$privacy_id = intval($_POST['act_privacy_id']);
			if($privacy_id > 0){
				$photos = new Photo($photo_ids);
				$photos->updateFields(array('photo_privacy' => $privacy_id));
			}
		}
		elseif($act == 'publish'){
			$photos = new Photo($photo_ids);
			$now = date('Y-m-d H:i:s');
			$time = time();
			foreach($photos->photos as $photo){
				if(empty($photo['photo_published']) or (strtotime($photo['photo_published']) > $time)){
					$alkaline->updateRow(array('photo_published' => $now), 'photos', $photo['photo_id'], false);
				}
			}
		}
	}
	
	$selected_photo_ids = $photo_ids;
}

$photo_ids = new Find();
$photo_ids->page(null, 100);
$photo_ids->find();

$photos = new Photo($photo_ids->photo_ids);
$photos->getImgUrl('square');

define('TAB', 'features');
define('TITLE', 'Alkaline Features');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div class="span-24 last">
	<div class="span-5 colborderr">
		<h2><a href="<?php echo BASE . ADMIN; ?>tags<?php echo URL_CAP; ?>"><img src="/images/icons/tags.png" alt="" /> Tags &#9656;</a></h2>
		<h2><a href="<?php echo BASE . ADMIN; ?>piles<?php echo URL_CAP; ?>"><img src="/images/icons/piles.png" alt="" /> Piles &#9656;</a></h2>
		<h2><a href="<?php echo BASE . ADMIN; ?>comments<?php echo URL_CAP; ?>"><img src="/images/icons/comments.png" alt="" /> Comments &#9656;</a></h2>
		<h2><a href="<?php echo BASE . ADMIN; ?>pages<?php echo URL_CAP; ?>"><img src="/images/icons/pages.png" alt="" /> Pages &#9656;</a></h2>
		<h2><a href="<?php echo BASE . ADMIN; ?>rights<?php echo URL_CAP; ?>"><img src="/images/icons/rights.png" alt="" /> Rights &#9656;</a></h2>
	</div>
	<div class="span-18 colborderl last">
		<div class="actions">
			<form action="" method="post">
				<select name="act" id="act">
					<option value="tag_add">Add tag</option>
					<option value="tag_remove">Remove tag</option>
					<option value="pile_add">Add to static pile</option>
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
			</form>
		</div>
		
		<h1>Editor (<span id="photo_count_selected">0</span> of <?php echo number_format($photo_ids->photo_count); ?>)</h1>
		
		<p>
			<a href="#select_all" id="select_all">Select all</a> &#0183; <a href="#deselect_all" id="deselect_all">Deselect all</a>
		</p>
		
		<p>
			<?php

			foreach($photos->photos as $photo){
				$selected = '';
				if(@in_array($photo['photo_id'], $selected_photo_ids)){
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
			<em>Tip: Hold down the Shift key to select a series multiple photos.</em>
		</p>
	</div>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>