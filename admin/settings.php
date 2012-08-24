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

// Enter, exit recovery mode
if(isset($_REQUEST['recovery'])){
	if($_REQUEST['recovery'] == 1){
		$user->setPref('recovery_mode', true);
		$alkaline->addNote('You have entered recovery mode.', 'success');
	}
	else{
		$user->setPref('recovery_mode', false);
		$alkaline->addNote('You have exited recovery mode.', 'success');
	}
	$user->savePref();
	
	header('Location: ' . BASE . ADMIN . 'dashboard' . URL_CAP);
	exit();
}

if($user->returnPref('recovery_mode') == true){
	$recovery_action = '<a href="?recovery=0" title="Recovery mode allows you to recover deleted images, posts, and more." class="tip"><button>Exit recovery mode</button></a>';
}
else{
	$recovery_action = '<a href="?recovery=1" title="Recovery mode allows you to recover deleted images, posts, and more." class="tip"><button>Enter recovery mode</button></a>';
}


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
	<div class="actions">
		<?php echo $recovery_action; ?>
		<a href="<?php echo BASE . 'cs.php'; ?>"><button>Go to compatibility suite</button></a>
	</div>
	
	<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/overview.png" alt="" /> Overview</h1>
	
	<?php ob_start(); ?>

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
			<td class="right">Database:</td>
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
			<td class="right">HTTP server:</td>
			<td><?php echo preg_replace('#\/([0-9.]*).*#si', ' (\\1) ', $_SERVER['SERVER_SOFTWARE']); ?></td>
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
			<td><?php if(class_exists('Imagick', false)){ $imagick = new Imagick; $im_info = $imagick->getVersion(); preg_match('#[0-9.]+#s', $im_info['versionString'], $version); echo $version[0]; } else { echo 'Not installed'; } ?></td>
		</tr>
		<tr>
			<td class="right">Sphinx status:</td>
			<td>
				<?php
				
				if(class_exists('SphinxClient', false)){
					$sphinx = new SphinxClient;
					if(method_exists($sphinx, 'status')){
						$status = $sphinx->status();
						if($status === false){ echo 'Not running'; }
						else{ echo 'Running&#0133;'; }
					}
					else{
						echo 'Installed';
					}
				}
				else{
					echo 'Not installed';
				}
				
				?>
			</td>
		</tr>
	</table>
	
	<?php
	
	$settings = ob_get_contents();
	ob_end_clean();
	echo $settings;
	
	?>
	
	<div class="actions" style="margin-top:0">
		<a href="<?php echo BASE . ADMIN . 'tasks/send-diagnostic-report.php'; ?>" class="tip" title="Sends a diagnostic report to Alkaline engineers to help identify the cause of your support case."><button>Send diagnostic report</button></a>
	</div>
	
	<h2>Diagnostics</h2>
	
	<p>
		<span class="switch">&#9656;</span> <a href="#" class="show">Show diagnostic report</a></span>
	</p>
	<div class="reveal">
		<?php
	
		ob_start();
		phpinfo();
		$phpinfo = ob_get_contents();
		ob_end_clean();
	
		?>
		<textarea style="height: 30em;" class="code"><?php
			// Cache
			require_once(PATH . CLASSES . 'cache_lite/Lite.php');

			// Set a few options
			$options = array(
			    'cacheDir' => PATH . CACHE,
			    'lifeTime' => 30,
			);

			// Create a Cache_Lite object
			$cache = new Cache_Lite($options);

			if($report = $cache->get('diagnostic_report')){
				echo $report;
			}
			else{
				$report = trim(strip_tags(preg_replace('#\n\s+#si', "\n", $settings))) . "\n\n";
				$report .= trim(strip_tags(preg_replace('#<style type="text/css">.*?</style>|<title>.*?</title>#si', '', $phpinfo)));
				$report = preg_replace("#\n{3,}#", "\n\n", $report);
				$report = preg_replace("#\x20{2,}|\t{2,}#", ' ', $report);
				$cache->save($report);
			}
			
			echo $report; ?></textarea>
	</div>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>