<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

define('TITLE', 'Alkaline Dashboard');
require_once(PATH . ADMIN . 'includes/header.php');
require_once(PATH . ADMIN . 'includes/dashboard.php');

?>

<h1>Dashboard</h1>

<?php
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

?>
<div id="statistics_holder" class="statistics_holder"></div>
<div id="statistics_views" title="<?php echo $views; ?>"></div>
<div id="statistics_visitors" title="<?php echo $visitors; ?>"></div>

<h1>Recent</h1>

<p>
	<?php
	$photo_ids = new Find;
	$photo_ids->page(1,20);
	$photo_ids->exec();
	$photos = new Photo($photo_ids);
	$photos->getImgUrl('square');

	foreach($photos->photos as $photo){
		?>
		<a href="<?php echo BASE . ADMIN . 'photo/' . $photo['photo_id']; ?>/">
			<img src="<?php echo $photo['photo_src_square']; ?>" alt="" title="<?php echo $photo['photo_title']; ?>" class="frame" />
		</a>
		<?php
	}
	?>
</p>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>