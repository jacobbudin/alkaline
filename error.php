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
$alkaline->recordStat('error');

$header = new Canvas;
$header->load('header');
$header->setTitle('Welcome');
$header->display();

$index = new Canvas;
$index->load('error');
$index->assignArray($_SESSION['alkaline']['error']);
$index->display();

$footer = new Canvas;
$footer->load('footer');
$footer->display();

?>