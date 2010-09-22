<?php

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

if(empty($_POST['photo_file'])){
	$photo_files = $alkaline->seekDirectory(PATH . SHOEBOX);
	$photo_files = array_map('base64_encode', $photo_files);
	echo json_encode($photo_files);
}
else{
	$photo = new Photo();
	$photo->import(base64_decode($_POST['photo_file']));
	$tags = $photo->getTags();
	$photo = $photo->photos[0];
	$tag_names = array();
	foreach($tags as $tag){
		$tag_names[] = $tag['tag_name'];
	}
	
	if($user->returnPref('shoe_pub') === true){
		$photo['photo_published'] = 'Now';
	}
	
	$photo['photo_tags'] = $tag_names;
	echo $alkaline->removeNull(json_encode($photo));
}

?>