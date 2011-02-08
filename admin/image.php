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

$user->perm(true);

// GET PHOTO
if(!$image_id = $alkaline->findID($_GET['id'])){
	header('Location: ' . LOCATION . BASE . ADMIN . 'library' . URL_CAP);
	exit();
}

// SAVE CHANGES
if(!empty($_POST['image_id'])){
	$images = new Image($image_id);
	$image_id = $alkaline->findID($_POST['image_id']);
	if(@$_POST['image_delete'] == 'delete'){
		if($images->delete()){
			$alkaline->addNote('Your image has been deleted.', 'success');
		}

	}
	else{
		$image_description_raw = @$_POST['image_description_raw'];
		
		// Configuration: image_markup
		if(!empty($_POST['image_markup'])){
			$image_markup_ext = $_POST['image_markup'];
			$image_description = $orbit->hook('markup_' . $image_markup_ext, $image_description_raw, $image_description);
		}
		elseif($alkaline->returnConf('image_markup')){
			$image_markup_ext = $alkaline->returnConf('image_markup_ext');
			$image_description = $orbit->hook('markup_' . $image_markup_ext, $image_description_raw, $image_description);
		}
		else{
			$image_markup_ext = '';
			$image_description = nl2br($image_description_raw);
		}
		
		if(@$_POST['image_comment_disabled'] == 'disabled'){
			$image_comment_disabled = 1;
		}
		else{
			$image_comment_disabled = 0;
		}
		
		$fields = array('image_title' => @$_POST['image_title'],
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
	
	$alkaline->callback();
}

$images = new Image($image_id);
$sizes = $images->getSizes();
$images->getTags();
$images->getColorkey(300, 40);
$comments = $images->getComments();
$exifs = $images->getEXIF();

if(!$image = @$images->images[0]){
	$alkaline->addNote('The image you requested could not be found.', 'error');
	$alkaline->callback();
}

$comment_count = count($comments);
if($comment_count > 0){
	$comment_action = '<a href="' . BASE . ADMIN . 'comments' . URL_CAP . '?image=' . $image['image_id'] . '">View ' . $alkaline->returnCount($comment_count, 'comments') . ' (' . $comment_count . ')</a>';
}
else{
	$comment_action = '';
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
<div class="span-24 last">
	<form action="" method="post">
		<div class="span-15 append-1">
			<img src="<?php echo $image['image_src_admin']; ?>" alt="" />
			<p>
				<input type="text" id="image_title" name="image_title" value="<?php echo $image['image_title']; ?>" class="title bottom-border" />
				<textarea id="image_description_raw" name="image_description_raw"><?php echo $image['image_description_raw']; ?></textarea>
				<input type="hidden" id="image_markup" name="image_markup" value="<?php echo $image['image_markup']; ?>" />
				<input type="hidden" name="image_id" value="<?php echo $image['image_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo $alkaline->back(); ?>">cancel</a>
			</p>
		</div>
		<div class="span-8 last">
			<div class="actions"><a href="<?php echo BASE . 'image' . URL_ID . $image['image_id'] . URL_RW; ?>">Go to image</a><?php echo $comment_action; ?></div>
			
			<div class="image_tag_container">
				<label for="image_tag">Tags:</label><br />
				<input type="text" id="image_tag" name="image_tag" class="image_tag" style="width: 40%;" /> <input type="submit" id="image_tag_add" class="image_tag_add" value="Add" /><br />
				<div id="image_tags" class="image_tags"></div>
				<div id="image_tags_load" class="image_tags_load none"><?php $tags = array(); foreach($images->tags as $tag){ $tags[] = $tag['tag_name']; } echo json_encode($tags); ?></div>
				<input type="hidden" name="image_tags_input" id="image_tags_input" class="image_tags_input" value="" />
			</div>
			<br />
			
			<p>
				<label for="">Location:</label><br />
				<input type="text" id="image_geo" name="image_geo" class="image_geo" value="<?php echo $image['image_geo']; ?>" />
				<?php
				
				if(!empty($image['image_geo_lat'])){
					?>
					<br />
					<img src="<?php echo BASE . ADMIN; ?>images/icons/geo.png" alt="" /> <?php echo round($image['image_geo_lat'], 5); ?>, <?php echo round($image['image_geo_long'], 5); ?>
					<?php
				}
				?>
			</p>
			
			<p>
				<label for="">Publish date:</label><br />
				<input type="text" id="image_published" name="image_published" value="<?php echo $alkaline->formatTime($image['image_published']); ?>" />
			</p>
			
			<p>
				<label for="">Privacy level:</label><br />
				<?php echo $alkaline->showPrivacy('image_privacy', $image['image_privacy']); ?>
			</p>
			
			<p>
				<label for="">Rights set:</label><br />
				<?php echo $alkaline->showRights('right_id', $image['right_id']); ?>
			</p>
			
			<?php if(!empty($image_colorkey)){ ?>
			<p class="slim"><span class="switch">&#9656;</span> <a href="#" class="show">Show color palette</a></p>
			
			<div class="reveal">
				<?php echo $image_colorkey; ?>
			</div>
			<?php } ?>
			
			<p class="slim"><span class="switch">&#9656;</span> <a href="#" class="show">Show thumbnail files</a></p>
			
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
				echo '<div class="reveal"><table>' . "\n";
				foreach($exifs as $exif){
					$value = @unserialize(stripslashes($exif['exif_value']));
					if(!is_array($value)){
						echo '<tr><td class="right">' . $exif['exif_name'] . ':</td><td>' . $value . '</td></tr>' . "\n";
					}
				}
				echo '</table></div>';
			}
			
			?>
			
			<hr />
			
			<table>
				<?php if($alkaline->returnConf('comm_enabled')){ ?>
				<tr>
					<td class="right" style="width: 5%"><input type="checkbox" id="image_comment_disabled" name="image_comment_disabled" value="disabled" <?php if($image['image_comment_disabled'] == 1){ echo 'checked="checked"'; } ?> /></td>
					<td>
						<strong><label for="image_comment_disabled">Disable comments on this image.</label></strong>
					</td>
				</tr>
				<?php } ?>
				<tr>
					<td class="right" style="width: 5%"><input type="checkbox" id="image_delete" name="image_delete" value="delete" /></td>
					<td>
						<strong><label for="image_delete">Delete this image.</label></strong><br />
						This action cannot be undone.
					</td>
				</tr>
			</table>
		</div>
	</form>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>