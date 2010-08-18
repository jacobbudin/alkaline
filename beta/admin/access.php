<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

define('TITLE', 'Alkaline Settings');
require_once(PATH . ADMIN . 'includes/header.php');
require_once(PATH . ADMIN . 'includes/settings.php');

$guests = $alkaline->getTable('guests', null, 10);
$guest_count = @count($guests);

$users = $alkaline->getTable('users', null, 10);
$user_count = @count($users);

?>
	
<h1>Access</h1>

<table>
	<h2><a href="<?php echo BASE . ADMIN . 'guests/'; ?>">Guests</a></h2>
	<tr>
		<th>Title</th>
		<th class="center">Views</th>
		<th>Last login</th>
	</tr>
	<?php

	foreach($guests as $guest){
		echo '<tr>';
			echo '<td><strong><a href="' . BASE . ADMIN . 'guests/' . $guest['guest_id'] . '">' . $guest['guest_title'] . '</a></strong></td>';
			echo '<td class="center">' . number_format($guest['guest_views']) . '</td>';
			echo '<td>' . $alkaline->formatTime($guest['guest_last_login']) . '</td>';
		echo '</tr>';
	}

	?>
</table>

<?php if($guest_count > 10){ echo '<p><a href="' . BASE . ADMIN . 'guests/">View all ' . $guest_count . ' guests.</a></p>'; } ?>

<table>
	<h2><a href="<?php echo BASE . ADMIN . 'users/'; ?>">Users</h2>
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

if($user_count > 10){ echo '<p><a href="' . BASE . ADMIN . 'users/">View all ' . $guest_count . ' users.</a></p>'; }

require_once(PATH . ADMIN . 'includes/footer.php');

?>