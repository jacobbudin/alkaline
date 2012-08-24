<?php

/*
// Alkaline
// Copyright (c) 2010-2012 by Budin Ltd. Some rights reserved.
// http://www.alkalineapp.com/
*/

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$markup = $alkaline->returnConf('web_markup_ext');

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

$query = $alkaline->prepare('SELECT image_id FROM images WHERE image_markup != :image_markup;');
$query->execute(array(':image_markup' => $markup));
$images = $query->fetchAll();

$image_ids = array();

foreach($images as $image){
	$image_ids[] = $image['image_id'];
}

if(count($image_ids) > 0){
	$query = $alkaline->prepare('UPDATE images SET image_description_raw = image_description, image_markup = :image_markup WHERE (image_id IN (' . implode(', ', $image_ids) . '));');
	$query->execute(array(':image_markup' => $markup));
}

?>