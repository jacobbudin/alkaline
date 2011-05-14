<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalineapp.com/
*/

require_once('../config.php');

if(session_id() == ''){ session_start(); }

define('TAB', 'Error');
define('TITLE', 'Alkaline Error');
require_once(PATH . ADMIN . 'includes/header.php');

$e = $_SESSION['alkaline']['exception'];

?>

<h2>Error</h2>

<p><strong><?php echo $e->getMessage(); ?></strong></p>

<ol>
<?php

$trace = $e->getTrace();

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