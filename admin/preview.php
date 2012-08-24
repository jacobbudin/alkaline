<?php

/*
// Alkaline
// Copyright (c) 2010-2012 by Budin Ltd. Some rights reserved.
// http://www.alkalineapp.com/
*/

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$orbit = new Orbit;
$user = new User;

$user->perm(true);

if(!empty($_POST['act']) and !empty($_POST['object'])){
	$_SESSION['alkaline']['preview']['act'] = $_POST['act'];
	$object = $_POST['object'];
	
	$block = $_SESSION['alkaline']['preview']['act'] . 's';
	$object[$block] = 1;
	
	if($block == 'posts'){
		if(!empty($object['post_markup'])){
			$post_markup_ext = $object['post_markup'];
			$object['post_text'] = $orbit->hook('markup_' . $post_markup_ext, $object['post_text_raw'], $object['post_text_raw']);
			$object['post_title'] = $orbit->hook('markup_title_' . $post_markup_ext, $object['post_title'], $object['post_title']);
			$object['post_excerpt'] = $orbit->hook('markup_' . $post_markup_ext, $object['post_excerpt_raw'], $object['post_excerpt_raw']);
		}
		elseif($alkaline->returnConf('web_markup')){
			$post_markup_ext = $alkaline->returnConf('web_markup_ext');
			$object['post_text'] = $orbit->hook('markup_' . $post_markup_ext, $object['post_text_raw'], $object['post_text_raw']);
			$object['post_title'] = $orbit->hook('markup_title_' . $post_markup_ext, $object['post_title'], $object['post_title']);
			$object['post_excerpt'] = $orbit->hook('markup_' . $post_markup_ext, $object['post_excerpt_raw'], $object['post_excerpt_raw']);
		}
		else{
			$post_markup_ext = '';
			$object['post_text'] = $alkaline->nl2br($object['post_text_raw']);
			$object['post_excerpt'] = $alkaline->nl2br($object['post_excerpt_raw']);
		}
	}
	elseif($block == 'images'){
		if(!empty($object['image_markup'])){
			$image_markup_ext = $object['image_markup'];
			$object['image_description'] = $orbit->hook('markup_' . $image_markup_ext, $object['image_description_raw'], $object['image_description_raw']);
			$object['image_title'] = $orbit->hook('markup_title_' . $image_markup_ext, $object['image_title'], $object['image_title']);
			$object['image_excerpt'] = $orbit->hook('markup_' . $image_markup_ext, $object['image_excerpt_raw'], $object['image_excerpt_raw']);
		}
		elseif($alkaline->returnConf('web_markup')){
			$image_markup_ext = $alkaline->returnConf('web_markup_ext');
			$object['image_description'] = $orbit->hook('markup_' . $image_markup_ext, $object['image_description_raw'], $object['image_description_raw']);
			$object['image_title'] = $orbit->hook('markup_title_' . $image_markup_ext, $object['image_title'], $object['image_title']);
			$object['image_excerpt'] = $orbit->hook('markup_' . $image_markup_ext, $object['image_excerpt_raw'], $object['image_excerpt_raw']);
		}
		else{
			$image_markup_ext = '';
			$object['image_description'] = $alkaline->nl2br($object['image_description_raw']);
			$object['image_excerpt'] = $alkaline->nl2br($object['image_excerpt_raw']);
		}
	}
	elseif($block == 'pages'){
		if(!empty($object['page_markup_ext'])){
			$page_markup_ext = $object['page_markup_ext'];
			$object['page_text'] = $orbit->hook('markup_' . $page_markup_ext, $object['page_text_raw'], $object['page_text_raw']);
			$object['page_title'] = $orbit->hook('markup_title_' . $page_markup_ext, $object['page_title'], $object['page_title']);
			$object['page_excerpt'] = $orbit->hook('markup_' . $page_markup_ext, $object['page_excerpt_raw'], $object['page_excerpt_raw']);
		}
		elseif($alkaline->returnConf('web_markup')){
			$page_markup_ext = $alkaline->returnConf('web_markup_ext');
			$object['page_text'] = $orbit->hook('markup_' . $page_markup_ext, $object['page_text_raw'], $object['page_text_raw']);
			$object['page_title'] = $orbit->hook('markup_title_' . $page_markup_ext, $object['page_title'], $object['page_title']);
			$object['page_excerpt'] = $orbit->hook('markup_' . $page_markup_ext, $object['page_excerpt_raw'], $object['page_excerpt_raw']);
		}
		else{
			$page_markup_ext = '';
			$object['page_text'] = $alkaline->nl2br($object['page_text_raw']);
			$object['page_excerpt'] = $alkaline->nl2br($object['page_excerpt_raw']);
		}
	}
	
	$id_label = $_POST['act'] . '_id';
	$id = $object[$id_label];
	
	$_SESSION['alkaline']['preview']['id'] = $id;
	$_SESSION['alkaline']['preview']['object'] = $object;
	exit();
}

if(empty($_SESSION['alkaline']['preview']['act'])){
	header('Location: ' . LOCATION . BASE . ADMIN);
}

$_GET['id'] = $_SESSION['alkaline']['preview']['id'];

chdir(PATH);
require_once('./' . $_SESSION['alkaline']['preview']['act'] . '.php');

unset($_SESSION['alkaline']['preview']);

?>