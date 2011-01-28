<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalineapp.com/
*/

require_once('config.php');

@session_start();

define('TAB', 'Error');
define('TITLE', 'Alkaline Error');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<p>A critical error has occurred.</p>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>