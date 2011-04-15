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


// Vitals
$stats = new Stat(strtotime('-30 days'));
$stats->getDaily();

$views = array();

foreach($stats->stats as $stat){
	$views[] = array($stat['stat_ts_js'], $stat['stat_views']);
}

$views = json_encode($views);

$visitors = array();

foreach($stats->stats as $stat){
	$visitors[] = array($stat['stat_ts_js'], $stat['stat_visitors']);
}

$visitors = json_encode($visitors);

define('TAB', 'dashboard');
define('TITLE', 'Alkaline Dashboard');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div class="actions">
	<a href="<?php echo BASE . ADMIN . 'upload' . URL_CAP; ?>"><button>Upload file</button></a>
	<a href="<?php echo BASE . ADMIN . 'posts' . URL_ACT . 'add' . URL_RW; ?>"><button>Write post</button></a>
</div>

<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/dashboard.png" alt="" /> Dashboard</h1>

<div class="span-24 last">
	<div class="span-17 append-1">
		<?php
		if($user->returnConf('stat_enabled') !== false){
			?>
			<div id="statistics_holder" class="statistics_holder"></div>
			<div id="statistics_views" title="<?php echo $views; ?>"></div>
			<div id="statistics_visitors" title="<?php echo $visitors; ?>"></div>
			<?php
		}
		?>
	</div>
	<div class="span-6 last">
		<h3>Hello</h3>
		
		<p>Welcome back! <?php echo ($user->user['user_last_login']) ? 'You last logged in on:  ' . $alkaline->formatTime($user->user['user_last_login'], 'l, F j \a\t g:i a') : ''; ?></p>
		
		<?php
		
		$badges = $alkaline->getBadges();

		if(($badges['library'] > 0) or ($badges['comments'] > 0)){
			?>

			<h3>New</h3>
			<table class="census">
				<?php if($badges['library'] > 0){ ?>
					<tr>
						<td class="right"><?php echo $badges['library']; ?></td>
						<td><a href="<?php echo BASE . ADMIN . 'shoebox' . URL_CAP; ?>">new <?php echo $alkaline->returnCount($badges['library'], 'image'); ?></a></td>
					</tr>
				<?php } ?>
				<?php if($badges['comments'] > 0){ ?>
					<tr>
						<td class="right">1</td>
						<td><a href="<?php echo BASE . ADMIN . 'comments' . URL_ACT . 'new' .  URL_RW; ?>">new <?php echo $alkaline->returnCount($badges['comments'], 'comment'); ?></a></td>
					</tr>
				<?php } ?>
			</table>
			<?php
		}
		?>
		
		<h3>Census</h3>
		<table class="census">
			<?php
			$tables = $alkaline->getInfo();
			foreach($tables as $table){
				echo '<tr><td class="right">' . number_format($table['count']) . '</td><td><a href="' . BASE . ADMIN . $table['table'] . URL_CAP . '">' . $table['display'] . '</a></td></tr>';
			}
			?>
		</table>
		
		<h3>Alkaline</h3>
		<p>You are running Alkaline <?php echo Alkaline::version; ?>.</p>
	</div>
</div>

<hr />

<div class="span-24 last">
	<?php
	
	// Preference: recent_images
	if($user->returnPref('recent_images') === true){
		?>
		<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/timeline.png" alt="" /> Timeline</h1>
		<p>
			<?php
		
			// Preference: recent_images_limit
			if(!$max = $user->returnPref('recent_images_limit')){
				$max = 10;
			}
		
			$image_ids = new Find('images');
			$image_ids->page(1,$max);
			$image_ids->sort('images.image_uploaded', 'DESC');
			$image_ids->find();
			$images = new Image($image_ids);
			$images->getSizes('square');

			foreach($images->images as $image){
				?>
				<a href="<?php echo BASE . ADMIN . 'image' . URL_ID . $image['image_id'] . URL_RW; ?>" class="nu">
					<img src="<?php echo $image['image_src_square']; ?>" alt="" title="<?php echo $image['image_title']; ?>" class="frame" />
				</a>
				<?php
			}
			?>
		</p>
		<?php
	}
	?>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>