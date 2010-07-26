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

<div id="statistics" class="container">
	<h2>Statistics</h2>
	
	<div class="span-23 last">
		<div class="span-7 append-1">
			<h3>Past 24 Hours</h3>
			<div id="24h_views" title="<?php echo $views; ?>"></div>
			<div id="24h_visitors" title="<?php echo $visitors; ?>"></div>
			<div id="24h_holder"></div>
		</div>
		<div class="span-7 append-1">
			<h3>Past 30 Days</h3>
			<div id="30d_views" title="<?php echo $views; ?>"></div>
			<div id="30d_visitors" title="<?php echo $visitors; ?>"></div>
			<div id="30d_holder"></div>
		</div>
		<div class="span-7 last">
			<h3>Past 12 Months</h3>
			<div id="12m_views" title="<?php echo $views; ?>"></div>
			<div id="12m_visitors" title="<?php echo $visitors; ?>"></div>
			<div id="12m_holder"></div>
		</div>
	</div>
</div>
<hr />
<div id="visits" class="container">
	<h2>Visits</h2>
	<div class="span-23 last">
		<div id="durations" class="span-7 append-1">
			<h3>Durations</h3>
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
			<h3>Popular Pages</h3>
		</div>
		<div class="span-7 last">
			<h3>Page Types</h3>
		</div>
	</div>
	<p class="small quiet">The visitor behavior above was derived from analytics of the last 60 days.</p>
</div>
<hr />
<div id="referrers" class="container">
	<h2>Referrers</h2>
	<div class="span-11 append-1">
		<h3>Recent</h3>
	</div>
	<div class="span-11 last">
		<h3>Popular</h3>
	</div>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>