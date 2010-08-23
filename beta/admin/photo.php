<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

// GET PHOTO
if(!$photo_id = $alkaline->findID($_GET['id'])){
	header('Location: ' . LOCATION . BASE . ADMIN . 'library/');
	exit();
}

// SAVE CHANGES
if(!empty($_POST['photo_id'])){
	$photos = new Photo($photo_id);
	$photo_id = $alkaline->findID($_POST['photo_id']);
	if(@$_POST['photo_delete'] == 'delete'){
		$photos->delete();
	}
	else{
		$fields = array('photo_title' => @$_POST['photo_title'],
			'photo_description' => @$_POST['photo_description'],
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
$photos->getImgUrl('admin');
$photos->getTags();

$photo = $photos->photos[0];

// Set title
if(!empty($photo['photo_title'])){	
	define('TITLE', 'Alkaline Photo: &#8220;' . $photo['photo_title']  . '&#8221;');
}
else{
	define('TITLE', 'Alkaline Photo');
}
require_once(PATH . ADMIN . 'includes/header.php');
require_once(PATH . ADMIN . 'includes/library.php');

?>

<p><img src="<?php echo $photo['photo_src_admin']; ?>" alt="" /></p>

<form action="" method="post">
	<p>
		<input type="text" id="photo_title" name="photo_title" value="<?php echo $photo['photo_title']; ?>" class="title bottom-border" />
		<textarea id="photo_description" name="photo_description"><?php echo $photo['photo_description']; ?></textarea>
	</p>
	<table>
		<tr>
			<td class="right pad"><label for="photo_tag">Tags:</label></td>
			<td>
				<input type="text" id="photo_tag" name="photo_tag" style="width: 40%;" /> <input type="submit" id="photo_tag_add" value="Add" /><br />
				<div id="photo_tags"><?php $tags = array(); foreach($photos->tags as $tag){ $tags[] = $tag['tag_name']; } echo json_encode($tags);  ?></div>
				<input type="hidden" name="photo_tags_input" id="photo_tags_input" value="" />
			</td>
		</tr>
		<tr>
			<td class="right middle"><label for="">Location:</label></td>
			<td><input type="text" id="photo_geo" name="photo_geo" value="<?php echo $photo['photo_geo']; ?>" /></td>
		</tr>
		<tr>
			<td class="right middle"><label for="">Publish date:</label></td>
			<td><input type="text" id="photo_published" name="photo_published" value="<?php echo $alkaline->formatTime($photo['photo_published']); ?>" /></td>
		</tr>
		<tr>
			<td class="right middle"><label for="">Privacy level:</label></td>
			<td><?php echo $alkaline->showPrivacy('photo_privacy', $photo['photo_privacy']); ?></td>
		</tr>
		<tr>
			<td class="right middle"><label for="">Rights set:</label></td>
			<td><?php echo $alkaline->showRights('right_id', $photo['right_id']); ?></td>
		</tr>
		<tr>
			<td class="right"><input type="checkbox" id="photo_delete" name="photo_delete" value="delete" /></td>
			<td><strong><label for="photo_delete">Delete this photo.</label></strong> This action cannot be undone.</td>
		</tr>
		<tr>
			<td></td>
			<td><input type="hidden" name="photo_id" value="<?php echo $photo['photo_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo BASE . ADMIN; ?>library/">cancel</a></td>
		</tr>
	</table>
</form>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>