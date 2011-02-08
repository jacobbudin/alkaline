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

$image_ids = new Find;
$image_ids->sort('images.image_published', 'DESC');
$image_ids->published();
$image_ids->privacy('public');
$image_ids->find();

$images = new Image($image_ids);
// $images->updateViews();
$images->formatTime();
$images->getImgUrl('square');
$images->getImgUrl('medium');
$images->getEXIF();
$images->getColorkey(670, 10);
$images->getSets();
$images->getTags();
$images->getRights();
$images->getComments();

$header = new Canvas;
$header->load('header');
$header->setTitle('Search Results (' . $image_ids->image_count . ')');
$header->display();

$index = new Canvas;
$index->load('results');
$index->assign('Page_Next', $image_ids->page_next);
$index->assign('Page_Previous', $image_ids->page_previous);
$index->assign('Page_Next_URI', $image_ids->page_next_uri);
$index->assign('Page_Previous_URI', $image_ids->page_previous_uri);
$index->assign('Page_Current', $image_ids->page);
$index->assign('Page_Count', $image_ids->page_count);
$index->loop($images);
$index->display();

$footer = new Canvas;
$footer->load('footer');
$footer->display();