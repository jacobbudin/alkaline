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
$alkaline->recordStat('pile');

$id = $alkaline->findID($_GET['id']);

if($id){
	$pile = new Pile($id);
	$pile = @$pile->piles[0];
	if(!$pile){ $alkaline->addError('No pile was not found.', 'Try searching for the pile you were seeking.', null, null, 404); }

	$pile['pile_created'] = $alkaline->formatTime($pile['pile_created']);
	$pile['pile_modified'] = $alkaline->formatTime($pile['pile_modified']);

	$image_ids = new Find;
	$image_ids->page(null,0);
	$image_ids->published();
	$image_ids->privacy('public');
	$image_ids->pile($pile['pile_id']);
	$image_ids->find();

	$images = new Image($image_ids);
	$images->formatTime();
	$images->getImgUrl('square');
	$images->getEXIF();
	$images->getTags();
	$images->getRights();

	$header = new Canvas;
	$header->load('header');
	$header->setTitle(@$pile['pile_title']);
	$header->display();

	$index = new Canvas;
	$index->load('pile');
	$index->assignArray($pile);
	$index->loop($images);
	$index->display();

	$footer = new Canvas;
	$footer->load('footer');
	$footer->display();
}

?>