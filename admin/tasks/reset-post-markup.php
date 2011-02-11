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

$markup = $alkaline->returnConf('post_markup_ext');

$query = $alkaline->prepare('SELECT post_id FROM posts WHERE post_markup != :post_markup;');
$query->execute(array(':post_markup' => $markup));
$posts = $query->fetchAll();

$post_ids = array();

foreach($posts as $post){
	$post_ids[] = $post['post_id'];
}

if(count($post_ids) > 0){
	$query = $alkaline->prepare('UPDATE posts SET post_text_raw = post_text, post_markup = :post_markup WHERE (post_id IN (' . implode(', ', $post_ids) . '));');
	$query->execute(array(':post_markup' => $markup));
}

?>