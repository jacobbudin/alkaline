<?php

require_once('./alkaline.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'photo.php');
require_once(PATH . CLASSES . 'render.php');

$header = new Render('header');
$header->setVar('TITLE', 'Jacob Budin');
$header->output();

// $photos = new Photo('82');
$photos = new Photo('82,83,84,85');
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