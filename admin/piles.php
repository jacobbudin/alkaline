<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$pile_id = @$alkaline->findID($_GET['id']);
$pile_act = @$_GET['act'];

// SAVE CHANGES
if(!empty($_POST['pile_id'])){
	$pile_id = $alkaline->findID($_POST['pile_id']);
	if(@$_POST['pile_delete'] == 'delete'){
		$alkaline->deleteRow('piles', $pile_id);
	}
	else{
		$pile_title = trim($_POST['pile_title']);
		
		if(!empty($_POST['pile_title_url'])){
			$pile_title_url = $alkaline->makeURL($_POST['pile_title_url']);
		}
		else{
			$pile_title_url = $alkaline->makeURL($pile_title);
		}
		
		$fields = array('pile_title' => $alkaline->makeUnicode($pile_title),
			'pile_title_url' => $pile_title_url,
			'pile_type' => $_POST['pile_type'],
			'pile_description' => $alkaline->makeUnicode($_POST['pile_description']));
		$alkaline->updateRow($fields, 'piles', $pile_id);
	}
	unset($pile_id);
}
else{
	$alkaline->deleteEmptyRow('piles', array('pile_title'));
}

// CREATE PILE
if($pile_act == 'build'){
	$pile_call = Find::recentMemory();
	if(!empty($pile_call)){
		$fields = array('pile_call' => $pile_call,
			'pile_type' => 'auto');
	}
	else{
		$fields = array('pile_type' => 'static');
	}
	$pile_id = $alkaline->addRow($fields, 'piles');
	
	$photos = new Find;
	$photos->pile($pile_id);
	$photos->find();
	
	$pile_photos = @implode(', ', $photos->photo_ids);
	$pile_photo_count = $photos->photo_count;
	
	$fields = array('pile_photos' => $pile_photos,
		'pile_photo_count' => $pile_photo_count);
	$alkaline->updateRow($fields, 'piles', $pile_id);
}

define('TAB', 'features');

// GET PILES TO VIEW OR PILE TO EDIT
if(empty($pile_id)){
	$piles = $alkaline->getTable('piles');
	$pile_count = @count($piles);
	
	define('TITLE', 'Alkaline Piles');
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<div class="actions"><a href="<?php echo BASE . ADMIN . 'piles' . URL_ACT . 'build' . URL_RW; ?>">Build static pile</a></div>

	<h1>Piles (<?php echo $pile_count; ?>)</h1>
	
	<p>Piles are collections of photos. You can build an automatic pile that updates itself by <a href="<?php echo BASE . ADMIN . 'library' . URL_CAP; ?>">performing a search</a>.</p>
	
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
				echo '<td><strong><a href="' . BASE . ADMIN . 'piles' . URL_ID . $pile['pile_id'] . URL_RW . '">' . $pile['pile_title'] . '</a></strong><br /><a href="' . BASE . 'pile' . URL_ID . $pile['pile_title_url'] . URL_RW . '" class="nu">/' . $pile['pile_title_url'] . '</td>';
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
	// Get pile
	$pile = $alkaline->getRow('piles', $pile_id);
	$pile = $alkaline->makeHTMLSafe($pile);
	
	// Update pile
	$photo_ids = new Find;
	$photo_ids->pile($pile_id);
	$photo_ids->find();
	
	if(!empty($pile['pile_title'])){	
		define('TITLE', 'Alkaline Pile: &#8220;' . $pile['pile_title']  . '&#8221;');
	}
	else{
		define('TITLE', 'Alkaline Pile');
	}
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<div class="actions"><a href="<?php echo BASE . ADMIN . 'search' . URL_ACT . 'piles' . URL_AID . $pile['pile_id'] . URL_RW; ?>">View photos (<?php echo $photo_ids->photo_count; ?>)</a> <a href="<?php echo BASE . 'piles' . URL_ID . $pile['pile_id'] . URL_RW; ?>">Go to pile</a></div>
	
	<h1>Pile</h1>
	
	<form id="pile" action="<?php echo BASE . ADMIN; ?>piles<?php echo URL_CAP; ?>" method="post">
		<table>
			<tr>
				<td class="right middle"><label for="pile_title">Title:</label></td>
				<td><input type="text" id="pile_title" name="pile_title" value="<?php echo $pile['pile_title']; ?>" class="title" /></td>
			</tr>
			<tr>
				<td class="right pad"><label for="pile_title_url">Custom URL:</label></td>
				<td class="quiet">
					<input type="text" id="pile_title_url" name="pile_title_url" value="<?php echo $pile['pile_title_url']; ?>" style="width: 300px;" /><br />
					Optional. Use only letters, numbers, underscores, and hyphens.
				</td>
			</tr>
			<tr>
				<td class="right pad"><label for="pile_description">Description:</label></td>
				<td><textarea id="pile_description" name="pile_description"><?php echo $pile['pile_description']; ?></textarea></td>
			</tr>
			<tr>
				<td class="right"><label for="pile_type">Type:</label></td>
				<td>
					<input type="radio" name="pile_type" id="pile_type_auto" value="auto" <?php if($pile['pile_type'] != 'static'){ echo 'checked="checked"'; } if(empty($pile['pile_call'])){ echo 'disabled="disabled"'; } ?> /> <label for="pile_type_auto">Automatic</label> &#8212; Automatically include new photos that meet the pile&#8217;s criteria<br />
					<input type="radio" name="pile_type" id="pile_type_static" value="static" <?php if($pile['pile_type'] == 'static'){ echo 'checked="checked"'; }  ?> /> <label for="pile_type_static">Static</label> &#8212; Only include the photos originally selected<br /><br />
				</td>
			</tr>
			<tr>
				<td class="right"><input type="checkbox" id="pile_delete" name="pile_delete" value="delete" /></td>
				<td><strong><label for="pile_delete">Delete this pile.</label></strong> This action cannot be undone.</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="hidden" name="pile_id" value="<?php echo $pile['pile_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo $alkaline->back(); ?>">cancel</a></td>
			</tr>
		</table>
	</form>

	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}

?>