<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$extension_id = @$alkaline->findID($_GET['id']);

// SAVE CHANGES
if(!empty($_POST['extension_id'])){
	$extension_id = $alkaline->findID($_POST['extension_id']);
	
	// Reset extension
	if(@$_POST['extension_reset'] == 'reset'){
		$fields = array('extension_preferences' => '');
		$bool = $alkaline->updateRow($fields, 'extensions', $extension_id);
		if($bool === true){
			$alkaline->addNotification('You successfully reset the extension.', 'success');
			$reset = 1;
		}
	}
	
	// Disable extension
	if(@$_POST['extension_disable'] == 'disable'){
		$fields = array('extension_status' => 0);
		$bool = $alkaline->updateRow($fields, 'extensions', $extension_id);
		if($bool === true){
			$alkaline->addNotification('You successfully disabled the extension.', 'success');
			$disable = 1;
		}
	}
	
	// Enable extension
	if(@$_POST['extension_enable'] == 'enable'){
		$fields = array('extension_status' => 1);
		$bool = $alkaline->updateRow($fields, 'extensions', $extension_id);
		if($bool === true){
			$alkaline->addNotification('You successfully enabled the extension.', 'success');
			$enable = 1;
		}
	}
	
	// Save extension, if no other action taken
	if((@$reset != 1) or (@$disable != 1) or (@$enable != 1)){
		$orbit = new Orbit($extension_id);
		$orbit->hook('config_save');
	}
	
	// If not only resetting, return to Extensions page
	if((@$reset != 1) or (@$disable == 1) or (@$enable != 1)){
		unset($extension_id);
	}
}

// Configuration: maint_disable
if($alkaline->returnConf('maint_disable')){
	$alkaline->addNotification('All extensions have been disabled.', 'notice');
}

define('TAB', 'settings');

if(empty($extension_id)){
	$extensions = $alkaline->getTable('extensions', null, null, null, array('extension_status DESC', 'extension_title ASC'));
	$extensions_count = @count($extensions);
	
	define('TITLE', 'Alkaline Extensions');
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<div class="actions"><a href="<?php echo BASE . ADMIN; ?>extensions<?php echo URL_ID; ?>install<?php echo URL_RW; ?>">Install extension</a></div>

	<h1>Extensions (<?php echo @$extensions_count; ?>)</h1>

	<table>
		<tr>
			<th>Extension</th>
			<th class="center">Status</th>
			<th class="center">Version</th>
			<th class="center">Update</th>
		</tr>
		<?php
	
		foreach($extensions as $extension){
			echo '<tr>';
			echo '<td><strong><a href="' . BASE . ADMIN . 'extensions' . URL_ID . $extension['extension_id'] . URL_RW . '">' . $extension['extension_title'] . '</a></strong>';
			if(!empty($extension['extension_creator'])){
				echo ' \ ';
				if(!empty($extension['extension_creator_url'])){
					echo '<a href="' . $extension['extension_creator_url'] . '" class="nu">' . $extension['extension_creator'] . '</a>';
				}
				else{
					echo $extension['extension_creator'];
				}
			}
			echo '<br />' . $extension['extension_description'] . '</td>';
			echo '<td class="center">';
			if($extension['extension_status'] == 1){
				echo 'Enabled';
			}
			else{
				echo 'Disabled';
			}
			echo '</td>';
			echo '<td class="center">' . $extension['extension_version'] . ' <span class="small">(' . $extension['extension_build'] . ')</span></td>';
			echo '<td class="center quiet">&#8212;</td>';
			echo '</tr>';
		}
	
		?>
	</table>
	
	<?php

	require_once(PATH . ADMIN . 'includes/footer.php');
	
}
else{
	// Get extension
	$extension = $alkaline->getRow('extensions', $extension_id);
	$extension = $alkaline->makeHTMLSafe($extension);
	
	if($extension['extension_status'] > 0){
		$orbit = new Orbit($extension_id);
		$orbit->hook('config_load');
	
		define('TITLE', 'Alkaline Extension: &#8220;' . $extension['extension_title']  . '&#8221;');
		require_once(PATH . ADMIN . 'includes/header.php');
	
		?>
	
		<h1><?php echo $extension['extension_title']; ?></h1>
	
		<form id="extension" action="<?php echo BASE . ADMIN; ?>extensions<?php echo URL_CAP; ?>" method="post">
			<div>
				<?php $orbit->hook('config'); ?>
			</div>
		
			<table>
				<tr>
					<td class="right"><input type="checkbox" id="extension_reset" name="extension_reset" value="reset" /></td>
					<td><strong><label for="extension_reset">Reset this extension.</label></strong> This action cannot be undone.</td>
				</tr>
				<tr>
					<td class="right"><input type="checkbox" id="extension_disable" name="extension_disable" value="disable" /></td>
					<td><strong><label for="extension_disable">Disable this extension.</label></strong></td>
				</tr>
				<tr>
					<td></td>
					<td><input type="hidden" name="extension_id" value="<?php echo $extension['extension_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo $alkaline->back(); ?>">cancel</a></td>
				</tr>
			</table>
		</form>
	
		<?php
	
		require_once(PATH . ADMIN . 'includes/footer.php');
	}
	else{
		define('TITLE', 'Alkaline Extension: &#8220;' . $extension['extension_title']  . '&#8221;');
		require_once(PATH . ADMIN . 'includes/header.php');
		
		?>
		
		<h1><?php echo $extension['extension_title']; ?></h1>
		
		<form id="extension" action="<?php echo BASE . ADMIN; ?>extensions<?php echo URL_CAP; ?>" method="post">
			<table>
				<tr>
					<td class="right"><input type="checkbox" id="extension_reset" name="extension_reset" value="reset" /></td>
					<td><strong><label for="extension_reset">Reset this extension.</label></strong> This action cannot be undone.</td>
				</tr>
				<tr>
					<td class="right"><input type="checkbox" id="extension_enable" name="extension_enable" value="enable" /></td>
					<td><strong><label for="extension_enable">Enable this extension.</label></strong></td>
				</tr>
				<tr>
					<td></td>
					<td><input type="hidden" name="extension_id" value="<?php echo $extension['extension_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo $alkaline->back(); ?>">cancel</a></td>
				</tr>
			</table>
		</form>
		
		<?php
		
		require_once(PATH . ADMIN . 'includes/footer.php');
	}
	
}
?>