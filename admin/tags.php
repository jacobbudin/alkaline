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
		
		if(count($tags) == 1){
			$alkaline->exec('UPDATE links SET tag_id = ' . $tag['tag_id'] . ' WHERE tag_id = ' . $tag_id);
			$alkaline->deleteRow('tags', $tag_id);
		}
		else{
			$fields = array('tag_name' => $alkaline->makeUnicode($tag_name));
			$alkaline->updateRow($fields, 'tags', $tag_id);
		}
	}
	
	unset($tag_id);
}
else{
	$alkaline->deleteEmptyRow('tags', array('tag_title'));
}

$tags = $alkaline->getTags();
$tag_count = count($tags);

define('TAB', 'features');

// GET TAG CLOUD TO VIEW OR TAG TO EDIT
if(empty($tag_id)){	
	define('TITLE', 'Alkaline Tags');
	require_once(PATH . ADMIN . 'includes/header.php');

	?>

	<h1>Tags (<?php echo $tag_count; ?>)</h1>
	
	<p>Tags help you organize your image library.</p>
	
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
	// Update photo count on rights set
	$photo_ids = new Find;
	$photo_ids->tags($tag_id);
	$photo_ids->find();
	
	// Get rights set
	$tag = $alkaline->getRow('tags', $tag_id);
	$tag = $alkaline->makeHTMLSafe($tag);

	if(!empty($tag['tag_name'])){	
		define('TITLE', 'Alkaline Tag: &#8220;' . $tag['tag_name']  . '&#8221;');
	}
	require_once(PATH . ADMIN . 'includes/header.php');
	
	?>
	
	<div class="actions"><a href="<?php echo BASE . ADMIN . 'search' . URL_ACT . 'tags' . URL_AID . $tag['tag_id'] . URL_RW; ?>">View photos (<?php echo $photo_ids->photo_count; ?>)</a></div>
	
	<h1>Tag</h1>
	
	<p>You can rename the tag&#8212;including to the name of a preexisting tag to merge them.</p>
	
	<form id="tags" action="<?php echo BASE . ADMIN . 'tags' . URL_CAP; ?>" method="post">
		<table>
			<tr>
				<td class="right middle"><label for="tag_name">Name:</label></td>
				<td><input type="text" id="tag_name" name="tag_name" value="<?php echo $tag['tag_name']; ?>" class="m" /></td>
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