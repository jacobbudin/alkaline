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

$user->perm(true, 'posts');

// GET POST
if(!empty($_GET['id'])){
	$post_id = $alkaline->findID($_GET['id']);
}

if(!empty($_GET['act'])){
	$post_act = @$_GET['act'];
}

// SAVE CHANGES
if(!empty($_POST['post_id'])){
	if(!$post_id = $alkaline->findID($_POST['post_id'])){
		header('Location: ' . LOCATION . BASE . ADMIN . 'posts' . URL_CAP);
		exit();
	}
	
	$posts = new Post($post_id);
	$posts->fetch();
	
	if(!empty($_POST['post_delete']) and ($_POST['post_delete'] == 'delete')){
		$alkaline->deleteRow('posts', $post_id);
	}
	else{
		$post_title = trim($_POST['post_title']);
		
		if(!empty($_POST['post_title_url'])){
			$post_title_url = $alkaline->makeURL($_POST['post_title_url']);
		}
		else{
			$post_title_url = $alkaline->makeURL($post_title);
		}
		
		$post_text_raw = $_POST['post_text_raw'];
		$post_text = $post_text_raw;
		
		// Configuration: post_markup
		if(!empty($_POST['post_markup'])){
			$post_markup_ext = $_POST['post_markup'];
			$post_text = $orbit->hook('markup_' . $post_markup_ext, $post_text_raw, $post_text);
		}
		elseif($alkaline->returnConf('post_markup')){
			$post_markup_ext = $alkaline->returnConf('post_markup_ext');
			$post_text = $orbit->hook('markup_' . $post_markup_ext, $post_text_raw, $post_text);
		}
		else{
			$post_markup_ext = '';
			$post_text = nl2br($post_text_raw);
		}
		
		// Comment disabling
		if(@$_POST['post_comment_disabled'] == 'disabled'){
			$post_comment_disabled = 1;
		}
		else{
			$post_comment_disabled = 0;
		}
		
		$post_images = implode(', ', $alkaline->findIDRef($post_text));
		
		$post_words = $alkaline->countWords($_POST['post_text_raw'], 0);
		
		$fields = array('post_title' => $alkaline->makeUnicode($post_title),
			'post_title_url' => $post_title_url,
			'post_text_raw' => $alkaline->makeUnicode($post_text_raw),
			'post_markup' => $post_markup_ext,
			'post_images' => $post_images,
			'post_text' => $alkaline->makeUnicode($post_text),
			'post_published' => @$_POST['post_published'],
			'post_comment_disabled' => $post_comment_disabled,
			'post_words' => $post_words);
		
		$posts->updateFields($fields);
	}
	
	unset($post_id);
}
else{
	$alkaline->deleteEmptyRow('posts', array('post_title', 'post_text_raw'));
}

define('TAB', 'posts');

// CREATE POST
if(!empty($post_act) and ($post_act == 'add')){
	$post_id = $alkaline->addRow(null, 'posts');
}

