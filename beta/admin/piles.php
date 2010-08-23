<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$pile_id = @$alkaline->findID($_GET['id']);
$pile_add = @$alkaline->findID($_GET['add']);

// SAVE CHANGES
if(!empty($_POST['pile_id'])){
	$pile_id = $alkaline->findID($_POST['pile_id']);
	if(@$_POST['pile_delete'] == 'delete'){
		$alkaline->deleteRow('piles', $pile_id);
	}
	else{
		$fields = array('pile_title' => $_POST['pile_title'],
			'pile_description' => $_POST['pile_description']);
		$alkaline->updateRow($fields, 'piles', $pile_id);
	}
	unset($pile_id);
}
else{
	$alkaline->deleteEmptyRow('piles', array('page_title', 'pile_call'));
}

// CREATE PILE
if($pile_add == 1){
	$pile_call = Find::recentMemory();
	$fields = array('pile_call' => $pile_call);
	$pile_id = $alkaline->addRow($fields, 'piles');
}

// GET PILES TO VIEW OR PILE TO EDIT
if(empty($pile_id)){

	$piles = $alkaline->getTable('piles');
	$pile_count = @count($piles);
	
	define('TITLE', 'Alkaline Piles');
	require_once(PATH . ADMIN . 'includes/header.php');
	require_once(PATH . ADMIN . 'includes/features.php');

	?>

	<h1>Piles (<?php echo $pile_count; ?>)</h1>
	
	<table>
		<tr>
			<th style="width: 60%;">Title</th>
			<th class="center">Views</th>
			<th class="center">Photos</th>
			<th>Last modified</th>
		</tr>
		<?php
	
		foreach($piles as $pile){
			echo '<tr>';
				echo '<td><strong><a href="' . BASE . ADMIN . 'piles/' . $pile['pile_id'] . '">' . $pile['pile_title'] . '</a></strong><br />' . $alkaline->fitString($pile['pile_description'], 150) . '</td>';
				echo '<td class="center">' . $pile['pile_views'] . '</td>';
				echo '<td class="center"><a href="' . BASE . ADMIN . 'search/piles/' . $pile['pile_id'] . '">' . $pile['pile_photo_count'] . '</a></td>';
				echo '<td>' . $alkaline->formatTime($pile['pile_modified']) . '</td>';
			echo '</tr>';
		}
	
		?>
	</table>

	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}
else{
	
	// Update photo count on pile
	$photo_ids = new Find;
	$photo_ids->pile($pile_id);
	$photo_ids->exec();
	
	$fields = array('pile_photo_count' => $photo_ids->photo_count);
	$alkaline->updateRow($fields, 'piles', $pile_id, false);
	
	// Get pile
	$piles = $alkaline->getTable('piles', $pile_id);
	$pile = $piles[0];

	if(!empty($pile['pile_title'])){	
		define('TITLE', 'Alkaline Pile: &#8220;' . $pile['pile_title']  . '&#8221;');
	}
	else{
		define('TITLE', 'Alkaline Pile');
	}
	require_once(PATH . ADMIN . 'includes/header.php');
	require_once(PATH . ADMIN . 'includes/features.php');

	?>
	
	<div style="float: right; margin: 1em 0;"><a href="<?php echo BASE . ADMIN; ?>results/piles/<?php echo $pile['pile_id']; ?>/" class="nu"><span class="button">&#0187;</span>View photos</a> &#0160; <a href="" class="nu"><span class="button">&#0187;</span>View pile</a></div>
	
	<h1>Pile</h1>
	
	<form id="pile" action="<?php echo BASE . ADMIN; ?>piles/" method="post">
		<table>
			<tr>
				<td class="right middle"><label for="pile_title">Title:</label></td>
				<td><input type="text" id="pile_title" name="pile_title" value="<?php echo $pile['pile_title']; ?>" class="title" /></td>
			</tr>
			<tr>
				<td class="right pad"><label for="pile_description">Description:</label></td>
				<td><textarea id="pile_description" name="pile_description"><?php echo $pile['pile_description']; ?></textarea></td>
			</tr>
			<tr>
				<td class="right"><input type="checkbox" id="pile_delete" name="pile_delete" value="delete" /></td>
				<td><strong><label for="pile_delete">Delete this pile.</label></strong> This action cannot be undone.</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="hidden" name="pile_id" value="<?php echo $pile['pile_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo BASE . ADMIN; ?>piles/">cancel</a></td>
			</tr>
		</table>
	</form>

	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}

?>