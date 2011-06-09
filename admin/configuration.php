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
$orbit = new Orbit;
$user = new User;

$user->perm(true, 'configuration');

if(!empty($_POST['configuration_save'])){
	$theme_id = intval($_POST['theme_id']);
	if($_POST['theme_id'] != $alkaline->returnConf('theme_id')){
		$theme = $alkaline->getRow('themes', $theme_id);
		
		$alkaline->setConf('theme_id', $theme_id);
		$alkaline->setConf('theme_folder', $theme['theme_folder']);
	}
	
	$page_size_id = intval($_POST['page_size_id']);
	if($_POST['page_size_id'] != $alkaline->returnConf('page_size_id')){
		$size = $alkaline->getRow('sizes', $page_size_id);
		
		$alkaline->setConf('page_size_id', $page_size_id);
		$alkaline->setConf('page_size_label', $size['size_label']);
	}
	
	$post_size_id = intval($_POST['post_size_id']);
	if($_POST['post_size_id'] != $alkaline->returnConf('post_size_id')){
		$size = $alkaline->getRow('sizes', $post_size_id);
		
		$alkaline->setConf('post_size_id', $post_size_id);
		$alkaline->setConf('post_size_label', $size['size_label']);
	}
	
	$alkaline->setConf('web_name', @$_POST['web_name']);
	$alkaline->setConf('web_title', @$_POST['web_title']);
	$alkaline->setConf('web_title_format', @$_POST['web_title_format']);
	$alkaline->setConf('web_description', @$_POST['web_description']);
	$alkaline->setConf('web_email', @$_POST['web_email']);
	$alkaline->setConf('web_timezone', @$_POST['web_timezone']);
	$alkaline->setConf('shoe_exif', @$_POST['shoe_exif']);
	$alkaline->setConf('shoe_iptc', @$_POST['shoe_iptc']);
	$alkaline->setConf('shoe_geo', @$_POST['shoe_geo']);
	$alkaline->setConf('image_hdm', @$_POST['image_hdm']);
	$alkaline->setConf('image_hdm_format', @$_POST['image_hdm_format']);
	$alkaline->setConf('web_markup', @$_POST['web_markup']);
	if(@$_POST['web_markup'] == ''){ $_POST['web_markup_ext'] = ''; }
	
	$alkaline->setConf('web_markup_ext', @$_POST['web_markup_ext']);
	
	$alkaline->setConf('post_markup_ext', @$_POST['post_markup_ext']);
	$alkaline->setConf('post_div_wrap', @$_POST['post_div_wrap']);
	$alkaline->setConf('post_div_wrap_class', @$_POST['post_div_wrap_class']);
	$alkaline->setConf('bulk_delete', @$_POST['bulk_delete']);
	
	$alkaline->setConf('thumb_imagick', @$_POST['thumb_imagick']);
	$alkaline->setConf('thumb_compress', @$_POST['thumb_compress']);
	if(@$_POST['thumb_compress'] == ''){ $_POST['thumb_compress_tol'] = 100; }
	
	$alkaline->setConf('thumb_compress_tol', intval(@$_POST['thumb_compress_tol']));
	$alkaline->setConf('thumb_watermark', @$_POST['thumb_watermark']);
	$alkaline->setConf('thumb_watermark_pos', @$_POST['thumb_watermark_pos']);
	$alkaline->setConf('thumb_watermark_margin', intval(@$_POST['thumb_watermark_margin']));
	$alkaline->setConf('tag_alpha', @$_POST['tag_alpha']);
	$alkaline->setConf('page_div_wrap', @$_POST['page_div_wrap']);
	$alkaline->setConf('page_div_wrap_class', @$_POST['page_div_wrap_class']);
	$alkaline->setConf('comm_enabled', @$_POST['comm_enabled']);
	$alkaline->setConf('comm_email', @$_POST['comm_email']);
	$alkaline->setConf('comm_mod', @$_POST['comm_mod']);
	$alkaline->setConf('comm_markup', @$_POST['comm_markup']);
	if(@$_POST['comment_markup'] == ''){ $_POST['comment_markup_ext'] = ''; }
	
	$alkaline->setConf('comm_markup_ext', @$_POST['comm_markup_ext']);
	$alkaline->setConf('comm_allow_html', @$_POST['comm_allow_html']);
	$alkaline->setConf('comm_allow_html_tags', @$_POST['comm_allow_html_tags']);
	
	$alkaline->setConf('trackback_enabled', @$_POST['trackback_enabled']);
	$alkaline->setConf('trackback_email', @$_POST['trackback_email']);
	
	$alkaline->setConf('guest_remember', @$_POST['guest_remember']);
	$alkaline->setConf('guest_remember_time', @$_POST['guest_remember_time']);
	
	$alkaline->setConf('rights_default', @$_POST['rights_default']);
	$alkaline->setConf('rights_default_id', @$_POST['rights_default_id']);
	$alkaline->setConf('stat_enabled', @$_POST['stat_enabled']);
	$alkaline->setConf('stat_ignore_user', @$_POST['stat_ignore_user']);
	$alkaline->setConf('canvas_remove_unused', @$_POST['canvas_remove_unused']);
	$alkaline->setConf('syndication_cache_time', @$_POST['syndication_cache_time']);
	$alkaline->setConf('syndication_summary_only', @$_POST['syndication_summary_only']);
	$alkaline->setConf('sphinx_enabled', @$_POST['sphinx_enabled']);
	$alkaline->setConf('sphinx_server', @$_POST['sphinx_server']);
	$alkaline->setConf('sphinx_port', @$_POST['sphinx_port']);
	$alkaline->setConf('sphinx_index', @$_POST['sphinx_index']);
	$alkaline->setConf('sphinx_max_exec', @$_POST['sphinx_max_exec']);
	$alkaline->setConf('maint_reports', @$_POST['maint_reports']);
	$alkaline->setConf('maint_debug', @$_POST['maint_debug']);
	$alkaline->setConf('maint_disable', @$_POST['maint_disable']);
	
	if($alkaline->saveConf()){
		$alkaline->addNote('The configuration has been saved.', 'success');
	}
	else{
		$alkaline->addNote('The configuration could not be saved.', 'error');
	}
	
	header('Location: ' . BASE . ADMIN . 'settings' . URL_CAP);
	exit();
}

