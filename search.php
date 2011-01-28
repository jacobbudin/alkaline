<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalinenapp.com/
*/

require_once('config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$alkaline->recordStat('home');

$photo_ids = new Find;
$photo_ids->sort('photos.photo_published', 'DESC');
$photo_ids->privacy('public');

// SANITIZE SEARCH QUERY
$_REQUEST = array_map('strip_tags', $_REQUEST);

if(empty($_REQUEST) and empty($_REQUEST)){
	$photo_ids->memory();
}

// Smart search
if(!empty($_REQUEST['act'])){
	$photo_ids->smart($_REQUEST['act']);
}

// Title and description
if(!empty($_REQUEST['search'])){
	$photo_ids->search($_REQUEST['search']);
}

// Tags
if(!empty($_REQUEST['tags'])){
	$photo_ids->tags($_REQUEST['tags']);
}

// Rights set
if(!empty($_REQUEST['rights'])){
	$photo_ids->rights(intval($_REQUEST['rights']));
}

// Date taken
if(!empty($_REQUEST['taken_begin']) or !empty($_REQUEST['taken_end'])){
	$photo_ids->taken($_REQUEST['taken_begin'], $_REQUEST['taken_end']);
}

// Date uploaded
if(!empty($_REQUEST['uploaded_begin']) or !empty($_REQUEST['uploaded_end'])){
	$photo_ids->uploaded($_REQUEST['uploaded_begin'], $_REQUEST['uploaded_end']);
}

// Location
if(!empty($_REQUEST['location'])){
	$photo_ids->location($_REQUEST['location'], $_REQUEST['location_proximity']);
}

// Primary color
if(!empty($_REQUEST['color'])){
	switch($_REQUEST['color']){
		case 'blue':
			$photo_ids->hsl(170, 235, 1, 100, 1, 100);
			break;
		case 'red':
			$photo_ids->hsl(345, 10, 1, 100, 1, 100);
			break;
		case 'yellow':
			$photo_ids->hsl(50, 75, 1, 100, 1, 100);
			break;
		case 'green':
			$photo_ids->hsl(75, 170, 1, 100, 1, 100);
			break;
		case 'purple':
			$photo_ids->hsl(235, 300, 1, 100, 1, 100);
			break;
		case 'orange':
			$photo_ids->hsl(10, 50, 1, 100, 1, 100);
			break;
		case 'brown':
			$photo_ids->hsl(null, null, null, null, 1, 20);
			break;
		case 'pink':
			$photo_ids->hsl(300, 345, 1, 100, 1, 100);
			break;
		default:
			break;
	}
}

// Views
if(!empty($_REQUEST['views'])){
	switch($_REQUEST['views_operator']){
		case 'greater':
			$photo_ids->views($_REQUEST['views'], null);
			break;
		case 'less':
			$photo_ids->views(null, $_REQUEST['views']);
			break;
		case 'equal':
			$photo_ids->views($_REQUEST['views'], $_REQUEST['views']);
			break;
	}
}

// Orientation
if(!empty($_REQUEST['orientation'])){
	switch($_REQUEST['orientation']){
		case 'portrait':
			$photo_ids->ratio(1, null, null);
			break;
		case 'landscape':
			$photo_ids->ratio(null, 1, null);
			break;
		case 'square':
			$photo_ids->ratio(null, null, 1);
			break;
	}
}

// Privacy
if(!empty($_REQUEST['privacy'])){
	switch($_REQUEST['privacy']){
		case 'public':
			$photo_ids->privacy(1);
			break;
		case 'protected':
			$photo_ids->privacy(2);
			break;
		case 'private':
			$photo_ids->privacy(3);
			break;
	}
}

// Published
if(!empty($_REQUEST['published'])){
	switch($_REQUEST['published']){
		case 'published':
			$photo_ids->published(true);
			break;
		case 'unpublished':
			$photo_ids->published(false);
			break;
	}
}

// Sort
if(!empty($_REQUEST['sort'])){
	switch($_REQUEST['sort']){
		case 'taken':
			$photo_ids->sort('photos.photo_taken', $_REQUEST['sort_direction']);
			$photo_ids->notnull('photos.photo_taken');
			break;
		case 'published':
			$photo_ids->sort('photos.photo_published', $_REQUEST['sort_direction']);
			$photo_ids->notnull('photos.photo_published');
			break;
		case 'uploaded':
			$photo_ids->sort('photos.photo_uploaded', $_REQUEST['sort_direction']);
			break;
		case 'updated':
			$photo_ids->sort('photos.photo_updated', $_REQUEST['sort_direction']);
			$photo_ids->notnull('photos.photo_updated');
			break;
		case 'title':
			$photo_ids->sort('photos.photo_title', $_REQUEST['sort_direction']);
			$photo_ids->notnull('photos.photo_title');
			break;
		case 'views':
			$photo_ids->sort('photos.photo_views', $_REQUEST['sort_direction']);
			break;
		default:
			break;
	}
}

$photo_ids->find();

$photos = new Photo($photo_ids);
// $photos->updateViews();
$photos->formatTime();
$photos->getImgUrl('square');
$photos->getImgUrl('medium');
$photos->getEXIF();
$photos->getPiles();
$photos->getTags();
$photos->getRights();
$photos->getComments();

$header = new Canvas;
$header->load('header');
$header->setTitle('Search Results (' . $photo_ids->photo_count . ')');
$header->display();

$index = new Canvas;
$index->load('search');
$index->assign('Page_Next', $photo_ids->page_next);
$index->assign('Page_Previous', $photo_ids->page_previous);
$index->assign('Page_Current', $photo_ids->page);
$index->assign('Page_Count', $photo_ids->page_count);
$index->loop($photos);
$index->display();

$footer = new Canvas;
$footer->load('footer');
$footer->display();