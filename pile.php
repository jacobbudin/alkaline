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

	$photo_ids = new Find;
	$photo_ids->page(null,0);
	$photo_ids->published();
	$photo_ids->privacy('public');
	$photo_ids->pile($pile['pile_id']);
	$photo_ids->find();

	$photos = new Photo($photo_ids);
	$photos->formatTime();
	$photos->getImgUrl('square');
	$photos->getEXIF();
	$photos->getTags();
	$photos->getRights();

	$header = new Canvas;
	$header->load('header');
	$header->setTitle(@$pile['pile_title']);
	$header->display();

	$index = new Canvas;
	$index->load('pile');
	$index->assignArray($pile);
	$index->loop($photos);
	$index->display();

	$footer = new Canvas;
	$footer->load('footer');
	$footer->display();
}

?>