<?php

require_once('config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$alkaline->recordStat('slideshow');

$photo_ids = new Find;
$photo_ids->sort('photos.photo_published', 'DESC');
$photo_ids->privacy('public');
$photo_ids->find();

$photos = new Photo($photo_ids);
$photos->formatTime();
$photos->getImgUrl('medium');
$photos->getExif();
$photos->getTags();
$photos->getRights();
$photos->getComments();

$header = new Canvas;
$header->load('slide_header');
$header->assign('TITLE', 'Welcome &#8212; ' . $alkaline->returnConf('web_title'));
$header->display();

$slideshow = new Canvas;
$slideshow->load('slide');
$slideshow->slideshow();
$slideshow->loop($photos);
$slideshow->display();

$header = new Canvas;
$header->load('slide_footer');
$header->display();

?>