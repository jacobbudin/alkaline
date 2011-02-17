<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalineapp.com/
*/

if($_SERVER['SCRIPT_FILENAME'][0] == '/'){
	define('SERVER_TYPE', '');
	$path = explode('/', __FILE__);
	$path = array_splice($path, 0, -2);
	$path = implode('/', $path);
	define('PATH', $path . '/');
}
else{
	define('SERVER_TYPE', 'win');
	$path = explode('\\', __FILE__);
	$path = array_splice($path, 1, -2);
	$path = implode('\\', $path);
	define('PATH', $path . '\\');
}

preg_match_all('#(?:/)?(.*)/(?:.*)install#si', $_SERVER['SCRIPT_NAME'], $matches);
if(!empty($matches[1][0])){
	$dir = $matches[1][0] . '/';
}
else{
	$dir = '';
}

define('BASE', '/' . $dir);
define('FOLDER_PREFIX', '');

define('ADMIN', FOLDER_PREFIX . 'admin/');
define('CACHE', FOLDER_PREFIX . 'cache/');
define('CLASSES', FOLDER_PREFIX . 'classes/');
define('DB', FOLDER_PREFIX . 'db/');
define('EXTENSIONS', FOLDER_PREFIX . 'extensions/');
define('FUNCTIONS', FOLDER_PREFIX . 'functions/');
define('INCLUDES', FOLDER_PREFIX . 'includes/');
define('JS', FOLDER_PREFIX . 'js/');
define('IMAGES', FOLDER_PREFIX . 'images/');
define('INSTALL', FOLDER_PREFIX . 'install/');
define('SHOEBOX', FOLDER_PREFIX . 'shoebox/');
define('THEMES', FOLDER_PREFIX . 'themes/');

require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;

$_POST = array_map('strip_tags', $_POST);

// Diagnostic checks

if($alkaline->checkPerm(PATH . DB) != '0777'){
	$alkaline->addNote('Database (db/) folder is not writable (CHMOD 777).', 'error');
}
if($alkaline->checkPerm(PATH . IMAGES) != '0777'){
	$alkaline->addNote('Images (images/) folder is not writable (CHMOD 777).', 'error');
}
if($alkaline->checkPerm(PATH . SHOEBOX) != '0777'){
	$alkaline->addNote('Shoebox (shoebox/) folder is not writable (CHMOD 777).', 'error');
}
if($alkaline->checkPerm(PATH . CACHE) != '0777'){
	$alkaline->addNote('Cache (cache/) folder is not writable (CHMOD 777).', 'error');
}
if($alkaline->checkPerm(PATH . 'config.json') != '0777'){
	$alkaline->addNote('Configuration (config.json) file is not writable (CHMOD 777).', 'error');
}
if($alkaline->checkPerm(PATH . 'config.php') == '0777'){
	$alkaline->addNote('Configuration (config.php) file should not be writable (CHMOD 644).', 'error');
}

// Configuration setup

