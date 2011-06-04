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

$id = $alkaline->findID(@$_POST['image_id']);

if(empty($id)){
	$image_ids = new Find('images', null, null, null, false);
	$image_ids->find();
	echo json_encode($image_ids->ids);
}
else{
	$images = new Image($id);
	$sizes = $images->getSizes();
	$image = $images->images[0];
	$src = $image['image_file'];
	
	$dir = '';
	
	if($alkaline->returnConf('image_hdm') == true){
		if($alkaline->returnConf('image_hdm_format') == 'yyyy/mm/dd'){
			$dir = substr($image['image_uploaded'], 0, 10);
			$dir = str_replace('-', '/', $dir);
		}
		elseif($alkaline->returnConf('image_hdm_format') == '1000'){
			if($image['image_id'] < 1000){
				$dir = '0000';
			}
			else{
				$dir = substr($image['image_id'], 0, -3) . '000';
			}
		}
		
		$dir .= '/';
	}
	
	$path = $alkaline->correctWinPath(PATH . IMAGES . $dir);
	$dest = $path . $image['image_id'] . '.' . $image['image_ext'];
	
	if($src != $dest){
		if(!is_dir($path)){
			mkdir($path, 0777, true);
		}
		
		$success = true;
		
		rename($src, $dest);
		
		foreach($sizes as $size){
			$src = $size['size_file'];
			$dest = $alkaline->correctWinPath(PATH . IMAGES . $dir . $size['size_prepend'] . $image['image_id'] . $size['size_append'] . '.' . $image['image_ext']);
			
			var_dump($src);
			var_dump($dest);
			
			rename($src, $dest);
		}
		
		$images->updateFields(array('image_directory' => $dir));
	}
}

?>