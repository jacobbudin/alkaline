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
	<div id="statistics_holder"></div>
	<p>
		Your <a href="">library</a> contains 1,829 <a href="">photos</a> including 567 unique <a href="">tags</a>, 19 <a href="">collections</a>, 2 <a href="">narratives</a>, and 103 <a href="">comments</a>.
	</p>
</div>
<hr />
<div id="recent" class="container">
	<h3>Recent</h3>
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
	<h3>Alkaline</h3>
	<p>You are running Alkaline <?php echo Alkaline::version; ?> (<?php echo Alkaline::build; ?>).</p>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>