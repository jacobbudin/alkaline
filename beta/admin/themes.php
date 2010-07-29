<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

define('TITLE', 'Alkaline Themes');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="module" class="container">
	<h1>Themes</h1>
	
	<p>You have <?php echo @$alkaline->theme_count; ?> themes installed.</p>
</div>

<div id="themes" class="container">
	<div style="float: right; margin: 1em 0 2em 0;"><a href="" class="nu"><span class="button">&#0043;</span>Install theme</a></div>
	<table>
		<tr>
			<th>Title</th>
			<th class="center">Photos</th>
			<th>Last modified</th>
		</tr>
		<tr>
			<td>
				<strong><a href="http://creativecommons.org/licenses/by/3.0/">CC-By-3.0</a></strong><br />
				Creative Commons - Attribution 3.0 Unported
			</td>
			<td class="center"><a href="">103</a></td>
			<td></td>
		</tr>
		<tr>
			<td>
				<strong><a href="http://creativecommons.org/licenses/by/3.0/">CC-By-3.0</a></strong><br />
				Creative Commons - Attribution 3.0 Unported
			</td>
			<td class="center"><a href="">25</a></td>
			<td></td>
		</tr>
	</table>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>