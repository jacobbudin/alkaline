<?php

require_once('config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$alkaline->recordStat('pile');

$id = $alkaline->findID($_GET['id']);
$pile = $alkaline->getRow('piles', $id);
if(!$pile){ $alkaline->error('No pile was found.', 404); }

$pile['pile_created'] = $alkaline->formatTime($pile['pile_created']);
$pile['pile_modified'] = $alkaline->formatTime($pile['pile_modified']);

$photo_ids = new Find;
$photo_ids->page(null,5);
$photo_ids->published();
$photo_ids->privacy('public');
$photo_ids->pile($id);
$photo_ids->find();

$photos = new Photo($photo_ids);
$photos->formatTime();
$photos->getImgUrl('square');
$photos->getExif();
$photos->getTags();
$photos->getRights();

$header = new Canvas;
$header->load('header');
$header->assign('Title', $pile['pile_title'] . ' &#8212; ' . $alkaline->returnConf('web_title'));
$header->display();

$index = new Canvas;
$index->load('pile');
$index->assignArray($pile);
$index->loop($photos);
$index->display();

$footer = new Canvas;
$footer->load('footer');
$footer->display();

?>