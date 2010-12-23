<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$orbit = new Orbit;
$user = new User;

$user->perm(true);

// GET PHOTO
if(!$photo_id = $alkaline->findID($_GET['id'])){
	header('Location: ' . LOCATION . BASE . ADMIN . 'library' . URL_CAP);
	exit();
}

// SAVE CHANGES
if(!empty($_POST['photo_id'])){
	$photos = new Photo($photo_id);
	$photo_id = $alkaline->findID($_POST['photo_id']);
	if(@$_POST['photo_delete'] == 'delete'){
		if($photos->delete()){
			$alkaline->addNotification('Your photo has been deleted.', 'success');
		}

	}
	else{
		$photo_description_raw = @$_POST['photo_description_raw'];
		
		// Configuration: photo_markup
		if(!empty($_POST['photo_markup'])){
			$photo_markup_ext = $_POST['photo_markup'];
			$photo_description = $orbit->hook('markup_' . $photo_markup_ext, $photo_description_raw, $photo_description);
		}
		elseif($alkaline->returnConf('photo_markup')){
			$photo_markup_ext = $alkaline->returnConf('photo_markup_ext');
			$photo_description = $orbit->hook('markup_' . $photo_markup_ext, $photo_description_raw, $photo_description);
		}
		else{
			$photo_markup_ext = '';
			$photo_description = nl2br($photo_description_raw);
		}
		
		$fields = array('photo_title' => @$_POST['photo_title'],
			'photo_description' => $photo_description,
			'photo_description_raw' => $photo_description_raw,
			'photo_markup' => $photo_markup_ext,
			'photo_geo' => @$_POST['photo_geo'],
			'photo_published' => @$_POST['photo_published'],
			'photo_privacy' => @$_POST['photo_privacy'],
			'right_id' => @$_POST['right_id']);
		
		$photos->updateFields($fields);
		$photos->updateTags(json_decode($_POST['photo_tags_input']));
	}
	
	$alkaline->callback();
}

$photos = new Photo($photo_id);
$sizes = $photos->getImgUrl();
$photos->getTags();

if(!$photo = @$photos->photos[0]){
	$alkaline->addNotification('The photo you requested could not be found.', 'error');
	$alkaline->callback();
}

$photo = $alkaline->makeHTMLSafe($photo);

define('TAB', 'library');
if(!empty($photo['photo_title'])){	
	define('TITLE', 'Alkaline Photo: &#8220;' . $photo['photo_title']  . '&#8221;');
}
else{
	define('TITLE', 'Alkaline Photo');
}
require_once(PATH . ADMIN . 'includes/header.php');

?>
<div class="span-24 last">
	<form action="" method="post">
		<div class="span-15 append-1">
			<img src="<?php echo $photo['photo_src_admin']; ?>" alt="" />
			<p>
				<input type="text" id="photo_title" name="photo_title" value="<?php echo $photo['photo_title']; ?>" class="title bottom-border" />
				<textarea id="photo_description_raw" name="photo_description_raw"><?php echo $photo['photo_description_raw']; ?></textarea>
				<input type="hidden" id="photo_markup" name="photo_markup" value="<?php echo $photo['photo_markup']; ?>" />
				<input type="hidden" name="photo_id" value="<?php echo $photo['photo_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo $alkaline->back(); ?>">cancel</a>
			</p>
		</div>
		<div class="span-8 last">
			<div class="actions"><a href="<?php echo BASE; ?>photo<?php echo URL_ID . $photo['photo_id'] . URL_RW; ?>">Go to photo</a></div>
			
			<div class="photo_tag_container">
				<label for="photo_tag">Tags:</label><br />
				<input type="text" id="photo_tag" name="photo_tag" class="photo_tag" style="width: 40%;" /> <input type="submit" id="photo_tag_add" class="photo_tag_add" value="Add" /><br />
				<div id="photo_tags" class="photo_tags"></div>
				<div id="photo_tags_load" class="photo_tags_load none"><?php $tags = array(); foreach($photos->tags as $tag){ $tags[] = $tag['tag_name']; } echo json_encode($tags); ?></div>
				<input type="hidden" name="photo_tags_input" id="photo_tags_input" class="photo_tags_input" value="" />
			</div>
			<br />
			
			<p>
				<label for="">Location:</label><br />
				<input type="text" id="photo_geo" name="photo_geo" class="photo_geo" value="<?php echo $photo['photo_geo']; ?>" />
				<?php
				
				if(!empty($photo['photo_geo_lat'])){
					?>
					<br />
					<img src="/images/icons/geo.png" alt="" /> <?php echo round($photo['photo_geo_lat'], 5); ?>, <?php echo round($photo['photo_geo_long'], 5); ?>
					<?php
				}
				?>
			</p>
			
			<p>
				<label for="">Publish date:</label><br />
				<input type="text" id="photo_published" name="photo_published" value="<?php echo $alkaline->formatTime($photo['photo_published']); ?>" />
			</p>
			
			<p>
				<label for="">Privacy level:</label><br />
				<?php echo $alkaline->showPrivacy('photo_privacy', $photo['photo_privacy']); ?>
			</p>
			
			<p>
				<label for="">Rights set:</label><br />
				<?php echo $alkaline->showRights('right_id', $photo['right_id']); ?>
			</p>
			
			<p class="slim"><span class="switch">&#9656;</span> <a href="#" class="show">Show image files</a></p>
			
			<div class="reveal">
				<ul>
					<li><a href="<?php echo $photo['photo_src']; ?>">Original</a> <span class="quiet">(<?php echo $photo['photo_width']; ?> &#0215; <?php echo $photo['photo_height']; ?>)</span></li>
					<?php

					foreach($sizes as $size){
						$field = 'photo_src_' . $size['size_label'];
						echo '<li><a href="' . $photo[$field] . '">' . $size['size_title'] . '</a></li>';
					}

					?>
				</ul>
			</div>
			
			<?php
			
			$exifs = $photos->getEXIF();
			
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
				<tr>
					<td class="right" style="width: 5%"><input type="checkbox" id="photo_delete" name="photo_delete" value="delete" /></td>
					<td>
						<strong><label for="photo_delete">Delete this image.</label></strong><br />
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