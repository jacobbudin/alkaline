<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$orbit = new Orbit;
$user = new User;

$user->perm(true);

$page_id = @$alkaline->findID($_GET['id']);
$page_add = @$alkaline->findID($_GET['add']);

// SAVE CHANGES
if(!empty($_POST['page_id'])){
	$page_id = $alkaline->findID($_POST['page_id']);
	if(@$_POST['page_delete'] == 'delete'){
		$alkaline->deleteRow('pages', $page_id);
	}
	else{
		$page_title = trim($_POST['page_title']);
		
		if(!empty($_POST['page_title_url'])){
			$page_title_url = $alkaline->makeURL($_POST['page_title_url']);
		}
		else{
			$page_title_url = $alkaline->makeURL($page_title);
		}
		
		$page_text_raw = $_POST['page_text_raw'];
		$page_text = $page_text_raw;
		
		$page_markup = $_POST['page_markup'];
		
		if(!empty($_POST['page_markup'])){
			$page_text = $orbit->hook('markup_' . $_POST['page_markup'], $page_text_raw, $page_text);
		}
		
		$page_words = str_word_count($_POST['page_text_raw'], 0);
		
		$fields = array('page_title' => $page_title,
			'page_title_url' => $page_title_url,
			'page_text_raw' => $page_text_raw,
			'page_markup' => $page_markup,
			'page_text' => $page_text,
			'page_words' => $page_words);
		$alkaline->updateRow($fields, 'pages', $page_id);
	}
	unset($page_id);
}
else{
	$alkaline->deleteEmptyRow('pages', array('page_title', 'page_text_raw'));
}

// CREATE PAGE
if($page_add == 1){
	$page_id = $alkaline->addRow(null, 'pages');
}

// GET PAGES TO VIEW OR PAGE TO EDIT
if(empty($page_id)){
	$pages = new Page();
	$pages->fetchAll();
	
	define('TITLE', 'Alkaline Pages');
	require_once(PATH . ADMIN . 'includes/header.php');
	require_once(PATH . ADMIN . 'includes/features.php');

	?>
	
	<h1>Pages (<?php echo $pages->count(); ?>)</h1>

	<?php
	
	if($pages->count() > 0){
		?>
		<table>
			<tr>
				<th>Title</th>
				<th class="center">Views</th>
				<th class="center">Words</th>
				<th>Created</th>
				<th>Last modified</th>
			</tr>
			<?php

			foreach($pages->pages as $page){
				echo '<tr>';
					echo '<td><a href="' . BASE . ADMIN . 'pages/' . $page['page_id'] . '"><strong>' . $page['page_title'] . '</strong></a><br /><a href="' . BASE . 'pages/' . $page['page_title_url'] . '" class="nu">/' . $page['page_title_url'] . '</td>';
					echo '<td class="center">' . number_format($page['page_views']) . '</td>';
					echo '<td class="center">' . number_format($page['page_words']) . '</td>';
					echo '<td>' . $pages->formatTime($page['page_created']) . '</td>';
					echo '<td>' . $pages->formatTime($page['page_modified']) . '</td>';
				echo '</tr>';
			}

			?>
		</table>
		<?php
	}

	require_once(PATH . ADMIN . 'includes/footer.php');
}
else{
	$pages = new Page($page_id);
	$page = $pages->pages[0];
	
	if(!empty($page['page_title'])){	
		define('TITLE', 'Alkaline Page: &#8220;' . $page['page_title']  . '&#8221;');
	}
	else{
		define('TITLE', 'Alkaline Page');
	}
	require_once(PATH . ADMIN . 'includes/header.php');
	require_once(PATH . ADMIN . 'includes/features.php');

	?>
	
	<div style="float: right; margin: 1em 0;"><a href="<?php echo BASE . ADMIN; ?>search/pages/<?php echo $page['page_id']; ?>/" class="nu"><span class="button">&#0187;</span>View photos</a> &#0160; <a href="" class="nu"><span class="button">&#0187;</span>View page</a></div>
	
	<h1>Page</h1>

	<form id="page" action="<?php echo BASE . ADMIN; ?>pages/" method="post">
		<table>
			<tr>
				<td class="right middle"><label for="page_title">Title:</label></td>
				<td><input type="text" id="page_title" name="page_title" value="<?php echo $page['page_title']; ?>" class="title" /></td>
			</tr>
			<tr>
				<td class="right pad"><label for="page_title_url">Custom URL:</label></td>
				<td class="quiet">
					<input type="text" id="page_title_url" name="page_title_url" value="<?php echo $page['page_title_url']; ?>" style="width: 300px;" /><br />
					Optional. Use only lowercase letters, numbers, underscores, and hyphens.
				</td>
			</tr>
			<tr>
				<td class="right"><label for="page_description">Text:</label></td>
				<td><textarea id="page_description" name="page_text_raw" style="height: 300px; font-size: 1.1em; line-height: 1.5em;"><?php echo $page['page_text_raw']; ?></textarea></td>
			</tr>
			<tr id="tr_page_markup">
				<td class="right"><input type="checkbox" id="page_markup" value="delete" /></td>
				<td><strong><label for="page_markup">Markup this page using <select name="page_markup" title="<?php echo $page['page_markup']; ?>"><option value=""></option><?php $orbit->hook('page_markup_html'); ?></select>.</label></strong></td>
			</tr>
			<tr>
				<td class="right"><input type="checkbox" id="page_delete" name="page_delete" value="delete" /></td>
				<td><strong><label for="page_delete">Delete this page.</label></strong> This action cannot be undone.</td>
			</tr>
			<tr>
				<td></td>
				<td><input type="hidden" name="page_id" value="<?php echo $page['page_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo BASE . ADMIN; ?>pages/">cancel</a></td>
			</tr>
		</table>
	</form>

	<?php

	require_once(PATH . ADMIN . 'includes/footer.php');
}

?>