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
	$olds = array();
	
	$query = $alkaline->prepare('SELECT DISTINCT versions.version_id FROM versions WHERE versions.version_similarity > :version_similarity AND versions.version_created < :version_created;');
	$query->execute(array(':version_similarity' => 65, ':version_created' => date('Y-m-d H:i:s', strtotime('-2 weeks'))));
	$versions1 = $query->fetchAll();
	
	$query->execute(array(':version_similarity' => 95, ':version_created' => date('Y-m-d H:i:s', strtotime('-6 months'))));
	$versions2 = $query->fetchAll();
	
	$versions = array_merge($versions1, $versions2);
	
	$version_ids = array();
	
	foreach($versions as $version){
		$version_ids[] = $version['version_id'];
	}
	
	echo json_encode($version_ids);
}
else{
	$alkaline->exec('DELETE FROM versions WHERE version_id = ' . intval($id));
}

?>