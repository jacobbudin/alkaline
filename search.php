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

$header = new Canvas;
$header->load('header_min');
$header->setTitle('Search');
$header->display();

$content = new Canvas;
$content->load('search');
$content->assign('EXIF_Names', $alkaline->showEXIFNames('exif_name'));
$content->assign('Rights', $alkaline->showRights('rights'));
$content->display();

$footer = new Canvas;
$footer->load('footer');
$footer->display();

?>