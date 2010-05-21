<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'user.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

define('TITLE', 'Alkaline Preferences');
define('COLUMNS', '19');
define('WIDTH', '750');

require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="content" class="span-<?php echo COLUMNS - 2; ?> prepend-1 append-1 last">
	<h2>Rights <span class="small quiet">(<span id="count"><?php echo @$right_count; ?></span>)</span></h2>
	
	<?php $alkaline->viewNotification(); ?>
	
	<form action="" method="post">
		<table>
			<tr>
				<th></th>
				<th>Rights</th>
				<th class="center">Actions</th>
			</tr>
			<tr>
				<td class="radio">
					<input type="radio" name="right_default" value="1" checked="checked" />
				</td>
				<td>
					<strong><a href="http://creativecommons.org/licenses/by/3.0/">CC-By-3.0</a></strong> (<a href="">103</a>)<br />
					Creative Commons - Attribution 3.0 Unported<br />
				</td>
				<td class="actions"><a href="">Edit</a> &middot; <strike>Delete</strike></td>
			</tr>
			<tr>
				<td class="radio">
					<input type="radio" name="right_default" value="1" />
				</td>
				<td>
					<strong><a href="http://creativecommons.org/licenses/by/3.0/">CC-By-3.0</a></strong> (<a href="">25</a>)<br />
					Creative Commons - Attribution 3.0 Unported<br />
				</td>
				<td class="actions"><a href="">Edit</a> &middot; <a href="">Delete</a></td>
			</tr>
		</table>
		<p class="center">
			<input id="right_ids" type="hidden" name="right_ids" value="" />
			<input id="save" type="submit" value="Save changes" />
		</p>
	</form>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>