<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

define('TITLE', 'Alkaline Dashboard');
require_once(PATH . ADMIN . 'includes/header.php');

?>
<div id="module" class="container">
	<h1>Dashboard</h1>
	<p>Welcome back, <?php echo $user->user['user_name']; ?>! <?php echo ($user->user['user_last_login']) ? 'You last logged in on ' . $alkaline->formatTime($user->user['user_last_login'], 'l, F j, Y \a\t g:i a') : ''; ?></p>
</div>
<div id="statistics" class="container">
	<h2>Vitals</h2>
	<?php
	
	$stats = new Stat();
	$stats->getDaily();
	
	$views = array();
	
	foreach($stats->daily as $stat){
		$views[] = array($stat['stat_ts_js'], $stat['stat_views']);
	}
	
	$views = json_encode($views);
	
	$visitors = array();
	
	foreach($stats->daily as $stat){
		$visitors[] = array($stat['stat_ts_js'], $stat['stat_visitors']);
	}
	
	$visitors = json_encode($visitors);
	
	?>
	<div id="statistics_views" title="<?php echo $views; ?>"></div>
	<div id="statistics_visitors" title="<?php echo $visitors; ?>"></div>
	
	<div class="span-18">
		<div id="statistics_holder"></div>
		<p>Your library has had 36 visitors over the past 30 days. &#0160; <a href="<?php echo BASE . ADMIN; ?>statistics/" class="nu"><span class="button">&#0187;</span>Go to statistics</a></p>
	</div>
	<div class="span-5 prepend-top last">
		<?php
		
		$shoebox_count = $alkaline->countShoebox();
		$comment_count = 0;
		
		if(($shoebox_count > 0) or ($comment_count > 0)){
			echo '<ul class="yellow">';
			if($shoebox_count > 0){
				if($shoebox_count > 1){
					$s = 's';
				}
				echo '<li><a href="' . BASE . ADMIN . 'shoebox/">' . $shoebox_count . ' new photo' . @$s . '</a></li>';
				unset($s);
			}
			if($comment_count > 0){
				if($comment_count > 1){
					$s = 's';
				}
				echo '<li><a href="' . BASE . ADMIN . 'comments/new/">' . $comment_count . ' new comment' . @$s . '</a></li>';
				unset($s);
			}
			echo '</ul>';
		}
		
		?>
		<?php
		
		if($tables = $alkaline->getInfo()){
			echo '<ul>';
			foreach($tables as $table => $count){
				echo '<li><a href="' . BASE . ADMIN . $table . '/">' . $count . ' ' . $table . '</a></li>';
			}
			echo '</ul>';
		}
		
		?>
	</div>
</div>
<div id="recent" class="container">
	<h2>Recent</h2>
	<hr />
	<?php
	
	$photo_ids = new Find;
	$photo_ids->page(1,20);
	$photo_ids->exec();
	$photos = new Photo($photo_ids);
	$photos->getImgUrl('square');
	
	foreach($photos->photos as $photo){
		?>
		<a href="<?php echo BASE . ADMIN . 'photo/' . $photo['photo_id']; ?>/">
			<img src="<?php echo $photo['photo_src_square']; ?>" alt="" title="<?php echo $photo['photo_title']; ?>" />
		</a>
		<?php
	}
	
	?>
</div>
<div id="alkaline" class="container">
	<h2>Alkaline</h2>
	<p>You are running Alkaline <?php echo Alkaline::version; ?> (<?php echo Alkaline::build; ?>).</p>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>