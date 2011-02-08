<?php

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

if(empty($_POST['image_file'])){
	$image_files = $alkaline->seekDirectory(PATH . SHOEBOX);
	$image_files = array_reverse($image_files);
	$image_files = array_map('base64_encode', $image_files);
	echo json_encode($image_files);
}
else{
	$image = new Image();
	$image->attachUser($user);
	$image->import(base64_decode($_POST['image_file']));
	$image->getSizes('admin');
	$tags = $image->getTags();
	$image = $image->images[0];
	$tag_names = array();
	foreach($tags as $tag){
		$tag_names[] = $tag['tag_name'];
	}
	
	if($user->returnPref('shoe_pub') === true){
		$image['image_published'] = 'Now';
	}
	
	$image['image_tags'] = $tag_names;
	echo $alkaline->removeNull(json_encode($image));
}

?>