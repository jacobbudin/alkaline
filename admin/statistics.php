<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

// PAST 24 HOURS

$then = strtotime('-24 hours');

$stats = new Stat($then);
$stats->getHourly();

$views = array();
$visitors = array();

foreach($stats->stats as $stat){
	$views[] = array($stat['stat_ts_js'], $stat['stat_views']);
	$visitors[] = array($stat['stat_ts_js'], $stat['stat_visitors']);
}

$h_views = json_encode($views);
$h_visitors = json_encode($visitors);

// PAST 30 DAYS

$then = strtotime('-30 days');

$stats = new Stat($then);
$stats->getDaily();

$views = array();
$visitors = array();

foreach($stats->stats as $stat){
	$views[] = array($stat['stat_ts_js'], $stat['stat_views']);
	$visitors[] = array($stat['stat_ts_js'], $stat['stat_visitors']);
}

$d_views = json_encode($views);
$d_visitors = json_encode($visitors);

// PAST 12 MONTHS

$then = strtotime('-12 months');

$stats = new Stat($then);
$stats->getMonthly();

$views = array();
$visitors = array();

foreach($stats->stats as $stat){
	$views[] = array($stat['stat_ts_js'], $stat['stat_views']);
	$visitors[] = array($stat['stat_ts_js'], $stat['stat_visitors']);
}

$m_views = json_encode($views);
$m_visitors = json_encode($visitors);

// DURATIONS

$then = strtotime('-60 days');

$stats = new Stat($then);
$stats->getDurations();

$levels = array('&#0060; 1 minute' => 60, '1-5 minutes' => 300, '5-10 minutes' => 600, '10-30 minutes' => 1800, '&#0062; 30 minutes');
$zeros = array_fill(0, count($levels), 0);
$last = implode('', array_slice($levels, -1, 1));

$keys = array_slice($levels, 0, -1);
$keys = array_keys($keys);
$keys[] = $last;
$durations = array_combine($keys, $zeros);

$durations_count = count($stats->durations);

$accounted_for = 0;

foreach($stats->durations as $duration){
	foreach($levels as $level_text => $level_max){
		if($duration['stat_duration'] < $level_max){
			$durations[$level_text]++;
			$accounted_for++;
			break;
		}
	}
}

$durations[$last] = $durations_count - $accounted_for;

if($durations_count > 0){
	foreach($durations as $text => $duration){
		$durations[$text] = round(($duration / $durations_count) * 100, 1);
	}
}

// PAGES

$stats->getPages();

// PAGE TYPES

$stats->getPageTypes();

// RECENT REFERRERS

$stats->getRecentReferrers(10);

foreach($stats->referrers_recent as &$referrer){
	$referrer['stat_referrer_display'] = $alkaline->fitString($alkaline->minimizeURL($referrer['stat_referrer']));
}

// POPULAR REFERRS

$stats->getPopularReferrers(10);

foreach($stats->referrers_popular as &$referrer){
	$referrer['stat_referrer_display'] = $alkaline->fitString($alkaline->minimizeURL($referrer['stat_referrer']));
}

define('TAB', 'dashboard');
define('TITLE', 'Alkaline Statistics');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<h1>Statistics</h1>

<div class="span-24 last">
	<div class="span-15 append-1">
		<h3>Past 24 Hours</h3>
		<div id="h_views" title="<?php echo $h_views; ?>"></div>
		<div id="h_visitors" title="<?php echo $h_visitors; ?>"></div>
		<div id="h_holder" class="statistics_holder"></div>

		<h3>Past 30 Days</h3>
		<div id="d_views" title="<?php echo $d_views; ?>"></div>
		<div id="d_visitors" title="<?php echo $d_visitors; ?>"></div>
		<div id="d_holder" class="statistics_holder"></div>

		<h3>Past 12 Months</h3>
		<div id="m_views" title="<?php echo $m_views; ?>"></div>
		<div id="m_visitors" title="<?php echo $m_visitors; ?>"></div>
		<div id="m_holder" class="statistics_holder"></div>
	</div>
	<div class="span-8 last">
		<h3>Durations</h3>
		<table>
			<tr>
				<th class="right">%</th>
				<th>Duration</th>
			</tr>
			<?php				
			foreach($durations as $label => $duration){
				echo '<tr>';
				echo '<td class="right">' . $duration . '%</td>';
				echo '<td>' . $label . '</td>';
				echo '</tr>';
			}
			?>
		</table>

		<h3>Popular Pages</h3>
		<table>
			<tr>
				<th class="right">Hits</th>
				<th>Page</th>
			</tr>
			<?php				
			foreach($stats->pages as $page){
				echo '<tr>';
				echo '<td class="right">' . number_format($page['stat_count']) . '</td>';
				echo '<td><a href="' . BASE . substr($page['stat_page'], 1) . '">' . $alkaline->fitString($page['stat_page'], 30) . '</a></td>';
				echo '</tr>';
			}
			?>
		</table>

		<h3>Page Types</h3>
		<table>
			<tr>
				<th class="right">Hits</th>
				<th>Page type</th>
			</tr>
			<?php				
			foreach($stats->page_types as $page_type){
				echo '<tr>';
				echo '<td class="right">' . number_format($page_type['stat_count']) . '</td>';
				echo '<td>' . ucwords($page_type['stat_page_type']) . '</td>';
				echo '</tr>';
			}
			?>
		</table>

		<h3>Popular Referrers</h3>
		<table>
			<tr>
				<th class="right">Hits</th>
				<th>Referrer</th>
			</tr>
			<?php				
			foreach($stats->referrers_popular as $referrer){
				echo '<tr>';
				echo '<td class="right">' . number_format($referrer['stat_referrer_count']) . '</td>';
				echo '<td><a href="' . $referrer['stat_referrer'] . '">' . $alkaline->fitString($referrer['stat_referrer_display'], 30) . '</a></td>';
				echo '</tr>';
			}
			?>
		</table>

		<h3>Recent Referrers</h3>
		<table>
			<tr>
				<th class="right">Date</th>
				<th>Referrer</th>
			</tr>
			<?php				
			foreach($stats->referrers_recent as $referrer){
				echo '<tr>';
				echo '<td class="right quiet">' . $alkaline->formatRelTime($referrer['stat_date'], 'M j, g:i a') . '</td>';
				echo '<td><a href="' . $referrer['stat_referrer'] . '">' . $alkaline->fitString($referrer['stat_referrer_display'], 30) . '</a></td>';
				echo '</tr>';
			}
			?>
		</table>
	</div>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>