<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$theme_id = @$alkaline->findID($_GET['id']);

// SAVE CHANGES
if(!empty($_POST['theme_id'])){
	$theme_id = $alkaline->findID($_POST['theme_id']);
	
	if(@$_POST['theme_default'] == 'default'){
		$fields = array('theme_default' => '1');
		$alkaline->updateRow($fields, 'themes', $theme_id);
	}
	
	if(@$_POST['theme_remove'] == 'remove'){
		$alkaline->deleteRow('themes', $theme_id);
	}
	
	unset($theme_id);
}


if(empty($theme_id)){
	$themes = $alkaline->getTable('themes', null, null, null, '');
	$theme_count = @count($themes);
	
	$blocks = $alkaline->getBlocks();
	
	define('TITLE', 'Alkaline Themes');
	require_once(PATH . ADMIN . 'includes/header.php');

	?>

	<div id="module" class="container">
		<h1>Themes &#0038; Blocks</h1>
		<p>You have <?php $alkaline->echoCount($theme_count, 'theme'); ?> installed.</p>
	</div>

	<div id="themes" class="container">
		<div style="text-align: right; margin: 0 0 1em 0;" class="span-23 last"><a href="" class="nu"><span class="button">&#0043;</span>Install theme</a> &#0160; <a href="" class="nu"><span class="button">&#0149;</span>Check for updates</a></div>
	
		<h3>Themes</h3><br />
	
		<table>
			<tr>
				<th>Theme</th>
				<th class="center">Version</th>
				<th class="center">Update</th>
			</tr>
			<?php
		
			foreach($themes as $theme){
				echo '<tr>';
				echo '<td><strong><a href="' . BASE . ADMIN . 'themes/' . $theme['theme_id'] . '">' . $theme['theme_title'] . '</a></strong>';
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
		
		<h3>Blocks</h3><br />
		
		<table>
			<tr>
				<th>Block</th>
				<th class="center">Canvas Markup</th>
			</tr>
			<?php
			
			foreach($blocks as $block){
				echo '<tr>';
				echo '<td><strong>' . $block . '</strong></td>';
				echo '<td class="center">&#0060;&#0033;&#0045;&#0045; CANVAS_' . strtoupper(preg_replace('#\..+#si', '', $block)) . ' &#0045;&#0045;&#0062;</td>';
				echo '</tr>';
			}
		
			?>
		</table>
		
	</div>
	<?php

	require_once(PATH . ADMIN . 'includes/footer.php');
	
}
else{
	
	// Get pile
	$themes = $alkaline->getTable('themes', $theme_id);
	$theme = $themes[0];

	if(!empty($theme['theme_title'])){	
		define('TITLE', 'Alkaline Theme: &#8220;' . $theme['theme_title']  . '&#8221;');
	}
	else{
		define('TITLE', 'Alkaline Theme');
	}
	
	require_once(PATH . ADMIN . 'includes/header.php');
	
	?>
	
	<div id="module" class="container">
		<h1><?php echo $theme['theme_title']; ?></h1>
		<p></p>
	</div>
	
	<form id="theme" class="container" action="<?php echo BASE . ADMIN; ?>themes/" method="post">
		<div style="float: right; margin: 1em 0;"><a href="<?php echo BASE; ?>?theme=<?php echo $theme['theme_id']; ?>/" class="nu"><span class="button">&#0187;</span>Preview theme</a></div>
		<table>
			<tr>
				<td class="right"><input type="checkbox" id="theme_default" name="theme_default" value="default" <?php if($theme['theme_default'] == 1){ echo 'checked="checked" disabled="disabled"'; } ?> /></td>
				<td><strong><label for="theme_default">Make this theme the default.</label></strong></td>
			</tr>
			<tr>
				<td class="right"><input type="checkbox" id="theme_remove" name="theme_remove" value="remove" <?php if($theme['theme_default'] == 1){ echo 'disabled="disabled"'; } ?> /></td>
				<td><strong><label for="theme_remove">Remove this extension.</label></strong></td>
			</tr>
			<tr>
				<td></td>
				<td><input type="hidden" name="theme_id" value="<?php echo $theme['theme_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo BASE . ADMIN; ?>themes/">cancel</a></td>
			</tr>
		</table>
	</form>
	
	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}
?>