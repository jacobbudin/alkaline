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
	
	if(@$_POST['extension_reset'] == 'reset'){
		$fields = array('extension_preferences' => '');
		$alkaline->updateRow($fields, 'extensions', $extension_id);
	}
	
	if(@$_POST['extension_disable'] == 'disable'){
		$fields = array('extension_status' => '-1');
		$alkaline->updateRow($fields, 'extensions', $extension_id);
	}
	
	$extensions = new Orbit($extension_id);
	$extensions->hook('config_save');
	
	unset($extension_id);
}

// Configuration: maint_disable
if($alkaline->returnConf('maint_disable')){
	$alkaline->addNotification('All extensions have been disabled.', 'notice');
}

define('TAB', 'settings');

if(empty($extension_id)){
	$orbit = new Orbit();
	
	define('TITLE', 'Alkaline Extensions');
	require_once(PATH . ADMIN . 'includes/header.php');

	?>
	
	<div class="actions"><a href="install/">Install extension</a></div>

	<h1>Extensions (<?php echo @$orbit->extension_count; ?>)</h1>

	<h3>Enabled</h3>

	<table>
		<tr>
			<th>Extension</th>
			<th class="center">Version</th>
			<th class="center">Update</th>
		</tr>
		<?php
	
		foreach($orbit->extensions as $extension){
			echo '<tr>';
			echo '<td><strong><a href="' . BASE . ADMIN . 'extensions/' . $extension['extension_id'] . '">' . $extension['extension_title'] . '</a></strong>';
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
			echo '<td class="center">' . $extension['extension_version'] . ' <span class="small">(' . $extension['extension_build'] . ')</span></td>';
			echo '<td class="center quiet">&#8212;</td>';
			echo '</tr>';
		}
	
		?>
	</table>

	<h3>Disabled</h3><br />
	
	<?php

	require_once(PATH . ADMIN . 'includes/footer.php');
	
}
else{
	$extensions = new Orbit($extension_id);
	$extension = $extensions->extensions[0];
	
	$extensions->hook('config_load');
	
	define('TITLE', 'Alkaline Extensions');
	require_once(PATH . ADMIN . 'includes/header.php');
	
	?>
	
	<h1><?php echo $extension['extension_title']; ?></h1>
	<p><?php echo $extension['extension_description']; ?></p>
	
	<form id="extension" action="<?php echo BASE . ADMIN; ?>extensions/" method="post">
		<div>
			<?php $has_config = $extensions->hook('config', true); ?>
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
				<td><input type="hidden" name="extension_id" value="<?php echo $extension['extension_id']; ?>" /><input type="submit" value="Save changes" /> or <a href="<?php echo $_SERVER['HTTP_REFERER']; ?>">cancel</a></td>
			</tr>
		</table>
	</form>
	
	<?php
	
	require_once(PATH . ADMIN . 'includes/footer.php');
	
}
?>