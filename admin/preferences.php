<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

if(!empty($_POST['preferences_save'])){
	$user->setPref('recent_photos', @$_POST['recent_photos']);
	$user->setPref('shoe_pub', @$_POST['shoe_pub']);
	$user->setPref('comm_email_photo', @$_POST['comm_email_photo']);
	$user->savePref();
	
	$alkaline->addNotification('Your prefences have been saved.', 'success');
	
	header('Location: ' . BASE . ADMIN . 'dashboard' . URL_CAP);
	exit();
}

define('TAB', 'dashboard');
define('TITLE', 'Alkaline Preferences');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<h1>Preferences</h1>

<form id="preferences" action="" method="post">
	<h3>Shoebox</h3>
	
	<table>
		<tr>
			<td class="input middle"><input type="checkbox" checked="checked" disabled="disabled" /></td>
			<td class="description">
				Show the
				<select name="recent_photos">
					<option value="10" <?php echo $user->readPref('recent_photos', 10); ?>>10</option>
					<option value="25" <?php echo $user->readPref('recent_photos', 25); ?>>25</option>
					<option value="50" <?php echo $user->readPref('recent_photos', 50); ?>>50</option>
					<option value="100" <?php echo $user->readPref('recent_photos', 100); ?>>100</option>
				</select>
				most recent photos on my dashboard.
			</td>
		</tr>
	</table>
	
	<h3>Shoebox</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="shoe_pub" name="shoe_pub" <?php echo $user->readPref('shoe_pub'); ?> value="true" /></td>
			<td class="description">
				<label for="shoe_pub">Set all photos to be published immediately after processing by default</label>
			</td>
		</tr>
	</table>
	
	<p><input type="submit" name="preferences_save" value="Save changes" /> or <a href="<?php echo $_SERVER['HTTP_REFERER']; ?>">cancel</a></p>
</form>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>