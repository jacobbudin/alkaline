<?php

require_once('./alkaline.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'find.php');
require_once(PATH . CLASSES . 'photo.php');
require_once(PATH . CLASSES . 'render.php');

$header = new Render('header');
$header->setVar('TITLE', 'Jacob Budin');
$header->output();

$photo_ids = new Find();
$photo_ids->findByUploaded('2010', '2011');
// $photo_ids->findByViews();
// $photo_ids->page(1,1);
$photo_ids->exec();

$photos = new Photo($photo_ids->photo_ids);
// $photos->updateViews();
$photos->addImgUrl('square');
$photos->addImgUrl('medium');
$photos->addExif();

$index = new Render('index');
$index->setArray('THUMBNAILS', 'PHOTO', $photos->photos);
$index->setArray('PHOTOS', 'PHOTO', $photos->photos);
$index->output();

$footer = new Render('footer');
$footer->output();

?>