define('TAB', 'settings');
define('TITLE', 'Alkaline Configuration');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<form action="" id="configuration" method="post">
	<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/configuration.png" alt="" /> Configuration</h1>
	
	<h3>General</h3>
	
	<table style="width: 70%">
		<tr>
			<td class="right middle"><label for="web_name">Name:</label></td>
			<td><input type="text" id="web_name" name="web_name" value="<?php echo $alkaline->returnConf('web_name'); ?>" class="m" /></td>
		</tr>
		<tr>
			<td class="right middle"><label for="web_title">Source:</label></td>
			<td><input type="text" id="web_title" name="web_title" value="<?php echo $alkaline->returnConf('web_title'); ?>" class="m" /></td>
		</tr>
		<tr>
			<td class="right middle"><label for="web_title_format">Title formatting:</label></td>
			<td>
				<select name="web_title_format">
					<option value="" <?php echo $user->readConf('web_title_format', ''); ?>>Source: Title</option>
					<option value="emdash" <?php echo $user->readConf('web_title_format', 'emdash'); ?>>Title &#8212; Source</option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="right"><label for="web_description">Description:</label></td>
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
			<td class="right middle"><label for="theme_id">Theme:</label></td>
			<td>
				<?php echo $alkaline->showThemes('theme_id', $alkaline->returnConf('theme_id')); ?>
			</td>
		</tr>
		<tr>
			<td class="right middle"><label for="web_timezone">Time zone:</label></td>
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
							
							if(isset($parts[2])){
								$district = str_replace('_', ' ', $parts[2]);
							}
							else{
								$district = '';
							}
							
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
		<tr>
			<td></td>
			<td></td>
		</tr>
		<tr class="markup">
			<td class="input right middle"><input type="checkbox" id="web_markup" name="web_markup" <?php echo $alkaline->readConf('web_markup'); ?> value="true" /></td>
			<td>
				<label for="web_markup">Markup future content (except visitor comments) using <select name="web_markup_ext" title="<?php echo $alkaline->returnConf('web_markup_ext'); ?>"><?php $orbit->hook('markup_html'); ?></select></label>
			</td>
		</tr>
	</table><br />
	
	<h3>Shoebox</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="shoe_exif" name="shoe_exif" <?php echo $alkaline->readConf('shoe_exif'); ?> value="true" /></td>
			<td class="description">
				<label for="shoe_exif">Import EXIF camera data</label> (as available)
			</td>
		</tr>
		<tr>
			<td class="input"><input type="checkbox" id="shoe_iptc" name="shoe_iptc" <?php echo $alkaline->readConf('shoe_iptc'); ?> value="true" /></td>
			<td class="description">
				<label for="shoe_iptc">Import IPTC keyword data</label> (as available)
			</td>
		</tr>
		<tr>
			<td class="input"><input type="checkbox" id="shoe_geo" name="shoe_geo" <?php echo $alkaline->readConf('shoe_geo'); ?> value="true" /></td>
			<td class="description">
				<label for="shoe_geo">Import geolocation data</label> (as available)
			</td>
		</tr>
	</table>
	
	<h3>Images</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="image_hdm" name="image_hdm" <?php echo $alkaline->readConf('image_hdm'); ?> value="true" /></td>
			<td class="description">
				<label for="image_hdm">
					Enable hierarchical directory mode using
					<select name="image_hdm_format">
						<option value="yyyy/mm/dd" <?php echo $user->readConf('image_hdm_format', 'yyyy/mm/dd'); ?>>date uploaded-based (YYYY/MM/DD)</option>
						<option value="1000" <?php echo $user->readConf('image_hdm_format', '1000'); ?>>ID-based (1000, 2000, 3000)</option>
					</select>
					format
				</label><br />
				Recommended for efficiently storing large image libraries, <a href="<?php echo BASE . ADMIN . 'maintenance' . URL_CAP; ?>">reorganize your image library</a> after changing this setting
			</td>
		</tr>
	</table>
	
	<h3>Posts</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="post_size_id" name="post_size_id" disabled="disabled" checked="checked" /></td>
			<td class="description">
				<label for="post_size_id">Use the thumbnail size <?php echo $alkaline->showSizes('post_size_id', $alkaline->returnConf('post_size_id')); ?> when adding images by point-and-click</label>
			</td>
		</tr>
		<tr>
			<td class="input middle"><input type="checkbox" id="post_div_wrap" name="post_div_wrap" <?php echo $alkaline->readConf('post_div_wrap'); ?> value="true" /></td>
			<td>
				<label for="post_div_wrap">Wrap thumbnails in a &#0060;div&#0062; wrapper with the classes:</label> <input type="text" id="post_div_wrap_class" name="post_div_wrap_class" value="<?php echo $alkaline->returnConf('post_div_wrap_class'); ?>" class="xs" />
			</td>
		</tr>
	</table>
	
	<h3>Bulk Editor</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="bulk_delete" name="bulk_delete" <?php echo $alkaline->readConf('bulk_delete'); ?> value="true" /></td>
			<td class="description">
				<label for="bulk_delete">Allow users to delete images using the bulk editor</label>
			</td>
		</tr>
	</table>
	
	<h3>Thumbnails</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="thumb_imagick" name="thumb_imagick" <?php echo $alkaline->readConf('thumb_imagick'); if(!class_exists('imagick')){ echo 'disabled="disabled"'; } ?> value="true" /></td>
			<td class="description">
				<label for="thumb_imagick">Use ImageMagick library</label>
				<?php if(!class_exists('imagick')){ echo '(not installed)'; } ?>
				<br />
				Create superior thumbnails at the cost of increased system resources
			</td>
		</tr>
		<tr>
			<td class="input"><input type="checkbox" id="thumb_compress" name="thumb_compress" <?php echo $alkaline->readConf('thumb_compress'); ?> value="true" /></td>
			<td class="description">
				<label for="thumb_compress">Compress thumbnails to reduce file size and conserve bandwidth</label><br />
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
				Apply the <a href="<?php echo BASE . WATERMARKS; ?>watermark.png">alpha-transparent PNG image</a> to the
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
	
	<h3>Pages</h3>
	
	<table>
		<tr>
			<td class="input middle"><input type="checkbox" id="page_size_id" name="page_size_id" disabled="disabled" checked="checked" /></td>
			<td class="description">
				<label for="page_size_id">Use the thumbnail size <?php echo $alkaline->showSizes('page_size_id', $alkaline->returnConf('page_size_id')); ?> when adding images by point-and-click</label>
			</td>
		</tr>
		<tr>
			<td class="input middle"><input type="checkbox" id="page_div_wrap" name="page_div_wrap" <?php echo $alkaline->readConf('page_div_wrap'); ?> value="true" /></td>
			<td>
				<label for="page_div_wrap">Wrap thumbnails in a &#0060;div&#0062; wrapper with the classes:</label> <input type="text" id="page_div_wrap_class" name="page_div_wrap_class" value="<?php echo $alkaline->returnConf('page_div_wrap_class'); ?>" class="xs" />
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
			<td class="input"><input type="checkbox" id="comm_markup" name="comm_markup" <?php echo $alkaline->readConf('comm_markup'); ?> value="true" /></td>
			<td>
				<label for="comm_markup">Markup visitor comments using <select name="comm_markup_ext" title="<?php echo $alkaline->returnConf('comm_markup_ext'); ?>"><?php $orbit->hook('markup_html'); ?></select></label>
			</td>
		</tr>
		<tr>
			<td class="input"><input type="checkbox" id="comm_allow_html" name="comm_allow_html" <?php echo $alkaline->readConf('comm_allow_html'); ?> value="true" /></td>
			<td>
				<label for="comm_allow_html">Allow only select HTML in comments</label><br />
				Permit the following HTML tags (for example, &#0060;a&#0062;&#0060;em&#0062;&#0060;strong&#0062;): <input type="text" id="comm_allow_html_tags" name="comm_allow_html_tags" value="<?php echo $alkaline->returnConf('comm_allow_html_tags'); ?>" class="s" />
			</td>
		</tr>
		<tr>
			<td class="input"><input type="checkbox" id="comm_close" name="comm_close" <?php echo $alkaline->readConf('comm_close'); ?> value="true" /></td>
			<td class="description">
				<label for="comm_close">Automatically close items to new comments
					<select name="comm_close_time">
						<option value="86400" <?php echo $user->readConf('comm_close_time', '86400'); ?>>24 hours</option>
						<option value="259200" <?php echo $user->readConf('comm_close_time', '259200'); ?>>three days</option>
						<option value="604800" <?php echo $user->readConf('comm_close_time', '604800'); ?>>one week</option>
						<option value="2592000" <?php echo $user->readConf('comm_close_time', '2592000'); ?>>one month</option>
						<option value="7776000" <?php echo $user->readConf('comm_close_time', '7776000'); ?>>three months</option>
						<option value="31536000" <?php echo $user->readConf('comm_close_time', '31536000'); ?>>one year</option>
					</select>
				after the item&#8217;s publication date</label>
			</td>
		</tr>
	</table>
	
	<h3>Trackbacks</h3>
	
	<p>Trackbacks allow you to monitor discussion of your posts on other Web sites.</p>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="trackback_enabled" name="trackback_enabled" <?php echo $alkaline->readConf('trackback_enabled'); ?> value="true" /></td>
			<td class="description">
				<label for="trackback_enabled">Enable trackbacks</label>
			</td>
		</tr>
		<tr>
			<td class="input"><input type="checkbox" id="trackback_email" name="trackback_email" <?php echo $alkaline->readConf('trackback_email'); ?> value="true" /></td>
			<td class="description">
				<label for="trackback_email">Email new trackbacks to administrator</label>
			</td>
		</tr>
	</table>
	
	<h3>Rights</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="rights_default" name="rights_default" <?php echo $alkaline->readConf('rights_default'); ?> value="true" /></td>
			<td class="description">
				<label for="rights_default">Attach the rights set <?php echo $alkaline->showRights('rights_default_id', $alkaline->returnConf('rights_default_id')); ?> to new images</label>
			</td>
		</tr>
	</table>
	
	<h3>Guests</h3>
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="guest_remember" name="guest_remember" <?php echo $alkaline->readConf('guest_remember'); ?> value="true" /></td>
			<td class="description">
				<label for="guest_remember">Remember guests for
					<select name="guest_remember_time">
						<option value="86400" <?php echo $user->readConf('guest_remember_time', '86400'); ?>>24 hours</option>
						<option value="259200" <?php echo $user->readConf('guest_remember_time', '259200'); ?>>three days</option>
						<option value="604800" <?php echo $user->readConf('guest_remember_time', '604800'); ?>>one week</option>
						<option value="2592000" <?php echo $user->readConf('guest_remember_time', '2592000'); ?>>one month</option>
						<option value="7776000" <?php echo $user->readConf('guest_remember_time', '7776000'); ?>>three months</option>
						<option value="31536000" <?php echo $user->readConf('guest_remember_time', '31536000'); ?>>one year</option>
					</select>
				after their session ends</label>
			</td>
		</tr>
	</table>

	<h3>Statistics</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="stat_enabled" name="stat_enabled" <?php echo $alkaline->readConf('stat_enabled'); ?> value="true" /></td>
			<td class="description">
				<label for="stat_enabled">Enable statistics</label><br />
				Affects only Alkaline&#8217;s built-in visitor tracking, can be disabled if using third-party analytics software
			</td>
		</tr>
		<tr>
			<td class="input"><input type="checkbox" id="stat_ignore_user" name="stat_ignore_user" <?php echo $alkaline->readConf('stat_ignore_user'); ?> value="true" /></td>
			<td class="description">
				<label for="stat_ignore_user">Ignore registered users&#8217; browsing activity</label><br />
			</td>
		</tr>
	</table>
	
	<h3>Canvas</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="canvas_remove_unused" name="canvas_remove_unused" <?php echo $alkaline->readConf('canvas_remove_unused'); ?> value="true" /></td>
			<td class="description">
				<label for="canvas_remove_unused">Remove unused insertions before displaying templates</label>
			</td>
		</tr>
	</table>
	
	<h3>Syndication</h3>
	
	<p><a href="http://atomenabled.org/">Atom</a> XML feeds allow your site&#8217;s visitors to keep track of updates on your site.</p>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" checked="checked" disabled="disabled" /></td>
			<td class="description">
				<label>Cache the newsfeed for
					<select id="syndication_cache_time" name="syndication_cache_time">
						<option value="15" <?php echo $user->readConf('syndication_cache_time', '15'); ?>>15 seconds</option>
						<option value="30" <?php echo $user->readConf('syndication_cache_time', '30'); ?>>30 seconds</option>
						<option value="60" <?php echo $user->readConf('syndication_cache_time', '60'); ?>>1 minute</option>
						<option value="120" <?php echo $user->readConf('syndication_cache_time', '120'); ?>>2 minutes</option>
						<option value="300" <?php echo $user->readConf('syndication_cache_time', '300'); ?>>5 minutes</option>
					</select>
				</label><br />
				Caching the newsfeed longer improves performance at the expense of being out of date
			</td>
		</tr>
		<tr>
			<td class="input"><input type="checkbox" id="syndication_summary_only" name="syndication_summary_only" <?php echo $alkaline->readConf('syndication_summary_only'); ?> value="true" /></td>
			<td class="description">
				<label for="syndication_summary_only">Only include post summaries</label><br />
				Requires visitors to click through to read full posts
			</td>
		</tr>
	</table>
	
	<h3>Sphinx</h3>
	
	<p><a href="http://sphinxsearch.com/">Sphinx</a> is an enterprise-grade search server. It must be manually installed and configured.</p>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="sphinx_enabled" name="sphinx_enabled" <?php echo $alkaline->readConf('sphinx_enabled'); ?> <?php if(!class_exists('SphinxClient', false)){ echo 'disabled="disabled"'; } ?> value="true" /></td>
			<td class="description">
				<label for="sphinx_enabled">Use Sphinx to process search queries</label>
				<?php if(class_exists('SphinxClient', false)){ ?>
					(if installed and configured)
				<?php } else{ ?>
					(not installed)
				<?php } ?>
				
				<p class="slim"><span class="switch">&#9656;</span> <a href="#" class="show">Show advanced options</a></p>

				<div class="reveal">
					<table>
						<tr>
							<td class="right middle"><label for="sphinx_server">Server:</label></td>
							<td><input type="text" id="sphinx_server" name="sphinx_server" class="s" placeholder="localhost" /> <span class="quiet">(optional)</span></td>
						</tr>
						<tr>
							<td class="right middle"><label for="sphinx_port">Port:</label></td>
							<td><input type="text" id="sphinx_port" name="sphinx_port" class="xs" /> <span class="quiet">(optional)</span></td>
						</tr>
						<tr>
							<td class="right middle"><label for="sphinx_index">Index(es):</label></td>
							<td><input type="text" id="sphinx_index" name="sphinx_index" class="s" /> <span class="quiet">(optional)</span></td>
						</tr>
						<tr>
							<td class="right middle"><label for="sphinx_max_exec">Maximum execution time:</label></td>
							<td>
								<select id="sphinx_max_exec" name="sphinx_max_exec">
									<option value="" <?php echo $user->readConf('sphinx_max_exec', ''); ?>>None</option>
									<option value="" <?php echo $user->readConf('sphinx_max_exec', '1'); ?>>1 second</option>
									<option value="" <?php echo $user->readConf('sphinx_max_exec', '3'); ?>>3 seconds</option>
									<option value="" <?php echo $user->readConf('sphinx_max_exec', '5'); ?>>5 seconds</option>
									<option value="" <?php echo $user->readConf('sphinx_max_exec', '10'); ?>>10 seconds</option>
									<option value="" <?php echo $user->readConf('sphinx_max_exec', '30'); ?>>30 seconds</option>
								</select>
							</td>
						</tr>
					</table>
				</div>
			</td>
		</tr>
	</table>
	
	<h3>Diagnostics</h3>
	
	<table>
		<tr>
			<td class="input"><input type="checkbox" id="maint_reports" name="maint_reports" <?php echo $alkaline->readConf('maint_reports'); ?> value="true" /></td>
			<td class="description">
				<label for="maint_reports">Send anonymous system profile and usage data</label><br />
				Transmits nonidentifiable data to <a href="http://www.alkalineapp.com/">alkalineapp.com</a> help improve Alkaline
			</td>
		</tr>
		<tr>
			<td class="input"><input type="checkbox" id="maint_debug" name="maint_debug" <?php echo $alkaline->readConf('maint_debug'); ?> value="true" /></td>
			<td class="description">
				<label for="maint_debug">Enable debug mode</label><br />
				Appends technical data to the footer of pages
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