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
$alkaline->recordStat('image');
$alkaline->addComments();

$id = $alkaline->findID($_GET['id'], true);
if(!$id){ $alkaline->addError('No image was found.', 'Try searching for the image you were seeking.', null, null, 404); }

$image_ids = new Find($id);
$image_ids->published();
$image_ids->privacy('public');
$image_ids->find();

if(empty($image_ids->image_ids)){ $alkaline->addError('No image was found.', 'Try searching for the image you were seeking.', null, null, 404); }

$images = new Image($image_ids);
$images->updateViews();
$images->formatTime();
$images->getSizes('medium');
$images->getEXIF();
$images->getColorkey(950, 15);
$images->getTags();
$images->getPages();
$images->getSets();
$images->getRights();
$images->getComments(true);
$images->hook();

$header = new Canvas;
$header->load('header');
$header->setTitle(@$images->images[0]['image_title']);
$header->display();

$content = new Canvas;
$content->wrapForm();
$content->load('image');
$content->loop($images);
$content->display();

$footer = new Canvas;
$footer->load('footer');
$footer->display();

?>