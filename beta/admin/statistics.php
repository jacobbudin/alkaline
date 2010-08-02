<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'user.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

define('TITLE', 'Alkaline Statistics');
require_once(PATH . ADMIN . 'includes/header.php');

// PAST 24 HOURS

$now = time();
$then = strtotime('-24 hours', $now);

$stats = new Stat($then, $now);
$stats->getHourly();

$views = array();
$visitors = array();

foreach($stats->hourly as $stat){
	$views[] = array($stat['stat_ts_js'], $stat['stat_views']);
	$visitors[] = array($stat['stat_ts_js'], $stat['stat_visitors']);
}

$h_views = json_encode($views);
$h_visitors = json_encode($visitors);


// PAST 30 DAYS

$now = time();
$then = strtotime('-30 days', $now);

$stats = new Stat($then, $now);
$stats->getDaily();

$views = array();
$visitors = array();

foreach($stats->daily as $stat){
	$views[] = array($stat['stat_ts_js'], $stat['stat_views']);
	$visitors[] = array($stat['stat_ts_js'], $stat['stat_visitors']);
}

$d_views = json_encode($views);
$d_visitors = json_encode($visitors);

// PAST 12 MONTHS

$now = time();
$then = strtotime('-12 months', $now);

$stats = new Stat($then, $now);
$stats->getMonthly();

$views = array();
$visitors = array();

foreach($stats->monthly as $stat){
	$views[] = array($stat['stat_ts_js'], $stat['stat_views']);
	$visitors[] = array($stat['stat_ts_js'], $stat['stat_visitors']);
}

$m_views = json_encode($views);
$m_visitors = json_encode($visitors);

// DURATIONS

$stats = new Stat();
$durations = $stats->getDurations();

$levels = array('&#0060; 1 minute' => 60, '1-5 minutes' => 300, '5-10 minutes' => 600, '10-30 minutes' => 1800, '&#0062; 30 minutes');
$zeros = array_fill(0, count($levels), 0);
$last = implode('', array_slice($levels, -1, 1));

$keys = array_slice($levels, 0, -1);
$keys = array_keys($keys);
$keys[] = $last;
$counts = array_combine($keys, $zeros);

$durations_count = count($durations);

foreach($durations as $duration){
	$accounted_for = false;
	foreach($levels as $level_text => $level_max){
		if($duration['stat_duration'] < $level_max){
			$counts[$level_text]++;
			$accounted_for = true;
			break;
		}
	}
	if($accounted_for == false){
		$counts[$last]++;
	}
}

foreach($counts as $text => &$count){
	$count = round(($count / $durations_count) * 100, 1);
}

// PAGES

$pages = $stats->getPages();

// PAGE TYPES

$page_types = $stats->getPageTypes();

// RECENT REFERRERS

$recent_referrers = $stats->getRecentReferrers();

// POPULAR REFERRS

$popular_referrers = $stats->getPopularReferrers();

?>

<div id="module" class="container">
	<h1>Statistics</h1>
	<p></p>
</div>

<div id="statistics" class="container">
	<div class="span-23 last append-bottom">
		<div class="span-7 append-1">
			<strong>Past 24 Hours</strong>
			<div id="h_views" title="<?php echo $h_views; ?>"></div>
			<div id="h_visitors" title="<?php echo $h_visitors; ?>"></div>
			<div id="h_holder" class="statistics_holder"></div>
		</div>
		<div class="span-7 append-1">
			<strong>Past 30 Days</strong>
			<div id="d_views" title="<?php echo $d_views; ?>"></div>
			<div id="d_visitors" title="<?php echo $d_visitors; ?>"></div>
			<div id="d_holder" class="statistics_holder"></div>
		</div>
		<div class="span-7 last">
			<strong>Past 12 Months</strong>
			<div id="m_views" title="<?php echo $m_views; ?>"></div>
			<div id="m_visitors" title="<?php echo $m_visitors; ?>"></div>
			<div id="m_holder" class="statistics_holder"></div>
		</div>
	</div>
	
	<h3>Visits</h3>
	<div class="span-23 last append-bottom">
		<div id="durations" class="span-7 append-1">
			<strong>Durations</strong>
			<table>
				<tr>
					<th class="right">%</th>
					<th>Duration</th>
				</tr>
				<?php				
				foreach($counts as $text => $count){
					echo '<tr>';
					echo '<td class="right">' . $count . '%</td>';
					echo '<td>' . $text . '</td>';
					echo '</tr>';
				}
				?>
			</table>
		</div>
		<div class="span-7 append-1">
			<strong>Popular Pages</strong>
			<table>
				<tr>
					<th class="right">Hits</th>
					<th>Page</th>
				</tr>
				<?php				
				foreach($pages as $page){
					echo '<tr>';
					echo '<td class="right">' . $page['stat_count'] . '</td>';
					echo '<td><a href="' . BASE . substr($page['stat_page'], 1) . '">' . $page['stat_page'] . '</a></td>';
					echo '</tr>';
				}
				?>
			</table>
		</div>
		<div class="span-7 last">
			<strong>Page Types</strong>
			<table>
				<tr>
					<th class="right">Hits</th>
					<th>Page type</th>
				</tr>
				<?php				
				foreach($page_types as $page_type){
					echo '<tr>';
					echo '<td class="right">' . $page_type['stat_count'] . '</td>';
					echo '<td>' . ucwords($page_type['stat_page_type']) . '</td>';
					echo '</tr>';
				}
				?>
			</table>
		</div>
	</div>
	
	<h3>Referrers</h3>
	<div class="span-23 last append-bottom">
		<div class="span-11 append-1">
			<strong>Recent</strong>
			<table>
				<tr>
					<th class="right">Date</th>
					<th>Referrer</th>
				</tr>
				<?php				
				foreach($recent_referrers as $referrer){
					echo '<tr>';
					echo '<td class="right small quiet">' . $alkaline->formatTime($referrer['stat_date'], 'M j, g:ia') . '</td>';
					echo '<td><a href="' . $referrer['stat_referrer'] . '">' . $alkaline->minimizeURL($referrer['stat_referrer']) . '</a></td>';
					echo '</tr>';
				}
				?>
			</table>
		</div>
		<div class="span-11 last">
			<strong>Popular</strong>
			<table>
				<tr>
					<th class="right">Hits</th>
					<th>Referrer</th>
				</tr>
				<?php				
				foreach($popular_referrers as $referrer){
					echo '<tr>';
					echo '<td class="right">' . $referrer['stat_referrer_count'] . '</td>';
					echo '<td><a href="' . $referrer['stat_referrer_count'] . '">' . $alkaline->minimizeURL($referrer['stat_referrer']) . '</a></td>';
					echo '</tr>';
				}
				?>
			</table>
		</div>
	</div>
	<p class="quiet">The visitor and referrer data above was derived from analytics of the past 60 days.</p>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>