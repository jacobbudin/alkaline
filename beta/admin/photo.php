<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

// GET PHOTO
if(!$photo_id = $alkaline->findID($_GET['id'])){
	header('Location: ' . LOCATION . BASE . ADMIN . 'library/');
	exit();
}

$photos = new Photo($photo_id);
$photos->getImgUrl('medium');

$photo = $photos->photos[0];

// Set title
if(!empty($photo['photo_title'])){	
	define('TITLE', 'Alkaline Photo: &#8220;' . $photo['photo_title']  . '&#8221;');
}
require_once(PATH . ADMIN . 'includes/header.php');
require_once(PATH . ADMIN . 'includes/library.php');

?>

<h1>Photo #<?php echo $photo['photo_id']; ?></h1>

<img src="<?php echo $photo['photo_src_medium']; ?>" alt="" />

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>