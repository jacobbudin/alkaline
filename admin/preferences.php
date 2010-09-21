<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

if(!empty($_POST['preferences_save'])){
	$user->setPref('shoe_pub', @$_POST['shoe_pub']);
	$user->setPref('comm_email_photo', @$_POST['comm_email_photo']);
	
	$fields = array('user_preferences' => serialize($user->user['user_preferences']));
	
	$user->updateFields($fields);
}

define('TAB', 'settings');
define('TITLE', 'Alkaline Preferences');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<h1>Preferences</h1>

<form id="preferences"  action="" method="post">	
	<h3>Shoebox</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="shoe_pub" name="shoe_pub" <?php echo $user->readPref('shoe_pub'); ?> value="true" /></td>
			<td class="description">
				<label for="shoe_pub">Set all photos to be published immediately after processing by default</label>
			</td>
		</tr>
	</table>
	
	<h3>Comments</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="comm_email_photo" name="comm_email_photo" <?php echo $user->readPref('comm_email_photo'); ?> value="true" /></td>
			<td class="description">
				<label for="comm_email_photo">Email me new comments on my photographs</label>
			</td>
		</tr>
	</table>
	
	<p><input type="submit" name="preferences_save" value="Save changes" /> or <a href="<?php echo $_SERVER['HTTP_REFERER']; ?>">cancel</a></p>
</form>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>