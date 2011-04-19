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

$user->perm(true, 'users');

if(!empty($_GET['id'])){
	$user_db_id = $alkaline->findID($_GET['id']);
}

if(!empty($_GET['act'])){
	$user_db_act = $_GET['act'];
}

// SAVE CHANGES
if(!empty($_POST['user_id'])){
	$user_db_id = $alkaline->findID($_POST['user_id']);
	if(isset($_POST['user_delete']) and ($_POST['user_delete'] == 'delete')){
		$alkaline->deleteRow('users', $user_db_id);
	}
	else{
		if($_POST['user_reset_pass'] == 'reset_pass'){
			$rand = $alkaline->randInt();
			echo $rand;
			$pass = substr(sha1($rand), 0, 8);
			$alkaline->email($_POST['user_email'], 'Password reset', 'Your password has been reset:' . "\r\n\n" . $pass . "\r\n\n" . LOCATION . BASE . ADMIN);
			$_POST['user_pass'] = $pass;
		}
		
		$permissions = array();
		
		if(@$_POST['user_permission_upload'] == 'true'){ $permissions[] = 'upload'; $permissions[] = 'library'; }
		if(@$_POST['user_permission_shoebox'] == 'true'){ $permissions[] = 'shoebox'; $permissions[] = 'library'; }
		if(@$_POST['user_permission_library'] == 'true'){ $permissions[] = 'images'; $permissions[] = 'library'; }
		if(@$_POST['user_permission_editor'] == 'true'){ $permissions[] = 'editor'; $permissions[] = 'features'; }
		if(@$_POST['user_permission_tags'] == 'true'){ $permissions[] = 'tags'; $permissions[] = 'features'; }
		if(@$_POST['user_permission_sets'] == 'true'){ $permissions[] = 'sets'; $permissions[] = 'features'; }
		if(@$_POST['user_permission_pages'] == 'true'){ $permissions[] = 'pages'; $permissions[] = 'features'; }
		if(@$_POST['user_permission_rights'] == 'true'){ $permissions[] = 'rights'; $permissions[] = 'features'; }
		if(@$_POST['user_permission_posts'] == 'true'){ $permissions[] = 'posts'; }
		if(@$_POST['user_permission_comments'] == 'true'){ $permissions[] = 'comments'; }
		if(@$_POST['user_permission_statistics'] == 'true'){ $permissions[] = 'statistics'; }
		if(@$_POST['user_permission_thumbnails'] == 'true'){ $permissions[] = 'thumbnails'; $permissions[] = 'settings'; }
		if(@$_POST['user_permission_users'] == 'true'){ $permissions[] = 'users'; $permissions[] = 'settings'; }
		if(@$_POST['user_permission_guests'] == 'true'){ $permissions[] = 'guests'; $permissions[] = 'settings'; }
		if(@$_POST['user_permission_themes'] == 'true'){ $permissions[] = 'themes'; $permissions[] = 'settings'; }
		if(@$_POST['user_permission_extensions'] == 'true'){ $permissions[] = 'extensions'; $permissions[] = 'settings'; }
		if(@$_POST['user_permission_configuration'] == 'true'){ $permissions[] = 'configuration'; $permissions[] = 'settings'; }
		if(@$_POST['user_permission_maintenance'] == 'true'){ $permissions[] = 'maintenance'; $permissions[] = 'settings'; }
		
		$permissions = array_unique($permissions);
		
		$fields = array('user_name' => $alkaline->makeUnicode($_POST['user_name']),
			'user_user' => $_POST['user_user'],
			'user_email' => $_POST['user_email'],
			'user_permissions' => serialize($permissions));
		if(!empty($_POST['user_pass']) and ($_POST['user_pass'] != '********')){
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
		<div class="actions"><a href="<?php echo BASE . ADMIN . 'users' . URL_ACT . 'add' . URL_RW; ?>"><button>Add user</button></a></div>
		<?php
	}
	
	?>

	<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/users.png" alt="" /> Users (<?php echo $user_db_count; ?>)</h1>
	
	<p>Users can add images to your Alkaline library and modify the your Alkaline installation.</p>
	
	<p>
		<input type="search" name="filter" placeholder="Filter" class="s" results="0" />
	</p>
	
	<table class="filter">
		<tr>
			<th>Username</th>
			<th>Name</th>
			<th>Email</th>
			<th class="center">Images</th>
			<th>Last login</th>
		</tr>
		<?php
	
		foreach($user_dbs as $user_db){
			echo '<tr class="ro">';
				echo '<td><strong><a href="' . BASE . ADMIN . 'users' . URL_ID . $user_db['user_id'] . URL_RW . '">' . $user_db['user_user'] . '</a></strong></td>';
				echo '<td>' . $user_db['user_name'] . '</td>';
				echo '<td><a href="mailto:' . $user_db['user_email'] . '">' . $user_db['user_email'] . '</a></td>';
				echo '<td class="center"><a href="' . BASE . ADMIN . 'search' . URL_ACT . 'users' . URL_AID . $user_db['user_id'] . URL_RW . '">' . number_format($user_db['user_image_count']) . '</a></td>';
				echo '<td>' . $alkaline->formatTime($user_db['user_last_login'], null, '<em>Never</em>') . '</td>';
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
	$user_db_perms = unserialize($user_db['user_permissions']);
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
	
	<div class="actions">
		<a href="mailto:<?php echo $user_db['user_email']; ?>"><button>Email user</button></a>
		<a href="<?php echo BASE . ADMIN . 'search' . URL_ACT . 'users' . URL_AID . $user_db['user_id'] . URL_RW; ?>"><button>View images (<?php echo $user_image_count; ?>)</button></a>
	</div>
	
	<?php
	
	if(empty($user_db['user_name'])){
		echo '<h1><img src="' . BASE . ADMIN . 'images/icons/users.png" alt="" /> New User</h1>';
	}
	else{
		echo '<h1><img src="' . BASE . ADMIN . 'images/icons/users.png" alt="" /> User: ' . $user_db['user_name'] . '</h1>';
	}
	
	?>
	
	<form id="user" action="<?php echo BASE . ADMIN . 'users' . URL_CAP; ?>" method="post">
		<table>
			<tr>
				<td class="right middle"><label for="user_name">Name:</label></td>
				<td><input type="text" id="user_name" name="user_name" value="<?php echo $user_db['user_name']; ?>" class="s notempty" /></td>
			</tr>
			<tr>
				<td class="right middle"><label for="user_user">Username:</label></td>
				<td><input type="text" id="user_user" name="user_user" value="<?php echo $user_db['user_user']; ?>" class="s notempty" /></td>
			</tr>
			<tr>
				<td class="right pad"><label for="user_pass">Password:</label></td>
				<td>
					<input type="password" id="user_pass" name="user_pass" value="<?php if(!empty($user_db['user_user'])){ echo '********'; } ?>" class="s notempty" />
				</td>
			</tr>
			<tr>
				<td class="right middle"><label for="user_email">Email:</label></td>
				<td><input type="text" id="user_email" name="user_email" value="<?php echo $user_db['user_email']; ?>" class="m" /></td>
			</tr>
			<?php
			if(($user_db['user_id'] != 1) and ($user_db['user_id'] != $user->user['user_id'])){
				?>
				<tr>
					<td class="right pad"><label>Access control:</label></td>
					<td>
						<table>
							<tr>
								<td class="input"><input type="checkbox" id="user_permission_upload" name="user_permission_upload" value="true" <?php if(in_array('upload', $user_db_perms)){ echo 'checked="checked"'; } ?> /></td>
								<td style="width: 15em;"><label for="user_permission_upload">Upload</label></td>
								<td class="input"><input type="checkbox" id="user_permission_shoebox" name="user_permission_shoebox" value="true" <?php if(in_array('shoebox', $user_db_perms)){ echo 'checked="checked"'; } ?> /></td>
								<td><label for="user_permission_shoebox">Shoebox</label></td>
							</tr>
							<tr>
								<td class="input"><input type="checkbox" id="user_permission_library" name="user_permission_library" value="true" <?php if(in_array('library', $user_db_perms)){ echo 'checked="checked"'; } ?> /></td>
								<td><label for="user_permission_library">Library</label> (edit images)</td>
							</tr>
							<tr>
								<td colspan="4"></td>
							</tr>
							<tr>
								<td class="input"><input type="checkbox" id="user_permission_posts" name="user_permission_posts" value="true" <?php if(in_array('posts', $user_db_perms)){ echo 'checked="checked"'; } ?> /></td>
								<td><label for="user_permission_posts">Posts</label></td>
							</tr>
							<tr>
								<td colspan="4"></td>
							</tr>
							<tr>
								<td class="input"><input type="checkbox" id="user_permission_comments" name="user_permission_comments" value="true" <?php if(in_array('comments', $user_db_perms)){ echo 'checked="checked"'; } ?> /></td>
								<td><label for="user_permission_comments">Comments</label></td>
							</tr>
							<tr>
								<td colspan="4"></td>
							</tr>
							<tr>
								<td class="input"><input type="checkbox" id="user_permission_editor" name="user_permission_editor" value="true" <?php if(in_array('editor', $user_db_perms)){ echo 'checked="checked"'; } ?> /></td>
								<td><label for="user_permission_editor">Editor</label> (bulk edit images)</td>
								<td class="input"><input type="checkbox" id="user_permission_tags" name="user_permission_tags" value="true" <?php if(in_array('tags', $user_db_perms)){ echo 'checked="checked"'; } ?> /></td>
								<td><label for="user_permission_tags">Tags</label></td>
							</tr>
							<tr>
								<td class="input"><input type="checkbox" id="user_permission_sets" name="user_permission_sets" value="true" <?php if(in_array('sets', $user_db_perms)){ echo 'checked="checked"'; } ?> /></td>
								<td><label for="user_permission_sets">Sets</label></td>
								<td class="input"><input type="checkbox" id="user_permission_pages" name="user_permission_pages" value="true" <?php if(in_array('pages', $user_db_perms)){ echo 'checked="checked"'; } ?> /></td>
								<td><label for="user_permission_pages">Pages</label></td>
							</tr>
							<tr>
								<td class="input"><input type="checkbox" id="user_permission_rights" name="user_permission_rights" value="true" <?php if(in_array('rights', $user_db_perms)){ echo 'checked="checked"'; } ?> /></td>
								<td><label for="user_permission_rights">Rights</label></td>
							</tr>
							<tr>
								<td colspan="4"></td>
							</tr>
							<tr>
								<td class="input"><input type="checkbox" id="user_permission_statistics" name="user_permission_statistics" value="true" <?php if(in_array('statistics', $user_db_perms)){ echo 'checked="checked"'; } ?> /></td>
								<td><label for="user_permission_statistics">Statistics</label></td>
								<td class="input"><input type="checkbox" id="user_permission_thumbnails" name="user_permission_thumbnails" value="true" <?php if(in_array('thumbnails', $user_db_perms)){ echo 'checked="checked"'; } ?> /></td>
								<td><label for="user_permission_thumbnails">Thumbnails</label></td>
							</tr>
							<tr>
								<td class="input"><input type="checkbox" id="user_permission_users" name="user_permission_users" value="true" <?php if(in_array('users', $user_db_perms)){ echo 'checked="checked"'; } ?> /></td>
								<td><label for="user_permission_users">Users</label></td>
								<td class="input"><input type="checkbox" id="user_permission_guests" name="user_permission_guests" value="true" <?php if(in_array('guests', $user_db_perms)){ echo 'checked="checked"'; } ?> /></td>
								<td><label for="user_permission_guests">Guests</label></td>
							</tr>
							<tr>
								<td class="input"><input type="checkbox" id="user_permission_themes" name="user_permission_themes" value="true" <?php if(in_array('themes', $user_db_perms)){ echo 'checked="checked"'; } ?> /></td>
								<td><label for="user_permission_themes">Themes</label></td>
								<td class="input"><input type="checkbox" id="user_permission_extensions" name="user_permission_extensions" value="true" <?php if(in_array('extensions', $user_db_perms)){ echo 'checked="checked"'; } ?> /></td>
								<td><label for="user_permission_extensions">Extensions</label></td>
							</tr>
							<tr>
								<td class="input"><input type="checkbox" id="user_permission_configuration" name="user_permission_configuration" value="true" <?php if(in_array('configuration', $user_db_perms)){ echo 'checked="checked"'; } ?> /></td>
								<td><label for="user_permission_configuration">Configuration</label></td>
								<td class="input"><input type="checkbox" id="user_permission_maintenance" name="user_permission_maintenance" value="true" <?php if(in_array('maintenance', $user_db_perms)){ echo 'checked="checked"'; } ?> /></td>
								<td><label for="user_permission_maintenance">Maintenance</label></td>
							</tr>
						</table>
					</td>
				</tr>
				<?php
			}
			?>
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