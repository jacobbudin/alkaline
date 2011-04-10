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
$alkaline->recordStat('page');

$id = $alkaline->findID($_GET['id']);
if(!$id){ $alkaline->addError('No page was found.', 'Try searching for the images you were seeking.', null, null, 404); }

$pages = new Page($id);
$pages->formatTime('F j, Y');
$pages->updateViews();
$page = $pages->pages[0];

if(!$page){ $alkaline->addError('No page was found.', 'Try searching for the images you were seeking.', null, null, 404); }

$header = new Canvas;
$header->load('header_slim');
$header->setTitle($page['page_title']);
$header->display();

$content = new Canvas;
$content->load('page');
$content->loop($pages);
$content->assignArray($page);
$content->display();

$footer = new Canvas;
$footer->load('footer_slim');
$footer->display();

?>