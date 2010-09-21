<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

if(!empty($_POST['configuration_save'])){
	$alkaline->setConf('shoe_exif', @$_POST['shoe_exif']);
	$alkaline->setConf('shoe_iptc', @$_POST['shoe_iptc']);
	$alkaline->setConf('shoe_geo', @$_POST['shoe_geo']);
	$alkaline->setConf('photo_original', @$_POST['photo_original']);
	$alkaline->setConf('comm_enabled', @$_POST['comm_enabled']);
	$alkaline->setConf('comm_email', @$_POST['comm_email']);
	$alkaline->setConf('comm_mod', @$_POST['comm_mod']);
	$alkaline->setConf('stat_enabled', @$_POST['stat_enabled']);
	$alkaline->setConf('maint_debug', @$_POST['maint_debug']);
	$alkaline->setConf('maint_disable', @$_POST['maint_disable']);
	
	$alkaline->saveConf();
	
	$alkaline->addNotification('The configuration have been saved.', 'success');
	
	header('Location: ' . BASE . ADMIN . '/settings/');
	exit();
}

define('TAB', 'settings');
define('TITLE', 'Alkaline Configuration');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<form action="" id="configuration" method="post">
	<h1>Configuration</h1>
	
	<h3>Shoebox</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="shoe_exif" name="shoe_exif" <?php echo $alkaline->readConf('shoe_exif'); ?> value="true" /></td>
			<td class="description">
				<label for="shoe_exif">Import EXIF camera data</label> (when available)
			</td>
		</tr>
		<tr>
			<td class="input"><input type="checkbox" id="shoe_iptc" name="shoe_iptc" <?php echo $alkaline->readConf('shoe_iptc'); ?> value="true" /></td>
			<td class="description">
				<label for="shoe_iptc">Import IPTC keyword data</label> (when available)
			</td>
		</tr>
		<tr>
			<td class="input"><input type="checkbox" id="shoe_geo" name="shoe_geo" <?php echo $alkaline->readConf('shoe_geo'); ?> value="true" /></td>
			<td class="description">
				<label for="shoe_geo">Import geolocation data</label> (when available)
			</td>
		</tr>
	</table>
	
	<h3>Photos</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="photo_original" name="photo_original" <?php echo $alkaline->readConf('photo_original'); ?> value="true" /></td>
			<td class="description">
				<label for="photo_original">Protect original files</label><br />
				Save your high-resolution originals in a password-protected folder
			</td>
		</tr>
	</table>
	
	<h3>Comments</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="comm_enabled" name="comm_enabled" <?php echo $alkaline->readConf('comm_enabled'); ?> value="true" /></td>
			<td class="description">
				<label for="comm_enabled">Enable comments</label>
			</td>
		</tr>
		<tr>
			<td class="input"><input type="checkbox" id="comm_email" name="comm_email" <?php echo $alkaline->readConf('comm_email'); ?> value="true" /></td>
			<td class="description">
				<label for="comm_email">Email new comments to administrator</label>
			</td>
		</tr>
		<tr>
			<td class="input"><input type="checkbox" id="comm_mod" name="comm_mod" <?php echo $alkaline->readConf('comm_mod'); ?> value="true" /></td>
			<td class="description">
				<label for="comm_mod">Moderate visitor comments</label><br />
				Require administrator approval before visitor comments appear
			</td>
		</tr>
	</table>

	<h3>Statistics</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="stat_enabled" name="stat_enabled" <?php echo $alkaline->readConf('stat_enabled'); ?> value="true" /></td>
			<td class="description">
				<label for="stat_enabled">Enable statistics</label>
			</td>
		</tr>
	</table>
	
	<h3>Diagnostics</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="maint_debug" name="maint_debug" <?php echo $alkaline->readConf('maint_debug'); ?> value="true" /></td>
			<td class="description">
				<label for="maint_debug">Enable debug mode</label><br />
				Incorporates technical data to the footer of every page
			</td>
		</tr>
		<tr>
			<td class="input"><input type="checkbox" id="maint_disable" name="maint_disable" <?php echo $alkaline->readConf('maint_disable'); ?> value="true" /></td>
			<td class="description">
				<label for="maint_disable">Disable all extensions</label>
			</td>
		</tr>
	</table>
	
	<p><input type="submit" name="configuration_save" value="Save changes" /> or <a href="<?php echo $_SERVER['HTTP_REFERER']; ?>">cancel</a></p>
</form>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>