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

$user->perm(true, 'images');

// GET PHOTO
if(!$image_id = $alkaline->findID($_GET['id'])){
	header('Location: ' . LOCATION . BASE . ADMIN . 'library' . URL_CAP);
	exit();
}

// SAVE CHANGES
if(!empty($_POST['image_id'])){
	if(!$image_id = $alkaline->findID($_POST['image_id'])){
		header('Location: ' . LOCATION . BASE . ADMIN . 'library' . URL_CAP);
		exit();
	}
	
	$images = new Image($image_id);
	
	if(!empty($_POST['image_send']) and ($_POST['image_send'] == 'send')){
		$orbit->hook('send_' . $_POST['image_send_service'] . '_image', $images->images, null);
	}
	elseif(@$_POST['image_delete'] == 'delete'){
		if($images->delete()){
			$alkaline->addNote('The image has been deleted.', 'success');
		}
	}
	elseif(@$_POST['image_recover'] == 'recover'){
		if($images->recover()){
			$alkaline->addNote('The image has been recovered.', 'success');
		}
	}
	else{
		$image_title = trim($_POST['image_title']);
		
		$image_description_raw = @$_POST['image_description_raw'];
		$image_description = $image_description_raw;
		
		// Configuration: image_markup
		if(!empty($_POST['image_markup'])){
			$image_markup_ext = $_POST['image_markup'];
			$image_title = $orbit->hook('markup_title_' . $image_markup_ext, $image_title, $image_title);
			$image_description = $orbit->hook('markup_' . $image_markup_ext, $image_description_raw, $image_description_raw);
		}
		elseif($alkaline->returnConf('web_markup')){
			$image_markup_ext = $alkaline->returnConf('web_markup_ext');
			$image_title = $orbit->hook('markup_title_' . $image_markup_ext, $image_title, $image_title);
			$image_description = $orbit->hook('markup_' . $image_markup_ext, $image_description_raw, $image_description_raw);
		}
		else{
			$image_markup_ext = '';
			$image_description = $alkaline->nl2br($image_description_raw);
		}
		
		if(@$_POST['image_comment_disabled'] == 'disabled'){
			$image_comment_disabled = 1;
		}
		else{
			$image_comment_disabled = 0;
		}
		
		$fields = array('image_title' => $image_title,
			'image_description' => $image_description,
			'image_description_raw' => $image_description_raw,
			'image_markup' => $image_markup_ext,
			'image_geo' => @$_POST['image_geo'],
			'image_published' => @$_POST['image_published'],
			'image_privacy' => @$_POST['image_privacy'],
			'image_comment_disabled' => $image_comment_disabled,
			'right_id' => @$_POST['right_id']);
		
		$images->updateFields($fields);
		$images->updateTags(json_decode($_POST['image_tags_input']));
	}
	
	if(!empty($_REQUEST['go'])){
		$image_ids = new Find('images');
		$image_ids->memory();
		$image_ids->with($image_id);
		$image_ids->offset(1);
		$image_ids->page(null, 1);
		$image_ids->find();
		if($_REQUEST['go'] == 'next'){
			$_SESSION['alkaline']['go'] = 'next';
			header('Location: ' . LOCATION . BASE . ADMIN . 'images' . URL_ID . $image_ids->ids_after[0] . URL_CAP);
		}
		else{
			$_SESSION['alkaline']['go'] = 'previous';
			header('Location: ' . LOCATION . BASE . ADMIN . 'images' . URL_ID . $image_ids->ids_before[0] . URL_CAP);
		}
		exit();
	}
	else{
		$alkaline->callback();
	}
}

$images = new Image($image_id);
$sizes = $images->getSizes();
$images->getTags(true);
$images->getRelated();
$images->related->getSizes('square');
$images->getColorkey(300, 40);
$comments = $images->getComments();
$exifs = $images->getEXIF();

if(!$image = @$images->images[0]){
	$alkaline->addNote('The image you requested could not be found.', 'error');
	$alkaline->callback();
}

$comment_count = count($comments);
if($comment_count > 0){
	$comment_action = '<a href="' . BASE . ADMIN . 'comments' . URL_CAP . '?image=' . $image['image_id'] . '"><button>View ' . $alkaline->returnCount($comment_count, 'comment') . ' (' . $comment_count . ')</button></a>';
}
else{
	$comment_action = '';
}

