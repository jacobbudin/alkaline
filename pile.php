<?php

require_once('config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$alkaline->recordStat('pile');

$id = $alkaline->findID($_GET['id']);

$photo_ids = new Find;
$photo_ids->page(null,5);
$photo_ids->published();
$photo_ids->privacy('public');
$photo_ids->pile($id);
$photo_ids->find();

$pile = $alkaline->getRow('piles', $id);

$photos = new Photo($photo_ids);
$photos->formatTime();
$photos->getImgUrl('square');
$photos->getExif();
$photos->getTags();
$photos->getRights();

$header = new Canvas;
$header->load('header');
$header->assign('TITLE', 'Welcome &#8212; ' . $alkaline->returnConf('web_title'));
$header->display();

$index = new Canvas;
$index->load('pile');
$index->assign('Pile_Title', $pile['pile_title']);
$index->assign('Pile_Description', $pile['pile_description']);
$index->loop($photos);
$index->display();

$footer = new Canvas;
$footer->load('footer');
$footer->display();

?>