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

// Load current themes
$themes = $alkaline->getTable('themes');
$theme_folders = array();

foreach($themes as $theme){
	$theme_folders[] = $theme['theme_folder'];
}

// Seek all themes
$seek_themes = $alkaline->seekDirectory(PATH . THEMES, '');

$theme_deleted = array();

// Determine which themes have been removed, delete rows from table
foreach($themes as $theme){
	$theme_folder = PATH . THEMES . $theme['theme_folder'];
	if(!in_array($theme_folder, $seek_themes)){
		$theme_deleted[] = $theme['theme_id'];
	}
}

$alkaline->deleteRow('themes', $theme_deleted);

// Determine which themes are new, install them
$themes_installed = array();

foreach($seek_themes as &$theme_folder){
	$theme_folder = $alkaline->getFilename($theme_folder);
	if(!in_array($theme_folder, $theme_folders)){
		$data = file_get_contents(PATH . THEMES . $theme_folder . '/theme.xml');
		if(empty($data)){ $alkaline->addNote('Alkaline could not install a new theme. Its XML file is missing or corrupted.', 'error'); continue; }
		
		$xml = new SimpleXMLElement($data);
		
		$fields = array('theme_uid' => $xml->uid,
			'theme_title' => $xml->title,
			'theme_folder' => $theme_folder,
			'theme_build' => $xml->build,
			'theme_version' => $xml->version,
			'theme_creator_name' => $xml->creator->name,
			'theme_creator_uri' => $xml->creator->uri);
		$theme_intalled_id = $alkaline->addRow($fields, 'themes');
		$themes_installed[] = $theme_intalled_id;
	}
}

$themes_installed_count = count($themes_installed);
if($themes_installed_count > 0){
	if($themes_installed_count == 1){
		$notification = 'You have successfully installed 1 theme.';
	}
	else{
		$notification = 'You have successfully installed ' . $themes_installed_count . ' themes.';
	}
	
	$alkaline->addNote($notification, 'success');
}

define('TAB', 'settings');

$themes = $alkaline->getTable('themes');
$theme_count = @count($themes);

define('TITLE', 'Alkaline Themes');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div class="actions"><a href="<?php echo BASE . ADMIN . 'configuration' . URL_CAP; ?>">Change theme</a></div>

<h1>Themes (<?php echo $theme_count; ?>)</h1>

<p>Themes change the look and feel of your Alkaline library. You can browse and download additional themes at the <a href="http://www.alkalineapp.com/users/">Alkaline Lounge</a>.</p>

<table>
	<tr>
		<th>Theme</th>
		<th class="center">Preview</th>
		<th class="center">Version</th>
		<th class="center">Update</th>
	</tr>
	<?php

	foreach($themes as $theme){
		echo '<tr>';
		echo '<td><strong>' . $theme['theme_title'] . '</strong>';
		
		if(!empty($theme['theme_creator_name'])){
			echo ' \ ';
			if(!empty($theme['theme_creator_uri'])){
				echo '<a href="' . $theme['theme_creator_uri'] . '" class="nu">' . $theme['theme_creator_name'] . '</a>';
			}
			else{
				echo $theme['theme_creator_name'];
			}
		}
		
		echo '</td>';
		echo '<td class="center"><a href="' . BASE . '?theme=' . $theme['theme_folder'] . '">Preview</a></td>';
		echo '<td class="center">' . $theme['theme_version'] . ' <span class="small">(' . $theme['theme_build'] . ')</span></td>';
		echo '<td class="center quiet">&#8212;</td>';
		echo '</tr>';
	}

	?>
</table>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>