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
$alkaline->recordStat('blog');

$posts = new Post;
$posts->page(null);
$posts->published();
$posts->fetch();
$posts->formatTime();

$header = new Canvas;
$header->load('header');
$header->setTitle('Blog');
$header->display();

$index = new Canvas;
$index->load('blog');
$index->assign('Page_Next', $posts->page_next);
$index->assign('Page_Previous', $posts->page_previous);
$index->assign('Page_Next_URI', $posts->page_next_uri);
$index->assign('Page_Previous_URI', $posts->page_previous_uri);
$index->assign('Page_Current', $posts->page);
$index->assign('Page_Count', $posts->page_count);
$index->loop($posts);
$index->display();

$footer = new Canvas;
$footer->load('footer');
$footer->display();

?>