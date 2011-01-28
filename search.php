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
$alkaline->recordStat('home');

$photo_ids = new Find;
$photo_ids->sort('photos.photo_published', 'DESC');
$photo_ids->privacy('public');
$photo_ids->find();

$photos = new Photo($photo_ids);
// $photos->updateViews();
$photos->formatTime();
$photos->getImgUrl('square');
$photos->getImgUrl('medium');
$photos->getEXIF();
$photos->getPiles();
$photos->getTags();
$photos->getRights();
$photos->getComments();

$header = new Canvas;
$header->load('header');
$header->setTitle('Search Results (' . $photo_ids->photo_count . ')');
$header->display();

$index = new Canvas;
$index->load('search');
$index->assign('Page_Next', $photo_ids->page_next);
$index->assign('Page_Previous', $photo_ids->page_previous);
$index->assign('Page_Current', $photo_ids->page);
$index->assign('Page_Count', $photo_ids->page_count);
$index->loop($photos);
$index->display();

$footer = new Canvas;
$footer->load('footer');
$footer->display();