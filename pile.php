<?php

require_once('config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$alkaline->recordStat('pile');

$id = $alkaline->findID($_GET['id']);

$photo_ids = new Find;
$photo_ids->page(null,5,3);
$photo_ids->published();
$photo_ids->privacy('public');
$photo_ids->pile($id);
$photo_ids->find();

$photos = new Photo($photo_ids);
$photos->formatTime();
$photos->getImgUrl('medium');
$photos->getExif();
$photos->getTags();
$photos->getRights();

$header = new Canvas;
$header->load('header');
$header->assign('TITLE', 'Welcome &#8212; ' . $alkaline->returnConf('web_title'));
$header->display();

$index = new Canvas;
$index->load('index');
$index->assign('PAGE_NEXT', $photo_ids->page_next);
$index->assign('PAGE_PREVIOUS', $photo_ids->page_previous);
$index->assign('PAGE_CURRENT', $photo_ids->page);
$index->assign('PAGE_COUNT', $photo_ids->page_count);
$index->loop($photos);
$index->display();

$footer = new Canvas;
$footer->load('footer');
$footer->display();

?>