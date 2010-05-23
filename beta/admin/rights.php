<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'user.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

define('TITLE', 'Alkaline Rights');
define('COLUMNS', '19');
define('WIDTH', '750');

require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="content" class="span-<?php echo COLUMNS - 2; ?> prepend-1 append-1 last">
	<h2>Rights</h2>
	
	<?php $alkaline->viewNotification(); ?>
	
	<table>
		<tr>
			<th></th>
			<th>Title</th>
			<th class="center">Photos</th>
		</tr>
		<tr>
			<td></td>
			<td>
				<strong><a href="http://creativecommons.org/licenses/by/3.0/">CC-By-3.0</a></strong><br />
				Creative Commons - Attribution 3.0 Unported
			</td>
			<td class="photos"><a href="">103</a></td>
		</tr>
		<tr class="alt">
			<td></td>
			<td>
				<strong><a href="http://creativecommons.org/licenses/by/3.0/">CC-By-3.0</a></strong><br />
				Creative Commons - Attribution 3.0 Unported
			</td>
			<td class="photos"><a href="">25</a></td>
		</tr>
	</table>

</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>