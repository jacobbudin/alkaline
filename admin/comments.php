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
	if($comment_act == 'search'){
		Find::clearMemory();
		
		$comment_ids = new Find('comments');
		$comment_ids->find();
		$comment_ids->saveMemory();
		
		header('Location: ' . LOCATION . BASE . ADMIN . 'comments' . URL_ACT . 'results' . URL_RW);
		exit();
	}
}

// SAVE CHANGES
if(!empty($_POST['comment_id'])){
	$comment_id = $alkaline->findID($_POST['comment_id']);
	
	$comment = new Comment($comment_id);
	
	if(!empty($_POST['post_response_raw'])){
		$comment_text_raw = $_POST['post_response_raw'];
		
		if(!empty($_POST['image_id'])){
			$id = $alkaline->findID($_POST['image_id']);
			$id_type = 'image_id';
		}
		elseif(!empty($_POST['post_id'])){
			$id = $alkaline->findID($_POST['post_id']);
			$id_type = 'post_id';
		}
		
		// Configuration: comm_markup
		if($alkaline->returnConf('web_markup')){
			$comm_markup_ext = $alkaline->returnConf('web_markup_ext');
			$comment_text = $orbit->hook('markup_' . $comm_markup_ext, $comment_text_raw, $comment_text_raw);
		}
		else{
			$comm_markup_ext = '';
			$comment_text = $alkaline->nl2br($comment_text_raw);
		}
		
		$fields = array($id_type => $id,
			'comment_response' => $comment_id,
			'comment_status' => 1,
			'comment_text' => $alkaline->makeUnicode($comment_text),
			'comment_text_raw' => $alkaline->makeUnicode($comment_text_raw),
			'comment_markup' => $comm_markup_ext,
			'user_id' => $user->user['user_id'],
			'comment_author_name' => $user->user['user_name'],
			'comment_author_uri' => $user->user['user_uri'],
			'comment_author_email' => $user->user['user_email'],
			'comment_author_ip' => $_SERVER['REMOTE_ADDR']);
		
		$fields = $orbit->hook('comment_add', $fields, $fields);
		
		if(!$comment_id = $alkaline->addRow($fields, 'comments')){
			$alkaline->addNote('The response could not be posted.', 'error');
		}
		else{
			// Update comment counts
			if($id_type == 'image_id'){
				$alkaline->updateCount('comments', 'images', 'image_comment_count', $id);
			}
			elseif($id_type == 'post_id'){
				$alkaline->updateCount('comments', 'posts', 'post_comment_count', $id);
			}
			
			$alkaline->addNote('The response was successfully posted.', 'success');
		}
	}
	
	if(isset($_POST['comment_delete']) and ($_POST['comment_delete'] == 'delete')){
		if($comment->delete()){
			$alkaline->addNote('The comment has been deleted.', 'success');
		}
		
		// Update comment counts
		if(!empty($_POST['image_id'])){
			$id = $alkaline->findID($_POST['image_id']);
			$id_type = 'image_id';
		}
		elseif(!empty($_POST['post_id'])){
			$id = $alkaline->findID($_POST['post_id']);
			$id_type = 'post_id';
		}
		
		if($id_type == 'image_id'){
			$alkaline->updateCount('comments', 'images', 'image_comment_count', $id);
		}
		elseif($id_type == 'post_id'){
			$alkaline->updateCount('comments', 'posts', 'post_comment_count', $id);
		}
	}
	elseif(isset($_POST['comment_recover']) and ($_POST['comment_recover'] == 'recover')){
		if($comment->recover()){
			$alkaline->addNote('The comment has been recovered.', 'success');
		}
		
		// Update comment counts
		if(!empty($_POST['image_id'])){
			$id = $alkaline->findID($_POST['image_id']);
			$id_type = 'image_id';
		}
		elseif(!empty($_POST['post_id'])){
			$id = $alkaline->findID($_POST['post_id']);
			$id_type = 'post_id';
		}
		
		if($id_type == 'image_id'){
			$alkaline->updateCount('comments', 'images', 'image_comment_count', $id);
		}
		elseif($id_type == 'post_id'){
			$alkaline->updateCount('comments', 'posts', 'post_comment_count', $id);
		}
	}
	elseif(!empty($_POST['comment_quick'])){
		if($_POST['comment_quick'] == 'go_image'){
			header('Location: ' . BASE . ADMIN . 'image' . URL_ID . $comment->comments[0]['image_id'] . URL_RW);
			exit();
		}
		elseif($_POST['comment_quick'] == 'go_post'){
			header('Location: ' . BASE . ADMIN . 'posts' . URL_ID . $comment->comments[0]['post_id'] . URL_RW);
			exit();
		}
		elseif($_POST['comment_quick'] == 'publish'){
			$fields = array('comment_status' => 1);
			$comment->updateFields($fields);
		}
		elseif($_POST['comment_quick'] == 'unpublish'){
			$fields = array('comment_status' => 0);
			$comment->updateFields($fields);
		}
		elseif($_POST['comment_quick'] == 'spam'){
			$fields = array('comment_status' => -1);
			$comment->updateFields($fields);
		}
		elseif($_POST['comment_quick'] == 'delete'){
			if($comment->delete()){
				$alkaline->addNote('The comment has been deleted.', 'success');
			}

			// Update comment counts
			if(!empty($_POST['image_id'])){
				$id = $alkaline->findID($_POST['image_id']);
				$id_type = 'image_id';
			}
			elseif(!empty($_POST['post_id'])){
				$id = $alkaline->findID($_POST['post_id']);
				$id_type = 'post_id';
			}

			if($id_type == 'image_id'){
				$alkaline->updateCount('comments', 'images', 'image_comment_count', $id);
			}
			elseif($id_type == 'post_id'){
				$alkaline->updateCount('comments', 'posts', 'post_comment_count', $id);
			}
		}
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
			$comment_text = $alkaline->nl2br($comment_text_raw);
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
		
		$comment->updateFields($fields);
	}
	
	if(!empty($_REQUEST['go'])){
		$comment_ids = new Find('comments');
		$comment_ids->memory();
		$comment_ids->with($comment_id);
		$comment_ids->offset(1);
		$comment_ids->page(null, 1);
		$comment_ids->find();
		
		if($_REQUEST['go'] == 'next'){
			$_SESSION['alkaline']['go'] = 'next';
			if(!empty($comment_ids->ids_after[0])){
				$comment_id = $comment_ids->ids_after[0];
			}
			else{
				unset($_SESSION['alkaline']['go']);
				unset($comment_id);
			}
		}
		else{
			$_SESSION['alkaline']['go'] = 'previous';
			if(!empty($comment_ids->ids_before[0])){
	 			$comment_id = $comment_ids->ids_before[0];
			}
			else{
				unset($_SESSION['alkaline']['go']);
				unset($comment_id);
			}
		}
	}
	else{
		unset($_SESSION['alkaline']['go']);
		unset($comment_id);
	}
}