if(@$_POST['install'] == 'Install'){
	$type = $_POST['install_db_type'];
	$name = $_POST['install_db_name'];
	$username = $_POST['install_db_user'];
	$password = $_POST['install_db_pass'];
	
	if(!$config = file_get_contents(PATH . INSTALL . 'config.php', false)){
		$alkaline->addNote('Cannot find configuration file.', 'error');
	}
	
	$config = $alkaline->replaceVar('$base', '$base = \'' . $_POST['install_base'] . '\';', $config);
	$config = $alkaline->replaceVar('$path', '$path = \'' . $_POST['install_path'] . '\';', $config);
	
	if($_POST['install_server'] == 'win'){
		$config = $alkaline->replaceVar('$server_type', '$server_type = \'win\';', $config);
	}
	
	if($_POST['install_db_type'] == 'mysql'){
		if(empty($name)){
			$alkaline->addNote('A database name is required for MySQL.', 'error');
		}
		if(empty($username)){
			$alkaline->addNote('A database username is required for MySQL.', 'error');
		}
		
		$dsn = 'mysql:';
		
		if(!empty($_POST['install_db_host'])){
			$dsn .= 'host=' . $_POST['install_db_host'] . ';';
		}
		else{
			$dsn .= 'host=localhost;';
		}
		
		if(!empty($_POST['install_db_port'])){
			$dsn .= 'port=' . intval($_POST['install_db_port']) . ';';
		}
		
		$dsn .= 'dbname=' . $_POST['install_db_name'];
		
		$config = $alkaline->replaceVar('$db_dsn', '$db_dsn = \'' . $dsn . '\';', $config);
		$config = $alkaline->replaceVar('$db_type', '$db_type = \'mysql\';', $config);
		$config = $alkaline->replaceVar('$db_user', '$db_user = \'' . $username . '\';', $config);
		$config = $alkaline->replaceVar('$db_pass', '$db_pass = \'' . $password . '\';', $config);
	}
	elseif($_POST['install_db_type'] == 'sqlite'){
		if(!empty($_POST['install_db_file'])){
			$path = $_POST['install_db_file'];
		}
		else{
			$path = PATH . DB . 'alkaline.db';
		}
		
		$path = $alkaline->correctWinPath($path);
		
		$dsn = 'sqlite:' . $path;
		
		$config = $alkaline->replaceVar('$db_dsn', '$db_dsn = \'' . $dsn . '\';', $config);
		$config = $alkaline->replaceVar('$db_type', '$db_type = \'sqlite\';', $config);
		
		if($alkaline->checkPerm($path) != '0777'){
			$alkaline->addNote('Your SQLite database is not writable (CHMOD 777).', 'error');
		}
	}
	elseif($_POST['install_db_type'] == 'pgsql'){
		if(empty($name)){
			$alkaline->addNote('A database name is required for PostgreSQL.', 'error');
		}
		if(empty($username)){
			$alkaline->addNote('A database username is required for PostgreSQL.', 'error');
		}
		
		$dsn = 'pgsql:';
		
		if(!empty($_POST['install_db_host'])){
			$dsn .= 'host=' . $_POST['install_db_host'] . ';';
		}
		else{
			$dsn .= 'host=localhost;';
		}
		
		if(!empty($_POST['install_db_port'])){
			$dsn .= 'port=' . intval($_POST['install_db_port']) . ';';
		}
		
		$dsn .= 'dbname=' . $_POST['install_db_name'];
		
		$config = $alkaline->replaceVar('$db_dsn', '$db_dsn = \'' . $dsn . '\';', $config);
		$config = $alkaline->replaceVar('$db_type', '$db_type = \'pgsql\';', $config);
		$config = $alkaline->replaceVar('$db_user', '$db_user = \'' . $username . '\';', $config);
		$config = $alkaline->replaceVar('$db_pass', '$db_pass = \'' . $username . '\';', $config);
	}
	
	if(!empty($_POST['install_db_prefix'])){
		$config = $alkaline->replaceVar('$table_prefix', '$table_prefix = \'' . $_POST['install_db_prefix'] . '\';', $config);
	}
}


// Database setup

if((@$_POST['install'] == 'Install') and ($alkaline->countNotes() == 0)){
	// Check to see if can connect
	if(!$db = new PDO($dsn, $username, $password)){
		$alkaline->addNote('The database could not be contacted. Check your settings.', 'error');
	}
	else{
		function appendTableName($query){
			if(!empty($_POST['install_db_prefix'])){
				return preg_replace('#TABLE ([[:punct:]]*)(\w+)#s', 'TABLE \\1' . $_POST['install_db_prefix'] . '\\2', $query);
			}
			else{
				return $query;
			}
		}
		
		// Import empty DB SQL
		if(@$_POST['install_db_empty'] == 1){
			$queries = file_get_contents(PATH . DB . 'empty.sql');
			$queries = explode("\n", $queries);

			foreach($queries as $query){
				$query = trim($query);
				if(!empty($query)){
					$query = appendTableName($query);
					$db->exec($query);
				}
			}
		}
		
		// Import default SQL
		$queries = file_get_contents(PATH . DB . $type . '.sql');
		$queries = explode("\n", $queries);
		
		foreach($queries as $query){
			$query = trim($query);
			if(!empty($query)){
				$query = appendTableName($query);
				$db->exec($query);
			}
		}
		
		// Add admin user
		$query = $db->prepare('INSERT INTO ' . $_POST['install_db_prefix'] . 'users (user_user, user_pass, user_name, user_email, user_created, user_image_count, user_preferences) VALUES (?, ?, ?, ?, ?, ?, ?);');
		
		$query->execute(array($_POST['install_user'], sha1($_POST['install_pass']), $_POST['install_name'], $_POST['install_email'], date('Y-m-d H:i:s'), 0, 'a:5:{s:10:"page_limit";s:3:"100";s:13:"recent_images";b:1;s:8:"shoe_pub";b:1;s:19:"recent_images_limit";s:2:"25";s:11:"home_target";b:1;}'));
		
		$query->closeCursor();
		
		// Add admin thumbnails
		
		$query = $db->prepare('INSERT INTO ' . $_POST['install_db_prefix'] . 'sizes (size_title, size_label, size_height, size_width, size_type, size_append) VALUES (?, ?, ?, ?, ?, ?);');
		$query->execute(array('Dashboard (L)', 'admin',  600, 600, 'scale', '_admin'));
		$query->execute(array('Dashboard (S)', 'square', 80, 80, 'fill', '_sq'));
		$query->execute(array('Large', 'large', 950, 950, 'scale', '_l'));
		$query->execute(array('Medium', 'medium', 270, 270, 'scale', '_m'));
		$query->execute(array('Small', 'small', 100, 100, 'fill', '_s'));
		
		$query->closeCursor();
		
		// Add default theme
		
		$query = $db->prepare('INSERT INTO ' . $_POST['install_db_prefix'] . 'themes (theme_uid, theme_title, theme_build, theme_version, theme_folder, theme_creator_name, theme_creator_uri) VALUES (?, ?, ?, ?, ?, ?, ?);');
		$query->execute(array('225b134b655901223d2f2ee26599b71763b1e5fe', 'P1', 1, '1.0', 'p1', 'Wilkes & Barre', 'http://www.wilkesandbarre.com/'));
		
		$query->closeCursor();
		
		$alkaline->setConf('theme_id', '1');
		$alkaline->setConf('theme_folder', 'p1');
		$alkaline->saveConf();
	}
}

