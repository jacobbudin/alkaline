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
$alkaline->recordStat('post');
$alkaline->addComments();

$id = $alkaline->findID($_GET['id']);

if($id){
	$posts = new Post($id);
	$posts->published();
	$posts->fetch();
	$posts->getComments(true);
	$posts->formatTime();
	$posts->updateViews();
	$post = $posts->posts[0];
	
	if(!$post){ $alkaline->addError('No post was not found.', 'Try searching for the post you were seeking.', null, null, 404); }

	$header = new Canvas;
	$header->load('header');
	$header->setTitle($post['post_title']);
	$header->display();

	$content = new Canvas;
	$content->wrapForm();
	$content->load('post');
	$content->loop($posts);
	$content->display();

	$footer = new Canvas;
	$footer->load('footer');
	$footer->display();
}

?>