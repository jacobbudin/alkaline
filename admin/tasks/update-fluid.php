<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalineapp.com/
*/

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$json = array();
$json['dockBadge'] = array_sum($alkaline->getBadges());

$now = time();

$comment_ids = new Find('comments');
$comment_ids->sort('comments.comment_created', 'DESC');
$comment_ids->created($now - 30, $now);
$comment_ids->page(1, 5);
$comment_ids->find();

$comments = new Comment($comment_ids);
$json['showGrowlNotification'] = array();

foreach($comments->comments as $comment){
	if(!empty($comment['comment_response'])){ continue; }
	$comment_text = html_entity_decode($alkaline->fitStringByWord($comment['comment_text'], 100), ENT_QUOTES, 'UTF-8');
	$json['showGrowlNotification'][] = array('title' => 'New comment', 'description' => $comment_text);
}

echo $alkaline->removeNull(json_encode($json));

?>