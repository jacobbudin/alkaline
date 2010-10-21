<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;

// Diagnostic checks

if($alkaline->checkPerm(PATH . SHOEBOX) != '0777'){
	$alkaline->addNotification('Assets folder is not writable.', 'error');
}
if($alkaline->checkPerm(PATH . SHOEBOX) != '0777'){
	$alkaline->addNotification('Photos folder is not writable.', 'error');
}
if($alkaline->checkPerm(PATH . SHOEBOX) != '0777'){
	$alkaline->addNotification('Shoebox folder is not writable.', 'error');
} 

// Configuration setup

if(@$_POST['install'] == 'Install'){
	if(!$config = file_get_contents(PATH . ASSETS . 'config-default.php', false)){
		$alkaline->addNotification('Cannot find configuration file.', 'error');
	}
	
	if($_POST['install_server'] == 'win'){
		$config = $alkaline->replaceVar('$server_type', '$server_type = "win";', $config);
	}
	
	if($_POST['install_db_type'] == 'mysql'){
		if(empty($_POST['install_db_name'])){
			$alkaline->addNotification('A database name is required for MySQL.', 'error');
		}
		if(empty($_POST['install_db_user'])){
			$alkaline->addNotification('A database username is required for MySQL.', 'error');
		}
		if(empty($_POST['install_db_pass'])){
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
		$config = $alkaline->replaceVar('$db_user', '$db_user = \'' . $_POST['install_db_user'] . '\';', $config);
		$config = $alkaline->replaceVar('$db_pass', '$db_pass = \'' . $_POST['install_db_pass'] . '\';', $config);
	}
	elseif($_POST['install_db_type'] == 'sqlite'){
		if(!empty($_POST['install_db_file'])){
			$path = $_POST['install_db_file'];
		}
		else{
			$path = PATH . ASSETS . 'alkaline.db';
		}
		
		$dsn = $alkaline->correctWinPath('sqlite:' . $path);
		
		$config = $alkaline->replaceVar('$db_dsn', '$db_dsn = \'' . $dsn . '\';', $config);
		$config = $alkaline->replaceVar('$db_type', '$db_type = \'sqlite\';', $config);
		
		if($alkaline->checkPerm($path) != '0777'){
			$alkaline->addNotification('Your SQLite database is not writable.', 'error');
		}
	}
	elseif($_POST['install_db_type'] == 'pgsql'){
		if(empty($_POST['install_db_name'])){
			$alkaline->addNotification('A database name is required for PostgreSQL.', 'error');
		}
		if(empty($_POST['install_db_user'])){
			$alkaline->addNotification('A database username is required for PostgreSQL.', 'error');
		}
		if(empty($_POST['install_db_pass'])){
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
		$config = $alkaline->replaceVar('$db_user', '$db_user = \'' . $_POST['install_db_user'] . '\';', $config);
		$config = $alkaline->replaceVar('$db_pass', '$db_pass = \'' . $_POST['install_db_pass'] . '\';', $config);
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
	
	// Import default SQL
	
	// Add admin user
	
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
					<label for="install_user">Database table prefix:</label>
				</td>
				<td>
					<input type="text" name="install_db_prefix" id="install_user" class="xs" /> <span class="quiet">(optional)</span>
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
					<input type="text" name="install_db_name" id="install_db_name" class="s" />
				</td>
			</tr>
			<tr>
				<td class="right pad">
					<label for="install_user">Database username:</label>
				</td>
				<td>
					<input type="text" name="install_db_user" id="install_user" class="s" />
				</td>
			</tr>
			<tr>
				<td class="right pad">
					<label for="install_db_pass">Database password:</label>
				</td>
				<td>
					<input type="text" name="install_db_pass" id="install_db_pass" class="s" />
				</td>
			</tr>
			<tr>
				<td class="right pad">
					<label for="install_db_host">Database host:</label>
				</td>
				<td>
					<input type="text" name="install_db_host" id="install_db_host" class="m" /> <span class="quiet">(optional)</span>
				</td>
			</tr>
			<tr>
				<td class="right pad">
					<label for="install_db_port">Database port:</label>
				</td>
				<td>
					<input type="text" name="install_db_port" id="install_db_port" class="xs" /> <span class="quiet">(optional)</span>
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
					<input type="text" name="install_db_file" id="install_db_file" class="m" /> <span class="quiet">(optional)</span><br />
					<span class="quiet">Defaults to /assets/alkaline.db. Must be writable (777).</span>
				</td>
			</tr>
		</table>
	
		<h3>Your Admin Account</h3>
	
		<p>Don&#8217;t worry, you can change these details later through your Alkaline Dashboard.</p>
	
		<table>
			<tr>
				<td class="right middle">
					<label for="install_user">Username:</label>
				</td>
				<td>
					<input type="text" name="install_user" id="install_user" class="s" />
				</td>
			</tr>
			<tr>
				<td class="right middle">
					<label for="install_pass">Password:</label>
				</td>
				<td>
					<input type="password" name="install_pass" id="install_pass" class="s" />
				</td>
			</tr>
			<tr>
				<td class="right middle">
					<label for="install_email">Email:</label>
				</td>
				<td>
					<input type="text" name="install_email" id="install_email" class="m" />
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
	
		<p>This may take a few moments, please be patient. Do not interrupt the process by stopping the page from loading or closing your Web browser.</p><p><input type="submit" name="install" value="Install" /></p>
	</form>

	<?php
}

require_once(PATH . ADMIN . 'includes/footer.php');

?>