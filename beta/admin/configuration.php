<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

define('TITLE', 'Alkaline Configuration');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="configuration" class="container">
	
	<h2>Configuration</h2>
	
	<p>Modify your library&#8217;s behavior with the options below.</p>
	
	<form>
		<h3>Shoebox</h3>
		
		<table>
			<tr>
				<td class="input"><input type="checkbox" name="shoe_exif" checked="checked" /></td>
				<td class="description">
					<strong>Import EXIF camera data</strong> (when available)
				</td>
			</tr>
			<tr>
				<td class="input"><input type="checkbox" name="shoe_iptc" checked="checked" /></td>
				<td class="description">
					<strong>Import IPTC keyword data</strong> (when available)
				</td>
			</tr>
			<tr>
				<td class="input"><input type="checkbox" name="shoe_geo" checked="checked" /></td>
				<td class="description">
					<strong>Import geolocation data</strong> (when available)<br />
					Use <select name="shoe_geo_prov" style="font-size: .9em;"><option value="bing">Bing</option><option value="google">Google</option><option value="yahoo">Yahoo</option></select> to map locations
				</td>
			</tr>
			<tr>
				<td class="input"><input type="checkbox" name="shoe_twitter" checked="checked" /></td>
				<td class="description">
					<strong>Send to Twitter</strong><br />
					Post the name of and a link to one photo at random each time you add photos to your library
				</td>
			</tr>
		</table>
		
		<h3>Photos</h3>
		
		<table>
			<tr>
				<td class="input"><input type="checkbox" name="shoe_geo" checked="checked" /></td>
				<td class="description">
					<strong>Protect original files</strong><br />
					Save your high-resolution originals in a password-protected folder
				</td>
			</tr>
		</table>
		
		<h3>Comments</h3>
		
		<table>
			<tr>
				<td class="input"><input type="checkbox" name="comm_enabled" /></td>
				<td class="description">
					<strong>Enable comments</strong>
				</td>
			</tr>
			<tr>
				<td class="input"><input type="checkbox" name="comm_email" /></td>
				<td class="description">
					<strong>Email new comments to administrator</strong>
				</td>
			</tr>
			<tr>
				<td class="input"><input type="checkbox" name="comm_mod" /></td>
				<td class="description">
					<strong>Moderate anonymous comments</strong><br />
					Require administrator approval before anonymous comments appear
				</td>
			</tr>
		</table>
	
		<h3>Statistics</h3>
		
		<table>
			<tr>
				<td class="input"><input type="checkbox" name="stat_enabled" checked="checked" /></td>
				<td class="description">
					<strong>Enable statistics</strong>
				</td>
			</tr>
		</table>
		
		<h3>Diagnostics</h3>
		
		<table>
			<tr>
				<td class="input"><input type="checkbox" name="maint_debug" /></td>
				<td class="description">
					<strong>Enable debug mode</strong><br />
					Incorporates technical data to the footer of every page
				</td>
			</tr>
			<tr>
				<td class="input"><input type="checkbox" name="maint_disable" /></td>
				<td class="description">
					<strong>Disable all extensions</strong>
				</td>
			</tr>
		</table>
		
		<p><input type="submit" name="configuration_save" value="Save changes" /></p>
		
	</form>
	
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>