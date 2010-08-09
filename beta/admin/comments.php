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
		$fields = array('comment_title' => $_POST['comment_title'],
			'comment_description' => $_POST['comment_description']);
		$alkaline->updateRow($fields, 'comments', $comment_id);
	}
	unset($comment_id);
}

// GET PILES TO VIEW OR PILE TO EDIT
if(empty($comment_id)){

	$comments = $alkaline->getTable('comments', null, null, null, 'comment_created DESC');
	$comment_count = @count($comments);

	define('TITLE', 'Alkaline Comments');
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	<div id="module" class="container">
		<h1>Comments</h1>
		<p>Your library contains <?php $alkaline->echoCount($comment_count, 'comment'); ?>.</p>
	</div>

	<div id="comments" class="container">
		<?php
	
		foreach($comments as $comment){
			echo '<p class="comment_block">';
				echo '<strong><a href="' . BASE . ADMIN . 'comments/' . $comment['comment_id'] . '">' . $comment['comment_text'] . '</a></strong>';
				echo '' . $alkaline->formatTime($comment['comment_created']) . '';
			echo '</p>';
		}
	
		?>
	</div>

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

	?>

	<div id="module" class="container">
		<h1>Comments</h1>
		<p>Comment #<?php echo $comment['comment_id']; ?> was written on <?php echo $alkaline->formatTime($comment['comment_created']); ?>
	</div>

	<form id="comment" class="container" action="<?php echo BASE . ADMIN; ?>comments/" method="post">
		<div style="float: right; margin: 1em 0;"><a href="<?php echo BASE . ADMIN; ?>search/comments/<?php echo $comment['comment_id']; ?>/" class="nu"><span class="button">&#0187;</span>View photos</a> &#0160; <a href="" class="nu"><span class="button">&#0187;</span>View pile</a></div>
		<table>
			<tr>
				<td class="right"><label for="comment_title">Title:</label></td>
				<td><input type="text" id="comment_title" name="comment_title" value="<?php echo $comment['comment_title']; ?>" class="title" /></td>
			</tr>
			<tr>
				<td class="right"><label for="comment_text">Text:</label></td>
				<td><textarea id="comment_text" name="comment_text"><?php echo $comment['comment_description']; ?></textarea></td>
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