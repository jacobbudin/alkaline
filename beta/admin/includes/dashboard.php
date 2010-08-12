<div id="primary" class="column">
	<a href="<?php echo BASE . ADMIN; ?>dashboard/"><img src="/images/alkaline.png" alt="Alkaline" class="bumper" /></a><br /><br />
	<ul id="navigation">
		<li><a href="<?php echo BASE . ADMIN; ?>dashboard/">Dashboard</a><img src="/images/pointer.png" alt="" /></li>
		<li><a href="<?php echo BASE . ADMIN; ?>library/">Library</a><img src="/images/pointer.png" alt="" class="hide" /></li>
		<li><a href="<?php echo BASE . ADMIN; ?>features/">Features</a><img src="/images/pointer.png" alt="" class="hide" /></li>
		<li><a href="<?php echo BASE . ADMIN; ?>settings/">Settings</a><img src="/images/pointer.png" alt="" class="hide" /></li>
		<li><a href="http://www.alkalineapp.com/help/" target="_blank">Help</a><img src="/images/block_cyan.png" alt="" class="hide" /></li>
		<li class="logout"><a href="">Logout</a><img src="/images/block_red.png" alt="" /></li>
	</ul>
</div>
<div id="secondary" class="column">
	<img src="/images/empty/64.png" alt="" class="bumper" /><br /><br />
	
	<img src="/images/iconblocks/dashboard.png" alt="" class="icon_block" />
	
	<h2>Hello</h2>
	<p>Welcome back, <a href=""><?php echo $user->user['user_name']; ?></a>!</p><p><?php echo ($user->user['user_last_login']) ? 'You last logged in on ' . $alkaline->formatTime($user->user['user_last_login'], 'l, F j \a\t g:i a') : ''; ?></p>
	
	<img src="/images/iconblocks/compass.png" alt="" class="icon_block" />
	
	<h2>Vitals</h2>
	<table id="vitals">
		<?php
		$tables = $alkaline->getInfo();
		foreach($tables as $table){
			echo '<tr><td class="right">' . number_format($table['count']) . '</td><td><a href="' . BASE . ADMIN . $table['table'] . '/">' . $table['display'] . '</a></td></tr>';
		}
		?>
	</table>
	
	<img src="/images/iconblocks/alkaline.png" alt="" class="icon_block" />
	
	<h2>Alkaline</h2>
	<p>You are running Alkaline <?php echo Alkaline::version; ?>.</p>
</div>
<div id="tertiary" class="column">
	<img src="/images/empty/64.png" alt="" class="bumper" /><br /><br />