// GET POSTS TO VIEW OR PAGE TO EDIT
if(empty($post_id)){
	$posts = new Post;
	$posts->page(null, 50);
	$posts->fetch();
	$posts->formatTime();
	$posts->hook();
	
	define('TITLE', 'Alkaline Posts');
	require_once(PATH . ADMIN . 'includes/header.php');

	?>

	<div class="span-24 last">
		<div class="actions"><a href="<?php echo BASE . ADMIN . 'posts' . URL_ACT . 'add' . URL_RW; ?>">Add post</a></div>
	
		<h1>Posts (<?php echo number_format($posts->post_count); ?>)</h1>
	
		<form action="<?php echo BASE . ADMIN; ?>posts<?php echo URL_ACT; ?>search<?php echo URL_RW; ?>" method="post">
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
							<select id="published" name="published">
								<option value="">All</option>
								<option value="true">Published</option>
								<option value="false">Unpublished</option>
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
					<tr>
						<td class="right middle"><label>Date modified:</label></td>
						<td class="quiet">
							between <input type="text" class="date" name="modified_begin" style="width: 10em;" />
							and <input type="text" class="date" name="modified_end" style="width: 10em;" />
						</td>
					</tr>
					<tr>
						<td class="right middle"><label>Sort results by:</label></td>
						<td>
							<select name="sort">
								<option value="created" selected="selected">Date created</option>
								<option value="modified">Date modified</option>
								<option value="published">Date published</option>
								<option value="title">Title</option>
								<option value="views">Views</option>
							</select>
							<select name="sort_direction">
								<option value="DESC">Descending</option>
								<option value="ASC">Ascending</option>
							</select>
						</td>
					</tr>
				</table>
			</div>
		</form>
	
		<table>
			<tr>
				<th>Title</th>
				<th class="center">Views</th>
				<th class="center">Words</th>
				<th>Created</th>
				<th>Last modified</th>
			</tr>
			<?php

			foreach($posts->posts as $post){
				echo '<tr>';
					echo '<td><a href="' . BASE . ADMIN . 'posts' . URL_ID . $post['post_id'] . URL_RW . '"><strong>' . $post['post_title'] . '</strong></a><br /><a href="' . BASE . 'post' . URL_ID . $post['post_title_url'] . URL_RW . '" class="nu">/' . $post['post_title_url'] . '</td>';
					echo '<td class="center">' . number_format($post['post_views']) . '</td>';
					echo '<td class="center">' . number_format($post['post_words']) . '</td>';
					echo '<td>' . $post['post_created_format'] . '</td>';
					echo '<td>' . $post['post_modified_format'] . '</td>';
				echo '</tr>';
			}

			?>
		</table>
	
		<?php
		if($posts->page_count > 1){
			?>
			<p>
				<?php
				if(!empty($posts->page_previous)){
					for($i = 1; $i <= $image_ids->page_previous; ++$i){
						echo '<a href="' . BASE . ADMIN . 'posts' . URL_PAGE . $i . URL_RW . '" class="page_no">' . number_format($i) . '</a>';
					}
				}
				?>
				<span class="page_no">Page <?php echo $image_ids->page; ?> of <?php echo $image_ids->page_count; ?></span>
				<?php
				if(!empty($posts->page_next)){
					for($i = $image_ids->page_next; $i <= $image_ids->page_count; ++$i){
						echo '<a href="' . BASE . ADMIN . 'posts' . URL_PAGE . $i . URL_RW . '" class="page_no">' . number_format($i) . '</a>';
					}
				}
				?>
			</p>
			<?php
		}
		?>
	</div>

	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
}
else{
	$posts = new Post($post_id);
	$posts->fetch();
	$posts->formatTime();
	
	$post = $posts->posts[0];
	$post = $alkaline->makeHTMLSafe($post);
	
	if(!empty($post_act) and ($post_act == 'add')){
		if($user->returnPref('post_pub') === true){
			$post['post_published'] = 'Now';
		}
	}
	
	if(!empty($post['post_title'])){	
		define('TITLE', 'Alkaline Post: &#8220;' . $post['post_title']  . '&#8221;');
	}
	else{
		define('TITLE', 'Alkaline Post');
	}
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<div class="actions"><a href="<?php echo BASE . ADMIN; ?>search<?php echo URL_ACT; ?>posts<?php echo URL_AID .  $post['post_id'] . URL_RW; ?>" class="button">View images</a> <a href="<?php echo $post['post_uri']; ?>">Go to post</a></div>
	
	<h1>Post</h1>

	<form id="post" action="<?php echo BASE . ADMIN; ?>posts<?php echo URL_CAP; ?>" method="post">
		<table>
			<tr>
				<td class="right middle"><label for="post_title">Title:</label></td>
				<td><input type="text" id="post_title" name="post_title" value="<?php echo @$post['post_title']; ?>" class="title notempty" /></td>
			</tr>
			<tr>
				<td class="right pad"><label for="post_title_url">Custom URL:</label></td>
				<td class="quiet">
					<input type="text" id="post_title_url" name="post_title_url" value="<?php echo @$post['post_title_url']; ?>" style="width: 300px;" /><br />
					Optional. Use only letters, numbers, underscores, and hyphens.
				</td>
			</tr>
			<tr>
				<td class="right pad"><label for="post_text_raw">Text:</label></td>
				<td><textarea id="post_text_raw" name="post_text_raw" style="height: 300px; font-size: 1.1em; line-height: 1.5em;"><?php echo @$post['post_text_raw']; ?></textarea></td>
			</tr>
			<tr>
				<td class="right middle"><label for="post_published">Publish date:</label></td>
				<td class="quiet">
					<input type="text" id="post_published" name="post_published" value="<?php echo @$post['post_published_format']; ?>" class="m" />
				</td>
			</tr>
			<tr>
				<td class="right"><label>Images:</label></td>
				<td>
					<p>
						<span class="switch">&#9656;</span> <a href="#" class="show">Show recent</a> <span class="quiet">(click to add images at cursor position)</span>
					</p>
					<div class="reveal image_click">
						<?php
						
						$image_ids = new Find;
						$image_ids->sort('image_uploaded', 'DESC');
						$image_ids->post(1, 100);
						$image_ids->find();
						
						$images = new Image($image_ids);
						$images->getSizes('square');
						
						if($alkaline->returnConf('post_size_label')){
							$label = 'image_src_' . $alkaline->returnConf('post_size_label');
						}
						else{
							$label = 'image_src_admin';
						}
						
						if($alkaline->returnConf('post_div_wrap')){
							echo '<div class="none wrap_class">' . $alkaline->returnConf('post_div_wrap_class') . '</div>';
						}
						
						foreach($images->images as $image){
							$image['image_title'] = $alkaline->makeHTMLSafe($image['image_title']);
							echo '<a href="' . $image[$label] . '"><img src="' . $image['image_src_square'] .'" alt="' . $image['image_title']  . '" class="frame" id="image-' . $image['image_id'] . '" /></a>';
							echo '<div class="none uri_rel image-' . $image['image_id'] . '">' . $image['image_uri_rel'] . '</div>';
						}
					
						?>
					</div>
				</td>
			</tr>
			<?php if($alkaline->returnConf('comm_enabled')){ ?>
			<tr>
				<td class="right center"><input type="checkbox" id="post_comment_disabled" name="post_comment_disabled" value="disabled" <?php if($post['post_comment_disabled'] == 1){ echo 'checked="checked"'; } ?> /></td>
				<td>
					<strong><label for="post_comment_disabled">Disable comments on this post.</label></strong>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td class="right center"><input type="checkbox" id="post_delete" name="post_delete" value="delete" /></td>
				<td><label for="post_delete">Delete this post.</label> This action cannot be undone.</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="hidden" name="post_id" value="<?php echo $post['post_id']; ?>" /><input type="hidden" id="post_markup" name="post_markup" value="<?php echo $post['post_markup']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo $alkaline->back(); ?>">cancel</a></td>
			</tr>
		</table>
	</form>

	<?php

	require_once(PATH . ADMIN . 'includes/footer.php');	
}

?>