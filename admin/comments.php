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
$orbit = new Orbit;
$user = new User;

$user->perm(true, 'editor');

if(!empty($_GET['id'])){
	$comment_id = $alkaline->findID($_GET['id']);
}

if(!empty($_GET['act'])){
	$comment_act = $_GET['act'];
	if($comment_act == 'unpublished'){
		$_REQUEST['status'] = 'unpublished';
	}
}

// SAVE CHANGES
if(!empty($_POST['comment_id'])){
	$comment_id = $alkaline->findID($_POST['comment_id']);
	if(@$_POST['comment_delete'] == 'delete'){
		$alkaline->deleteRow('comments', $comment_id);
	}
	else{
		$comment_text_raw = $_POST['comment_text_raw'];
		$comment_text = $comment_text_raw;
		
		// Configuration: comm_markup
		if(!empty($_POST['comm_markup'])){
			$comment_markup_ext = $_POST['comm_markup'];
			$comment_text = $orbit->hook('markup_' . $comment_markup_ext, $comment_text_raw, $comment_text);
		}
		elseif($alkaline->returnConf('comm_markup')){
			$comment_markup_ext = $alkaline->returnConf('comm_markup_ext');
			$comment_text = $orbit->hook('markup_' . $comment_markup_ext, $comment_text_raw, $comment_text);
		}
		else{
			$comment_markup_ext = '';
			$comment_text = nl2br($comment_text_raw);
		}
		
		
		if(@$_POST['comment_spam'] == 'spam'){
			$comment_status = -1;
		}
		else{
			$comment_status = 1;
		}
		
		$fields = array('comment_text_raw' => $alkaline->makeUnicode($comment_text_raw),
			'comment_text' => $alkaline->makeUnicode($comment_text),
			'comment_status' => $comment_status);
		$alkaline->updateRow($fields, 'comments', $comment_id);
	}
	unset($comment_id);
}

// Configuration: comm_enabled
if(!$alkaline->returnConf('comm_enabled')){
	$alkaline->addNote('New comments have been disabled. You can enabled comments in your <a href="' . BASE . ADMIN . 'configuration' . URL_CAP . '">configuration</a>.', 'notice');
}

define('TAB', 'comments');

