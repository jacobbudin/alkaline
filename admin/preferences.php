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

if(!empty($_POST['preferences_save'])){
	$user->setPref('page_limit', @$_POST['page_limit']);
	$user->setPref('home_target', @$_POST['home_target']);
	$user->setPref('recent_photos', @$_POST['recent_photos']);
	$user->setPref('recent_photos_limit', @$_POST['recent_photos_limit']);
	$user->setPref('shoe_pub', @$_POST['shoe_pub']);
	$user->savePref();
	
	$alkaline->addNote('Your prefences have been saved.', 'success');
	
	header('Location: ' . BASE . ADMIN . 'dashboard' . URL_CAP);
	exit();
}

define('TAB', 'dashboard');
define('TITLE', 'Alkaline Preferences');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<h1>Preferences</h1>

<form id="preferences" action="" method="post">
	<h3>General</h3>
	
	<table>
		<tr>
			<td class="input pad"><input type="checkbox" checked="checked" disabled="disabled" /></td>
			<td class="description">
				<label>Display
				<select name="page_limit">
					<option value="25" <?php echo $user->readPref('page_limit', 25); ?>>25</option>
					<option value="50" <?php echo $user->readPref('page_limit', 50); ?>>50</option>
					<option value="100" <?php echo $user->readPref('page_limit', 100); ?>>100</option>
					<option value="250" <?php echo $user->readPref('page_limit', 250); ?>>250</option>
					<option value="500" <?php echo $user->readPref('page_limit', 500); ?>>500</option>
					<option value="1000" <?php echo $user->readPref('page_limit', 1000); ?>>1,000</option>
				</select>
				photos per page</label><br />
				Determines pagination in photo library, search results, and bulk editor
			</td>
		</tr>
		<tr>
			<td class="input middle"><input type="checkbox" id="home_target" name="home_target" <?php echo $user->readPref('home_target'); ?> value="true"  /></td>
			<td class="description">
				<label for="home_target">Open the home page in new tab or window when using the link in the header</label>
			</td>
		</tr>
		
	</table>
	
	<h3>Dashboard</h3>
	
	<table>
		<tr>
			<td class="input middle"><input type="checkbox" id="recent_photos" name="recent_photos" <?php echo $user->readPref('recent_photos'); ?> value="true"  /></td>
			<td class="description">
				<label for="recent_photos">Show the
				<select name="recent_photos_limit">
					<option value="10" <?php echo $user->readPref('recent_photos_limit', 10); ?>>10</option>
					<option value="25" <?php echo $user->readPref('recent_photos_limit', 25); ?>>25</option>
					<option value="50" <?php echo $user->readPref('recent_photos_limit', 50); ?>>50</option>
					<option value="100" <?php echo $user->readPref('recent_photos_limit', 100); ?>>100</option>
					<option value="250" <?php echo $user->readPref('recent_photos_limit', 250); ?>>250</option>
				</select>
				most recent photos on my dashboard</label>
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
	
	<p><input type="submit" name="preferences_save" value="Save changes" /> or <a href="<?php echo $alkaline->back(); ?>">cancel</a></p>
</form>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>