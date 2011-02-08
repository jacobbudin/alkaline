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
	$user_db_id = $alkaline->findID($_GET['id']);
}

if(!empty($_GET['act'])){
	$user_db_act = $_GET['act'];
}

// SAVE CHANGES
if(!empty($_POST['user_id'])){
	$user_db_id = $alkaline->findID($_POST['user_id']);
	if(@$_POST['user_delete'] == 'delete'){
		$alkaline->deleteRow('users', $user_db_id);
	}
	else{
		$fields = array('user_name' => $alkaline->makeUnicode($_POST['user_name']),
			'user_user' => $_POST['user_user'],
			'user_email' => $_POST['user_email']);
		if(!empty($_POST['user_pass'])){
			$fields['user_pass'] = sha1($_POST['user_pass']);
		}
		$alkaline->updateRow($fields, 'users', $user_db_id);
	}
	unset($user_db_id);
}
else{
	$alkaline->deleteEmptyRow('users', array('user_user', 'user_pass', 'user_name'));
}

// CREATE User
if(!empty($user_db_act) and ($user_db_act == 'add')){
	$user_db_id = $alkaline->addRow(null, 'users');
}

define('TAB', 'settings');

// GET USERS TO VIEW OR USER TO EDIT
if(empty($user_db_id)){
	// Update image counts
	$alkaline->updateCounts('images', 'users', 'user_image_count');
	
	$user_dbs = $alkaline->getTable('users');
	$user_db_count = @count($user_dbs);
	
	define('TITLE', 'Alkaline Users');
	require_once(PATH . ADMIN . 'includes/header.php');
	
	if(Alkaline::edition == 'multiuser'){
		?>
		<div class="actions"><a href="<?php echo BASE . ADMIN . 'users' . URL_ACT . 'add' . URL_RW; ?>">Add user</a></div>
		<?php
	}
	
	?>

	<h1>Users (<?php echo $user_db_count; ?>)</h1>
	
	<p>Users can add images to your Alkaline library and modify the your Alkaline installation.</p>
	
	<p>
		<input type="search" name="filter" placeholder="Filter" class="s" results="0" />
	</p>
	
	<table class="filter">
		<tr>
			<th>Username</th>
			<th>Name</th>
			<th class="center">Images</th>
			<th>Last login</th>
		</tr>
		<?php
	
		foreach($user_dbs as $user_db){
			echo '<tr>';
				echo '<td><strong><a href="' . BASE . ADMIN . 'users' . URL_ID . $user_db['user_id'] . URL_RW . '">' . $user_db['user_user'] . '</a></strong></td>';
				echo '<td>' . $user_db['user_name'] . '</td>';
				echo '<td class="center"><a href="' . BASE . ADMIN . 'search' . URL_ACT . 'users' . URL_AID . $user_db['user_id'] . URL_RW . '">' . number_format($user_db['user_image_count']) . '</a></td>';
				echo '<td>' . $alkaline->formatTime($user_db['user_last_login'], null, '<em>(Never)</em>') . '</td>';
			echo '</tr>';
		}
	
		?>
	</table>
	
	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}
else{
	// Update image count
	$alkaline->updateCount('images', 'users', 'user_image_count', $user_db_id);
	
	// Get user
	$user_db = $alkaline->getRow('users', $user_db_id);
	$user_db = $alkaline->makeHTMLSafe($user_db);
	$user_image_count = $user_db['user_image_count'];
	
	if(empty($user_image_count)){
		$user_image_count = 0;
	}
	
	if(!empty($user_db['user_name'])){
		define('TITLE', 'Alkaline User: ' . $user_db['user_name']);
	}
	else{
		define('TITLE', 'Alkaline user');
	}
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<div class="actions"><a href="<?php echo BASE . ADMIN . 'search' . URL_ACT . 'users' . URL_AID . $user_db['user_id'] . URL_RW; ?>">View images (<?php echo $user_image_count; ?>)</a></div>
	
	<h1>User</h1>
	
	<form id="user" action="<?php echo BASE . ADMIN . 'users' . URL_CAP; ?>" method="post">
		<table>
			<tr>
				<td class="right middle"><label for="user_name">Name:</label></td>
				<td><input type="text" id="user_name" name="user_name" value="<?php echo $user_db['user_name']; ?>" class="s" /></td>
			</tr>
			<tr>
				<td class="right middle"><label for="user_user">Username:</label></td>
				<td><input type="text" id="user_user" name="user_user" value="<?php echo $user_db['user_user']; ?>" class="s" /></td>
			</tr>
			<tr>
				<td class="right pad"><label for="user_pass">Password:</label></td>
				<td>
					<input type="text" id="user_pass" name="user_pass" value="" class="s" /><br />
					Enter a password only if you wish to change it (optional)
				</td>
			</tr>
			<tr>
				<td class="right middle"><label for="user_email">Email:</label></td>
				<td><input type="text" id="user_email" name="user_email" value="<?php echo $user_db['user_email']; ?>" class="m" /></td>
			</tr>
			<tr>
				<td class="right"><input type="checkbox" id="user_reset_pass" name="user_reset_pass" value="reset_pass" /></td>
				<td><strong><label for="user_reset_pass">Reset password.</label></strong> This action cannot be undone.</td>
			</tr>
			<tr>
				<td class="right"><input type="checkbox" id="user_delete" name="user_delete" value="delete" /></td>
				<td><strong><label for="user_delete">Delete this user.</label></strong> This action cannot be undone.</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="hidden" name="user_id" value="<?php echo $user_db['user_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo $alkaline->back(); ?>">cancel</a></td>
			</tr>
		</table>
	</form>

	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}

?>