<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$size_id = $alkaline->findID(@$_GET['id']);
$size_act = @$_GET['act'];

// SAVE CHANGES
if(!empty($_POST['size_id'])){
	$size_id = $alkaline->findID($_POST['size_id']);
	
	// Delete size
	if(@$_POST['size_delete'] == 'delete'){
		$alkaline->deleteRow('sizes', $size_id);
	}
	
	// Update size
	else{
		// Check for file append, prepend duplicates--will overwrite
		$query = $alkaline->prepare('SELECT size_title FROM sizes WHERE size_append = :size_append AND size_prepend = :size_prepend AND size_id != ' . $size_id);
		$query->execute(array(':size_append' => @$_POST['size_append'], ':size_prepend' => @$_POST['size_prepend']));
		$sizes = $query->fetchAll();
		
		if(count($sizes) > 0){
			$size_title = $sizes[0]['size_title'];
			$alkaline->addNotification('The thumbnail &#8220;' . $size_title . '&#8221; already uses these prepend and append to filename settings.', 'error');
		}
		else{
			if(@$_POST['size_watermark'] == 'watermark'){
				$size_watermark = 1;
			}
			else{
				$size_watermark = 0;
			}
		
			$fields = array('size_title' => $alkaline->makeUnicode($_POST['size_title']),
				'size_label' => preg_replace('#[^a-z]#si', '', $alkaline->makeUnicode($_POST['size_label'])),
				'size_height' => $_POST['size_height'],
				'size_width' => $_POST['size_width'],
				'size_type' => $_POST['size_type'],
				'size_append' => @$_POST['size_append'],
				'size_prepend' => @$_POST['size_prepend'],
				'size_watermark' => $size_watermark);
		
			$alkaline->updateRow($fields, 'sizes', $size_id);
		}
	}
	
	// Build size
	if((@$_POST['size_build'] == 'build') and !$alkaline->isNotification('error')){
		// Store to build thumbnails
		$_SESSION['alkaline']['maintenance']['size_id'] = $size_id;
		
		sleep(1);
		
		header('Location: ' . LOCATION . BASE . ADMIN . 'maintenance' . URL_CAP . '#build-thumbnail');
		exit();
	}
	
	if(!$alkaline->isNotification('error')){
		unset($size_id);
	}
}
else{
	$alkaline->deleteEmptyRow('sizes', array('size_title'));
}

// CREATE SIZE
if($size_act == 'build'){
	$size_id = $alkaline->addRow(null, 'sizes');
}

define('TAB', 'settings');

// GET SIZES TO VIEW OR SIZE TO EDIT
if(empty($size_id)){
	$sizes = $alkaline->getTable('sizes');
	$size_count = @count($sizes);
	
	define('TITLE', 'Alkaline Thumbnails');
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<div class="actions"><a href="<?php echo BASE . ADMIN; ?>thumbnails<?php echo URL_ACT; ?>build<?php echo URL_RW; ?>">Build thumbnial</a></div>

	<h1>Thumbnails (<?php echo $size_count; ?>)</h1>

	<table>
		<tr>
			<th>Title</th>
			<th class="center">Dimensions</th>
			<th class="center">Type</th>
			<th class="center">Canvas Markup</th>
		</tr>
		<?php
	
		foreach($sizes as $size){
			echo '<tr>';
				echo '<td><strong><a href="' . BASE . ADMIN . 'thumbnails' . URL_ID . $size['size_id'] . URL_RW . '">' . $size['size_title'] . '</a></strong></td>';
				echo '<td class="center">' . $size['size_width'] . ' &#0215; ' . $size['size_height'] . '</td>';
				echo '<td class="center">' . ucwords($size['size_type']) . '</a></td>';
				echo '<td class="center">{Photo_Src_' . ucwords($size['size_label']) . '}</td>';
			echo '</tr>';
		}
	
		?>
	</table>

	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}
