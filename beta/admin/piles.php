<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$pile_id = @$alkaline->findID($_GET['id']);

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

// GET PILES TO VIEW OR PILE TO EDIT
if(empty($pile_id)){

	$piles = $alkaline->getTable('piles');
	$pile_count = @count($piles);

	if($pile_count == 1){
		$pile_count_text = '1 pile';
	}
	elseif($pile_count > 1){
		$pile_count_text = $pile_count . ' piles';
	}
	else{
		$pile_count_text = '0 piles';
	}

	define('TITLE', 'Alkaline Piles');
	require_once(PATH . ADMIN . 'includes/header.php');

	?>

	<div id="module" class="container">
		<h1>Piles</h1>
		<p>Your library contains <?php echo $pile_count_text; ?>.</p>
	</div>

	<div id="piles" class="container">
		<div style="float: right; margin: 1em 0 2em 0;"><a href="" class="nu"><span class="button">&#0043;</span>Build pile</a></div>
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
					echo '<td><strong><a href="' . BASE . ADMIN . 'piles/' . $pile['pile_id'] . '">' . $pile['pile_title'] . '</a></strong><br />' . $pile['pile_description'] . '</td>';
					echo '<td class="center">' . $pile['pile_views'] . '</td>';
					echo '<td class="center">&#0126;<a href="' . BASE . ADMIN . 'search/piles/' . $pile['pile_id'] . '">' . $pile['pile_photo_count'] . '</a></td>';
					echo '<td>' . $alkaline->formatTime($pile['pile_modified']) . '</td>';
				echo '</tr>';
			}
		
			?>
		</table>
	</div>

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

	?>

	<div id="module" class="container">
		<h1>Piles</h1>
		<p>Pile #<?php echo $pile['pile_id']; ?> was last modified on <?php echo $alkaline->formatTime($pile['pile_modified']); ?>
	</div>

	<form id="pile" class="container" action="<?php echo BASE . ADMIN; ?>piles/" method="post">
		<div style="float: right; margin: 1em 0;"><a href="<?php echo BASE . ADMIN; ?>search/piles/<?php echo $pile['pile_id']; ?>/" class="nu"><span class="button">&#0187;</span>View photos</a> &#0160; <a href="" class="nu"><span class="button">&#0187;</span>View pile</a></div>
		<table>
			<tr>
				<td class="right"><label for="pile_title">Title:</label></td>
				<td><input type="text" id="pile_title" name="pile_title" value="<?php echo $pile['pile_title']; ?>" class="title" /></td>
			</tr>
			<tr>
				<td class="right"><label for="pile_description">Description:</label></td>
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