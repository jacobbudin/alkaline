<?php

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$markup = $alkaline->returnConf('comment_markup_ext');

$query = $alkaline->prepare('SELECT comment_id FROM comments WHERE comment_markup != :comment_markup;');
$query->execute(array(':comment_markup' => $markup));
$comments = $query->fetchAll();

$comment_ids = array();

foreach($comments as $comment){
	$comment_ids[] = $comment['comment_id'];
}

if(count($comment_ids) > 0){
	$query = $alkaline->prepare('UPDATE comments SET comment_text_raw = comment_text, comment_markup = :comment_markup WHERE (comment_id IN (' . implode(', ', $comment_ids) . '));');
	$query->execute(array(':comment_markup' => $markup));
}

?>