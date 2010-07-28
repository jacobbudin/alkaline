<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$photo_id = $alkaline->findID($_GET['photo_id']);

$photos = new Photo($photo_id);
$photos->getImgUrl('medium');

$photo = $photos->photos[0];

define('TITLE', 'Alkaline');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="module" class="container">
	<h1>Photo</h1>
	<p>Photo #<?php echo $photo['photo_id']; ?> was uploaded on <?php echo $alkaline->formatTime($photo['photo_uploaded']); ?></p>
</div>

<div id="edit" class="container">
	<img src="<?php echo $photo['photo_src_medium']; ?>" alt="" />
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>