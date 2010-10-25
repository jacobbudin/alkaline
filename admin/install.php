<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;

$_POST = array_map('strip_tags', $_POST);

// Diagnostic checks

if($alkaline->checkPerm(PATH . SHOEBOX) != '0777'){
	$alkaline->addNotification('Assets folder is not writable (CHMOD 777).', 'error');
}
if($alkaline->checkPerm(PATH . SHOEBOX) != '0777'){
	$alkaline->addNotification('Photos folder is not writable (CHMOD 777).', 'error');
}
if($alkaline->checkPerm(PATH . SHOEBOX) != '0777'){
	$alkaline->addNotification('Shoebox folder is not writable (CHMOD 777).', 'error');
} 

// Configuration setup

if(@$_POST['install'] == 'Install'){
	$type = $_POST['install_db_type'];
	$name = $_POST['install_db_name'];
	$username = $_POST['install_db_user'];
	$password = $_POST['install_db_pass'];
	
	if(!$config = file_get_contents(PATH . ASSETS . 'config-default.php', false)){
		$alkaline->addNotification('Cannot find configuration file.', 'error');
	}
	
	if($_POST['install_server'] == 'win'){
		$config = $alkaline->replaceVar('$server_type', '$server_type = "win";', $config);
	}
	
	if($_POST['install_db_type'] == 'mysql'){
		if(empty($name)){
			$alkaline->addNotification('A database name is required for MySQL.', 'error');
		}
		if(empty($username)){
			$alkaline->addNotification('A database username is required for MySQL.', 'error');
		}
		if(empty($password)){
			$alkaline->addNotification('A database passsword is required for MySQL.', 'error');
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
			$path = PATH . ASSETS . 'alkaline.db';
		}
		
		$path = $alkaline->correctWinPath($path);
		
		$dsn = 'sqlite:' . $path;
		
		$config = $alkaline->replaceVar('$db_dsn', '$db_dsn = \'' . $dsn . '\';', $config);
		$config = $alkaline->replaceVar('$db_type', '$db_type = \'sqlite\';', $config);
		
		if($alkaline->checkPerm($path) != '0777'){
			$alkaline->addNotification('Your SQLite database is not writable (CHMOD 777).', 'error');
		}
	}
	elseif($_POST['install_db_type'] == 'pgsql'){
		if(empty($name)){
			$alkaline->addNotification('A database name is required for PostgreSQL.', 'error');
		}
		if(empty($username)){
			$alkaline->addNotification('A database username is required for PostgreSQL.', 'error');
		}
		if(empty($password)){
			$alkaline->addNotification('A database passsword is required for PostgreSQL.', 'error');
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
	
	if(!empty($_POST['install_base'])){
		$config = $alkaline->replaceVar('$folder_base', '$folder_base = \'' . $_POST['install_base'] . '\';', $config);
	}
}


// Database setup

if((@$_POST['install'] == 'Install') and ($alkaline->isNotification() === false)){	
	// Check to see if can connect
	if(!$db = new PDO($dsn, $username, $password)){
		$alkaline->addNotification('The database could not be contacted. Check your settings.', 'error');
	}
	else{
		// Import empty DB SQL
		if(@$_POST['install_db_empty'] == 1){
			$queries = file_get_contents(PATH . ASSETS . 'empty.sql');
			$queries = explode("\n", $queries);

			foreach($queries as $query){
				$query = trim($query);
				if(!empty($query)){
					$db->exec($query . ';');
				}
			}
		}
		
		// Import default SQL
		$queries = file_get_contents(PATH . ASSETS . $type . '.sql');
		$queries = explode(";", $queries);
		
		foreach($queries as $query){
			$query = trim($query);
			if(!empty($query)){
				$db->exec($query . ';');
			}
		}
		
		// Import geo database
		$queries = file_get_contents(PATH . ASSETS . 'geo.sql');
		$queries = explode("\n", $queries);
		
		foreach($queries as $query){
			if($type != 'sqlite'){
				$query = str_replace('\'\'', '\\\'', $query);
			}
			$query = trim($query);
			if(!empty($query)){
				$db->exec($query . ';');
			}
		}
		
		// Add admin user
		$query = $db->prepare('INSERT INTO users (user_user, user_pass, user_name, user_email, user_created, user_photo_count) VALUES (?, ?, ?, ?, ?, ?);');
		
		$query->execute(array($_POST['install_user'], sha1($_POST['install_pass']), $_POST['install_name'], $_POST['install_email'], date('Y-m-d H:i:s'), 0));
		// Add admin thumbnails
		
		$query = $db->prepare('INSERT INTO sizes (size_title, size_label, size_height, size_width, size_type, size_append) VALUES (?, ?, ?, ?, ?, ?);');
		$query->execute(array('Dashboard (L)', 'admin',  600, 600, 'scale', '_admin'));
		$query->execute(array('Dashboard (S)', 'square', 80, 80, 'fill', '_sq'));
		
		// Add default theme
		
		$query = $db->prepare('INSERT INTO themes (theme_uid, theme_title, theme_default, theme_build, theme_version, theme_folder, theme_creator, theme_creator_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?);');
		$query->execute(array('cc3a6ff5921c68f0887b28a1982e13d09747feb1', 'Basic', 1, 1, '1.0', 'basic', 'Alkaline', 'http://www.alkalineapp.com/'));
	}
}


define('TAB', 'Installation');
define('TITLE', 'Alkaline Installation');

if((@$_POST['install'] == 'Install') and ($alkaline->isNotification() === false)){
	require_once(PATH . ADMIN . 'includes/header.php');
	
	?>
	
	<p class="large"><strong>Almost there.</strong> Copy and paste the text below into a text editor and save it as &#8220;config.php&#8221; to your hard disk. Then upload this file (overwriting the file that is already there) to your Alkaline directory to complete your installation.</p>
	
	<textarea style="height: 30em;" class="code"><?php echo $config; ?></textarea>
	
	<?php
}
else{
	require_once(PATH . ADMIN . 'includes/header.php');
	
	?>

	<p class="large"><strong>Welcome to Alkaline.</strong> You&#8217;re halfway there, simply complete the fields below and follow the remaining instructions.</p>

	<form input="" method="post">
		<h3>Your Server OS</h3>
	
		<p>Not sure? Keep the default option&#8212;it&#8217;ll work in the majority of cases.</p>
	
		<table>
			<tr>
				<td class="right middle">
					<input type="radio" name="install_server" id="install_server_x" value="x" <?php if(@$_POST['install_server'] != 'win'){ echo 'checked="checked"'; } ?> />
				</td>
				<td>
					<label for="install_server_x"><strong>Linux, BSD, OS X Server, or similar</strong></label>
				</td>
			</tr>
			<tr>
				<td class="right middle">
					<input type="radio" name="install_server" id="install_server_win" value="win" <?php if(@$_POST['install_server'] == 'win'){ echo 'checked="checked"'; } ?> />
				</td>
				<td>
					<label for="install_server_win" style="font-weight: normal;">Windows&#0174; Server 2008 or similar</label>
				</td>
			</tr>
		</table>
		
		<h3>Your File Structure</h3>
	
		<p>Where did you install Alkaline relative to your domain name?</p>
	
		<table>
			<tr>
				<td class="right pad">
					<label for="install_base">Base path:</label>
				</td>
				<td>
					<input type="text" name="install_base" id="install_base" class="s" value="<?php preg_match_all('#(?:/)?(.*)/(?:.*)admin/install#si', $_SERVER['SCRIPT_NAME'], $matches); if(!empty($matches[1][0])){ echo $matches[1][0] . '/'; } ?>" /> <span class="quiet">(optional)</span><br />
					<span class="quiet">For example, http://www.yourdomain.com/photos/ would be <strong>photos/</strong></span>
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
					<span class="quiet">Defaults to /assets/alkaline.db. Your database file be writable (CHMOD 777).</span>
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
			<tr>
				<td style="text-align: right;">
					<input type="checkbox" name="install_welcome" id="install_welcome" value="1" checked="checked">
				</td>
				<td>
					<label for="install_welcome" style="font-weight: normal;">Send me a welcome email containing my username and password.</label>
				</td>
			</tr>
		</table>
		
		<h3>Install Alkaline</h3>
	
		<p>This may take several minutes, please be patient. Do not interrupt the process by stopping the page from loading or closing your Web browser.</p><p><input type="submit" name="install" value="Install" onclick="this.disabled=true;this.value='Installing...'" /></p>
	</form>

	<?php
}

require_once(PATH . ADMIN . 'includes/footer.php');

?>