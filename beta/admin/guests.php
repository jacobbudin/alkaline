<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$guest_id = @$alkaline->findID($_GET['id']);
$guest_add = @$alkaline->findID($_GET['add']);

// SAVE CHANGES
if(!empty($_POST['guest_id'])){
	$guest_id = $alkaline->findID($_POST['guest_id']);
	if(@$_POST['guest_delete'] == 'delete'){
		$alkaline->deleteRow('guests', $guest_id);
	}
	else{
		$fields = array('guest_title' => @$_POST['guest_title'],
			'guest_key' => @$_POST['guest_key']);
		if(@$_POST['guest_reset_view_count'] == 'reset_view_count'){
			$fields['guest_views'] = 0;
		}
		$alkaline->updateRow($fields, 'guests', $guest_id);
	}
	unset($guest_id);
}
else{
	$alkaline->deleteEmptyRow('guests', array('guest_title', 'guest_key'));
}

// CREATE GUEST
if($guest_add == 1){
	$guest_id = $alkaline->addRow(null, 'guests');
}

// GET GUEST TO VIEW OR GUEST TO EDIT
if(empty($guest_id)){

	$guests = $alkaline->getTable('guests');
	$guest_count = @count($guests);
	
	define('TITLE', 'Alkaline Guests');
	require_once(PATH . ADMIN . 'includes/header.php');
	require_once(PATH . ADMIN . 'includes/settings.php');

	?>

	<h1>Guests (<?php echo $guest_count; ?>)</h1>
	
	<table>
		<tr>
			<th>Title</th>
			<th class="center">Views</th>
			<th>Last login</th>
		</tr>
		<?php
	
		foreach($guests as $guest){
			echo '<tr>';
				echo '<td><strong><a href="' . BASE . ADMIN . 'guests/' . $guest['guest_id'] . '">' . $guest['guest_title'] . '</a></strong></td>';
				echo '<td class="center">' . number_format($guest['guest_views']) . '</td>';
				echo '<td>' . $alkaline->formatTime($guest['guest_last_login'], null, '<em>(Never)</em>') . '</td>';
			echo '</tr>';
		}
	
		?>
	</table>
	
	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}
else{
	
	// Get guest
	$guests = $alkaline->getTable('guests', $guest_id);
	$guest = $guests[0];

	if(!empty($guest['guest_title'])){	
		define('TITLE', 'Alkaline Guest: ' . $guest['guest_title']);
	}
	else{
		define('TITLE', 'Alkaline Guest');
	}
	require_once(PATH . ADMIN . 'includes/header.php');
	require_once(PATH . ADMIN . 'includes/settings.php');

	?>
	
	<div style="float: right; margin: 1em 0;"><a href="<?php echo BASE . ADMIN; ?>search/guests/<?php echo $guest['guest_id']; ?>/" class="nu"><span class="button">&#0187;</span>View photos</a></div>
	
	<h1>guest</h1>
	
	<form id="guest" action="<?php echo BASE . ADMIN; ?>guests/" method="post">
		<table>
			<tr>
				<td class="right middle"><label for="guest_title">Title:</label></td>
				<td><input type="text" id="guest_title" name="guest_title" value="<?php echo $guest['guest_title']; ?>" class="title" /></td>
			</tr>
			<tr>
				<td class="right middle"><label for="guest_key">Key:</label></td>
				<td><input type="text" id="guest_key" name="guest_key" value="<?php echo $guest['guest_key']; ?>" /></td>
			</tr>
			<tr>
				<td class="right"><input type="checkbox" id="guest_reset_view_count" name="guest_reset_view_count" value="reset_view_count" /></td>
				<td><strong><label for="guest_reset_view_count">Reset view count.</label></strong> This action cannot be undone.</td>
			</tr>
			<tr>
				<td class="right"><input type="checkbox" id="guest_delete" name="guest_delete" value="delete" /></td>
				<td><strong><label for="guest_delete">Delete this guest.</label></strong> This action cannot be undone.</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="hidden" name="guest_id" value="<?php echo $guest['guest_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo BASE . ADMIN; ?>guests/">cancel</a></td>
			</tr>
		</table>
	</form>

	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}

?>