define('TAB', 'comments');

// GET COMMENTS TO VIEW OR PILE TO EDIT
if(empty($comment_id)){
	$comment_ids = new Find('comments');
	$comment_ids->page(null, 50);
	if(isset($comment_act) and ($comment_act == 'results')){ $comment_ids->memory(); }
	$comment_ids->find();
	
	$comments = new Comment($comment_ids);
	$comments->formatTime();
	$comments->comments = $alkaline->stripTags($comments->comments);
	
	$image_ids = $comments->image_ids;
	
	$images = new Image($image_ids);
	$images->getSizes('square');
	
	define('TITLE', 'Alkaline Comments');
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/comments.png" alt="" /> Comments (<?php echo $comments->comment_count; ?>)</h1>
	
	<form action="<?php echo BASE . ADMIN . 'comments' . URL_ACT . 'search' . URL_RW; ?>" method="post">
		<p style="margin-bottom: 0;">
			<input type="search" name="q" style="width: 30em; margin-left: 0;" results="10" /> <input type="submit" value="Search" />
		</p>

		<p>
			<span class="switch">&#9656;</span> <a href="#" class="show" style="line-height: 2.5em;">Show options and presets</a>
		</p>
		
		<div class="reveal span-24 last">
			<div class="span-15 append-1">
				<table>
					<tr>
						<td class="right middle"><label for="status">Status:</label></td>
						<td class="quiet">
							<select id="status" name="status">
								<option value="">All</option>
								<option value="1">Live</option>
								<option value="0">Pending</option>
								<option value="-1">Spam</option>
							</select>
						</td>
					</tr>
					<tr>
						<td class="right middle"><label for="response">Response:</label></td>
						<td class="quiet">
							<select id="response" name="response">
								<option value="">All</option>
								<option value="true">Yes</option>
								<option value="false">No</option>
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
			<div class="span-8 last">
				<h3>Presets</h3>
				
				<ul>
					<li><a href="<?php echo BASE . ADMIN . 'comments' . URL_ACT . 'live' . URL_RW; ?>">Live comments</a></li>
					<li><a href="<?php echo BASE . ADMIN . 'comments' . URL_ACT . 'pending' . URL_RW; ?>">Pending comments</a></li>
					<li><a href="<?php echo BASE . ADMIN . 'comments' . URL_ACT . 'spam' . URL_RW; ?>">Spam comments</a></li>
				</ul>
			</div>
		</div>
	</form>
	
	<?php
	// Configuration: comm_enabled
	if(!$alkaline->returnConf('comm_enabled')){
		?>
		<p class="notice">New comments have been disabled. You can enabled comments in your <a href="/~jacobbudin/Alkaline/admin/configuration.php">configuration</a>.</p><br />
		<?php
	}
	?>
			
	<table>
		<tr>
			<th style="width:25px"></th>
			<th>Comment</th>
			<th></th>
			<th>Created</th>
		</tr>
		<?php
	
		foreach($comments->comments as $comment){
			echo '<tr class="ro">';
			echo '<td>';
			$key = array_search($comment['image_id'], $image_ids);
			if(is_int($key)){
				echo '<img src="' . $images->images[$key]['image_src_square'] . '" title="' . $images->images[$key]['image_title'] . '" class="frame_mini" />';
			}
			echo '</td>';
			echo '<td class="status' . $comment['comment_status'] . '">';
			echo '<div class="actions"><button class="tip" title=\'<form action="" method="post"><select name="comment_quick">';
			if($comment['comment_status'] == 0){
				echo '<option value="publish">Publish</option>';
				echo '<option value="spam">Mark as spam</option>';
				echo '<option value="delete">Delete</option>';
			}
			elseif($comment['comment_status'] == 1){
				echo '<option value="unpublish">Unpublish</option>';
				echo '<option value="delete">Delete</option>';
			}
			elseif($comment['comment_status'] == -1){
				echo '<option value="publish">Publish</option>';
				echo '<option value="delete">Delete</option>';
			}
			if(is_int($key)){
				echo '<option value="go_image">Go to image</option>';
			}
			else{
				echo '<option value="go_post">Go to post</option>';
			}
			echo '</select> <input type="hidden" name="comment_id" value="' . $comment['comment_id'] . '" /><input type="submit" value="Do" /></form>\'></button></div>';
			echo '<strong><a href="' . BASE . ADMIN . 'comments' . URL_ID . $comment['comment_id'] . URL_RW . '" class="large tip" title="' . $alkaline->makeHTMLSafe($alkaline->fitStringByWord(strip_tags($comment['comment_text']), 150)) . '">';
			echo $alkaline->fitStringByWord(strip_tags($comment['comment_text']), 50);
			echo '</a></strong><br /><span class="quiet">';
			
			if(!empty($comment['user_id'])){
				echo '<img src="' . BASE . ADMIN . 'images/icons/user.png" alt="" /> <a href="' . BASE . ADMIN . 'comments' . URL_ACT . 'user' . URL_AID . $comment['user_id'] . URL_RW . '" class="nu">' . $comment['comment_author_name'] . '</a>';
			}
			elseif(!empty($comment['comment_author_name'])){
				echo '<a href="' . BASE . ADMIN . 'comments' . URL_CAP . '?q=' . urlencode($comment['comment_author_name']) . '" class="nu">' . $comment['comment_author_name'] . '</a>';
			}
			else{
				'<em>Anonymous</em>';
			}
		
			if(!empty($comment['comment_author_ip']) and empty($comment['user_id'])){
				echo ' (<a href="' . BASE . ADMIN . 'comments' . URL_CAP . '?q=' . urlencode($comment['comment_author_ip']) . '" class="nu">' . $comment['comment_author_ip'] . '</a>)';
			}
			echo '</span></td><td></td><td>' . $comment['comment_created_format'] . '</td></tr>';
		
		}
	
		?>
	</table>
	
	<?php
	
	if($comment_ids->page_count > 1){
		?>
		<p>
			<?php
			if(!empty($comment_ids->page_previous)){
				for($i = 1; $i <= $comment_ids->page_previous; ++$i){
					$page_uri = 'page_' . $i . '_uri';
					echo '<a href="' . $comment_ids->$page_uri  .'" class="page_no">' . number_format($i) . '</a>';
				}
			}
			?>
			<span class="page_no">Page <?php echo $comment_ids->page; ?> of <?php echo $comment_ids->page_count; ?></span>
			<?php
			if(!empty($comment_ids->page_next)){
				for($i = $comment_ids->page_next; $i <= $comment_ids->page_count; ++$i){
					$page_uri = 'page_' . $i . '_uri';
					echo '<a href="' . $comment_ids->$page_uri  .'" class="page_no">' . number_format($i) . '</a>';
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
	
	$email_action = '';
	
	if(!empty($comment['comment_author_email'])){
		$email_action = '<a href="mailto:' . $comment['comment_author_email'] . '"><button>Email author</button></a>';
	}
	
	?>
	
	<?php if($comment['image_id'] != 0){ ?>
		<div class="actions">
			<?php echo $email_action; ?>
			<a href="<?php echo BASE . ADMIN . 'images' . URL_ID . $comment['image_id'] . URL_RW; ?>"><button>Go to image</button></a>
			<a href="<?php echo BASE . 'image' . URL_ID . $comment['image_id'] . URL_RW; ?>"><button>Launch image</button></a>
		</div>
	<?php } if($comment['post_id'] != 0){ ?>
		<div class="actions">
			<?php echo $email_action; ?>
			<a href="<?php echo BASE . ADMIN . 'posts' . URL_ID . $comment['post_id'] . URL_RW; ?>"><button>Go to post</button></a>
			<a href="<?php echo BASE . 'post' . URL_ID . $comment['post_id'] . URL_RW; ?>"><button>Launch post</button></a>
		</div>
	<?php } ?>
	
	<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/comments.png" alt="" /> Comment</h1>

	<form action="<?php echo BASE . ADMIN . 'comments' . URL_CAP; ?>" method="post">
		<div class="span-24 last">
			<div class="span-15 append-1">
				<textarea id="comment_text_raw" name="comment_text_raw" placeholder="Text" style="height: 300px;" class="<?php if($user->returnPref('text_code')){ echo $user->returnPref('text_code_class'); } ?>"><?php echo @$comment['comment_text_raw']; ?></textarea>
				
				<p>
					<span class="switch">&#9656;</span> <a href="#" class="show">Post response</a> <span class="quiet">(response will become new comment)</span>
				</p>
				<div class="reveal">
					<textarea id="post_response_raw" name="post_response_raw" style="height: 150px;" class="<?php if($user->returnPref('text_code')){ echo $user->returnPref('text_code_class'); } ?>"></textarea>
				</div>
			</div>
			<div class="span-8 last">
				<p>
					<label for="comment_text">Author:</label><br />
					<span class="quiet">
					<?php

					if(!empty($comment['comment_author_name'])){
						if($comment['user_id']){
							echo '<img src="' . BASE . ADMIN . 'images/icons/user.png" alt="" /> <a href="' . BASE . ADMIN . 'comments' . URL_ACT . 'user' . URL_AID . $comment['user_id'] . URL_RW . '" class="nu"> ';
						}
						echo '<a href="">' . $comment['comment_author_name'] . '</a>';
					}
					else{
						echo '<em>Anonymous</em>';
					}

					?>
					</span>
				</p>
				<?php
				if(!empty($comment['comment_author_email'])){
					?>
					<p>
						<label>Email:</label><br />
						<span class="quiet">
							<a href="mailto:<?php echo $comment['comment_author_email']; ?>"><?php echo $comment['comment_author_email']; ?></a>
						</span>
					</p>
					<?php
				}
				?>
				<?php		
				if(!empty($comment['comment_author_uri'])){
					?>
					<p>
						<label>Web site:</label><br />
						<span class="quiet">
							<a href="<?php echo $comment['comment_author_uri']; ?>"><?php echo $alkaline->fitString($alkaline->minimizeURL($comment['comment_author_uri']), 100); ?></a>
						</span>
					</p>
					<?php
				}

				?>
				<p>
					<label for="comment_ip_address">IP address:</label><br />
					<span class="quiet"><?php echo $comment['comment_author_ip']; ?></span>
				</p>
				
				<hr />
				
				<table>
					<tr>
						<td class="right" style="width: 5%"><input type="checkbox" id="comment_spam" name="comment_spam" value="spam" <?php if($comment['comment_status'] == -1){ echo 'checked="checked"'; } ?>/></td>
						<td><strong><label for="comment_spam">Mark this comment as spam.</label></strong></td>
					</tr>
					<?php if(empty($comment['comment_deleted'])){ ?>
					<tr>
						<td class="right" style="width: 5%"><input type="checkbox" id="comment_delete" name="comment_delete" value="delete" /></td>
						<td>
							<strong><label for="comment_delete">Delete this comment.</label></strong>
						</td>
					</tr>
					<?php } else{ ?>
					<tr>
						<td class="right" style="width: 5%"><input type="checkbox" id="comment_recover" name="comment_recover" value="recover" /></td>
						<td>
							<strong><label for="comment_recover">Recover this comment.</label></strong>
						</td>
					</tr>
					<?php } ?>
				</table>
			</div>
		</div>
		<p>
			<input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>" />
			<input type="hidden" name="image_id" value="<?php echo $comment['image_id']; ?>" />
			<input type="hidden" name="post_id" value="<?php echo $comment['post_id']; ?>" />
			<input type="hidden" id="comm_markup" name="comm_markup" value="<?php echo $comment['comment_markup']; ?>" />
			<input type="submit" value="<?php echo (($comment['comment_status'] == 0) ? 'Publish' : 'Save changes'); ?>" />
			and
			<select name="go">
				<option value="">return to previous screen</option>
				<option value="next" <?php echo $alkaline->readForm($_SESSION['alkaline'], 'go', 'next'); ?>>go to next comment</option>
				<option value="previous" <?php echo $alkaline->readForm($_SESSION['alkaline'], 'go', 'previous'); ?>>go to previous comment</option>
			</select>
			or <a href="<?php echo $alkaline->back(); ?>">cancel</a>
		</p>
	</form>

	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}

?>