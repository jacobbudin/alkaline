<?php

require_once('config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$alkaline->recordStat('slideshow');

$photo_ids = new Find;
$photo_ids->sort('photos.photo_published', 'DESC');
$photo_ids->privacy('protected', true);
$photo_ids->find();

$photos = new Photo($photo_ids);
$photos->formatTime();
$photos->getImgUrl('medium');
$photos->getExif();
$photos->getTags();
$photos->getRights();
$photos->getComments();

$slideshow = new Canvas;
$slideshow->load('slideshow');
$slideshow->assign('TITLE', 'Slideshow &#8212; ' . SITE);
$slideshow->loop($photos);
$slideshow->display();

?>