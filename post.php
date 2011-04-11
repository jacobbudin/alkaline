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
if(!$id){ $alkaline->addError('No post was found.', 'Try searching for the post you were seeking.', null, null, 404); }

$post_ids = new Find('posts', $id);
$post_ids->published();
$post_ids->find();

if(!$post_ids){ $alkaline->addError('No post was found.', 'Try searching for the post you were seeking.', null, null, 404); }

$posts = new Post($post_ids);
$posts->getComments(true);
$posts->formatTime();
$posts->updateViews();
$post = $posts->posts[0];

$header = new Canvas;
$header->load('header');
$header->setTitle($post['post_title']);
$header->assign('Canonical', $post['image_uri']);
$header->display();

$content = new Canvas;
$content->wrapForm();
$content->load('post');
$content->loop($posts);
$content->display();

$breadcrumb = array('Posts' => 'blog' . URL_CAP);

$footer = new Canvas;
$footer->load('footer');
$footer->setBreadcrumb($breadcrumb);
$footer->display();

?>