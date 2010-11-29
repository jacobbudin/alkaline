<?php

require_once('config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$alkaline->recordStat('page');

$id = $alkaline->findID($_GET['id']);
$page = new Page($id);
$page = @$page->pages[0];

if(!$page){ $alkaline->error('No page was found.', 404); }

$page['page_created'] = $alkaline->formatTime($page['page_created']);
$page['page_modified'] = $alkaline->formatTime($page['page_modified']);

$header = new Canvas;
$header->load('header');
$header->assign('Title', $page['page_title'] . ' &#8212; ' . $alkaline->returnConf('web_title'));
$header->display();

$index = new Canvas;
$index->load('page');
$index->assignArray($page);
$index->display();

$footer = new Canvas;
$footer->load('footer');
$footer->display();

?>