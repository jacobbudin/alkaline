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

// Check for updates
$latest = @$alkaline->boomerang('latest');
if($latest['build'] > Alkaline::build){
	$alkaline->addNote('A new version of Alkaline (v' . $latest['version'] . ') is available. Learn more and download the update at <a href="http://www.alkalineapp.com/">alkalineapp.com</a>.', 'notice');
}

define('TAB', 'settings');
define('TITLE', 'Alkaline Settings');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="overview" class="span-24 last">
		<div class="actions"><a href="<?php echo BASE . 'cs.php'; ?>"><button>Go to compatibility suite</button></a></div>
	
	<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/overview.png" alt="" /> Overview</h1>

	<h2>Alkaline</h2>
	<table>
		<tr>
			<td class="right">Product:</td>
			<td><?php echo Alkaline::product ?></td>
		</tr>
		<tr>
			<td class="right">Version:</td>
			<td><?php echo Alkaline::version; ?> <span class="small">(<?php echo Alkaline::build; ?>)</span></td>
		</tr>
		<tr>
			<td class="right">Database type:</td>
			<td>
				<?php
				
				switch($alkaline->db_type){
					case 'mssql':
						echo 'Microsoft SQL Server';
						break;
					case 'mysql':
						echo 'MySQL';
						break;
					case 'pgsql':
						echo 'PostgreSQL';
						break;
					case 'sqlite':
						echo 'SQLite';
						break;
					default:
						echo 'Unknown';
						break;
				}
				
				?>
			</td>
		</tr>
		<tr>
			<td class="right">Theme:</td>
			<td><?php $theme = $alkaline->getRow('themes', $alkaline->returnConf('theme_id')); if(!empty($theme)){ echo $theme['theme_title'] . ' <span class="small">(' . $theme['theme_build'] . ')</span>'; } else { echo '&#8212;'; } ?></td>
		</tr>
		<tr>
			<td class="right">Extensions:</td>
			<td><?php $orbit = new Orbit(); if(count($orbit->extensions) > 0){ $extensions = array(); foreach($orbit->extensions as $extension){ $extensions[] = $extension['extension_title'] . ' <span class="small">(' . $extension['extension_build'] . ')</span>'; } echo implode(', ', $extensions); } else{ echo '&#8212;'; } ?></td>
		</tr>
	</table>

	<h2>Environment</h2>
	<table>
		<tr>
			<td class="right">Server OS:</td>
			<td><?php echo preg_replace('#\/([0-9.]*)#si', ' <span class="small">(\\1)</span> ', $_SERVER['SERVER_SOFTWARE']); ?></td>
		</tr>
		<tr>
			<td class="right">PHP version:</td>
			<td><?php echo phpversion(); ?></td>
		</tr>
		<tr>
			<td class="right">
				<?php
				
				switch($alkaline->db_type){
					case 'mssql':
						echo 'Microsoft SQL Server';
						break;
					case 'mysql':
						echo 'MySQL';
						break;
					case 'pgsql':
						echo 'PostgreSQL';
						break;
					case 'sqlite':
						echo 'SQLite';
						break;
					default:
						echo 'Unknown';
						break;
				}
				
				?>
				version:
			</td>
			<td>
				<?php echo $alkaline->db_version; ?>
			</td>
		</tr>
		<tr>
			<td class="right">GD version:</td>
			<td><?php if($gd_info = @gd_info()){ preg_match('#[0-9.]+#s', $gd_info['GD Version'], $version); echo $version[0]; } else { echo 'Not installed'; } ?></td>
		</tr>
		<tr>
			<td class="right">ImageMagick version:</td>
			<td><?php if(class_exists('Imagick', false)){ $im_info = Imagick::getVersion(); preg_match('#[0-9.]+#s', $im_info['versionString'], $version); echo $version[0]; } else { echo 'Not installed'; } ?></td>
		</tr>
	</table>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>