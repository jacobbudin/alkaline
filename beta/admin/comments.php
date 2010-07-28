<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

define('TITLE', 'Alkaline Comments');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="module" class="container">
	<h1>Comments</h1>
	<p>Your library contains <?php echo @$alkaline->comment_count; ?> comments.</p>
</div>

<div id="comments" class="container">

</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>