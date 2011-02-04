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
if(!$id){ $alkaline->addError(E_USER_ERROR, 'No page was found'); }

$page = new Page($id);
$page = @$page->pages[0];
if(!$page){ $alkaline->addError(E_USER_WARNING, 'No page was found'); }

$page['page_created'] = $alkaline->formatTime($page['page_created']);
$page['page_modified'] = $alkaline->formatTime($page['page_modified']);

$header = new Canvas;
$header->load('header');
$header->setTitle($page['page_title']);
$header->display();

$index = new Canvas;
$index->load('page');
$index->assignArray($page);
$index->display();

$footer = new Canvas;
$footer->load('footer');
$footer->display();

?>