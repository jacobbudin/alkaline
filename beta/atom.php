<?php

require_once('./config.php');
require_once(PATH . CLASSES . 'alkaline.php');

header('Content-Type: application/xml');

$photo_ids = new Find();
$photo_ids->sort('photos.photo_published', 'DESC');
$photo_ids->page(1,20);
$photo_ids->published();
$photo_ids->exec();

$photos = new Photo($photo_ids);
$photos->getImgUrl('medium');
$photos->formatTime('c');

$entries = new Canvas('
<!-- LOOP(PHOTOS) -->
	<entry>
		<title type="text"><!-- PHOTO_TITLE --></title>
		<link href="" />
		<id><!-- LOCATION --><!-- BASE --><!-- PHOTO_ID --></id>
		<updated><!-- PHOTO_UPDATED --></updated>
		<published><!-- PHOTO_PUBLISHED --></published>
		<!-- IF(PHOTO_DESCRIPTION) -->
			<summary type="text"><!-- PHOTO_DESCRIPTION --></summary>
		<!-- ENDIF(PHOTO_DESCRIPTION) -->
		<content type="xhtml">
			<div xmlns="http://www.w3.org/1999/xhtml">
				<a href=""><img src="<!-- LOCATION --><!-- PHOTO_SRC_MEDIUM -->" title="<!-- PHOTO_TITLE -->" /></a>
			</div>
		</content>
		<link rel="enclosure" type="<!-- PHOTO_MIME -->" href="<!-- LOCATION --><!-- PHOTO_SRC_MEDIUM -->" />
	</entry>
<!-- ENDLOOP(PHOTOS) -->');
$entries->assign('BASE', BASE);
$entries->assign('LOCATION', LOCATION);
$entries->loop($photos);

echo '<?xml version="1.0" encoding="utf-8"?>';

?>

<feed xmlns="http://www.w3.org/2005/Atom">
	
	<title><?php echo SITE; ?></title>
	<updated><?php echo date('c'); ?></updated>
	<link href="<?php echo BASE; ?>"/>
	<link rel="self" type="application/atom+xml" href="<?php echo LOCATION . BASE; ?>atom.php" />
	<id>tag:<?php echo DOMAIN; ?>,2010:/</id>
	<author>
		<name><?php echo OWNER; ?></name>
	</author>
	
	<?php echo $entries; ?>

</feed>