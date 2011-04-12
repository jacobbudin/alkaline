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
$alkaline->recordStat('set');

$id = $alkaline->findID($_GET['id']);
if(!$id){ $alkaline->addError('No set was found.', 'Try searching for the set you were seeking.', null, null, 404); }

$set = new Set($id);
$set = @$set->sets[0];
if(!$set){ $alkaline->addError('No set was found.', 'Try searching for the set you were seeking.', null, null, 404); }

$set['set_created'] = $alkaline->formatTime($set['set_created']);
$set['set_modified'] = $alkaline->formatTime($set['set_modified']);

$image_ids = new Find('images');
$image_ids->page(null, 0);
$image_ids->published();
$image_ids->privacy('public');
$image_ids->sets(intval($set['set_id']));
$image_ids->find();

$images = new Image($image_ids);
$images->formatTime();
$images->getSizes('small');
$images->getEXIF();
$images->getTags();
$images->getRights();

$header = new Canvas;
$header->load('header');
$header->setTitle(@$set['set_title']);
$header->assign('Canonical', $set['set_uri']);
$header->display();

$content = new Canvas;
$content->load('set');
$content->loop($images);
$content->assignArray($set);
$content->display();

$footer = new Canvas;
$footer->load('footer');
$footer->display();

?>