<?php

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

// Open cities JSON file
$id = $alkaline->findID(@$_POST['photo_id']);

if(empty($id)){
	$query = $alkaline->prepare('SELECT DISTINCT tags.tag_id FROM tags, links WHERE tags.tag_id != links.tag_id;');
	$query->execute();
	$orphans = $query->fetchAll();
	
	$tag_ids = array();
	
	foreach($orphans as $orphan){
		$tag_ids = $orphan['tag_id'];
	}
	
	echo json_encode($tag_ids);
}
else{
	$alkaline->exec('DELETE FROM tags WHERE tag_id = ' . intval($id));
}

?>