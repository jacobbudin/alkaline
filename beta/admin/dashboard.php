<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'user.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

define('TITLE', 'Alkaline Dashboard');
define('COLUMNS', '19');
define('WIDTH', '750');

require_once(PATH . ADMIN . 'includes/header.php');

?>

	
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
	
	<div class="span-19">
		<div id="statistics_holder"></div>
	</div>
	<div class="span-4 last">
		<ul class="sticky">
			<li><a href="shoebox/">6 new photos</a></li>
			<li><a href="shoebox/">1 new comment</a></li>
		</ul>
		<ul>
			<li><a href="library/">1,829 photos</a></li>
			<li><a href="">19 collections</a></li>
			<li><a href="">2 narratives</a></li>
			<li><a href="">567 tags</a></li>
			<li><a href="">103 comments</a></li>
		</ul>
	</div>
</div>
<hr />
<div id="recent" class="container">
	<h2>Recent</h2>
	<?php
	
	$photo_ids = new Find;
	$photo_ids->page(1,20);
	$photo_ids->exec();
	$photos = new Photo($photo_ids);
	$photos->getImgUrl('square');
	
	foreach($photos->photos as $photo){
		echo '<img src="' . $photo['photo_src_square'] . '" alt="" title="' . $photo['photo_title'] . '" />';
	}
	
	?>
</div>
<hr />
<div id="alkaline" class="container">
	<h2>Alkaline</h2>
	<p>You are running Alkaline <?php echo Alkaline::version; ?> (<?php echo Alkaline::build; ?>).</p>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>