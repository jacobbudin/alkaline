<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

define('TAB', 'settings');
define('TITLE', 'Alkaline Configuration');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<form id="configuration">
	<h1>Configuration</h1>
	
	<h3>Shoebox</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="shoe_exif" name="shoe_exif" checked="checked" /></td>
			<td class="description">
				<label for="shoe_exif">Import EXIF camera data</label> (when available)
			</td>
		</tr>
		<tr>
			<td class="input"><input type="checkbox" id="shoe_iptc" name="shoe_iptc" checked="checked" /></td>
			<td class="description">
				<label for="shoe_iptc">Import IPTC keyword data</label> (when available)
			</td>
		</tr>
		<tr>
			<td class="input"><input type="checkbox" id="shoe_geo" name="shoe_geo" checked="checked" /></td>
			<td class="description">
				<label for="shoe_geo">Import geolocation data</label> (when available)<br />
				Use <select name="shoe_geo_prov" style="font-size: .9em;"><option value="bing">Bing</option><option value="google">Google</option><option value="yahoo">Yahoo</option></select> to map locations
			</td>
		</tr>
	</table>
	
	<h3>Photos</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="photo_original" name="photo_original" checked="checked" /></td>
			<td class="description">
				<label for="photo_original">Protect original files</label><br />
				Save your high-resolution originals in a password-protected folder
			</td>
		</tr>
	</table>
	
	<h3>Comments</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="comm_enabled" name="comm_enabled" /></td>
			<td class="description">
				<label for="comm_enabled">Enable comments</label>
			</td>
		</tr>
		<tr>
			<td class="input"><input type="checkbox" id="comm_email" name="comm_email" /></td>
			<td class="description">
				<label for="comm_email">Email new comments to administrator</label>
			</td>
		</tr>
		<tr>
			<td class="input"><input type="checkbox" id="comm_mod" name="comm_mod" /></td>
			<td class="description">
				<label for="comm_mod">Moderate visitor comments</label><br />
				Require administrator approval before visitor comments appear
			</td>
		</tr>
	</table>

	<h3>Statistics</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="stat_enabled" name="stat_enabled" checked="checked" /></td>
			<td class="description">
				<label for="stat_enabled">Enable statistics</label>
			</td>
		</tr>
	</table>
	
	<h3>Diagnostics</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="maint_debug" name="maint_debug" /></td>
			<td class="description">
				<label for="maint_debug">Enable debug mode</label><br />
				Incorporates technical data to the footer of every page
			</td>
		</tr>
		<tr>
			<td class="input"><input type="checkbox" id="maint_disable" name="maint_disable" /></td>
			<td class="description">
				<label for="maint_disable">Disable all extensions</label>
			</td>
		</tr>
	</table>
	
	<p><input type="submit" name="configuration_save" value="Save changes" /> or <a href="<?php echo BASE . ADMIN; ?>customize/">cancel</a></p>
</form>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>