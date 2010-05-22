<?php

require_once('./config.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'canvas.php');
require_once(PATH . CLASSES . 'find.php');
require_once(PATH . CLASSES . 'photo.php');
require_once(PATH . CLASSES . 'user.php');

header('Content-Type: application/xml');

$photo_ids = new Find();
$photo_ids->sort('photos.photo_published', 'DESC');
$photo_ids->page(1,20);
$photo_ids->published();
$photo_ids->exec();

$photos = new Photo($photo_ids->photo_ids);
$photos->addImgUrl('medium');
$photos->formatTime('c');

$entries = new Canvas('
<!-- LOOP(PHOTOS) -->
<entry>
	<title><!-- PHOTO_TITLE --></title>
	<link href="" />
	<id><!-- PHOTO_ID --></id>
	<updated><!-- PHOTO_PUBLISHED --></updated>
	<summary><!-- PHOTO_DESCRIPTION --></summary>
</entry>
<!-- ENDLOOP(PHOTOS) -->');
$entries->setArray('PHOTOS', 'PHOTO', $photos->photos);

ob_start();

echo '<?xml version="1.0" encoding="utf-8"?>';

?>

<feed xmlns="http://www.w3.org/2005/Atom">
	
	<title><?php echo SITE; ?></title>
	<link href="<?php echo BASE; ?>"/>
	<updated><?php date('c'); ?></updated>
	<author>
		<name><?php echo OWNER; ?></name>
	</author>
	
	<?php $entries->output(); ?>

</feed>

<?php

ob_end_flush();

?>