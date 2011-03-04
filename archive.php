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
$alkaline->recordStat('archive');

$year = intval($_REQUEST['y']);
$month = intval($_REQUEST['m']);
$day = intval($_REQUEST['d']);

$date = $year;
if(!empty($month)){ $date .= '-' . $month; }
if(!empty($day)){ $date .= '-' . $day; }

$image_ids = new Find;
$image_ids->page(null, 12);
$image_ids->published($date);
$image_ids->privacy('public');
$image_ids->sort('image_published', 'ASC');
$image_ids->find();

if(empty($image_ids->image_ids)){ $alkaline->addError('No images were found.', 'Try searching for the images you were seeking.', null, null, 404); }

$images = new Image($image_ids);
$images->formatTime();
$images->getSizes();
$images->getEXIF();
$images->getColorkey(950, 15);
$images->getSets();
$images->getTags();
$images->getRights();
$images->getPages();
$images->getComments();
$images->addSequence('medium_last', 3);
$images->hook();

$header = new Canvas;
$header->load('header');
$header->setTitle('Welcome');
$header->display();

$pages = new Page;
$pages->fetchAll();

$sets = new Set;
$sets->fetchAll();

$directory = new Canvas;
$directory->load('directory');
$directory->loop($pages);
$directory->loop($sets);
$directory->display();

$index = new Canvas;
$index->load('index_sub');
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

?>