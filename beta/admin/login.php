<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

@$username = strip_tags($_POST['login_user']);
@$password = strip_tags($_POST['login_pass']);
@$remember = strip_tags($_POST['login_remember']);

if($remember == 1){ $remember = true; }

if($user->perm()){
	header('Location: ' . LOCATION . BASE . ADMIN . 'dashboard/');
	exit();
}

if(!empty($username) or !empty($password)){
	if($user->auth($username, $password, $remember)){
		header('Location: ' . LOCATION . BASE . ADMIN . 'dashboard/');
		exit();
	}
	else{
		$alkaline->addNotification('Your username or password is invalid. Please try again.', 'error');
	}
}


define('TITLE', 'Alkaline Login');
define('EMBED_CSS', '.container{ position: relative; top: 18%; width: 590px; }');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="login" class="container">
	<form input="" method="post">
		<table>
			<tr>
				<td style="width: 40%; text-align: right;">
					<label for="login_user">Username:</label>
				</td>
				<td>
					<input type="text" name="login_user" id="login_user" />
				</td>
			</tr>
			<tr>
				<td style="text-align: right;">
					<label for="login_pass">Password:</label>
				</td>
				<td>
					<input type="password" name="login_pass" id="login_pass" />
				</td>
			</tr>
			<tr>
				<td style="text-align: right;">
					<input type="checkbox" name="login_remember" id="login_remember" value="1" checked="checked">
				</td>
				<td style="padding-top: .65em;">
					<label for="login_remember">Remember me on this computer.</label>
				</td>
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