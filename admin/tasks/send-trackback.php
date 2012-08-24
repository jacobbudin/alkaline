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

if(!empty($_REQUEST['id'])){
	$posts = new Post($_REQUEST['id']);
	$bool = $posts->sendTrackbacks();
}

if($bool === true){
	$alkaline->addNote('A trackback has successfully been sent.', 'success');
}
else{
	$alkaline->addNote('A trackback could not be sent.', 'error');
}

header('Location: ' . BASE . ADMIN . 'posts' . URL_ID . $posts->posts[0]['post_id'] . URL_RW);

?>