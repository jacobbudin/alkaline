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

<div id="extensions" class="container">
	<h2>Extensions</h2>

	<?php
	if(!empty($extension_id)){
		?>
		<h3><?php echo $orbit->title; ?></h3>

		<hr />

		<?php
		$orbit->hook('config');
	}
	else{	
		?>
		<h3>Available <span class="small quiet">(<span id="count"><?php echo @$orbit->extension_count; ?></span>)</span></h3>

		<hr />

		<?php
		foreach($orbit->extensions as $extension){
			echo '<p>' . $extension['extension_title'] . ' (<a href="?identifier=' . $extension['extension_id'] . '-' . $extension['extension_class']  . '">Config</a>)</p>';
		}
	}
	?>
</div>
<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>