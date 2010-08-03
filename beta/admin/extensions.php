<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$extension_id = @$alkaline->findID($_GET['id']);
$orbit = new Orbit($extension_id);

define('TITLE', 'Alkaline Extensions');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="module" class="container">
	<h1>Extensions</h1>
	
	<p>You have <?php echo @$orbit->extension_count; ?> extensions enabled.</p>
</div>

<div id="extensions" class="container">
	<div style="text-align: right; margin: 0 0 1em 0;" class="span-23 last"><a href="" class="nu"><span class="button">&#0043;</span>Install extension</a> &#0160; <a href="" class="nu"><span class="button">&#0149;</span>Check for updates</a></div>
	
	<h3>Enabled</h3><br />
	
	<table>
		<tr>
			<th>Extension</th>
			<th class="center">Version</th>
			<th class="center">Update</th>
		</tr>
		<?php
		
		foreach($orbit->extensions as $extension){
			echo '<tr>';
			echo '<td><strong><a href="' . BASE . ADMIN . 'extensions/' . $extension['extension_id'] . '">' . $extension['extension_title'] . '</a></strong></td>';
			echo '<td class="center">' . $extension['extension_version'] . ' <span class="small">(' . $extension['extension_build'] . ')</span></td>';
			echo '<td class="center quiet">&#8212;</td>';
			echo '</tr>';
		}
		
		?>
	</table>
	
	<h3>Disabled</h3><br />
	
</div>
<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>