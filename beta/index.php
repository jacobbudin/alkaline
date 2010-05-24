<?php

require_once('./config.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'canvas.php');
require_once(PATH . CLASSES . 'find.php');
require_once(PATH . CLASSES . 'photo.php');
require_once(PATH . CLASSES . 'user.php');

$header = new Canvas();
$header->load('header');
$header->setVar('TITLE', 'Home Page - ' . SITE);
$header->output();

$photo_ids = new Find();
// $photo_ids->search('abacus');
// $photo_ids->findByUploaded('2010', '2011');
// $photo_ids->findByViews(1,2);
$photo_ids->sort('photos.photo_published', 'DESC');
$photo_ids->page(1,5);
$photo_ids->published();
$photo_ids->exec();

$photos = new Photo($photo_ids->photo_ids);
// $photos->updateViews();
$photos->formatTime();
$photos->addImgUrl('square');
$photos->addImgUrl('medium');
$photos->addExif();
$photos->addTags();

$index = new Canvas();
$index->load('index');
$index->setVar('PAGE_NEXT', $photo_ids->page_next);
$index->setVar('PAGE_PREVIOUS', $photo_ids->page_previous);
$index->setVar('PAGE_CURRENT', $photo_ids->page);
$index->setArray('THUMBNAILS', 'PHOTO', $photos->photos);
$index->setArray('PHOTOS', 'PHOTO', $photos->photos);
$index->output();

$footer = new Canvas();
$footer->load('footer');
$footer->output();

?>