<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

define('TITLE', 'Alkaline Settings');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="module" class="container">
	<h1>Customize</h1>
	<p></p>
</div>

<div id="customize" class="container">
	
	<div id="features" class="span-23 last prepend-top">
		<h2>Features</h2>
		<div class="span-7 append-1">
			<h3>Themes</h3>
			<img src="/images/icons/themes.png" alt="" />
			<ul>
				<li><a href="<?php echo BASE . ADMIN; ?>themes/">View themes</a></li>
				<li><a href="http://www.alkalineapp.com/">Get more themes</a></li>
				<li><a href="<?php echo BASE . ADMIN; ?>themes/install/">Install new theme</a></li>
			</ul>
		</div>
		<div class="span-7 append-1">
			<h3>Extensions</h3>
			<img src="/images/icons/extensions.png" alt="" />
			<ul>
				<li><a href="<?php echo BASE . ADMIN; ?>extensions/">View extensions</a></li>
				<li><a href="http://www.alkalineapp.com/">Get more extensions</a></li>
				<li><a href="<?php echo BASE . ADMIN; ?>extensions/install/">Install new extension</a></li>
			</ul>
		</div>
		<div class="span-6 last">
			<h3>System</h3>
			<img src="/images/icons/settings.png" alt="" />
			<ul>
				<li><a href="<?php echo BASE . ADMIN; ?>configuration/">Change configuration</a></li>
				<li><a href="<?php echo BASE . ADMIN; ?>preferences/">Edit personal preferences</a></li>
				<li><a href="<?php echo BASE . ADMIN; ?>maintenance/">Perform maintenance</a></li>
			</ul>
		</div>
	</div>
	
	<div class="span-23 last">
		<div class="span-11 colborder">
			<div style="float: right;"><a href="<?php echo BASE . ADMIN; ?>guests/add/" class="nu"><span class="button">&#0043;</span>Add guest</a></div>
			<h2 style="margin-top: 0;">Guests</h2>
			<table>
				<tr>
					<th>Title</th>
					<th class="center">Views</th>
					<th>Last login</th>
				</tr>
				<tr>
					<td><strong><a href="">Models</a></strong></td>
					<td class="center">1,034</td>
					<td>Yesterday, 1:33 p.m.</td>
				</tr>
				<tr>
					<td><strong><a href="">Parents</a></strong></td>
					<td class="center">4,056</td>
					<td>Today, 1:13 p.m.</td>
				</tr>
			</table>
		</div>
		<div class="span-11 last">
			<div style="float: right;"><a href="<?php echo BASE . ADMIN; ?>users/add/" class="nu"><span class="button">&#0043;</span>Add user</a></div>
			<h2 style="margin-top: 0;">Users</h2>
			<table>
				<tr>
					<th>Name</th>
					<th class="center">Photos</th>
					<th>Last login</th>
				</tr>
				<tr>
					<td><strong><a href="">Alex</a></strong></td>
					<td class="center"><a href="">133</a></td>
					<td>Yesterday, 9:33 p.m.</td>
				</tr>
				<tr>
					<td><strong><a href="">Brian</a></strong></td>
					<td class="center"><a href="">12</a></td>
					<td>Yesterday, 2:33 p.m.</td>
				</tr>
			</table>
		</div>
	</div>

</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>