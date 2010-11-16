<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$guest_id = @$alkaline->findID($_GET['id']);
$guest_act = @$_GET['act'];

// SAVE CHANGES
if(!empty($_POST['guest_id'])){
	$guest_id = $alkaline->findID($_POST['guest_id']);
	if(@$_POST['guest_delete'] == 'delete'){
		$alkaline->deleteRow('guests', $guest_id);
	}
	else{
		$guest_piles = @$_POST['guest_piles'];
		
		if($guest_piles == 'all'){
			$guest_piles = '';
		}
		else{
			$guest_piles = @$_POST['guest_piles_select'];
		}
		
		$fields = array('guest_title' => $alkaline->makeUnicode(@$_POST['guest_title']),
			'guest_key' => @$_POST['guest_key'],
			'guest_piles' => $guest_piles);
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
if($guest_act == 'add'){
	$guest_id = $alkaline->addRow(null, 'guests');
}

define('TAB', 'settings');

// GET GUEST TO VIEW OR GUEST TO EDIT
if(empty($guest_id)){
	$guests = $alkaline->getTable('guests');
	$guest_count = @count($guests);
	
	define('TITLE', 'Alkaline Guests');
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<div class="actions"><a href="<?php echo BASE . ADMIN . 'guests' . URL_ACT . 'add' . URL_RW; ?>">Add guest</a></div>

	<h1>Guests (<?php echo $guest_count; ?>)</h1>
	
	<p>Guests use an access key to view some or all protected photos in your library.</p>
	
	<table>
		<tr>
			<th>Title</th>
			<th class="center">Views</th>
			<th>Last login</th>
		</tr>
		<?php
	
		foreach($guests as $guest){
			echo '<tr>';
				echo '<td><strong><a href="' . BASE . ADMIN . 'guests' . URL_ID . $guest['guest_id'] . URL_RW . '">' . $guest['guest_title'] . '</a></strong></td>';
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
	$guest = $alkaline->getRow('guests', $guest_id);
	$guest = $alkaline->makeHTMLSafe($guest);
	
	// Save credentials
	$_SESSION['alkaline']['guest'] = $guest;
	
	if(!empty($guest['guest_title'])){	
		define('TITLE', 'Alkaline Guest: ' . $guest['guest_title']);
	}
	else{
		define('TITLE', 'Alkaline Guest');
	}
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<div class="actions"><a href="<?php echo BASE . ADMIN; ?>search<?php echo URL_ACT; ?>guests<?php echo URL_AID . $guest['guest_id'] . URL_RW; ?>">View photos</a> <a href="<?php echo BASE; ?>guest<?php echo URL_ID . $guest['guest_id'] . URL_RW; ?>">Go to guest</a></div>
	
	<h1>Guest</h1>
	
	<form id="guest" action="<?php echo BASE . ADMIN . 'guests' . URL_CAP; ?>" method="post">
		<table>
			<tr>
				<td class="right middle"><label for="guest_title">Title:</label></td>
				<td><input type="text" id="guest_title" name="guest_title" value="<?php echo $guest['guest_title']; ?>" class="m" /></td>
			</tr>
			<tr>
				<td class="right middle"><label for="guest_key">Key:</label></td>
				<td><input type="text" id="guest_key" name="guest_key" value="<?php echo $guest['guest_key']; ?>" class="s" /></td>
			</tr>
			<tr>
				<td class="right"><label for="guest_piles">Privileges:</label></td>
				<td>
					<input type="radio" name="guest_piles" value="all" id="guest_piles_all" <?php if(empty($guest['guest_piles'])){ echo 'checked="checked" '; } ?>/> <label for="guest_piles_all">Grant access to all protected photos</label><br />
					<input type="radio" name="guest_piles" value="select" id="guest_piles_select" <?php if(!empty($guest['guest_piles'])){ echo 'checked="checked" '; } ?>/> <label for="guest_piles_select">Restrict access to the protected photos in the pile: &#0160; <?php echo $alkaline->showPiles('guest_piles_select', @$guest['guest_piles']); ?></label><br /><br />
				</td>
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
				<td><input type="hidden" name="guest_id" value="<?php echo $guest['guest_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo $alkaline->back(); ?>">cancel</a></td>
			</tr>
		</table>
	</form>

	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}

?>