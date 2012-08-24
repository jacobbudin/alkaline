<?php

/*
// Alkaline
// Copyright (c) 2010-2012 by Budin Ltd. Some rights reserved.
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
	$user->setPref('text_code', @$_POST['text_code']);
	$user->setPref('text_code_style', @$_POST['text_code_style']);
	$user->setPref('text_code_size', @$_POST['text_code_size']);
	$user->setPref('text_code_class', @$_POST['text_code_style'] . ' ' . @$_POST['text_code_size']);
	$user->setPref('recent_images', @$_POST['recent_images']);
	$user->setPref('recent_images_limit', @$_POST['recent_images_limit']);
	$user->setPref('shoe_pub', @$_POST['shoe_pub']);
	$user->setPref('shoe_to_bulk', @$_POST['shoe_to_bulk']);
	$user->setPref('post_pub', @$_POST['post_pub']);
	$user->savePref();
	
	$alkaline->addNote('Your preferences have been saved.', 'success');
	
	header('Location: ' . BASE . ADMIN . 'dashboard' . URL_CAP);
	exit();
}

define('TAB', 'dashboard');
define('TITLE', 'Alkaline Preferences');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/preferences.png" alt="" /> Preferences</h1>

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
				images per page</label><br />
				Determines pagination in image library, search results, and bulk editor
			</td>
		</tr>
		<tr>
			<td class="input middle"><input type="checkbox" id="home_target" name="home_target" <?php echo $user->readPref('home_target'); ?> value="true"  /></td>
			<td class="description">
				<label for="home_target">Open the home page in new tab or window when using the link in the header</label>
			</td>
		</tr>
		<tr>
			<td class="input middle"><input type="checkbox" id="text_code" name="text_code" <?php echo $user->readPref('text_code'); ?> value="true"  /></td>
			<td class="description">
				<label for="text_code">Stylize
					<select name="text_code_style">
						<option value="code" <?php echo $user->readPref('text_code_style', 'code'); ?>>code</option>
						<option value="serif" <?php echo $user->readPref('text_code_style', 'serif'); ?>>serif</option>
					</select>
					<select name="text_code_size">
						<option value="" <?php echo $user->readPref('text_code_size', ''); ?>>normal</option>
						<option value="l" <?php echo $user->readPref('text_code_size', 'l'); ?>>larger</option>
						<option value="xl" <?php echo $user->readPref('text_code_size', 'xl'); ?>>largest</option>
					</select>
					text areas where markup can be applied</label>
			</td>
		</tr>
		
	</table>
	<!-- 
	<h3>Dashboard</h3>
	
	<table>
		<tr>
			<td class="input middle"><input type="checkbox" id="recent_images" name="recent_images" <?php echo $user->readPref('recent_images'); ?> value="true"  /></td>
			<td class="description">
				<label for="recent_images">Show the
				<select name="recent_images_limit">
					<option value="10" <?php echo $user->readPref('recent_images_limit', 10); ?>>10</option>
					<option value="25" <?php echo $user->readPref('recent_images_limit', 25); ?>>25</option>
					<option value="50" <?php echo $user->readPref('recent_images_limit', 50); ?>>50</option>
					<option value="100" <?php echo $user->readPref('recent_images_limit', 100); ?>>100</option>
					<option value="250" <?php echo $user->readPref('recent_images_limit', 250); ?>>250</option>
				</select>
				most recent images on my dashboard</label>
			</td>
		</tr>
	</table>
	-->
	
	<h3>Shoebox</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="shoe_pub" name="shoe_pub" <?php echo $user->readPref('shoe_pub'); ?> value="true" /></td>
			<td class="description">
				<label for="shoe_pub">Set all images to be published immediately after processing by default</label>
			</td>
		</tr>
		<tr>
			<td class="input"><input type="checkbox" id="shoe_to_bulk" name="shoe_to_bulk" <?php echo $user->readPref('shoe_to_bulk'); ?> value="true" /></td>
			<td class="description">
				<label for="shoe_to_bulk">Send images to bulk editor after processing</label>
			</td>
		</tr>
	</table>
	
	<h3>Posts</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="post_pub" name="post_pub" <?php echo $user->readPref('post_pub'); ?> value="true" /></td>
			<td class="description">
				<label for="post_pub">Set all posts to be published immediately after creating by default</label>
			</td>
		</tr>
	</table>
	
	<p><input type="submit" name="preferences_save" value="Save changes" /> or <a href="<?php echo $alkaline->back(); ?>">cancel</a></p>
</form>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>