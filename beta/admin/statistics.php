<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'user.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

define('TITLE', 'Alkaline Statistics');
define('COLUMNS', '19');
define('WIDTH', '750');

require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="statistics" class="container">
	<h2>Visits</h2>
	<div class="span-14 append-1">
		<h3>Past 24 Hours</h3>
	</div>
	<div id="durations" class="span-8 last">
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
	<div class="span-14 append-1">
		<h3>Past 12 Months</h3>
	</div>
	<div class="span-8 last">
		<h3>Page Types</h3>
	</div>
</div>
<hr />
<div id="referrers" class="container">
	<h2>Referrers</h2>
	<div class="span-11 append-1">
		<h3>Most Recent</h3>
	</div>
	<div class="span-11 last">
		<h3>Most Popular</h3>
	</div>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>