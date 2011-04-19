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
	
	<div class="actions"><a href="<?php echo BASE . ADMIN . 'rights' . URL_ACT . 'add' . URL_RW; ?>"><button>Add rights set</button></a></div>

	<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/rights.png" alt="" /> Right Sets (<?php echo $right_count; ?>)</h1>
	
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
			echo '<tr class="ro">';
				echo '<td><strong class="large"><a href="' . BASE . ADMIN . 'rights' . URL_ID . $right['right_id'] . URL_RW . '" class="tip" title="' . $alkaline->fitStringByWord($right['right_description'], 150) . '">' . $right['right_title'] . '</a></strong></td>';
				echo '<td class="center"><a href="' . BASE . ADMIN . 'search' . URL_ACT . 'rights' . URL_AID . $right['right_id'] . URL_RW . '">' . $right['right_image_count'] . '</a></td>';
				echo '<td>' . $alkaline->formatTime($right['right_created']) . '</td>';
				echo '<td>' . ucfirst($alkaline->formatRelTime($right['right_modified'])) . '</td>';
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
	
	$fields = array('right_image_count' => $image_ids->count);
	$alkaline->updateRow($fields, 'rights', $right_id, false);
	
	// Get rights set
	$right = $alkaline->getRow('rights', $right_id);
	$right = $alkaline->makeHTMLSafe($right);

	if(!empty($right['right_title'])){	
		define('TITLE', 'Alkaline Right Set: &#8220;' . $right['right_title']  . '&#8221;');
	}
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<div class="actions"><a href="<?php echo BASE . ADMIN . 'search' . URL_ACT . 'rights' . URL_AID . $right['right_id'] . URL_RW; ?>"><button>View images (<?php echo $image_ids->count; ?>)</button></a></div>
	
	<?php
	
	if(empty($right['right_title'])){
		echo '<h1><img src="' . BASE . ADMIN . 'images/icons/rights.png" alt="" /> New Right Set</h1>';
	}
	else{
		echo '<h1><img src="' . BASE . ADMIN . 'images/icons/rights.png" alt="" /> Right Set: ' . $right['right_title'] . '</h1>';
	}
	
	?>
	
	<form id="rights" action="<?php echo BASE . ADMIN . 'rights' . URL_CAP; ?>" method="post">
		<div class="span-24 last">
			<div class="span-15 append-1">
				<input type="text" id="right_title" name="right_title" placeholder="Title" value="<?php echo $right['right_title']; ?>" class="title notempty" />
				<textarea id="right_description" name="right_description" placeholder="Description" class="<?php if($user->returnPref('text_code')){ echo $user->returnPref('text_code_class'); } ?>"><?php echo $right['right_description']; ?></textarea>
			</div>
			<div class="span-8 last">
				<table>
					<tr>
						<td><input type="checkbox" id="right_merge" name="right_merge" value="merge" /></td>
						<td>
							<label for="right_merge">Transfer images to <?php echo $alkaline->showRights('right_merge_id'); ?> rights set.</label><br />
							This action cannot be undone.
						</td>
					</tr>
					<tr>
						<td><input type="checkbox" id="right_delete" name="right_delete" value="delete" /></td>
						<td>
							<label for="right_delete">Delete this rights set.</label><br />
							This action cannot be undone.
						</td>
					</tr>
				</table>
			</div>
		</div>
		
		<p>
			<input type="hidden" name="right_id" value="<?php echo $right['right_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo $alkaline->back(); ?>">cancel</a>
		</p>
	</form>

	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}

?>