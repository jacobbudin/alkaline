<?php

/*
// Alkaline
// Copyright (c) 2010-2012 by Budin Ltd. Some rights reserved.
// http://www.alkalineapp.com/
*/

if(empty($_SERVER['PHP_AUTH_USER'])){
    header('WWW-Authenticate: Basic realm="Alkaline Dashboard Feed"');
    header('HTTP/1.0 401 Unauthorized');
    exit();
}

require_once('../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline();
$user = new User();

if($user->auth($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']) === false){
	header('WWW-Authenticate: Basic realm="Alkaline Dashboard Feed"');
    header('HTTP/1.0 401 Unauthorized');
    exit();
}

header('Content-Type: application/xml');

// Cache
require_once(PATH . CLASSES . 'cache_lite/Lite.php');

// Set a few options
$options = array(
    'cacheDir' => PATH . CACHE,
    'lifeTime' => $alkaline->returnConf('syndication_cache_time')
);

// Create a Cache_Lite object
$cache = new Cache_Lite($options);

if($xml = $cache->get('xml:user', 'xml')){
	echo $xml;
}
else{
	ob_start();

	// Daily stats
	$yesterday = strtotime('-1 day', strtotime(date('Y-m-d')));

	$hourly = new Stat($yesterday, $yesterday+86400);
	$hourly->getHourly();

	$h_views = number_format($hourly->views);
	$h_visitors = number_format($hourly->visitors);

	// Gather comments

	$comment_ids = new Find('comments');
	$comment_ids->sort('comments.comment_created', 'DESC');
	$comment_ids->page(1,10);
	$comment_ids->response(false);
	$comment_ids->find();

	$comments = new Comment($comment_ids);
	$comments->formatTime('c');

	$comment_entries = new Canvas('
	{block:Comments}
		<entry>
			<title type="text">New comment ({Comment_Author_Name})</title>
			<link href="{define:Location}{define:Base}{define:Admin}comments{define:URL_ID}{Comment_ID}{define:URL_RW}" />
			<id>{define:Location}{define:Base}{define:Admin}comments{define:URL_ID}{Comment_ID}{define:URL_RW}</id>
			<updated>{Comment_Modified_Format}</updated>
			<published>{Comment_Created_Format}</published>
			<content type="xhtml">
				<div xmlns="http://www.w3.org/1999/xhtml">
					{Comment_Text}
				</div>
			</content>
		</entry>
	{/block:Comments}');
	$comment_entries->assign('Base', BASE);
	$comment_entries->assign('Location', LOCATION);
	$comment_entries->loop($comments);

	// Gather trackbacks

	$trackback_ids = new Find('trackbacks');
	$trackback_ids->sort('trackbacks.trackback_created', 'DESC');
	$trackback_ids->page(1,10);
	$trackback_ids->find();

	$trackbacks = $alkaline->getTable('trackbacks', $trackback_ids->trackback_ids);
	foreach($trackbacks as &$trackback){
		$trackback['trackback_created_format'] = date('c', strtotime($trackback['trackback_created']));
	}

	$trackbacks['trackback_ids'] = $trackback_ids->trackback_ids;
	$trackbacks['trackbacks'] = $trackbacks;
	$trackbacks = (object) $trackbacks;

	$trackback_entries = new Canvas('
	{block:Trackbacks}
		<entry>
			<title type="text">New trackback ({Trackback_Title})</title>
			<link href="{Trackback_URI}" />
			<id>{Trackback_URI}</id>
			<updated>{Trackback_Created_Format}</updated>
			<published>{Trackback_Created_Format}</published>
			<content type="xhtml">
				<div xmlns="http://www.w3.org/1999/xhtml">
					{Trackback_Excerpt}
				</div>
			</content>
		</entry>
	{/block:Trackbacks}');
	$trackback_entries->loop($trackbacks);

	$updated['comment'] = strtotime($images->images[0]['comment_created']);
	$updated['trackback'] = strtotime($trackbacks->trackbacks[0]['trackback_created']);

	$last_updated = 0;

	foreach($updated as $table => $time){
		if($time > $last_updated){
			$last_updated = $time;
		}
	}

	echo '<?xml version="1.0" encoding="utf-8"?>';

	?>

	<feed xmlns="http://www.w3.org/2005/Atom">
		<title>Alkaline Dashboard Feed</title>
		<updated><?php echo date('c', strtotime($images->images[0]['image_published'])); ?></updated>
		<link href="<?php echo BASE . ADMIN; ?>" />
		<link rel="self" type="application/atom+xml" href="<?php echo LOCATION . BASE . ADMIN; ?>atom.php" />
		<id>tag:<?php echo DOMAIN; ?>,2010:/</id>
	
		<entry>
			<title type="text">Daily report (<?php echo date('l, F j', $yesterday) ?>)</title>
			<link href="{define:Location}{define:Base}{define:Admin}statistics{define:URL_CAP}" />
			<id>{define:LOCATION}{define:BASE}{define:ADMIN}statistics{define:URL_CAP}#<?php echo $yesterday; ?></id>
			<updated><?php echo date('c', $yesterday); ?></updated>
			<published><?php echo date('c', $yesterday); ?></published>
			<content type="xhtml">
				<div xmlns="http://www.w3.org/1999/xhtml">
					<?php echo $h_visitors; ?> visitors, <?php echo $h_views; ?> views
				</div>
			</content>
		</entry>
	
		<?php echo $comment_entries; ?>
	
		<?php echo $trackback_entries; ?>

	</feed>
	
	<?php
	
	$xml = ob_get_flush();
	$cache->save($xml);	
}

?>