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

$user->perm(true, 'rights');

if(!empty($_GET['id'])){
	$right_id = $alkaline->findID($_GET['id']);
}

if(!empty($_GET['act'])){
	$right_act = $_GET['act'];
}

// SAVE CHANGES
if(!empty($_POST['right_id'])){
	$right_id = $alkaline->findID($_POST['right_id']);
	
	// Merge rights set
	if(@$_POST['right_merge'] == 'merge'){
		$right_merge_id = $_POST['right_merge_id'];
		
		if(!empty($right_merge_id)){
			$right_merge_id = intval($right_merge_id);
		}
		else{
			$right_merge_id = '';
		}
		
		$query = $alkaline->prepare('UPDATE images SET right_id = :right_merge_id WHERE right_id = :right_id;');
		$query->execute(array(':right_merge_id' => $right_merge_id, ':right_id' => $right_id));
	}
	
	// Delete rights set
	if(@$_POST['right_delete'] == 'delete'){
		$alkaline->deleteRow('rights', $right_id);
	}
	
	// Update rights set
	else{
		$fields = array('right_title' => $alkaline->makeUnicode($_POST['right_title']),
			'right_description' => $alkaline->makeUnicode($_POST['right_description']));
		
		$alkaline->updateRow($fields, 'rights', $right_id);
	}
	
	unset($right_id);
}
else{
	$alkaline->deleteEmptyRow('rights', array('right_title'));
}

// CREATE RIGHTS SET
if($right_act == 'add'){
	$right_id = $alkaline->addRow(null, 'rights');
}

define('TAB', 'features');

// GET RIGHTS SETS TO VIEW OR RIGHTS SET TO EDIT
if(empty($right_id)){
	$alkaline->updateCounts('images', 'rights', 'right_image_count');
	$rights = $alkaline->getTable('rights', null, null, null, 'right_modified DESC');
	$rights = $alkaline->stripTags($rights);
	$right_count = @count($rights);
	
	define('TITLE', 'Alkaline Right Sets');
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<div class="actions"><a href="<?php echo BASE . ADMIN . 'rights' . URL_ACT . 'add' . URL_RW; ?>">Add rights set</a></div>

	<h1>Right Sets (<?php echo $right_count; ?>)</h1>
	
	<p>Right sets clarify which copyrights you retain on your images to discourage illicit use.</p>
	
	<p>
		<input type="search" name="filter" placeholder="Filter" class="s" results="0" />
	</p>
	
	<table class="filter">
		<tr>
			<th>Title</th>
			<th class="center">Images</th>
			<th>Created</th>
			<th>Last modified</th>
		</tr>
		<?php
	
		foreach($rights as $right){
			echo '<tr>';
				echo '<td><strong><a href="' . BASE . ADMIN . 'rights' . URL_ID . $right['right_id'] . URL_RW . '">' . $right['right_title'] . '</a></strong><br />' . $alkaline->fitString($right['right_description'], 150) . '&#0160;</td>';
				echo '<td class="center"><a href="' . BASE . ADMIN . 'search/rights/' . $right['right_id'] . '">' . $right['right_image_count'] . '</a></td>';
				echo '<td>' . $alkaline->formatTime($right['right_created']) . '</td>';
				echo '<td>' . $alkaline->formatRelTime($right['right_modified']) . '</td>';
			echo '</tr>';
		}
	
		?>
	</table>

	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}
else{	
	// Update image count on rights set
	$image_ids = new Find('images');
	$image_ids->rights($right_id);
	$image_ids->find();
	
	$fields = array('right_image_count' => $image_ids->image_count);
	$alkaline->updateRow($fields, 'rights', $right_id, false);
	
	// Get rights set
	$right = $alkaline->getRow('rights', $right_id);
	$right = $alkaline->makeHTMLSafe($right);

	if(!empty($right['right_title'])){	
		define('TITLE', 'Alkaline Right Set: &#8220;' . $right['right_title']  . '&#8221;');
	}
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<div class="actions"><a href="<?php echo BASE . ADMIN . 'search' . URL_ACT . 'rights' . URL_AID . $right['right_id'] . URL_RW; ?>">View images (<?php echo $image_ids->image_count; ?>)</a></div>
	
	<?php
	
	if(empty($right['right_title'])){
		echo '<h1>New Right Set</h1>';
	}
	else{
		echo '<h1>Right Set: ' . $right['right_title'] . '</h1>';
	}
	
	?>
	
	<form id="rights" action="<?php echo BASE . ADMIN; ?>rights<?php echo URL_CAP; ?>" method="post">
		<table>
			<tr>
				<td class="right middle"><label for="right_title">Title:</label></td>
				<td><input type="text" id="right_title" name="right_title" value="<?php echo $right['right_title']; ?>" class="title notempty" /></td>
			</tr>
			<tr>
				<td class="right pad"><label for="right_description">Description:</label></td>
				<td><textarea id="right_description" name="right_description"><?php echo $right['right_description']; ?></textarea></td>
			</tr>
			<tr>
				<td class="right pad"><input type="checkbox" id="right_merge" name="right_merge" value="merge" /></td>
				<td><strong><label for="right_merge">Transfer images to <?php echo $alkaline->showRights('right_merge_id'); ?> rights set.</label></strong> This action cannot be undone.</td>
			</tr>
			<tr>
				<td class="right"><input type="checkbox" id="right_delete" name="right_delete" value="delete" /></td>
				<td><strong><label for="right_delete">Delete this rights set.</label></strong> This action cannot be undone.</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="hidden" name="right_id" value="<?php echo $right['right_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo $alkaline->back(); ?>">cancel</a></td>
			</tr>
		</table>
	</form>

	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}

?>