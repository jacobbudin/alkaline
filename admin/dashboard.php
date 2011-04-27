<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalineapp.com/
*/

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$alkaline->setCallback();

// Vitals
$stats = new Stat(strtotime('-30 days'));
$stats->getDaily();

$views = array();

foreach($stats->stats as $stat){
	$views[] = array($stat['stat_ts_js'], $stat['stat_views']);
}

$views = json_encode($views);

$visitors = array();

foreach($stats->stats as $stat){
	$visitors[] = array($stat['stat_ts_js'], $stat['stat_visitors']);
}

$visitors = json_encode($visitors);

define('TAB', 'dashboard');
define('TITLE', 'Alkaline Dashboard');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div class="actions">
	<a href="<?php echo BASE . ADMIN . 'upload' . URL_CAP; ?>"><button>Upload file</button></a>
	<a href="<?php echo BASE . ADMIN . 'posts' . URL_ACT . 'add' . URL_RW; ?>"><button>Write post</button></a>
</div>

<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/dashboard.png" alt="" /> Dashboard</h1>

<div class="span-24 last">
	<div class="span-17 append-1">
		<?php
		if($user->returnConf('stat_enabled') !== false){
			?>
			<div id="statistics_holder" class="statistics_holder"></div>
			<div id="statistics_views" title="<?php echo $views; ?>"></div>
			<div id="statistics_visitors" title="<?php echo $visitors; ?>"></div>
			<?php
		}
		?>
	</div>
	<div class="span-6 prepend-top last">
		<h3>Hello</h3>
		
		<p>Welcome back! <?php echo ($user->user['user_last_login']) ? 'You last logged in on:  ' . $alkaline->formatTime($user->user['user_last_login'], 'l, F j \a\t g:i a') : ''; ?></p>
		
		<h3>Census</h3>
		<table class="census">
			<?php
			$tables = $alkaline->getInfo();
			foreach($tables as $table){
				echo '<tr><td class="right">' . number_format($table['count']) . '</td><td><a href="' . BASE . ADMIN . $table['table'] . URL_CAP . '">' . $table['display'] . '</a></td></tr>';
			}
			?>
		</table>
		
		<h3>Alkaline</h3>
		<p>You are running Alkaline <?php echo Alkaline::version; ?>.</p>
	</div>
</div>

<hr />

<div class="span-24 last">
	<div class="actions">
		<a href="<?php echo BASE . ADMIN . 'atom' . URL_CAP; ?>"><button>Subscribe</button></a>
	</div>
	
	<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/timeline.png" alt="" /> Timeline</h1><br />
	

		<?php

	$timestamps = array();
	$items = array();
	$types = array();

	$comment_ids = new Find('comments');
	$comment_ids->sort('comments.comment_created', 'DESC');
	$comment_ids->page(1, 60);
	$comment_ids->find();

	$comments = new Comment($comment_ids);

	for($i=0; $i < $comments->comment_count; $i++){
		$timestamps[] = strtotime($comments->comments[$i]['comment_created']);
		$items[] = $comments->comments[$i];
		$types[] = 'comment';
	}

	$image_ids = new Find('images');
	$image_ids->sort('images.image_modified', 'DESC');
	$image_ids->page(1, 60);
	$image_ids->find();

	$images = new Image($image_ids);
	$images->getSizes('square');

	for($i=0; $i < $images->image_count; $i++){
		$timestamps[] = strtotime($images->images[$i]['image_modified']);
		$items[] = $images->images[$i];
		$types[] = 'image';
	}

	$post_ids = new Find('posts');
	$post_ids->sort('posts.post_modified', 'DESC');
	$post_ids->page(1, 60);
	$post_ids->find();

	$posts = new Post($post_ids);

	for($i=0; $i < $posts->post_count; $i++){
		$timestamps[] = strtotime($posts->posts[$i]['post_modified']);
		$items[] = $posts->posts[$i];
		$types[] = 'post';
	}

	array_multisort($timestamps, SORT_DESC, $items, $types);

	if(count($items) == 0){
		echo 'Welcome to Alkaline, starting enjoying your new Web site to populate the timeline.';
	}
	else{
		$timeline = array();
		$modified_last = '';

		for($i=0; $i < 60; $i++){
			if(!isset($types[$i])){ continue; }
	
			$type = $types[$i];
	
			$modified = $alkaline->formatRelTime($timestamps[$i]);
	
			if($modified != $modified_last){
				$timeline[$modified] = array();
				$modified_last = $modified;
			}
	
			ob_start();
	
			if($type == 'comment'){
				echo '<p><strong><a href="' . BASE . ADMIN . 'comments' . URL_ID . $items[$i]['comment_id'] . URL_RW . '" class="large tip" title="' . $alkaline->fitStringByWord(strip_tags($items[$i]['comment_text']), 150) . '">';
				echo $alkaline->fitStringByWord(strip_tags($items[$i]['comment_text']), 50);
				echo '</a></strong><br />';
				if(!empty($items[$i]['comment_author_name'])){
					echo '<span class="quiet"><a href="">' . $items[$i]['comment_author_name'] . '</a>';
				}
				else{
					'<em>Anonymous</em>';
				}

				if(!empty($items[$i]['comment_author_ip']) and empty($items[$i]['user_id'])){
					echo ' (<a href="">' . $items[$i]['comment_author_ip'] . '</a>)</span>';
				}
				elseif(!empty($items[$i]['user_id'])){
					echo ' (User)';
				}
				echo '</p>';
		
				$timeline[$modified][] = ob_get_contents();
			}
			elseif($type == 'image'){
				echo '<a href="' . BASE . ADMIN . 'image' . URL_ID . $items[$i]['image_id'] . URL_RW . '" class="nu">
					<img src="' . $items[$i]['image_src_square'] . '" alt="" title="' . $items[$i]['image_title'] . '" class="frame tip" />
				</a>';
		
				$timeline[$modified][] = ob_get_contents();
			}
			elseif($type == 'post'){
				echo '<p><strong class="large"><a href="' . BASE . ADMIN . 'posts' . URL_ID . $items[$i]['post_id'] . URL_RW . '" title="' . $alkaline->fitStringByWord(strip_tags($items[$i]['post_text']), 150) . '" class="tip">' . $items[$i]['post_title'] . '</a></strong></p>';
		
				$timeline[$modified][] = ob_get_contents();
			}
	
			ob_end_clean();
		}
	}

	echo '<table>';

	foreach($timeline as $modified => $items){
		echo '<tr><td class="right" style="width:15%;"><strong class="quiet">' . ucfirst($modified) . '</strong></td><td>' . "\n";
		foreach($items as $item){
			echo $item . "\n";
		}
		echo '</td></tr>' . "\n";
	}

	echo '</table>';

	?>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>