<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$photo_ids = new Find();
$photo_ids->page(1,100);
$photo_ids->exec();

$photos = new Photo($photo_ids->photo_ids);
$photos->getImgUrl('square');

define('TITLE', 'Alkaline Features');
require_once(PATH . ADMIN . 'includes/header.php');
require_once(PATH . ADMIN . 'includes/features.php');

?>

<h1>Features</h1>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>