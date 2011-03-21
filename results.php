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

if($_REQUEST['type'] == 'posts'){
	$post_ids = new Find('posts');
	$post_ids->sort('posts.post_published', 'DESC');
	$post_ids->published();
	$post_ids->page(null, 3);
	$post_ids->find();
	
	$posts = new Post($post_ids);
	$posts->formatTime();
	$posts->addSequence('last', 2);

	for ($i=0; $i < $posts->post_count; $i++) { 
		if($i > 1){
			$posts->posts[$i]['post_hr'] = '<hr />';
		}
	}
	
	$count = $post_ids->count;
	$model = $post_ids;
	$loop = $posts;
	
	$content = new Canvas;
	$content->load('results-posts');
}
else{
	$image_ids = new Find('images');
	$image_ids->sort('images.image_published', 'DESC');
	$image_ids->published();
	$image_ids->page();
	$image_ids->privacy('public');
	$image_ids->find();

	$images = new Image($image_ids);
	$images->formatTime();
	$images->getSizes();
	
	$count = $image_ids->count;
	$model = $image_ids;
	$loop = $images;
	
	$content = new Canvas;
	$content->load('results-images');
}

$header = new Canvas;
$header->load('header');
$header->setTitle('Search Results (' . $count . ')');
$header->display();

$content->assign('Results_Count', $count, true);
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