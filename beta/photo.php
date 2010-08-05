<?php

require_once('./config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$orbit = new Orbit;

$alkaline->recordStat('photo');
$alkaline->addComments();

$id = $alkaline->findID($_GET['identifier']);

$photos = new Photo($id);
$photos->updateViews();
$photos->formatTime();
$photos->getImgUrl('medium');
$photos->getExif();
$photos->getTags();
$photos->getRights();
$photos->getComments();

$header = new Canvas;
$header->load('header');
$header->assign('TITLE', $photos->photos[0]['photo_title']);
$header->display();

$index = new Canvas;
$index->load('photo');
$index->loop($photos);
$index->display();

$footer = new Canvas;
$footer->load('footer');
$footer->display();

?>