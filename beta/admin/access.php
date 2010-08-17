<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

define('TITLE', 'Alkaline Settings');
require_once(PATH . ADMIN . 'includes/header.php');
require_once(PATH . ADMIN . 'includes/settings.php');

$users = $alkaline->getTable('users');

?>
	
<h1>Access</h1>

<table>
	<h2>Guests</h2>
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

<table>
	<h2>Users</h2>
	<tr>
		<th>Username</th>
		<th class="center">Photos</th>
		<th>Last login</th>
	</tr>
	<?php

	foreach($users as $user){
		echo '<tr>';
			echo '<td><strong><a href="' . BASE . ADMIN . 'users/' . $user['user_id'] . '">' . $user['user_user'] . '</a></strong></td>';
			echo '<td class="center">' . number_format($user['user_photo_count']) . '</td>';
			echo '<td>' . $alkaline->formatTime($user['user_last_login']) . '</td>';
		echo '</tr>';
	}

	?>
</table>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>