<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$comment_id = @$alkaline->findID($_GET['id']);

// SAVE CHANGES
if(!empty($_POST['comment_id'])){
	$comment_id = $alkaline->findID($_POST['comment_id']);
	if(@$_POST['comment_delete'] == 'delete'){
		$alkaline->deleteRow('comments', $comment_id);
	}
	else{
		if($_POST['comment_spam'] == 'spam'){
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

// GET PILES TO VIEW OR PILE TO EDIT
if(empty($comment_id)){

	$comments = $alkaline->getTable('comments', null, null, null, 'comment_created DESC');
	$comment_count = @count($comments);
	
	$photo_ids = array();
	
	foreach($comments as $comment){
		$photo_ids[] = $comment['photo_id'];
	}
	
	$photo_ids = array_unique($photo_ids);
	
	$photos = new Photo($photo_ids);
	$photos->getImgUrl('square');
	
	define('TITLE', 'Alkaline Comments');
	require_once(PATH . ADMIN . 'includes/header.php');
	require_once(PATH . ADMIN . 'includes/features.php');

	?>
	
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
				$key = array_search($comment['photo_id'], $photos->photo_ids);
				if(is_int($key)){
					echo '<img src="' . $photos->photos[$key]['photo_src_square'] . '" class="frame" />';
				}
				echo '</td>';
				echo '<td><strong><a href="' . BASE . ADMIN . 'comments/' . $comment['comment_id'] . '">';
				if(!empty($comment['comment_author_name'])){
					echo $comment['comment_author_name'];
				}
				else{
					echo '<em>(Unsigned)</em>';
				}
				echo '</a></strong> wrote &#8220;' . $alkaline->fitString($comment['comment_text'], 225) . '&#8221;</td>';
				echo '<td>' . $alkaline->formatTime($comment['comment_created']) . '</td>';
			echo '</tr>';
		}
		
		?>
	</table>
	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}
else{
	
	$comments = $alkaline->getTable('comments', $comment_id);
	$comment = $comments[0];

	if(!empty($comment['pile_title'])){	
		define('TITLE', 'Alkaline Comment: &#8220;' . $comment['pile_title']  . '&#8221;');
	}
	else{
		define('TITLE', 'Alkaline Comment');
	}
	require_once(PATH . ADMIN . 'includes/header.php');
	require_once(PATH . ADMIN . 'includes/features.php');
	
	?>
	
			<div style="float: right; margin: 1em 0;"><a href="<?php echo BASE . ADMIN; ?>search/comments/<?php echo $comment['comment_id']; ?>/" class="nu"><span class="button">&#0187;</span>View photo</a></div>
	
	<h1>Comment</h1>

	<form action="<?php echo BASE . ADMIN; ?>comments/" method="post">
		<table>
			<tr>
				<td class="right"><label for="comment_text">Author:</label></td>
				<td><?php echo (!empty($comment['comment_author_email'])) ? '<a href="mailto:' . $comment['comment_author_email'] . '">': ''; echo (!empty($comment['comment_author_name'])) ? $comment['comment_author_name'] : '<em>(Unsigned)</em>'; echo (!empty($comment['comment_author_email'])) ? '</a>': ''; ?></td>
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
				<td><input type="hidden" name="comment_id" value="<?php echo $comment['comment_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo BASE . ADMIN; ?>comments/">cancel</a></td>
			</tr>
		</table>
	</form>

	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}

?>