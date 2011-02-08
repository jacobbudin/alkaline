<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalineapp.com/
*/

require_once('config.php');
require_once(PATH . CLASSES . 'alkaline.php');

header('Content-Type: application/xml');

$alkaline = new Alkaline();

$image_ids = new Find();
$image_ids->sort('images.image_published', 'DESC');
$image_ids->page(1,20);
$image_ids->published();
$image_ids->find();

$images = new Image($image_ids);
$images->getSizes('medium');
$images->formatTime('c');

// $images->images = $alkaline->makeHTMLSafe($images->images);

$entries = new Canvas('
{block:Images}
	<entry>
		<title type="text">{if:Image_Title}{Image_Title}{else:Image_Title}(Untitled){/if:Image_Title}</title>
		<link href="" />
		<id>{LOCATION}{BASE}{PHOTO_ID}</id>
		<updated>{PHOTO_UPDATED}</updated>
		<published>{PHOTO_PUBLISHED}</published>
		{if:PHOTO_DESCRIPTION}
			<summary type="xhtml">
				<div xmlns="http://www.w3.org/1999/xhtml">
					{PHOTO_DESCRIPTION}
				</div>
			</summary>
		{/if:PHOTO_DESCRIPTION}
		<content type="xhtml">
			<div xmlns="http://www.w3.org/1999/xhtml">
				<a href=""><img src="{LOCATION}{PHOTO_SRC_MEDIUM}" title="{PHOTO_TITLE}" /></a>
			</div>
		</content>
		<link rel="enclosure" type="{PHOTO_MIME}" href="{LOCATION}{PHOTO_SRC_MEDIUM}" />
	</entry>
{/block:Images}');
$entries->assign('BASE', BASE);
$entries->assign('LOCATION', LOCATION);
$entries->loop($images);

echo '<?xml version="1.0" encoding="utf-8"?>';

?>

<feed xmlns="http://www.w3.org/2005/Atom">
	
	<title><?php echo $alkaline->returnConf('web_title'); ?></title>
	<updated><?php echo date('c', strtotime($images->images[0]['image_published'])); ?></updated>
	<link href="<?php echo BASE; ?>" />
	<link rel="self" type="application/atom+xml" href="<?php echo LOCATION . BASE; ?>atom.php" />
	<id>tag:<?php echo DOMAIN; ?>,2010:/</id>
	<author>
		<name><?php echo $alkaline->returnConf('web_name'); ?></name>
	</author>
	
	<?php echo $entries; ?>

</feed>