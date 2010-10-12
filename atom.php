<?php

require_once('config.php');
require_once(PATH . CLASSES . 'alkaline.php');

header('Content-Type: application/xml');

$alkaline = new Alkaline();

$photo_ids = new Find();
$photo_ids->sort('photos.photo_published', 'DESC');
$photo_ids->page(1,20);
$photo_ids->published();
$photo_ids->find();

$photos = new Photo($photo_ids);
$photos->getImgUrl('medium');
$photos->formatTime('c');

$photos->photos = $alkaline->makeHTMLSafe($photos->photos);

$entries = new Canvas('
{block:Photos}
	<entry>
		<title type="text">{Photo_Title}</title>
		<link href="" />
		<id>{LOCATION}{BASE}{PHOTO_ID}</id>
		<updated>{PHOTO_UPDATED}</updated>
		<published>{PHOTO_PUBLISHED}</published>
		{if:PHOTO_DESCRIPTION}
			<summary type="text">{PHOTO_DESCRIPTION}</summary>
		{/if:PHOTO_DESCRIPTION}
		<content type="xhtml">
			<div xmlns="http://www.w3.org/1999/xhtml">
				<a href=""><img src="{LOCATION}{PHOTO_SRC_MEDIUM}" title="{PHOTO_TITLE}" /></a>
			</div>
		</content>
		<link rel="enclosure" type="{PHOTO_MIME}" href="{LOCATION}{PHOTO_SRC_MEDIUM}" />
	</entry>
{/block:Photos}');
$entries->assign('BASE', BASE);
$entries->assign('LOCATION', LOCATION);
$entries->loop($photos);

echo '<?xml version="1.0" encoding="utf-8"?>';

?>

<feed xmlns="http://www.w3.org/2005/Atom">
	
	<title><?php echo $alkaline->returnConf('web_title'); ?></title>
	<updated><?php echo date('c', strtotime($photos->photos[0]['photo_published'])); ?></updated>
	<link href="<?php echo BASE; ?>"/>
	<link rel="self" type="application/atom+xml" href="<?php echo LOCATION . BASE; ?>atom.php" />
	<id>tag:<?php echo DOMAIN; ?>,2010:/</id>
	<author>
		<name><?php echo $alkaline->returnConf('web_name'); ?></name>
	</author>
	
	<?php echo $entries; ?>

</feed>