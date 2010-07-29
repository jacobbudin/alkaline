<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$right_id = @$alkaline->findID($_GET['id']);

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

// GET PILES TO VIEW OR PILE TO EDIT
if(empty($right_id)){

	$rights = $alkaline->getTable('rights');
	$right_count = @count($rights);

	if($right_count == 1){
		$right_count_text = '1 rights set';
	}
	elseif($right_count > 1){
		$right_count_text = $right_count . ' rights sets';
	}
	else{
		$right_count_text = '0 rights sets';
	}

	define('TITLE', 'Alkaline Rights Sets');
	require_once(PATH . ADMIN . 'includes/header.php');

	?>

	<div id="module" class="container">
		<h1>Rights</h1>
		<p>Your library contains <?php echo $right_count_text; ?>.</p>
	</div>

	<div id="rights" class="container">
		<div style="float: right; margin: 1em 0 2em 0;"><a href="" class="nu"><span class="button">&#0043;</span>Add rights</a></div>
		<table>
			<tr>
				<th style="width: 60%;">Title</th>
				<th class="center">Photos</th>
				<th>Last modified</th>
			</tr>
			<?php
		
			foreach($rights as $pile){
				echo '<tr>';
					echo '<td><strong><a href="' . BASE . ADMIN . 'rights/' . $pile['right_id'] . '">' . $pile['right_title'] . '</a></strong><br />' . $pile['right_description'] . '</td>';
					echo '<td class="center">&#0126;<a href="' . BASE . ADMIN . 'search/rights/' . $pile['right_id'] . '">' . $pile['right_photos'] . '</a></td>';
					echo '<td>' . $alkaline->formatTime($pile['right_modified']) . '</td>';
				echo '</tr>';
			}
		
			?>
		</table>
	</div>

	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}
else{
	
	$rights = $alkaline->getTable('rights', $right_id);
	$right = $rights[0];

	if(!empty($right['right_title'])){	
		define('TITLE', 'Alkaline Rights Set: &#8220;' . $right['right_title']  . '&#8221;');
	}
	require_once(PATH . ADMIN . 'includes/header.php');

	?>

	<div id="module" class="container">
		<h1>Rights Set</h1>
		<p>Rights set #<?php echo $right['right_id']; ?> was last modified on <?php echo $alkaline->formatTime($right['right_modified']); ?>
	</div>

	<form id="rights" class="container" action="<?php echo BASE . ADMIN; ?>rights/" method="post">
		<div style="float: right; margin: 1em 0;"><a href="<?php echo BASE . ADMIN; ?>search/rights/<?php echo $right['right_id']; ?>/" class="nu"><span class="button">&#0187;</span>View photos</a></div>
		<table>
			<tr>
				<td class="right"><label for="right_title">Title:</label></td>
				<td><input type="text" id="right_title" name="right_title" value="<?php echo $right['right_title']; ?>" class="title" /></td>
			</tr>
			<tr>
				<td class="right"><label for="right_description">Description:</label></td>
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