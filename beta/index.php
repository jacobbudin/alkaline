<?php

require_once('./config.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'find.php');
require_once(PATH . CLASSES . 'photo.php');
require_once(PATH . CLASSES . 'user.php');
require_once(PATH . CLASSES . 'render.php');

$header = new Render('header');
$header->setVar('TITLE', 'Home Page - Jacob Budin');
$header->output();

$photo_ids = new Find();
// $photo_ids->search('abacus');
// $photo_ids->findByUploaded('2010', '2011');
// $photo_ids->findByViews(1,2);
$photo_ids->page(2,5);
$photo_ids->published();
$photo_ids->exec();

$photos = new Photo($photo_ids->photo_ids);
// $photos->updateViews();
$photos->addImgUrl('square');
$photos->addImgUrl('medium');
$photos->addExif();

$index = new Render('index');
$index->setVar('PAGE_NEXT', $photo_ids->page_next);
$index->setVar('PAGE_PREVIOUS', $photo_ids->page_previous);
$index->setVar('PAGE_CURRENT', $photo_ids->page);
$index->setArray('THUMBNAILS', 'PHOTO', $photos->photos);
$index->setArray('PHOTOS', 'PHOTO', $photos->photos);
$index->output();

$footer = new Render('footer');
$footer->output();

?>