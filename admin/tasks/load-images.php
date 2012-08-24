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

$image_ids = new Find('images');
$image_ids->sort('image_uploaded', 'DESC');
$image_ids->page(1, 100);
$image_ids->find();

$images = new Image($image_ids);
$images->getSizes();

if($alkaline->returnConf('post_size_label')){
	$label = 'image_src_' . $alkaline->returnConf('post_size_label');
}
else{
	$label = 'image_src_admin';
}

if($alkaline->returnConf('post_div_wrap')){
	echo '<div class="none wrap_class">' . $alkaline->returnConf('post_div_wrap_class') . '</div>';
}

foreach($images->images as $image){
	$image['image_title'] = $alkaline->makeHTMLSafe($image['image_title']);
	echo '<a href="' . $image[$label] . '"><img src="' . $image['image_src_square'] .'" alt="' . $image['image_title']  . '" class="frame" id="image-' . $image['image_id'] . '" /></a>';
	echo '<div class="none uri_rel image-' . $image['image_id'] . '">' . $image['image_uri_rel'] . '</div>';
}

?>