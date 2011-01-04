<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalinenapp.com/
*/

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$includes = $alkaline->getincludes();
$include_count = count($includes);

define('TAB', 'settings');
define('TITLE', 'Alkaline Theme Includes');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<h1>Theme Includes (<?php echo $include_count; ?>)</h1>

<table>
	<tr>
		<th>include</th>
		<th class="center">Canvas tag</th>
	</tr>
	<?php
	
	foreach($includes as $include){
		echo '<tr>';
		echo '<td><strong>' . $include . '</strong></td>';
		echo '<td class="center">{include:' . preg_replace('#\..+#si', '', ucwords($include)) . '}</td>';
		echo '</tr>';
	}

	?>
</table>
	
<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>