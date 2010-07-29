<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$extension_id = @$alkaline->findID($_GET['identifier']);
$orbit = new Orbit($extension_id);

define('TITLE', 'Alkaline Extensions');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="module" class="container">
	<h1>Extensions</h1>
	
	<p>You have <?php echo @$orbit->extension_count; ?> extensions installed.</p>
</div>

<div id="extensions" class="container">
	<div style="float: right; margin: 1em 0 0 0;"><a href="" class="nu"><span class="button">&#0043;</span>Install extension</a> &#0160; <a href="" class="nu"><span class="button">&#0149;</span>Check for updates</a></div>
	<table>
		<tr>
			<th>Extension</th>
			<th class="center">Configure</th>
			<th class="center">Web site</th>
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
	<?php
	if(!empty($extension_id)){
		?>
		<h3><?php echo $orbit->title; ?></h3>

		<hr />

		<?php
		$orbit->hook('config');
	}
	else{
		foreach($orbit->extensions as $extension){
			echo '<p>' . $extension['extension_title'] . ' (<a href="?identifier=' . $extension['extension_id'] . '-' . $extension['extension_class']  . '">Config</a>)</p>';
		}
	}
	?>
</div>
<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>