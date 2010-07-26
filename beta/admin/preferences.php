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

<div id="preferences" class="container">
	<h2>Preferences</h2>
	
	<p>Modify your user preferences.</p>
	
	<form action="" method="post">
		
		<h3>Shoebox</h3>
		
		<table>
			<tr>
				<td class="input"><input type="checkbox" name="shoe_pub" <?php echo $user->readPref('shoe_pub'); ?> /></td>
				<td class="description">
					<strong>Set all photos to be published after processing by default</strong>
				</td>
			</tr>
		</table>
		
		<h3>Comments</h3>
		
		<table>
			<tr>
				<td class="input"><input type="checkbox" name="comm_email_photo" <?php echo $user->readPref('comm_email_photo'); ?> /></td>
				<td class="description">
					<strong>Email new comments to photographer</strong>
				</td>
			</tr>
		</table>
		
		<p><input type="submit" name="preferences_save" value="Save changes" /></p>
		
	</form>
	
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>