else{
	// Get sizes set
	$size = $alkaline->getRow('sizes', $size_id);
	$size = $alkaline->makeHTMLSafe($size);
	
	// Dashboard thumbnail warning
	if(($size['size_label'] == 'admin') or ($size['size_label'] == 'square')){
		$alkaline->addNotification('This thumbnail is crucial to the proper functioning of your dashboard. Modify at your own risk.', 'notice');
	}
	
	if(!empty($size['size_title'])){	
		define('TITLE', 'Alkaline Thumbnail: &#8220;' . ucwords($size['size_title'])  . '&#8221;');
	}
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<h1>Thumbnail</h1>
	
	<p>All fields are required except append to and prepend to filename&#8212;use one or both.</p>
	
	<form action="<?php echo BASE . ADMIN; ?>thumbnails<?php echo URL_CAP; ?>" method="post">
		<table>
			<tr>
				<td class="right middle"><label for="size_title">Title:</label></td>
				<td><input type="text" id="size_title" name="size_title" value="<?php echo $size['size_title']; ?>" class="m" /></td>
			</tr>
			<tr>
				<td class="right pad"><label for="size_label">Label:</label></td>
				<td>
					<input type="text" id="size_label" name="size_label" value="<?php echo @$size['size_label']; ?>" class="s" />
				</td>
			</tr>
			<tr>
				<td class="right middle"><label>Dimensions:</label></td>
				<td><input type="text" id="size_width" name="size_width" value="<?php echo $size['size_width']; ?>" style="width: 4em;" /> pixels (width) &#0215; <input type="text" id="size_height" name="size_height" value="<?php echo $size['size_height']; ?>" style="width: 4em;" /> pixels (height)</td>
			</tr>
			<tr>
				<td class="right"><label>Type:</label></td>
				<td>
					<input type="radio" name="size_type" value="scale" id="size_type_scale" <?php if(($size['size_type'] == 'scale') or (empty($size['size_type']))){ echo 'checked="checked" '; } ?> /> <label for="size_type_scale">Scale image</label><br />
					&#0160;&#0160;&#0160;&#0160;&#0160;&#0160; Scales to the restricting dimension&#8212;&#8220;normal&#8221; thumbnails<br />
					<input type="radio" name="size_type" value="fill" id="size_type_fill" <?php if($size['size_type'] == 'fill'){ echo 'checked="checked" '; } ?> /> <label for="size_type_fill">Fill canvas</label><br />
					&#0160;&#0160;&#0160;&#0160;&#0160;&#0160; Fills the thumbnail, crops excess&#8212;good for arranging in grids
				</td>
			</tr>
			<tr>
				<td class="right pad"><label for="size_append">Append to filename:</label></td>
				<td>
					<input type="text" id="size_append" name="size_append" value="<?php echo @$size['size_append']; ?>" style="width: 5em;" /><br />
				</td>
			</tr>
			<tr>
				<td class="right pad"><label for="size_prepend">Prepend to filename:</label></td>
				<td>
					<input type="text" id="size_prepend" name="size_prepend" value="<?php echo @$size['size_prepend']; ?>" style="width: 5em;" />
				</td>
			</tr>
			<tr>
				<td class="right"><input type="checkbox" id="size_watermark" name="size_watermark" value="watermark" <?php if($size['size_watermark'] == 1){ echo 'checked="checked"'; } ?> /></td>
				<td><strong><label for="size_watermark">Apply watermark to this thumbnail size.</label></strong></td>
			</tr>
			<tr>
				<td class="right"><input type="checkbox" id="size_build" name="size_build" value="build" <?php if(empty($size['size_title'])){ echo 'checked="checked"'; } ?> /></td>
				<td><strong><label for="size_build">Build thumbnails of this size.</label></strong> This action cannot be undone.</td>
			</tr>
			<tr>
				<td class="right"><input type="checkbox" id="size_delete" name="size_delete" value="delete" <?php if(@$size_lock === true){ echo 'disabled="disabled"'; } ?> /></td>
				<td><strong><label for="size_delete">Delete this thumbnail size.</label></strong> This action cannot be undone.</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="hidden" name="size_id" value="<?php echo $size['size_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo $alkaline->back(); ?>">cancel</a></td>
			</tr>
		</table>
	</form>

	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}

?>