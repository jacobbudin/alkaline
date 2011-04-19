<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalineapp.com/
*/

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;

// Get statistics

$then = strtotime('-6 days');

$hourly = new Stat($then);
$hourly->getDaily();

$cumulative = array();

foreach($hourly->stats as $stat){
	$cumulative[] = $stat['stat_views'] + $stat['stat_visitors'];
}

$h_views = number_format($hourly->views);
$h_visitors = number_format($hourly->visitors);

define('TITLE', 'Alkaline');
require_once(PATH . ADMIN . 'mobile/includes/header.php');

?>

<div id="home">
    <div class="toolbar">
        <h1>Alkaline</h1>
		<a href="#info" class="button leftButton flip">About</a>
		<a href="#add" class="button slideup">Upload</a>
    </div>
	<div class="stats">
		<?php echo implode(',', $cumulative); ?>
	</div>
	
	<h3>This Week</h3>
	<p style="margin: 0 20px 5px;font-size:.9em;">
		<strong><?php echo $h_visitors; ?></strong> visitors, <strong><?php echo $h_views; ?></strong> views
	</p>
	
	<ul class="rounded">
		<?php
		
		$tables = $alkaline->getInfo();
		$show_tables = array('images', 'posts', 'comments');
		
		foreach($tables as $table){
			if(!in_array($table['table'], $show_tables)){ continue; }
			echo '<li class="arrow withicon"><img src="images/icons/' . $table['table'] . '.png" class="icon" /> <a href="#' . $table['table'] . '">' . ucwords($table['display']) . '</a> <small class="counter">' . number_format($table['count']) . '</small></li>';
		}
		
		?>
	</ul>
	
	<ul class="rounded">
		<li class="arrow withicon"><img src="images/icons/settings.png" class="icon" /> <a href="#settings">Settings</a></li>
	</ul>
	
	<ul class="rounded">
        <li class="forward withicon"><img src="images/icons/dashboard.png" class="icon" /> <a href="<?php echo BASE . ADMIN; ?>" target="_blank">Dashboard</a></li>
		<?php
		$title = $alkaline->returnConf('web_title');
		?>
        <li class="forward withicon"><img src="images/icons/home.png" class="icon" /> <a href="<?php echo BASE; ?>" target="_blank"><?php echo (!empty($title) ? $title : 'Web site'); ?></a></li>
    </ul>
</div>

<div id="images">
    <div class="toolbar">
        <h1>Images</h1>
		<a href="" class="button back">Back</a>
        <a href="#add" class="button slideup">Upload</a>
    </div>
	<ul class="grid">
		<?php
		
		$image_ids = new Find('images');
		$image_ids->clearMemory();
		$image_ids->page(null, 60);
		$image_ids->find();

		$images = new Image($image_ids);
		$images->getSizes('square');
		$images->hook();
		
		foreach($images->images as $image){
			?>
			<li><a href="#image">
				<img src="<?php echo $image['image_src_square']; ?>" alt="" title="<?php echo $image['image_title']; ?>" height="67" width="67" />
			</a></li>
			<?php
		}
		
		?>
	</ul>
</div>

<div id="image">
	<div class="toolbar">
        <h1>Image</h1>
		<a href="" class="button back">Back</a>
    </div>
	<ul class="edit rounded">
		<li><input type="text" name="image_title" placeholder="Title" id="image_title" /></li>
		<li><textarea placeholder="Description" name="image_description_raw" id="image_description_raw"></textarea></li>
	</ul>
	<h2>Options</h2>
	<ul class="edit rounded">
		<li><input type="text" name="image_location" placeholder="Location" id="image_location" /></li>
		<li>Publish <span class="toggle"><input type="checkbox" /></span></li>
	</ul>
	<a style="margin:0 10px;color:rgba(0,0,0,.9)" href="#" class="submit whiteButton">Save changes</a>
</div>

<div id="info">
    <div class="toolbar">
        <h1>About</h1>
        <a href="#home" class="cancel">Cancel</a>
    </div>
	<h2 style="text-align:center;">Alkaline</h2>
	<p style="margin: 0 20px 5px;font-size:.9em;text-align:center;">
		Copyright &#0169; 2010-2011 by <a href="http://www.budinltd.com/">Budin Ltd.</a> All rights reserved.
	</p>
	
	<h2 style="text-align:center;">Icons</h2>
	<p style="margin: 0 20px 5px;font-size:.9em;text-align:center;">
		Licensed under Creative Commons Attribution 3.0 by <a href="http://glyphish.com/">Joseph Wain</a>.
	</p>
	<ul class="individual">
		<li><a href="http://www.alkalineapp.com/">Web site</a></li>
		<li><a href="mailto:support@alkalineapp.com">Email</a></li>
	</ul>
</div>

<div id="posts">
    <div class="toolbar">
        <h1>Posts</h1>
		<a href="" class="button back">Back</a>
        <a href="#write" class="button slideup">New post</a>
    </div>
	<ul class="plastic">
		<?php
		
		$post_ids = new Find('posts');
		$post_ids->page(null, 30);
		$post_ids->sort('post_modified', 'DESC');
		$post_ids->find();
		
		$posts = new Post($post_ids);
		$posts->hook();
		
		foreach($posts->posts as $post){
			echo '<li class="arrow"><a href="#post" class="post short" title="' . $post['post_id'] . '">' . $post['post_title'] . ' <div class="rightnote">' . ucwords($alkaline->formatRelTime($post['post_modified'])) . '</div></a></li>';
		}
		
		?>
	</ul>
</div>

<div id="post">
	<div class="toolbar">
        <h1>Post</h1>
		<a href="" class="button back">Back</a>
    </div>
	<ul class="edit rounded">
		<li><input type="text" name="post_title" placeholder="Title" id="post_title" /></li>
		<li><textarea placeholder="Text" name="post_text_raw" id="post_text_raw" ></textarea></li>
	</ul>
	<h2>Options</h2>
	<ul class="edit rounded">
		<li><input type="text" name="post_category" placeholder="Category" id="post_category" /></li>
		<li>Publish <span class="toggle"><input type="checkbox" /></span></li>
	</ul>
	<a style="margin:0 10px;color:rgba(0,0,0,.9)" href="" class="submit whiteButton">Save changes</a>
</div>

<div id="write">
	<div class="toolbar">
        <h1>New post</h1>
		<a href="" class="button back">Back</a>
    </div>
	<ul class="edit rounded">
		<li><input type="text" name="post_title" placeholder="Title" id="post_title" /></li>
		<li><textarea placeholder="Text" name="post_text_raw" id="post_text_raw" ></textarea></li>
		<li>Publish <span class="toggle"><input type="checkbox" /></span></li>
	</ul>
	<a style="margin:0 10px;color:rgba(0,0,0,.9)" href="#" class="submit whiteButton">Save changes</a>
</div>

<?php

require_once(PATH . ADMIN . 'mobile/includes/footer.php');

?>