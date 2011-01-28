<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalineapp.com/
*/

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
$photos->getEXIF();
$photos->getTags();
$photos->getRights();
$photos->getComments();

$header = new Canvas;
$header->load('slide_header');
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