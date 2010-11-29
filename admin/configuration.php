<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$orbit = new Orbit;
$user = new User;

$user->perm(true);

if(!empty($_POST['configuration_save'])){
	$theme_id = intval($_POST['theme_id']);
	if($_POST['theme_id'] != $alkaline->returnConf('theme_id')){
		$theme = $alkaline->getRow('themes', $theme_id);
		$theme_folder = $theme['theme_folder'];
		
		$alkaline->setConf('theme_id', $theme_id);
		$alkaline->setConf('theme_folder', $theme_folder);
	}
	
	$alkaline->setConf('web_name', @$_POST['web_name']);
	$alkaline->setConf('web_title', @$_POST['web_title']);
	$alkaline->setConf('web_description', @$_POST['web_description']);
	$alkaline->setConf('web_email', @$_POST['web_email']);
	$alkaline->setConf('web_timezone', @$_POST['web_timezone']);
	$alkaline->setConf('shoe_exif', @$_POST['shoe_exif']);
	$alkaline->setConf('shoe_iptc', @$_POST['shoe_iptc']);
	$alkaline->setConf('shoe_geo', @$_POST['shoe_geo']);
	$alkaline->setConf('photo_markup', @$_POST['photo_markup']);
	if(@$_POST['photo_markup'] == ''){ $_POST['photo_markup_ext'] = ''; }
	
	$alkaline->setConf('photo_markup_ext', @$_POST['photo_markup_ext']);
	$alkaline->setConf('thumb_imagick', @$_POST['thumb_imagick']);
	$alkaline->setConf('thumb_compress', @$_POST['thumb_compress']);
	if(@$_POST['thumb_compress'] == ''){ $_POST['thumb_compress_tol'] = 100; }
	
	$alkaline->setConf('thumb_compress_tol', intval(@$_POST['thumb_compress_tol']));
	$alkaline->setConf('thumb_watermark', @$_POST['thumb_watermark']);
	$alkaline->setConf('thumb_watermark_pos', @$_POST['thumb_watermark_pos']);
	$alkaline->setConf('thumb_watermark_margin', intval(@$_POST['thumb_watermark_margin']));
	$alkaline->setConf('photo_original', @$_POST['photo_original']);
	$alkaline->setConf('tag_alpha', @$_POST['tag_alpha']);
	$alkaline->setConf('comm_enabled', @$_POST['comm_enabled']);
	$alkaline->setConf('comm_email', @$_POST['comm_email']);
	$alkaline->setConf('comm_mod', @$_POST['comm_mod']);
	$alkaline->setConf('comm_markup', @$_POST['comm_markup']);
	if(@$_POST['comment_markup'] == ''){ $_POST['comment_markup_ext'] = ''; }
	
	$alkaline->setConf('comm_markup_ext', @$_POST['comm_markup_ext']);
	$alkaline->setConf('rights_default', @$_POST['rights_default']);
	$alkaline->setConf('rights_default_id', @$_POST['rights_default_id']);
	$alkaline->setConf('stat_enabled', @$_POST['stat_enabled']);
	$alkaline->setConf('maint_reports', @$_POST['maint_reports']);
	$alkaline->setConf('maint_debug', @$_POST['maint_debug']);
	$alkaline->setConf('maint_disable', @$_POST['maint_disable']);
	
	if($alkaline->saveConf()){
		$alkaline->addNotification('The configuration has been saved.', 'success');
	}
	else{
		$alkaline->addNotification('The configuration could not be saved.', 'error');
	}
	
	header('Location: ' . BASE . ADMIN . 'settings' . URL_CAP);
	exit();
}

