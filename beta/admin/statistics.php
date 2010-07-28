<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'user.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

define('TITLE', 'Alkaline Statistics');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="module" class="container">
	<h1>Statistics</h1>
	<p>Your library has had 36 visitors over the past 30 days.</p>
</div>

<div id="statistics" class="container">
	<div class="span-23 last append-bottom">
		<div class="span-7 append-1">
			<strong>Past 24 Hours</strong>
			<div id="24h_views" title="<?php echo $views; ?>"></div>
			<div id="24h_visitors" title="<?php echo $visitors; ?>"></div>
			<div id="24h_holder"></div>
		</div>
		<div class="span-7 append-1">
			<strong>Past 30 Days</strong>
			<div id="30d_views" title="<?php echo $views; ?>"></div>
			<div id="30d_visitors" title="<?php echo $visitors; ?>"></div>
			<div id="30d_holder"></div>
		</div>
		<div class="span-7 last">
			<strong>Past 12 Months</strong>
			<div id="12m_views" title="<?php echo $views; ?>"></div>
			<div id="12m_visitors" title="<?php echo $visitors; ?>"></div>
			<div id="12m_holder"></div>
		</div>
	</div>
	
	<h3>Visits</h3>
	<div class="span-23 last append-bottom">
		<div id="durations" class="span-7 append-1">
			<strong>Durations</strong>
			<table>
				<tr>
					<th></th>
					<th>Length</th>
				</tr>
				<tr>
					<td>x%</td>
					<td>&#0060; 1 minute</td>
				</tr>
				<tr>
					<td>x%</td>
					<td>1-5 minutes</td>
				</tr>
				<tr>
					<td>x%</td>
					<td>5-10 minutes</td>
				</tr>
				<tr>
					<td>x%</td>
					<td>10-30 minutes</td>
				</tr>
				<tr>
					<td>x%</td>
					<td>&#0062; 30 minutes</td>
				</tr>
			</table>
		</div>
		<div class="span-7 append-1">
			<strong>Popular Pages</strong>
		</div>
		<div class="span-7 last">
			<strong>Page Types</strong>
		</div>
	</div>
	
	<h3>Referrers</h3>
	<div class="span-23 last append-bottom">
		<div class="span-11 append-1">
			<strong>Recent</strong>
		</div>
		<div class="span-11 last">
			<strong>Popular</strong>
		</div>
	</div>
	<p class="quiet">The visitor and referrer data above was derived from analytics of the past 60 days.</p>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>