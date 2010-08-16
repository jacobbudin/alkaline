<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

define('TITLE', 'Alkaline Settings');
require_once(PATH . ADMIN . 'includes/header.php');
require_once(PATH . ADMIN . 'includes/settings.php');

?>
	
<h1>Access</h1>

<h2>Guests</h2>
<table>
	<tr>
		<th>Title</th>
		<th class="center">Views</th>
		<th>Last login</th>
	</tr>
	<tr>
		<td><strong><a href="">Models</a></strong></td>
		<td class="center">1,034</td>
		<td>Yesterday, 1:33 p.m.</td>
	</tr>
	<tr>
		<td><strong><a href="">Parents</a></strong></td>
		<td class="center">4,056</td>
		<td>Today, 1:13 p.m.</td>
	</tr>
</table>

<h2>Users</h2>
<table>
	<tr>
		<th>Name</th>
		<th class="center">Photos</th>
		<th>Last login</th>
	</tr>
	<tr>
		<td><strong><a href="">Alex</a></strong></td>
		<td class="center"><a href="">133</a></td>
		<td>Yesterday, 9:33 p.m.</td>
	</tr>
	<tr>
		<td><strong><a href="">Brian</a></strong></td>
		<td class="center"><a href="">12</a></td>
		<td>Yesterday, 2:33 p.m.</td>
	</tr>
</table>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>