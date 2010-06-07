<?php

require_once('./config.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'canvas.php');
require_once(PATH . CLASSES . 'find.php');
require_once(PATH . CLASSES . 'photo.php');
require_once(PATH . CLASSES . 'user.php');

$alkaline = new Alkaline();
$alkaline->recordStat('home');

$header = new Canvas();
$header->load('header');
$header->setVar('TITLE', 'Home Page - ' . SITE);
$header->display();

$photo_ids = new Find();
// $photo_ids->search('abacus');
// $photo_ids->uploaded('2010', '2011');
// $photo_ids->views(1,2);
// $photo_ids->sort('photos.photo_published', 'DESC');
$photo_ids->tags('beach');
$photo_ids->page(1,5);
$photo_ids->published();
// $photo_ids->pile('fun');
$photo_ids->exec();

$photos = new Photo($photo_ids->photo_ids);
// $photos->updateViews();
$photos->formatTime();
$photos->getImgUrl('square');
$photos->getImgUrl('medium');
$photos->getExif();
$photos->getTags();
$photos->getComments();

$index = new Canvas();
$index->load('index');
$index->setVar('PAGE_NEXT', $photo_ids->page_next);
$index->setVar('PAGE_PREVIOUS', $photo_ids->page_previous);
$index->setVar('PAGE_CURRENT', $photo_ids->page);
$index->setArray('THUMBNAILS', 'PHOTO', $photos->photos);
$index->setArray('PHOTOS', 'PHOTO', $photos->photos);
$index->display();

$footer = new Canvas();
$footer->load('footer');
$footer->display();

?>