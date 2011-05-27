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

$user->perm(true, 'tags');

if(!empty($_GET['id'])){
	$tag_id = $alkaline->findID($_GET['id']);
}

// SAVE CHANGES
if(!empty($_POST['tag_id'])){
	$tag_id = $alkaline->findID($_POST['tag_id']);
	$tag_name = $_POST['tag_name'];
	
	// Delete tags set
	if(@$_POST['tag_delete'] == 'delete'){
		$alkaline->exec('DELETE FROM links WHERE tag_id = ' . $tag_id);
		$alkaline->deleteRow('tags', $tag_id);
	}
	
	// Update tags set
	else{
		$query = $alkaline->prepare('SELECT tag_id FROM tags WHERE tag_name = :tag_name AND tag_id != ' . $tag_id);
		$query->execute(array(':tag_name' => $tag_name));
		$tags = $query->fetchAll();
		$tag = @$tags[0];
		
		// Tag parents
		$tag_parents = json_decode($_POST['image_tags_input']);
		$tag_parents = array_map(array($alkaline, 'makeUnicode'), $tag_parents);
		
		$fields = array('tag_name' => $alkaline->makeUnicode($tag_name),
			'tag_parents' => serialize($tag_parents));
		$alkaline->updateRow($fields, 'tags', $tag_id);
		
		if(count($tags) == 1){
			$alkaline->exec('UPDATE links SET tag_id = ' . $tag['tag_id'] . ' WHERE tag_id = ' . $tag_id);
			$alkaline->deleteRow('tags', $tag_id);
		}
	}
	
	unset($tag_id);
}
else{
	$alkaline->deleteEmptyRow('tags', array('tag_name'));
}

$tags = $alkaline->getTags(true);
$tag_count = count($tags);

define('TAB', 'features');

// GET TAG CLOUD TO VIEW OR TAG TO EDIT
if(empty($tag_id)){	
	define('TITLE', 'Alkaline Tags');
	require_once(PATH . ADMIN . 'includes/header.php');

	?>

	<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/tags.png" alt="" /> Tags (<?php echo $tag_count; ?>)</h1>
	
	<p>Tags help you organize your image library. Add tags to images in <a href="<?php echo BASE . ADMIN . 'library' . URL_CAP; ?>">your library</a>.</p>
	
	<p>
		<input type="search" name="filter" placeholder="Filter" class="s" results="0" />
	</p>

	<p class="center tags filter">
		<?php
	
		$tags_html = array();
	
		foreach($tags as $tag){
			echo '<span class="tag"><a href="' . BASE . ADMIN . 'tags' . URL_ID . $tag['id'] . URL_RW . '" style="font-size: ' . $tag['size'] . 'em;">' . $tag['name'] . '</a>&#0160;<span class="small quiet">(<a href="' . BASE . ADMIN . 'search' . URL_ACT . 'tags' . URL_AID . $tag['id'] . URL_RW . '">' . $tag['count'] . '</a>)</span></span> ';
		}
	
		?>
	</p>

	<?php

	require_once(PATH . ADMIN . 'includes/footer.php');
}
else{
	// Update image count on rights set
	$image_ids = new Find('images');
	$image_ids->tags($tag_id);
	$image_ids->find();
	
	// Get rights set
	$tag = $alkaline->getRow('tags', $tag_id);
	$tag = $alkaline->makeHTMLSafe($tag);

	if(!empty($tag['tag_name'])){	
		define('TITLE', 'Alkaline Tag: &#8220;' . $tag['tag_name']  . '&#8221;');
	}
	require_once(PATH . ADMIN . 'includes/header.php');
	
	?>
	
	<div class="actions">
		<a href="<?php echo BASE . ADMIN . 'search' . URL_ACT . 'tags' . URL_AID . $tag['tag_id'] . URL_RW; ?>"><button>View images (<?php echo $image_ids->count; ?>)</button></a>
		<a href="<?php echo BASE . 'tag' . URL_ID . $tag['tag_id'] . URL_RW; ?>"><button>Launch tag</button></a>
	</div>
		
	<?php
	
	if(empty($tag['tag_name'])){
		echo '<h1><img src="' . BASE . ADMIN . 'images/icons/tags.png" alt="" /> New Tag</h1>';
	}
	else{
		echo '<h1><img src="' . BASE . ADMIN . 'images/icons/tags.png" alt="" /> Tag: ' . $tag['tag_name'] . '</h1>';
	}
	
	?>
	
	<p>You can rename the tag&#8212;including to the name of a preexisting tag to merge them.</p>
	
	<form id="tags" action="<?php echo BASE . ADMIN . 'tags' . URL_CAP; ?>" method="post">
		<table>
			<tr>
				<td class="right middle"><label for="tag_name">Name:</label></td>
				<td><input type="text" id="tag_name" name="tag_name" value="<?php echo $tag['tag_name']; ?>" class="m notempty" /></td>
			</tr>
			<tr>
				<td class="right pad"><label for="image_tag">Parents:</label></td>
				<td>
					<div class="image_tag_container">
						<input type="text" id="image_tag" name="image_tag" class="image_tag" style="width: 40%;" /> <input type="submit" id="image_tag_add" class="image_tag_add" value="Add" /><br />
						<div id="image_tags" class="image_tags"></div>
						<div id="image_tags_load" class="image_tags_load none"><?php if(!empty($tag['tag_parents'])){ echo json_encode(unserialize($tag['tag_parents'])); } ?></div>
						<input type="hidden" name="image_tags_input" id="image_tags_input" class="image_tags_input" value="" />
					</div>
					
					<span class="quiet">Tag parents are non-recursive.</span>
				</td>
			</tr>
			<tr>
				<td class="right"><input type="checkbox" id="tag_delete" name="tag_delete" value="delete" /></td>
				<td><strong><label for="tag_delete">Delete this tag.</label></strong> This action cannot be undone.</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="hidden" name="tag_id" value="<?php echo $tag['tag_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo $alkaline->back(); ?>">cancel</a></td>
			</tr>
		</table>
	</form>
	
	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}

?>