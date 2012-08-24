<?php

/*
// Alkaline
// Copyright (c) 2010-2012 by Budin Ltd. Some rights reserved.
// http://www.alkalineapp.com/
*/

require_once('config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$alkaline->recordStat('blog');

$post_ids = new Find('posts');
$post_ids->page(null, 3);
$post_ids->published();
$post_ids->find();

$posts = new Post($post_ids);
$posts->formatTime();
$posts->addSequence('last', 2);

for ($i=0; $i < $posts->post_count; $i++) { 
	if($i > 1){
		$posts->posts[$i]['post_hr'] = '<hr />';
	}
}

$header = new Canvas;
$header->load('header');
$header->setTitle('Blog');
$header->display();

$index = new Canvas;
$index->load('blog');
$index->assign('Page_Next', $post_ids->page_next);
$index->assign('Page_Previous', $post_ids->page_previous);
$index->assign('Page_Next_URI', $post_ids->page_next_uri);
$index->assign('Page_Previous_URI', $post_ids->page_previous_uri);
$index->assign('Page_Current', $post_ids->page);
$index->assign('Page_Count', $post_ids->page_count);
$index->loop($posts);
$index->display();

$footer = new Canvas;
$footer->load('footer');
$footer->display();

?>