$now = time();
$published = strtotime($image['image_published']);
if($published <= $now){
	$launch_action = '<a href="' . BASE . 'image' . URL_ID . $image['image_id'] . URL_RW . '"><button>Launch image</button></a>';
}
else{
	$launch_action = '';
}

$image_colorkey = $image['image_colorkey'];
$image = $alkaline->makeHTMLSafe($image);

define('TAB', 'library');
if(!empty($image['image_title'])){	
	define('TITLE', 'Alkaline Image: &#8220;' . $image['image_title']  . '&#8221;');
}
else{
	define('TITLE', 'Alkaline Image');
}
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div class="actions">
	<a href="<?php echo BASE . ADMIN . 'tasks/download-image.php?id=' . $image['image_id']; ?>"><button>Download original</button></a>
	<?php echo $comment_action; ?>
	<?php echo $launch_action; ?>
</div>

<?php

if(empty($image['image_title'])){
	echo '<h1><img src="' . BASE . ADMIN . 'images/icons/images.png" alt="" /> Image</h1>';
}
else{
	echo '<h1><img src="' . BASE . ADMIN . 'images/icons/images.png" alt="" /> Image: ' . $image['image_title'] . '</h1>';
}

?>
<div class="span-24 last">
	<form action="" method="post">
		<div class="span-15 append-1">
			<img src="<?php echo $image['image_src_admin']; ?>" alt="" />
			<input type="text" id="image_title" name="image_title" placeholder="Title" value="<?php echo $image['image_title']; ?>" class="title bottom-border" />
			<textarea id="image_description_raw" name="image_description_raw" placeholder="Description" class="<?php if($user->returnPref('text_code')){ echo $user->returnPref('text_code_class'); } ?>"><?php echo $image['image_description_raw']; ?></textarea>
		</div>
		<div class="span-8 last">
			<div class="image_tag_container">
				<label for="image_tag">Tags:</label><br />
				<input type="text" id="image_tag" name="image_tag" class="image_tag" style="width: 40%;" /> <input type="submit" id="image_tag_add" class="image_tag_add" value="Add" /><br />
				<div id="image_tags" class="image_tags"></div>
				<div id="image_tags_load" class="image_tags_load none"><?php $tags = array(); foreach($images->tags as $tag){ $tags[] = $tag['tag_name']; } echo json_encode($tags); ?></div>
				<input type="hidden" name="image_tags_input" id="image_tags_input" class="image_tags_input" value="" />
			</div>
			<br />
			
			<p>
				<label for="image_geo">Location:</label><br />
				<input type="text" id="image_geo" name="image_geo" class="image_geo get_location_result l" value="<?php echo $image['image_geo']; ?>" />&#0160;
				<a href="#get_location" class="get_location"><img src="<?php echo BASE . ADMIN; ?>images/icons/location.png" alt="" style="vertical-align: middle;" /></a>
				<?php
				
				if(!empty($image['image_geo_lat'])){
					?>
					<br />
					<img src="<?php echo BASE . ADMIN; ?>images/icons/geo.png" alt="" /> <?php echo round($image['image_geo_lat'], 5); ?>, <?php echo round($image['image_geo_long'], 5); ?>
					<?php
				}
				?>
				<div class="none get_location_set"><?php echo @$_SESSION['alkaline']['location']; ?></div>
			</p>
			
			<p>
				<label for="image_published">Publish date:</label><br />
				<input type="text" id="image_published" name="image_published" placeholder="Unpublished" value="<?php echo $alkaline->formatTime($image['image_published']); ?>" />
			</p>
			
			<p>
				<label for="image_privacy">Privacy level:</label><br />
				<?php echo $alkaline->showPrivacy('image_privacy', $image['image_privacy']); ?>
			</p>
			
			<p>
				<label for="right_id">Rights set:</label><br />
				<?php echo $alkaline->showRights('right_id', $image['right_id']); ?>
			</p>
			
			<?php if(!empty($image_colorkey)){ ?>
			<p class="slim"><span class="switch">&#9656;</span> <a href="#" class="show">Show color palette</a></p>
			
			<div class="reveal">
				<?php echo $image_colorkey; ?>
				
				<ul>
					<li>Export: <a href="<?php echo BASE . ADMIN . 'tasks/export-palette.php?image_id=' . $image['image_id'] . '&format=ase'; ?>" title="Adobe Swatch Exchange">ASE</a>, <a href="<?php echo BASE . ADMIN . 'tasks/export-palette.php?image_id=' . $image['image_id'] . '&format=css'; ?>" title="Cascading Style Sheets">CSS</a>, <a href="<?php echo BASE . ADMIN . 'tasks/export-palette.php?image_id=' . $image['image_id'] . '&format=gpl'; ?>" title="GIMP Palette">GPL</a></li>
				</ul>
			</div>
			<?php } ?>
			
			<p class="slim"><span class="switch">&#9656;</span> <a href="#" class="show">Show image files</a></p>
			
			<div class="reveal">
				<ul>
					<li><a href="<?php echo $image['image_src']; ?>">Original</a> <span class="quiet">(<?php echo $image['image_width']; ?> &#0215; <?php echo $image['image_height']; ?>)</span></li>
					<?php

					foreach($sizes as $size){
						echo '<li><a href="' . $size['size_src'] . '">' . $size['size_title'] . '</a> <span class="quiet">(' . $size['size_width'] . ' &#0215; ' . $size['size_height'] . ')</span></li>';
					}

					?>
				</ul>
			</div>
			
			<?php
			
			if(count($exifs) > 0){
				echo '<p><span class="switch">&#9656;</span> <a href="#" class="show">Show EXIF data</a></p>';
				echo '<div class="reveal"><ul>' . "\n";
				foreach($exifs as $exif){
					$value = @unserialize(stripslashes($exif['exif_value']));
					if(!is_array($value)){
						echo '<li><strong>' . ucwords(strtolower($exif['exif_key'])) . '_' . $exif['exif_name'] . ':</strong><br />' . $value . '</li>' . "\n";
					}
				}
				echo '</ul></div>';
			}
			
			?>
			
			<hr />
			
			<table>
				<tr>
					<td class="right" style="width: 5%"><input type="checkbox" id="image_send" name="image_send" value="send" /></td>
					<td>
						<label for="image_send">
							Send to
							<select id="image_send_service" name="image_send_service">
								<?php $orbit->hook('send_html_image'); ?>
							</select>.
						</label>
					</td>
				</tr>
				<?php if($alkaline->returnConf('comm_enabled')){ ?>
				<tr>
					<td class="right" style="width: 5%"><input type="checkbox" id="image_comment_disabled" name="image_comment_disabled" value="disabled" <?php if($image['image_comment_disabled'] == 1){ echo 'checked="checked"'; } ?> /></td>
					<td>
						<strong><label for="image_comment_disabled">Disable comments on this image.</label></strong>
					</td>
				</tr>
				<?php } ?>
				<?php if(empty($image['image_deleted'])){ ?>
				<tr>
					<td class="right" style="width: 5%"><input type="checkbox" id="image_delete" name="image_delete" value="delete" /></td>
					<td>
						<strong><label for="image_delete">Delete this image.</label></strong>
					</td>
				</tr>
				<?php } else{ ?>
				<tr>
					<td class="right" style="width: 5%"><input type="checkbox" id="image_recover" name="image_recover" value="recover" /></td>
					<td>
						<strong><label for="image_recover">Recover this image.</label></strong>
					</td>
				</tr>
				<?php } ?>
			</table>
		</div>
		
		<div class="span-24 last">
			<p>
				<span class="switch">&#9656;</span> <a href="#" class="show">Display related images</a> <span class="quiet">(<?php echo $images->related->image_count; ?>)</span>
			</p>
			<div class="reveal">
				<?php
				
				foreach($images->related->images as $related_image){
					?>
					<a href="<?php echo BASE . ADMIN . 'image' . URL_ID . $related_image['image_id'] . URL_RW; ?>" class="nu">
						<img src="<?php echo $related_image['image_src_square']; ?>" alt="" title="<?php echo $related_image['image_title']; ?>" class="frame tip" />
					</a>
					<?php
				}

				?><br /><br />
			</div>
			<p>
				<input type="hidden" id="image_markup" name="image_markup" value="<?php echo $image['image_markup']; ?>" />
				<input type="hidden" name="image_id" value="<?php echo $image['image_id']; ?>" /><input type="submit" value="Save changes" class="autosave_delete" />
				and
				<select name="go">
					<option value="">return to previous screen</option>
					<option value="next" <?php echo $alkaline->readForm($_SESSION['alkaline'], 'go', 'next'); ?>>go to next image</option>
					<option value="previous" <?php echo $alkaline->readForm($_SESSION['alkaline'], 'go', 'previous'); ?>>go to previous image</option>
				</select>
				or <a href="<?php echo $alkaline->back(); ?>" class="autosave_delete">cancel</a>
			</p>
		</div>
	</form>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>