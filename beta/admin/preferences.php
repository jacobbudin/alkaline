<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'user.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

if(!empty($_POST['save'])){
	$user->setPref('shoe_pub', 0);
	$user->setPref('comm_email_photo', 0);
	
	$fields = array('user_preferences' => serialize($user->user['user_preferences']));
	
	$user->updateFields($fields);
}

define('TITLE', 'Alkaline Preferences');
define('COLUMNS', '19');
define('WIDTH', '750');

require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="content" class="span-<?php echo COLUMNS - 2; ?> prepend-1 append-1 last">
	<h2>Preferences</h2>
	
	<?php $alkaline->viewNotification(); ?>
	
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
		
		<p class="center"><input type="submit" name="save" value="Save changes" /></p>
		
	</form>
	
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>