define('TAB', 'Installation');
define('TITLE', 'Alkaline Installation');

if((@$_POST['install'] == 'Install') and ($alkaline->countNotes() == 0)){
	require_once(PATH . ADMIN . 'includes/header.php');
	
	?>
	
	<p class="large"><strong>Almost there.</strong> Copy and paste the text below into a text editor and save it as &#8220;config.php&#8221; to your hard disk. Then upload this file (overwriting the file that is already there) to your Alkaline directory to complete your installation.</p>
	
	<p>Once you&#8217;re done, <a href="<?php echo BASE . ADMIN; ?>">log in to access your Dashboard</a>.</p>
	
	<textarea style="height: 30em;" class="code"><?php echo $config; ?></textarea>
	
	<?php
}
else{
	require_once(PATH . ADMIN . 'includes/header.php');
	
	?>

	<p class="large"><strong>Welcome to Alkaline.</strong> You&#8217;re halfway there, simply complete the fields below and follow the remaining instructions.</p>

	<form input="" method="post">
		<h3>Your Server OS</h3>
		
		<p>Not sure? We&#8217;ve automatically determined your server OS for you.</p>
	
		<table>
			<tr>
				<td class="right middle">
					<input type="radio" name="install_server" id="install_server_x" value="x" <?php if(SERVER_TYPE != 'win'){ echo 'checked="checked"'; } ?> />
				</td>
				<td>
					<label for="install_server_x">Linux, UNIX, OS X Server, or similar</label>
				</td>
			</tr>
			<tr>
				<td class="right middle">
					<input type="radio" name="install_server" id="install_server_win" value="win" <?php if(SERVER_TYPE == 'win'){ echo 'checked="checked"'; } ?> />
				</td>
				<td>
					<label for="install_server_win">Windows&#0174; Server 2008 or similar</label>
				</td>
			</tr>
		</table>
		
		<h3>Your File Structure</h3>
	
		<p>Where did you install Alkaline relative to your domain name?</p>
	
		<table>
			<tr>
				<td class="right pad">
					<label for="install_path">Full path:</label>
				</td>
				<td>
					<input type="text" name="install_path" id="install_path" class="m" value="<?php echo PATH; ?>" /><br />
					<span class="quiet">For example, /var/www/<em>yourdomain.com</em>/</span>
				</td>
			</tr>
			<tr>
				<td class="right pad">
					<label for="install_base">URL base:</label>
				</td>
				<td>
					<input type="text" name="install_base" id="install_base" class="s" value="<?php echo BASE; ?>" /><br />
					<span class="quiet">For example, http://<em>www.yourdomain.com</em>/images/ would be <strong>/images/</strong></span>
				</td>
			</tr>
		</table>
	
		<h3>Your Database Server</h3>
	
		<table>
			<tr>
				<td class="right middle">
					<label for="install_db_type">Database type:</label>
				</td>
				<td>
					<select name="install_db_type" id="install_db_type">
						<?php
						
						$php_pdo_drivers = @PDO::getAvailableDrivers();
						foreach($php_pdo_drivers as $driver){
							switch($driver){
								case 'odbc':
									// echo '<option value="mssql">Microsoft SQL Server</option>';
									break;
								case 'mysql':
									echo '<option value="mysql">MySQL</option>';
									break;
								case 'pgsql':
									echo '<option value="sqlite">SQLite</option>';
									break;
								case 'sqlite':
									echo '<option value="pgsql">PostgreSQL</option>';
									break;
								default:
									break;
							}
						}
						
						?>
					</select>
				</td>
			</tr>
			<tr>
				<td class="right pad">
					<label for="install_db_prefix">Database table prefix:</label>
				</td>
				<td>
					<input type="text" name="install_db_prefix" id="install_db_prefix" value="<?php echo @$_POST['install_db_prefix'] ?>" class="xs" /> <span class="quiet">(optional)</span>
				</td>
			</tr>
			<tr>
				<td style="text-align: right;">
					<input type="checkbox" name="install_db_empty" id="install_db_empty" value="1">
				</td>
				<td>
					<label for="install_db_empty" style="font-weight: normal;">Delete Alkaline database contents if they already exist.</label>
				</td>
			</tr>
		</table>
	
		<h4>MySQL and PostgreSQL</h4>
	
		<table>
			<tr>
				<td class="right pad">
					<label for="install_db_name">Database name:</label>
				</td>
				<td>
					<input type="text" name="install_db_name" id="install_db_name" value="<?php echo @$_POST['install_db_name'] ?>" class="s" />
				</td>
			</tr>
			<tr>
				<td class="right pad">
					<label for="install_db_user">Database username:</label>
				</td>
				<td>
					<input type="text" name="install_db_user" id="install_db_user" value="<?php echo @$_POST['install_db_user'] ?>" class="s" />
				</td>
			</tr>
			<tr>
				<td class="right pad">
					<label for="install_db_pass">Database password:</label>
				</td>
				<td>
					<input type="text" name="install_db_pass" id="install_db_pass" value="<?php echo @$_POST['install_db_pass'] ?>" class="s" />
				</td>
			</tr>
			<tr>
				<td class="right pad">
					<label for="install_db_host">Database host:</label>
				</td>
				<td>
					<input type="text" name="install_db_host" id="install_db_host" value="<?php echo @$_POST['install_db_host'] ?>" class="m" /> <span class="quiet">(optional)</span>
				</td>
			</tr>
			<tr>
				<td class="right pad">
					<label for="install_db_port">Database port:</label>
				</td>
				<td>
					<input type="text" name="install_db_port" id="install_db_port" value="<?php echo @$_POST['install_db_port'] ?>" class="xs" /> <span class="quiet">(optional)</span>
				</td>
			</tr>
		</table>
	
		<h4>SQLite</h4>
	
		<table>
			<tr>
				<td class="right pad">
					<label for="install_db_file">Database file (full path):</label>
				</td>
				<td>
					<input type="text" name="install_db_file" id="install_db_file" value="<?php echo @$_POST['install_db_file'] ?>" class="m" /> <span class="quiet">(optional)</span><br />
					<span class="quiet">Defaults to /<?php echo DB; ?>alkaline.db. Your database file be writable (CHMOD 777).</span>
				</td>
			</tr>
		</table>
	
		<h3>Your Admin Account</h3>
	
		<p>Don&#8217;t worry, you can change these details later through your Alkaline Dashboard.</p>
	
		<table>
			<tr>
				<td class="right middle">
					<label for="install_name">Name:</label>
				</td>
				<td>
					<input type="text" name="install_name" id="install_name" value="<?php echo @$_POST['install_name'] ?>" class="s" />
				</td>
			</tr>
			<tr>
				<td class="right middle">
					<label for="install_user">Username:</label>
				</td>
				<td>
					<input type="text" name="install_user" id="install_user" value="<?php echo @$_POST['install_user'] ?>" class="s" />
				</td>
			</tr>
			<tr>
				<td class="right middle">
					<label for="install_pass">Password:</label>
				</td>
				<td>
					<input type="password" name="install_pass" id="install_pass" value="<?php echo @$_POST['install_pass'] ?>" class="s" />
				</td>
			</tr>
			<tr>
				<td class="right middle">
					<label for="install_email">Email:</label>
				</td>
				<td>
					<input type="text" name="install_email" id="install_email" value="<?php echo @$_POST['install_email'] ?>" class="m" />
				</td>
			</tr>
			<!-- <tr>
				<td style="text-align: right;">
					<input type="checkbox" name="install_welcome" id="install_welcome" value="1" checked="checked">
				</td>
				<td>
					<label for="install_welcome" style="font-weight: normal;">Send me a welcome email containing my username and password.</label>
				</td>
			</tr> -->
		</table>
		
		<h3>Install Alkaline</h3>
	
		<p>This may take several moments, please be patient. Do not interrupt the process by stopping the page from loading or closing your Web browser.</p><p><input type="submit" name="install" value="Install" /></p>
	</form>

	<?php
}

require_once(PATH . ADMIN . 'includes/footer.php');

?>