// GET COMMENTS TO VIEW OR PILE TO EDIT
if(empty($comment_id)){
	$comments = new Comment();
	$comments->page(null);
	$comments->fetch();
	$comments->formatTime();
	$comments->comments = $alkaline->stripTags($comments->comments);
	
	$image_ids = $comments->image_ids;
	
	$images = new Image($image_ids);
	$images->getSizes('square');
	
	define('TITLE', 'Alkaline Comments');
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<h1>Comments (<?php echo $comments->comment_count; ?>)</h1>
	
	<form action="<?php echo BASE . ADMIN; ?>comments<?php echo URL_ACT; ?>search<?php echo URL_RW; ?>" method="post">
		<p style="margin-bottom: 0;">
			<input type="search" name="q" style="width: 30em; margin-left: 0;" results="10" /> <input type="submit" value="Search" />
		</p>

		<p>
			<span class="switch">&#9656;</span> <a href="#" class="show" style="line-height: 2.5em;">Show options</a>
		</p>
		
		<div class="reveal">
			<table>
				<tr>
					<td class="right middle"><label for="published">Publication status:</label></td>
					<td class="quiet">
						<select id="status" name="status">
							<option value="">All</option>
							<option value="published">Published</option>
							<option value="unpublished">Unpublished</option>
							<option value="spam">Spam</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="right middle"><label>Date created:</label></td>
					<td class="quiet">
						between <input type="text" class="date" name="created_begin" style="width: 10em;" />
						and <input type="text" class="date" name="created_end" style="width: 10em;" />
					</td>
				</tr>
			</table>
		</div>
	</form>
	
	<div class="span-24 last">	
		<?php
		$i = 1;
		foreach($comments->comments as $comment){
			if($i == 3){ $last = 'last'; $i = 0; }
			else{ $last = ''; $i++; }
			
			echo '<div class="span-8 append-bottom ' . $last . '">';
			echo '<a href="' . BASE . ADMIN . 'comments' . URL_ID . $comment['comment_id'] . URL_RW . '" class="comment_mini">';
			$key = array_search($comment['image_id'], $image_ids);
			if(is_int($key)){
				echo '<img src="' . $images->images[$key]['image_src_square'] . '" title="' . $images->images[$key]['image_title'] . '" class="frame_mini" />';
			}
			if(!empty($comment['comment_author_name'])){
				echo '<strong>' . $comment['comment_author_name'] . '</strong>: ';
			}
			echo '&#8220;' . $comment['comment_text'] . '&#8221;';
			echo '</a></div>';
		}
	
		?>
	</div>
	
	<?php
	
	if($comments->page_count > 1){
		?>
		<p>
			<?php
			if(!empty($comments->page_previous)){
				for($i = 1; $i <= $comments->page_previous; ++$i){
					echo '<a href="' . BASE . ADMIN . 'comments' . URL_PAGE . $i . URL_RW . '" class="page_no">' . number_format($i) . '</a>';
				}
			}
			?>
			<span class="page_no">Page <?php echo $comments->page; ?> of <?php echo $comments->page_count; ?></span>
			<?php
			if(!empty($comments->page_next)){
				for($i = $comments->page_next; $i <= $comments->page_count; ++$i){
					echo '<a href="' . BASE . ADMIN . 'comments' . URL_PAGE . $i . URL_RW . '" class="page_no">' . number_format($i) . '</a>';
				}
			}
			?>
		</p>
		<?php
	}
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}
else{
	$comment = $alkaline->getRow('comments', $comment_id);
	$comment = $alkaline->makeHTMLSafe($comment);
	
	define('TITLE', 'Alkaline Comment');
	require_once(PATH . ADMIN . 'includes/header.php');
	
	?>
	
	<?php if($comment['image_id'] != 0){ ?>
		<div class="actions">
			<a href="<?php echo BASE . ADMIN . 'images' . URL_ID . $comment['image_id'] . URL_RW; ?>">View image</a>
			<a href="<?php echo BASE . 'image' . URL_ID . $comment['image_id'] . URL_RW; ?>">Go to image</a>
		</div>
	<?php } if($comment['post_id'] != 0){ ?>
		<div class="actions">
			<a href="<?php echo BASE . ADMIN . 'posts' . URL_ID . $comment['post_id'] . URL_RW; ?>">View post</a>
			<a href="<?php echo BASE . 'post' . URL_ID . $comment['post_id'] . URL_RW; ?>">Go to post</a>
		</div>
	<?php } ?>
	
	<h1>Comment</h1>

	<form action="<?php echo BASE . ADMIN; ?>comments<?php echo URL_CAP; ?>" method="post">
		<table>
			<tr>
				<td class="right"><label for="comment_text">Author:</label></td>
				<td>
					<?php
					
					if(!empty($comment['comment_author_name'])){
						echo $comment['comment_author_name'];
					}
					else{
						echo '<em>(Unsigned)</em>';
					}
					
					?>
				</td>
			</tr>
			<?php
			if(!empty($comment['comment_author_email'])){
				echo '<tr><td class="right"><label>Email:</label></td><td><a href="mailto:' . $comment['comment_author_email'] . '">' . $comment['comment_author_email'] . '</a></td></tr>';
			}
			?>
			<?php		
			if(!empty($comment['comment_author_uri'])){
				echo '<tr><td class="right"><label>Web site:</label></td><td><a href="' . $comment['comment_author_uri'] . '">' . $alkaline->fitString($alkaline->minimizeURL($comment['comment_author_uri']), 100) . '</a></td></tr>';
			}
			
			?>
			<tr>
				<td class="right pad"><label for="post_text_raw">Text:</label></td>
				<td><textarea id="comment_text_raw" name="comment_text_raw" style="height: 300px; font-size: 1.1em; line-height: 1.5em;"><?php echo @$comment['comment_text_raw']; ?></textarea></td>
			</tr>
			<tr>
				<td class="right"><label for="comment_ip_address">IP address:</label></td>
				<td><?php echo $comment['comment_author_ip']; ?></td>
			</tr>
			<tr>
				<td class="right"><input type="checkbox" id="comment_spam" name="comment_spam" value="spam" <?php if($comment['comment_status'] == -1){ echo 'checked="checked"'; } ?>/></td>
				<td><strong><label for="comment_spam">Mark this comment as spam.</label></strong></td>
			</tr>
			<tr>
				<td class="right"><input type="checkbox" id="comment_delete" name="comment_delete" value="delete" /></td>
				<td><strong><label for="comment_delete">Delete this comment.</label></strong> This action cannot be undone.</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>" /><input type="hidden" id="comm_markup" name="comm_markup" value="<?php echo $post['comm_markup']; ?>" /><input type="submit" value="<?php echo (($comment['comment_status'] == 0) ? 'Publish' : 'Save changes'); ?>" /> or <a href="<?php echo $alkaline->back(); ?>">cancel</a></td>
			</tr>
		</table>
	</form>

	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}

?>