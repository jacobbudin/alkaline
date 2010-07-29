<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

if(!empty($_POST['preferences_save'])){
	$user->setPref('shoe_pub', 0);
	$user->setPref('comm_email_photo', 0);
	
	$fields = array('user_preferences' => serialize($user->user['user_preferences']));
	
	$user->updateFields($fields);
}

define('TITLE', 'Alkaline Preferences');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="module" class="container">
	<h1>Preferences</h1>
	<p>Modify your personal preferences.</p>
</div>

<form id="preferences" class="container" action="" method="post">	
	<h3>Shoebox</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="shoe_pub" name="shoe_pub" <?php echo $user->readPref('shoe_pub'); ?> /></td>
			<td class="description">
				<label for="shoe_pub">Set all photos to be published after processing by default</label>
			</td>
		</tr>
	</table>
	
	<h3>Comments</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="comm_email_photo" name="comm_email_photo" <?php echo $user->readPref('comm_email_photo'); ?> /></td>
			<td class="description">
				<label for="comm_email_photo">Email new comments to photographer</label>
			</td>
		</tr>
	</table>
	
	<p><input type="submit" name="preferences_save" value="Save changes" /> or <a href="<?php echo BASE . ADMIN; ?>customize/">cancel</a></p>
</form>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>