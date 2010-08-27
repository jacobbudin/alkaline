<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

define('TAB', 'settings');
define('TITLE', 'Alkaline Settings');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div class="span-24 last">
	<div id="overview" class="span-16 colborder">
		<h1>Overview</h1>
	
		<h2>Alkaline</h2>
		<table>
			<tr>
				<td class="right">Product:</td>
				<td>Alkaline Multiuser</td>
			</tr>
			<tr>
				<td class="right">License:</td>
				<td>Jacob Budin, Budin Ltd.</td>
			</tr>
			<tr>
				<td class="right">Version:</td>
				<td><?php echo Alkaline::version; ?> <span class="small">(<?php echo Alkaline::build; ?>)</span></td>
			</tr>
			<tr>
				<td class="right">Theme:</td>
				<td><?php $themes = $alkaline->getTable('themes', null, 1, null, 'theme_default DESC'); $theme = $themes[0]; echo $theme['theme_title'] . ' <span class="small">(' . $theme['theme_build'] . ')</span>'; ?></td>
			</tr>
			<tr>
				<td class="right">Extensions:</td>
				<td><?php $orbit = new Orbit(); if(count($orbit->extensions) > 0){ $extensions = array(); foreach($orbit->extensions as $extension){ $extensions[] = $extension['extension_title'] . ' <span class="small">(' . $extension['extension_build'] . ')</span>'; } echo implode(', ', $extensions); } else{ echo '&#8212;'; } ?></td>
			</tr>
		</table>

		<h2>Environment</h2>
		<table>
			<tr>
				<td class="right">PHP Version:</td>
				<td><?php echo phpversion(); ?></td>
			</tr>
			<tr>
				<td class="right">GD Version:</td>
				<td><?php $gd_info = gd_info(); echo ucfirst($gd_info['GD Version']); ?></td>
			</tr>
		</table>
	</div>
	<div class="span-7 last">
		<h2><a href="<?php echo BASE . ADMIN; ?>access/"><img src="/images/icons/access.png" alt="" /> Users &amp; Guests &#9656;</a></h2>
		<h2><a href="<?php echo BASE . ADMIN; ?>themes/"><img src="/images/icons/themes.png" alt="" /> Themes &#9656;</a></h2>
		<h2><a href="<?php echo BASE . ADMIN; ?>extensions/"><img src="/images/icons/extensions.png" alt="" /> Extensions &#9656;</a></h2>
		<h2><a href="<?php echo BASE . ADMIN; ?>thumbnails/"><img src="/images/icons/thumbnails.png" alt="" /> Thumbnails &#9656;</a></h2>
		<h2><a href="<?php echo BASE . ADMIN; ?>maintenance/"><img src="/images/icons/maintenance.png" alt="" /> Maintenance &#9656;</a></h2>
		<h2><a href="<?php echo BASE . ADMIN; ?>configuration/"><img src="/images/icons/configuration.png" alt="" /> Configuration &#9656;</a></h2>
	</div>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>