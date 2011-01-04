<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalinenapp.com/
*/

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$theme_id = @$alkaline->findID($_GET['id']);

// SAVE CHANGES
if(!empty($_POST['theme_id'])){
	$theme_id = $alkaline->findID($_POST['theme_id']);
	
	if(@$_POST['theme_remove'] == 'remove'){
		$alkaline->deleteRow('themes', $theme_id);
	}
	
	unset($theme_id);
}

define('TAB', 'settings');

if(empty($theme_id)){
	$themes = $alkaline->getTable('themes');
	$theme_count = @count($themes);
	
	define('TITLE', 'Alkaline Themes');
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<div class="actions"><a href="<?php echo BASE . ADMIN; ?>themes<?php echo URL_ID; ?>install<?php echo URL_RW; ?>">Install theme</a></div>

	<h1>Themes (<?php echo $theme_count; ?>)</h1>
	
	<p>Themes change the look and feel of your Alkaline library. You can browse and download additional themes at the <a href="http://www.alkalineapp.com/users/">Alkaline Lounge</a>.</p>

	<table>
		<tr>
			<th>Theme</th>
			<th class="center">Version</th>
			<th class="center">Update</th>
		</tr>
		<?php

		foreach($themes as $theme){
			echo '<tr>';
			echo '<td><strong><a href="' . BASE . ADMIN . 'themes' . URL_ID . $theme['theme_id'] . URL_RW . '">' . $theme['theme_title'] . '</a></strong>';
			if(!empty($theme['theme_creator'])){
				echo ' \ ';
				if(!empty($theme['theme_creator_url'])){
					echo '<a href="' . $theme['theme_creator_url'] . '" class="nu">' . $theme['theme_creator'] . '</a>';
				}
				else{
					echo $theme['theme_creator'];
				}
			}
			echo '</td>';
			echo '<td class="center">' . $theme['theme_version'] . ' <span class="small">(' . $theme['theme_build'] . ')</span></td>';
			echo '<td class="center quiet">&#8212;</td>';
			echo '</tr>';
		}

		?>
	</table>
	
	<?php

	require_once(PATH . ADMIN . 'includes/footer.php');
	
}
else{
	// Get pile
	$theme = $alkaline->getRow('themes', $theme_id);
	$theme = $alkaline->makeHTMLSafe($theme);

	if(!empty($theme['theme_title'])){	
		define('TITLE', 'Alkaline Theme: &#8220;' . $theme['theme_title']  . '&#8221;');
	}
	else{
		define('TITLE', 'Alkaline Theme');
	}
	
	require_once(PATH . ADMIN . 'includes/header.php');
	
	?>
	
	<div style="float: right; margin: 1em 0;"><a href="<?php echo BASE; ?>?theme=<?php echo $theme['theme_id']; ?>/" class="button">Preview theme</a></div>
	
	<h1><?php echo $theme['theme_title']; ?></h1>
	
	<form id="theme" action="<?php echo BASE . ADMIN; ?>themes<?php echo URL_CAP; ?>" method="post">
		<table>
			<tr>
				<td class="right"><input type="checkbox" id="theme_remove" name="theme_remove" value="remove" <?php if($theme['theme_default'] == 1){ echo 'disabled="disabled"'; } ?> /></td>
				<td><strong><label for="theme_remove">Remove this theme.</label></strong></td>
			</tr>
			<tr>
				<td></td>
				<td><?php if($theme['theme_default'] == 1){ echo '<input type="hidden" name="theme_default" value="default" />'; } ?><input type="hidden" name="theme_id" value="<?php echo $theme['theme_id']; ?>" /><input type="hidden" name="theme_folder" value="<?php echo $theme['theme_folder']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo $alkaline->back(); ?>">cancel</a></td>
			</tr>
		</table>
	</form>
	
	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}
?>