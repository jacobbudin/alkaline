<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$user_db_id = @$alkaline->findID($_GET['id']);
$user_db_act = @$_GET['act'];

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
if($user_db_act == 'add'){
	$user_db_id = $alkaline->addRow(null, 'users');
}

define('TAB', 'settings');

// GET USERS TO VIEW OR USER TO EDIT
if(empty($user_db_id)){

	$user_dbs = $alkaline->getTable('users');
	$user_db_count = @count($user_dbs);
	
	define('TITLE', 'Alkaline Users');
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<div class="actions"><a href="<?php echo BASE . ADMIN . 'users' . URL_ACT . 'add' . URL_RW; ?>">Add user</a></div>

	<h1>Users (<?php echo $user_db_count; ?>)</h1>
	
	<p>Users can add photos and modify the library.</p>
	
	<table>
		<tr>
			<th>Username</th>
			<th>Name</th>
			<th class="center">Photos</th>
			<th>Last login</th>
		</tr>
		<?php
	
		foreach($user_dbs as $user_db){
			echo '<tr>';
				echo '<td><strong><a href="' . BASE . ADMIN . 'users' . URL_ID . $user_db['user_id'] . URL_RW . '">' . $user_db['user_user'] . '</a></strong></td>';
				echo '<td>' . $user_db['user_name'] . '</td>';
				echo '<td class="center">' . number_format($user_db['user_photo_count']) . '</td>';
				echo '<td>' . $alkaline->formatTime($user_db['user_last_login'], null, '<em>(Never)</em>') . '</td>';
			echo '</tr>';
		}
	
		?>
	</table>
	
	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}
else{
	
	// Update photo count on user
	$photo_ids = new Find;
	$photo_ids->user($user_db_id);
	$photo_ids->find();
	
	$fields = array('user_photo_count' => $photo_ids->photo_count);
	$alkaline->updateRow($fields, 'users', $user_db_id, false);
	
	// Get user
	$user_dbs = $alkaline->getTable('users', $user_db_id);
	$user_db = $user_dbs[0];
	$user_db = $alkaline->makeHTMLSafe($user_db);

	if(!empty($user_db['user_name'])){
		define('TITLE', 'Alkaline User: ' . $user_db['user_name']);
	}
	else{
		define('TITLE', 'Alkaline user');
	}
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<div class="actions"><a href="<?php echo BASE . ADMIN . 'search' . URL_ACT . 'users' . URL_AID . $user_db['user_id'] . URL_RW; ?>">View photos</a></div>
	
	<h1>User</h1>
	
	<form id="user" action="<?php echo BASE . ADMIN . 'users' . URL_CAP; ?>" method="post">
		<table>
			<tr>
				<td class="right middle"><label for="user_name">Name:</label></td>
				<td><input type="text" id="user_name" name="user_name" value="<?php echo $user_db['user_name']; ?>" class="title" /></td>
			</tr>
			<tr>
				<td class="right middle"><label for="user_user">Username:</label></td>
				<td><input type="text" id="user_user" name="user_user" value="<?php echo $user_db['user_user']; ?>" /></td>
			</tr>
			<tr>
				<td class="right pad"><label for="user_pass">New password:</label></td>
				<td><input type="text" id="user_pass" name="user_pass" value="" /></td>
			</tr>
			<tr>
				<td class="right middle"><label for="user_email">Email:</label></td>
				<td><input type="text" id="user_email" name="user_email" value="<?php echo $user_db['user_email']; ?>" style="width: 24em;" /></td>
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