define('TAB', 'settings');
define('TITLE', 'Alkaline Configuration');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<form action="" id="configuration" method="post">
	<h1>Configuration</h1>
	
	<h3>General</h3>
	
	<table style="width: 70%">
		<tr>
			<td class="right middle"><label for="web_title">Name:</label></td>
			<td><input type="text" id="web_name" name="web_name" value="<?php echo $alkaline->returnConf('web_name'); ?>" class="m" /></td>
		</tr>
		<tr>
			<td class="right middle"><label for="web_title">Title:</label></td>
			<td><input type="text" id="web_title" name="web_title" value="<?php echo $alkaline->returnConf('web_title'); ?>" class="m" /></td>
		</tr>
		<tr>
			<td class="right pad"><label for="web_description">Description:</label></td>
			<td><textarea id="web_description" name="web_description" style="height: 70px; line-height: 1.5em;"><?php echo $alkaline->returnConf('web_description'); ?></textarea></td>
		</tr>
		<tr>
			<td class="right pad"><label for="web_email">Email:</label></td>
			<td>
				<input type="text" id="web_email" name="web_email" value="<?php echo $alkaline->returnConf('web_email'); ?>" class="m" /><br />
				Notifications will be sent from this email address
			</td>
		</tr>
		<tr>
			<td class="right pad"><label for="theme_id">Theme:</label></td>
			<td>
				<?php echo $alkaline->showThemes('theme_id', $alkaline->returnConf('theme_id')); ?>
			</td>
		</tr>
		<tr>
			<td class="right pad"><label for="web_timezone">Time zone:</label></td>
			<td>
				<?php

				$timezones = timezone_abbreviations_list();
				$continents = array('Africa', 'America', 'Antarctica', 'Arctic', 'Asia', 'Atlantic', 'Australia', 'Europe', 'Indian', 'Pacific');
				$places = array();

				foreach($timezones as $unknown){
					foreach($unknown as $timezone){
						$cities = array();
						$timezone_id = $timezone['timezone_id'];
						$parts = explode('/', $timezone_id);
						$continent = $parts[0];
						if(in_array($continent, $continents)){
							$city = str_replace('_', ' ', $parts[1]);
							$district = @str_replace('_', ' ', $parts[2]);
							if(empty($district)){
								$places[$continent][$timezone_id] = $city;
							}
							else{
								$places[$continent][$timezone_id] = $city . ' (' . $district . ')';
							}
						}
					}
				}
				
				$web_timezone = $alkaline->returnConf('web_timezone');
				
				echo '<select id="web_timezone" name="web_timezone">';

				foreach($places as $continent => $cities){
					echo '<optgroup label="' . $continent . '">';
						natsort($cities);
						foreach($cities as $abbr => $city){
							echo '<option value="' . $abbr . '"';
							if($abbr == $web_timezone){
								echo 'selected="selected"';
							}
							echo '>' . $city . '</option>';
						}
					echo '</optgroup>';
				}

				echo '</select>';

				?>
			</td>
		</tr>
	</table>
	
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
				Save your high-resolution originals behind a password-protected firewall
			</td>
		</tr>
		<tr class="markup">
			<td class="pad"><input type="checkbox" id="photo_markup" name="photo_markup" <?php echo $alkaline->readConf('photo_markup'); ?> value="true" /></td>
			<td>
				<label for="photo_markup">Markup new photo descriptions using <select name="photo_markup_ext" title="<?php echo $alkaline->returnConf('photo_markup_ext'); ?>"><?php $orbit->hook('markup_html'); ?></select></label><br />
				To use this setting for all your photos, save your configuration and then <a href="<?php echo BASE . ADMIN . 'maintenance' . URL_CAP . '#reset-photo-markup' ?>">reset your photo markup</a>
			</td>
		</tr>
	</table>
	
	<h3>Thumbnails</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="thumb_imagick" name="thumb_imagick" <?php echo $alkaline->readConf('thumb_imagick'); ?> value="true" /></td>
			<td class="description">
				<label for="thumb_imagick">Use ImageMagick library</label> (if installed)<br />
				Create higher-quality thumbnails at the cost of increased system resources
			</td>
		</tr>
		<tr>
			<td class="input"><input type="checkbox" id="thumb_compress" name="thumb_compress" <?php echo $alkaline->readConf('thumb_compress'); ?> value="true" /></td>
			<td class="description">
				<label for="thumb_compress">Compress thumbnails to reduce file size</label><br />
				Use
				<select name="thumb_compress_tol">
					<option value="95" <?php echo $user->readConf('thumb_compress_tol', '95'); ?>>very low</option>
					<option value="90" <?php echo $user->readConf('thumb_compress_tol', '90'); ?>>low</option>
					<option value="85" <?php echo $user->readConf('thumb_compress_tol', '85'); ?>>medium</option>
					<option value="70" <?php echo $user->readConf('thumb_compress_tol', '70'); ?>>high</option>
				</select>
				compression when producing thumbnails
			</td>
		</tr>
		<tr>
			<td class="input"><input type="checkbox" id="thumb_watermark" name="thumb_watermark" <?php echo $alkaline->readConf('thumb_watermark'); ?> value="true" /></td>
			<td class="description">
				<label for="thumb_watermark">Apply watermark</label> (on selected thumbnails)<br />
				Apply the <a href="<?php echo BASE; ?>watermark.png">alpha-transparent PNG image</a> to the
				<select name="thumb_watermark_pos">
					<option value="nw" <?php echo $user->readConf('thumb_watermark_pos', 'nw'); ?>>NW corner</option>
					<option value="ne" <?php echo $user->readConf('thumb_watermark_pos', 'ne'); ?>>NE corner</option>
					<option value="sw" <?php echo $user->readConf('thumb_watermark_pos', 'sw'); ?>>SW corner</option>
					<option value="se" <?php echo $user->readConf('thumb_watermark_pos', 'se'); ?>>SE corner</option>
					<option value="00" <?php echo $user->readConf('thumb_watermark_pos', '00'); ?>>centroid</option>
					<option value="n0" <?php echo $user->readConf('thumb_watermark_pos', 'n0'); ?>>north center</option>
					<option value="s0" <?php echo $user->readConf('thumb_watermark_pos', 's0'); ?>>south center</option>
					<option value="0e" <?php echo $user->readConf('thumb_watermark_pos', '0e'); ?>>middle east</option>
					<option value="0w" <?php echo $user->readConf('thumb_watermark_pos', '0w'); ?>>middle west</option>
				</select>
				of thumbnails with a
				<select name="thumb_watermark_margin">
					<option value="0" <?php echo $user->readConf('thumb_watermark_margin', '0'); ?>>0</option>
					<option value="5" <?php echo $user->readConf('thumb_watermark_margin', '5'); ?>>5</option>
					<option value="10" <?php echo $user->readConf('thumb_watermark_margin', '10'); ?>>10</option>
					<option value="25" <?php echo $user->readConf('thumb_watermark_margin', '25'); ?>>25</option>
					<option value="50" <?php echo $user->readConf('thumb_watermark_margin', '50'); ?>>50</option>
					<option value="100" <?php echo $user->readConf('thumb_watermark_margin', '100'); ?>>100</option>
				</select>
				pixel margin
			</td>
		</tr>
	</table>
	
	<h3>Tags</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="tag_alpha" name="tag_alpha" <?php echo $alkaline->readConf('tag_alpha'); ?> value="true" /></td>
			<td class="description">
				<label for="tag_alpha">Sort tags in alphabetical order</label> (instead of by order added)
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
		<tr>
			<td class="pad"><input type="checkbox" id="comm_markup" name="comm_markup" <?php echo $alkaline->readConf('comm_markup'); ?> value="true" /></td>
			<td>
				<label for="comm_markup">Markup new comments using <select name="comm_markup_ext" title="<?php echo $alkaline->returnConf('comm_markup_ext'); ?>"><?php $orbit->hook('markup_html'); ?></select></label><br />
				To use this setting for all your comments, save your configuration and then <a href="<?php echo BASE . ADMIN . 'maintenance' . URL_CAP . '#reset-comment-markup' ?>">reset your comment markup</a>
			</td>
		</tr>
	</table>
	
	<h3>Rights</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="rights_default" name="rights_default" <?php echo $alkaline->readConf('rights_default'); ?> value="true" /></td>
			<td class="description">
				<label for="rights_default">Attach rights set to new photos</label><br />
				Use the <?php echo $alkaline->showRights('rights_default_id', $alkaline->returnConf('rights_default_id')); ?> rights set
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
			<td class="input"><input type="checkbox" id="maint_reports" name="maint_reports" <?php echo $alkaline->readConf('maint_reports'); ?> value="true" /></td>
			<td class="description">
				<label for="maint_reports">Send anonymous system profile and usage data</label><br />
				Transparently transmits nonidentifiable data to help improve Alkaline
			</td>
		</tr>
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
	
	<p><input type="submit" name="configuration_save" value="Save changes" /> or <a href="<?php echo $alkaline->back(); ?>">cancel</a></p>
</form>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>