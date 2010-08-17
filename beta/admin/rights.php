<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$right_id = @$alkaline->findID($_GET['id']);
$right_add = @$alkaline->findID($_GET['add']);

// SAVE CHANGES
if(!empty($_POST['right_id'])){
	$right_id = $alkaline->findID($_POST['right_id']);
	
	// Delete rights set
	if(@$_POST['right_delete'] == 'delete'){
		$alkaline->deleteRow('rights', $right_id);
	}
	
	// Update rights set
	else{
		$fields = array('right_title' => $_POST['right_title'],
			'right_description' => $_POST['right_description']);
		
		// Check default rights set
		if(@$_POST['right_default'] == 'default'){
			$fields['right_default'] = 1;
		}
		else{
			$fields['right_default'] = 0;
		}
		$alkaline->updateRow($fields, 'rights', $right_id);
	}
	
	unset($right_id);
}
else{
	$alkaline->deleteEmptyRow('rights', array('right_title', 'right_description', 'right_url', 'right_image', 'right_default'));
}

// CREATE RIGHTS SET
if($right_add == 1){
	$right_id = $alkaline->addRow(null, 'rights');
}

// GET RIGHTS SETS TO VIEW OR RIGHTS SET TO EDIT
if(empty($right_id)){
	$rights = $alkaline->getTable('rights');
	$right_count = @count($rights);
	
	define('TITLE', 'Alkaline Rights Sets');
	require_once(PATH . ADMIN . 'includes/header.php');
	require_once(PATH . ADMIN . 'includes/features.php');

	?>

	<h1>Rights (<?php echo $right_count; ?>)</h1>

	<table>
		<tr>
			<th style="width: 60%;">Title</th>
			<th class="center">Photos</th>
			<th>Last modified</th>
		</tr>
		<?php
	
		foreach($rights as $right){
			echo '<tr>';
				echo '<td><strong><a href="' . BASE . ADMIN . 'rights/' . $right['right_id'] . '">' . $right['right_title'] . '</a></strong><br />' . $alkaline->fitString($right['right_description'], 150) . '</td>';
				echo '<td class="center"><a href="' . BASE . ADMIN . 'search/rights/' . $right['right_id'] . '">' . $right['right_photo_count'] . '</a></td>';
				echo '<td>' . $alkaline->formatTime($right['right_modified']) . '</td>';
			echo '</tr>';
		}
	
		?>
	</table>

	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}
else{
	
	// Update photo count on rights set
	$photo_ids = new Find;
	$photo_ids->rights($right_id);
	$photo_ids->exec();
	
	$fields = array('right_photo_count' => $photo_ids->photo_count);
	$alkaline->updateRow($fields, 'rights', $right_id, false);
	
	// Get rights set
	$rights = $alkaline->getTable('rights', $right_id);
	$right = $rights[0];

	if(!empty($right['right_title'])){	
		define('TITLE', 'Alkaline Rights Set: &#8220;' . $right['right_title']  . '&#8221;');
	}
	require_once(PATH . ADMIN . 'includes/header.php');
	require_once(PATH . ADMIN . 'includes/features.php');

	?>
	
	<div style="float: right; margin: 1em 0;"><a href="<?php echo BASE . ADMIN; ?>search/rights/<?php echo $right['right_id']; ?>/" class="nu"><span class="button">&#0187;</span>View photos</a></div>
	
	<h1>Rights Set</h1>
	
	<form id="rights" action="<?php echo BASE . ADMIN; ?>rights/" method="post">
		<table>
			<tr>
				<td class="right middle"><label for="right_title">Title:</label></td>
				<td><input type="text" id="right_title" name="right_title" value="<?php echo $right['right_title']; ?>" class="title" /></td>
			</tr>
			<tr>
				<td class="right pad"><label for="right_description">Description:</label></td>
				<td><textarea id="right_description" name="right_description"><?php echo $right['right_description']; ?></textarea></td>
			</tr>
			<tr>
				<td class="right"><input type="checkbox" id="right_default" name="right_default" value="default" <?php echo ($right['right_default'] == 1) ? 'checked="checked"' : ''; ?> /></td>
				<td><strong><label for="right_default">Make default rights set.</label></strong> New photos will be given these rights.</td>
			</tr>
			<tr>
				<td class="right"><input type="checkbox" id="right_delete" name="right_delete" value="delete" /></td>
				<td><strong><label for="right_delete">Delete this rights set.</label></strong> This action cannot be undone.</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="hidden" name="right_id" value="<?php echo $right['right_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo BASE . ADMIN; ?>rights/">cancel</a></td>
			</tr>
		</table>
	</form>

	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}

?>