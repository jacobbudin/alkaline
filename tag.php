<?php

require_once('config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$alkaline->recordStat('tag');

$id = $alkaline->findID($_GET['id']);
$tag = $alkaline->getRow('tags', $id);
if(!$tag){ $alkaline->error('No tag was found.', 404); }

$photo_ids = new Find;
$photo_ids->page(null,5,4);
$photo_ids->published();
$photo_ids->privacy('public');
$photo_ids->tags($id);
$photo_ids->find();

$photos = new Photo($photo_ids);
$photos->formatTime();
$photos->getImgUrl('medium');
$photos->getExif();
$photos->getTags();
$photos->getRights();

$header = new Canvas;
$header->load('header');
$header->assign('Title', '#' . $tag['tag_name'] . ' &#8212; ' . $alkaline->returnConf('web_title'));
$header->display();

$index = new Canvas;
$index->load('index');
$index->assign('Page_Next', $photo_ids->page_next);
$index->assign('Page_Previous', $photo_ids->page_previous);
$index->assign('Page_Current', $photo_ids->page);
$index->assign('Page_Count', $photo_ids->page_count);
$index->assignArray($tag);
$index->loop($photos);
$index->display();

$footer = new Canvas;
$footer->load('footer');
$footer->display();

?>