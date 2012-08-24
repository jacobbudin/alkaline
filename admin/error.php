<?php

/*
// Alkaline
// Copyright (c) 2010-2012 by Budin Ltd. Some rights reserved.
// http://www.alkalineapp.com/
*/

require_once('../config.php');

if(session_id() == ''){ session_start(); }

$e = $_SESSION['alkaline']['exception'];

define('TAB', 'Error');
define('TITLE', 'Alkaline Error');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<h2>Error</h2>

<p><strong><?php echo $e->getPublicMessage(); ?></strong></p>

<ol>
<?php

$trace = $e->getPublicTrace();

foreach($trace as $stack){
	?>
	<li>
		<?php echo $stack['class']; ?> <?php echo str_replace('->', '&#8594;', $stack['type']); ?> <?php echo $stack['function']; ?>
		<span class="quiet">(<?php echo $stack['file']; ?>, line <?php echo $stack['line']; ?>)</span>
	</li>
	<?php
}

?>
</ol>
<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>