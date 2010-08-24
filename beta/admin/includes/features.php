<div id="primary" class="column">
	<a href="<?php echo BASE . ADMIN; ?>dashboard/"><img src="/images/alkaline.png" alt="Alkaline" class="bumper" /></a><br /><br />
	<ul id="navigation">
		<li><a href="<?php echo BASE . ADMIN; ?>dashboard/">Dashboard</a><img src="/images/pointer.png" alt="" class="hide" /></li>
		<li><a href="<?php echo BASE . ADMIN; ?>library/">Library</a><img src="/images/pointer.png" alt="" class="hide" /></li>
		<li><a href="<?php echo BASE . ADMIN; ?>features/">Features</a><img src="/images/pointer.png" alt="" /></li>
		<li><a href="<?php echo BASE . ADMIN; ?>settings/">Settings</a><img src="/images/pointer.png" alt="" class="hide" /></li>
		<li><a href="http://www.alkalineapp.com/help/" target="_blank">Help</a><img src="/images/block_cyan.png" alt="" class="hide" /></li>
		<li class="logout"><a href="">Logout</a><img src="/images/block_red.png" alt="" /></li>
	</ul>
</div>
<div id="secondary" class="column">
	<img src="/images/empty/64.png" alt="" class="bumper" /><br /><br />
	
	<img src="/images/iconblocks/tags.png" alt="" class="icon_block" />
	
	<h2>Tags</h2>
	<ul>
		<li><a href="<?php echo BASE . ADMIN; ?>tags/">View tag cloud</a></li>
		<li><a href="<?php echo BASE . ADMIN; ?>search/untagged/">Find untagged photos</a></li>
	</ul>
	
	<img src="/images/iconblocks/comments.png" alt="" class="icon_block" />
	
	<h2>Comments</h2>
	<ul>
		<li><a href="<?php echo BASE . ADMIN; ?>comments/">View comments</a></li>
		<li><a href="<?php echo BASE . ADMIN; ?>comments/unpublished/">Find unpublished comments</a></li>
	</ul>
	
	<img src="/images/iconblocks/piles.png" alt="" class="icon_block" />

	<h2>Piles</h2>
	<ul>
		<li><a href="<?php echo BASE . ADMIN; ?>piles/">View piles</a></li>
		<li><a href="<?php echo BASE . ADMIN; ?>piles/build/">Build new pile</a></li>
	</ul>
	
	<img src="/images/iconblocks/pages.png" alt="" class="icon_block" />
	
	<h2>Pages</h2>
	<ul>
		<li><a href="<?php echo BASE . ADMIN; ?>pages/">View pages</a></li>
		<li><a href="<?php echo BASE . ADMIN; ?>pages/create/">Create new page</a></li>
	</ul>
	
	<img src="/images/iconblocks/rights.png" alt="" class="icon_block" />

	<h2>Rights</h2>
	<ul>
		<li><a href="<?php echo BASE . ADMIN; ?>rights/">View rights sets</a></li>
		<li><a href="<?php echo BASE . ADMIN; ?>rights/add/">Add new rights set</a></li>
	</ul>
</div>
<div id="tertiary" class="column">
	<img src="/images/empty/64.png" alt="" class="bumper" /><br /><br />
	
	<?php $alkaline->viewNotification(); ?>