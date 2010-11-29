<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$comment_id = @$alkaline->findID($_GET['id']);
$comment_act = @$_GET['act'];

// SAVE CHANGES
if(!empty($_POST['comment_id'])){
	$comment_id = $alkaline->findID($_POST['comment_id']);
	if(@$_POST['comment_delete'] == 'delete'){
		$alkaline->deleteRow('comments', $comment_id);
	}
	else{
		if(@$_POST['comment_spam'] == 'spam'){
			$comment_status = -1;
		}
		else{
			$comment_status = 1;
		}
		$fields = array('comment_status' => $comment_status);
		$alkaline->updateRow($fields, 'comments', $comment_id);
	}
	unset($comment_id);
}

// Configuration: comm_enabled
if(!$alkaline->returnConf('comm_enabled')){
	$alkaline->addNotification('New comments have been disabled.', 'notice');
}

define('TAB', 'features');

// GET PILES TO VIEW OR PILE TO EDIT
if(empty($comment_id)){
	$comments = new Comment();
	
	if($comment_act == 'search'){
		if(!empty($_POST['search'])){
			$comments->search($_POST['search']);
		}
		if(!empty($_POST['created_begin']) or !empty($_POST['created_end'])){
			$comments->created($_POST['created_begin'], $_POST['created_end']);
		}
		if(!empty($_POST['status'])){
			$comments->created($_POST['status']);
		}
	}
	
	$comments->fetch();
	$comments->formatTime();
	
	$photo_ids = $comments->photo_ids;
	$comment_count = $comments->comment_count;
	$comments = $comments->comments;
	
	$photos = new Photo($photo_ids);
	$photos->getImgUrl('square');
	
	define('TITLE', 'Alkaline Comments');
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<h1>Search</h1>
	
	<form action="<?php echo BASE . ADMIN; ?>comments<?php echo URL_ACT; ?>search<?php echo URL_RW; ?>" method="post">
		<p style="margin-bottom: 0;">
			<input type="search" name="search" style="width: 30em; margin-left: 0;" results="10" /> <input type="submit" value="Search" />
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
	
	<h1>Comments (<?php echo $comment_count; ?>)</h1>
	
	<table>
		<tr>
			<th></th>
			<th>Comment</th>
			<th style="width: 30%;">Created</th>
		</tr>
		<?php

		foreach($comments as $comment){
			echo '<tr>';
				echo '<td>';
				$key = array_search($comment['photo_id'], $photo_ids);
				if(is_int($key)){
					echo '<a href="' . BASE . ADMIN . 'photo' . URL_ID . $photos->photos[$key]['photo_id'] . URL_RW . '"><img src="' . $photos->photos[$key]['photo_src_square'] . '" title="' . $photos->photos[$key]['photo_title'] . '" class="frame" /></a>';
				}
				echo '</td>';
				echo '<td><strong><a href="' . BASE . ADMIN . 'comments' . URL_ID . $comment['comment_id'] . URL_RW . '">';
				if(!empty($comment['comment_author_name'])){
					echo $comment['comment_author_name'];
				}
				else{
					echo '<em>(Unsigned)</em>';
				}
				echo '</a></strong> wrote &#8220;' . $alkaline->fitString($comment['comment_text'], 225) . '&#8221;</td>';
				echo '<td>' . $comment['comment_created'] . '</td>';
			echo '</tr>';
		}
		
		?>
	</table>
	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}
else{
	$comment = $alkaline->getRow('comments', $comment_id);
	$comment = $alkaline->makeHTMLSafe($comment);
	
	define('TITLE', 'Alkaline Comment');
	require_once(PATH . ADMIN . 'includes/header.php');
	
	?>
	
	<div class="actions"><a href="<?php echo BASE; ?>photo/<?php echo $comment['photo_id']; ?>/">Go to photo</a></div>
	
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
					
					echo '<br />';
					
					
					if(!empty($comment['comment_author_email'])){
						echo '<a href="mailto:' . $comment['comment_author_email'] . '">' . $comment['comment_author_email'] . '</a><br />';
					}
					
					if(!empty($comment['comment_author_uri'])){
						echo '<a href="' . $comment['comment_author_uri'] . '">' . $comment['comment_author_uri'] . '</a>';
					}
					
					?>
				</td>
			</tr>
			<tr>
				<td class="right"><label for="comment_text">Text:</label></td>
				<td><?php echo $comment['comment_text']; ?></td>
			</tr>
			<tr>
				<td class="right"><label for="comment_text">IP Address:</label></td>
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
				<td><input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>" /><input type="submit" value="<?php echo (($comment['comment_status'] == 0) ? 'Publish' : 'Save changes'); ?>" /> or <a href="<?php echo $alkaline->back(); ?>">cancel</a></td>
			</tr>
		</table>
	</form>

	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}

?>