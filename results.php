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

if($_REQUEST['type'] == 'images'){
	$image_ids = new Find;
	$image_ids->sort('images.image_published', 'DESC');
	$image_ids->published();
	$image_ids->page();
	$image_ids->privacy('public');
	$image_ids->find();

	$images = new Image($image_ids);
	$images->formatTime();
	$images->getSizes();
	
	$count = $image_ids->image_count;
	$model = $image_ids;
	$loop = $images;
	
	$content = new Canvas;
	$content->load('results-images');
}
elseif($_REQUEST['type'] == 'posts'){
	$posts = new Post;
	$posts->page(null, 3);
	$posts->published();
	$posts->fetch();
	$posts->formatTime();
	$posts->addSequence('last', 2);

	for ($i=0; $i < $posts->post_count; $i++) { 
		if($i > 1){
			$posts->posts[$i]['post_hr'] = '<hr />';
		}
	}
	
	$count = $posts->post_count;
	$model = $posts;
	$loop = $posts;
	
	$content = new Canvas;
	$content->load('results-posts');
}

$header = new Canvas;
$header->load('header');
$header->setTitle('Search Results (' . $count . ')');
$header->display();

$content->assign('Results_Count', $count);
$content->assign('Page_Next', $model->page_next);
$content->assign('Page_Previous', $model->page_previous);
$content->assign('Page_Next_URI', $model->page_next_uri);
$content->assign('Page_Previous_URI', $model->page_previous_uri);
$content->assign('Page_Current', $model->page);
$content->assign('Page_Count', $model->page_count);
$content->loop($loop);
$content->display();

$footer = new Canvas;
$footer->load('footer');
$footer->display();