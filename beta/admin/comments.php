<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

define('TITLE', 'Alkaline Comments');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="comments" class="container">
	<h2>Comments<?php echo ($alkaline->tag_count) ? ' <span id="comments_count" class="small quiet">(' . $alkaline->tag_count . ')</span>' : ''; ?></h2>
	
	<p>Administer comments posted to your photos.</p>
	
	<hr />
	
	<table>
		<tr>
			<th>Title</th>
			<th class="center">Photos</th>
		</tr>
		<tr>
			<td>
				<strong><a href="http://creativecommons.org/licenses/by/3.0/">CC-By-3.0</a></strong><br />
				Creative Commons - Attribution 3.0 Unported
			</td>
			<td class="center"><a href="">103</a></td>
		</tr>
		<tr>
			<td>
				<strong><a href="http://creativecommons.org/licenses/by/3.0/">CC-By-3.0</a></strong><br />
				Creative Commons - Attribution 3.0 Unported
			</td>
			<td class="center"><a href="">25</a></td>
		</tr>
	</table>

</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>