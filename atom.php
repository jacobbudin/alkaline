<?php

/*
// Alkaline
// Copyright (c) 2010-2012 by Budin Ltd. Some rights reserved.
// http://www.alkalineapp.com/
*/

require_once('config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline();
$alkaline->recordStat('atom');

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

if($xml = $cache->get('xml:visitor', 'xml')){
	echo $xml;
}
else{
	ob_start();

	// Gather images

	$image_ids = new Find('images');
	$image_ids->sort('images.image_published', 'DESC');
	$image_ids->page(1,10);
	$image_ids->privacy('public');
	$image_ids->published();
	$image_ids->find();

	$images = new Image($image_ids);
	$images->getSizes('medium');
	$images->formatTime('c');

	$image_entries = new Canvas('
	{block:Images}
		<entry>
			<title type="text">{if:Image_Title}{Image_Title}{else:Image_Title}(Untitled){/if:Image_Title}</title>
			<link href="{Image_URI}" />
			<id>{Image_URI}</id>
			<updated>{Image_Modified_Format}</updated>
			<published>{Image_Published_Format}</published>
			{if:Image_Description}
				<summary type="xhtml">
					<div xmlns="http://www.w3.org/1999/xhtml">
						{Image_Description}
					</div>
				</summary>
			{/if:Image_Description}
			<content type="xhtml">
				<div xmlns="http://www.w3.org/1999/xhtml">
					<a href="{Image_URI}"><img src="{define:Location}{Image_Src_Medium}" alt="" title="{Image_Title}" /></a>
				</div>
			</content>
			<link rel="enclosure" type="{Image_MIME}" href="{define:Location}{Image_Src_Medium}" />
		</entry>
	{/block:Images}');
	$image_entries->loop($images);

	// Gather posts

	$post_ids = new Find('posts');
	$post_ids->sort('posts.post_published', 'DESC');
	$post_ids->page(1,10);
	$post_ids->published();
	$post_ids->find();

	$posts = new Post($post_ids);
	$posts->formatTime('c');

	if($alkaline->returnConf('syndication_summary_only')){
		foreach($posts->posts as &$post){
			$post['post_text'] = $alkaline->fitStringByWord($post['post_text'], 400);
		}
	}

	$post_entries = new Canvas('
	{block:Posts}
		<entry>
			<title type="text">{if:Post_Title}{Post_Title}{else:Post_Title}(Untitled){/if:Post_Title}</title>
			<link href="{Post_URI}" />
			<id>{Post_URI}</id>
			<updated>{Post_Modified_Format}</updated>
			<published>{Post_Published_Format}</published>
			<content type="xhtml">
				<div xmlns="http://www.w3.org/1999/xhtml">
					{Post_Text}
				</div>
			</content>
		</entry>
	{/block:Posts}');
	$post_entries->loop($posts);

	$updated['image'] = strtotime($images->images[0]['image_published']);
	$updated['post'] = strtotime($posts->posts[0]['post_published']);

	$last_updated = 0;

	foreach($updated as $table => $time){
		if($time > $last_updated){
			$last_updated = $time;
		}
	}

	echo '<?xml version="1.0" encoding="utf-8"?>';

	?>

	<feed xmlns="http://www.w3.org/2005/Atom">

		<title><?php echo $alkaline->returnConf('web_title'); ?></title>
		<updated><?php echo date('c', $last_updated); ?></updated>
		<link href="<?php echo BASE; ?>" />
		<link rel="self" type="application/atom+xml" href="<?php echo LOCATION . BASE; ?>atom.php" />
		<id>tag:<?php echo DOMAIN; ?>,2010:/</id>
		<author>
			<name><?php echo $alkaline->returnConf('web_name'); ?></name>
		</author>

		<?php echo $image_entries; ?>

		<?php echo $post_entries; ?>

	</feed>
	
	<?php
	
	$xml = ob_get_flush();
	$cache->save($xml);
}

?>