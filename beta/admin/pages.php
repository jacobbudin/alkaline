<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

define('TITLE', 'Alkaline Pages');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="module" class="container">
	<h1>Pages</h1>
	<p>You have <?php echo @$alkaline->page_count; ?> pages. <a href="create/">Create a new page.</a></p>
</div>

<div id="pages" class="container">
	<table>
		<tr>
			<th>Title</th>
			<th class="center">Views</th>
			<th class="center">Words</th>
			<th>Created</th>
			<th>Last modified</th>
		</tr>
		<tr>
			<td>
				<strong><a href="http://creativecommons.org/licenses/by/3.0/">CC-By-3.0</a></strong>&#0160;&#0160;<a href="" class="nu">/cc-explained/</a><br />
				Creative Commons - Attribution 3.0 Unported
			</td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
		<tr>
			<td>
				<strong><a href="http://creativecommons.org/licenses/by/3.0/">CC-By-3.0</a></strong><br />
				Creative Commons - Attribution 3.0 Unported
			</td>
			<td></td>
			<td></td>
			<td></td>
			<td></td>
		</tr>
	</table>

</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>