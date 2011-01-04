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

@$username = strip_tags($_POST['login_user']);
@$password = strip_tags($_POST['login_pass']);
@$remember = strip_tags($_POST['login_remember']);

if($remember == 1){ $remember = true; }

if($user->perm()){
	header('Location: ' . LOCATION . BASE . ADMIN . 'dashboard' . URL_CAP);
	exit();
}

if(!empty($username) or !empty($password)){
	if($user->auth($username, $password, $remember)){
		// Check for updates
		$latest = @$alkaline->boomerang('latest');
		if($latest['build'] > Alkaline::build){
			$alkaline->addNotification('A new version of Alkaline is available. Learn more and download the update at <a href="http://www.alkalineapp.com/">alkalineapp.com</a>.', 'notice');
		}
		
		header('Location: ' . LOCATION . BASE . ADMIN . 'dashboard' . URL_CAP);
		exit();
	}
	else{
		$alkaline->addNotification('Your username or password is invalid. Please try again.', 'error');
	}
}

define('TAB', 'Login');
define('TITLE', 'Alkaline Login');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div class="span-24 last">
	<form input="" method="post">
		<table>
			<tr>
				<td class="right middle">
					<label for="login_user">Username:</label>
				</td>
				<td>
					<input type="text" name="login_user" id="login_user" class="s" />
				</td>
			</tr>
			<tr>
				<td class="right middle">
					<label for="login_pass">Password:</label>
				</td>
				<td>
					<input type="password" name="login_pass" id="login_pass" class="s" />
				</td>
			</tr>
			<tr>
				<td style="text-align: right;">
					<input type="checkbox" name="login_remember" id="login_remember" value="1" checked="checked">
				</td>
				<td style="padding-top: .65em;">
					<label for="login_remember">Remember me on this computer.</label>
				</td>
			</tr>
			<tr>
				<td></td>
				<td>
					<input type="submit" value="Login" />
				</td>
			</tr>
		</table>
	</form>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>