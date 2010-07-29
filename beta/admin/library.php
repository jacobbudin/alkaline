<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$photo_ids = new Find();
$photo_ids->page(1,100);
$photo_ids->exec();

$photos = new Photo($photo_ids->photo_ids);
$photos->getImgUrl('square');

define('TITLE', 'Alkaline Library');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="module" class="container">
	<h1>Library</h1>
	<p>Your library contains <?php echo $photo_ids->photo_count_result; ?> photos.</p>
</div>

<div id="library" class="container">
	
	<div id="features" class="span-23 last">
		<h2>Features</h2>
		<div class="span-6">
			<h3>Tags</h3>
			<img src="/images/icons/tag.png" alt="" />
			<ul>
				<li><a href="<?php echo BASE . ADMIN; ?>tags/">View tag cloud</a></li>
				<li><a href="<?php echo BASE . ADMIN; ?>tags/edit/">Bulk edit tags</a></li>
				<li><a href="<?php echo BASE . ADMIN; ?>search/untagged/">Find untagged photos</a></li>
			</ul>
		</div>
		<div class="span-5">
			<h3>Piles</h3>
			<img src="/images/icons/piles.png" alt="" />
			<ul>
				<li><a href="<?php echo BASE . ADMIN; ?>piles/">View piles</a></li>
				<li><a href="<?php echo BASE . ADMIN; ?>stats/piles/">Analyze pile stats</a></li>
				<li><a href="<?php echo BASE . ADMIN; ?>piles/build/">Build new pile</a></li>
			</ul>
		</div>
		<div class="span-6">
			<h3>Rights</h3>
			<img src="/images/icons/rights.png" alt="" />
			<ul>
				<li><a href="<?php echo BASE . ADMIN; ?>rights/">View rights sets</a></li>
				<li><a href="<?php echo BASE . ADMIN; ?>rights/merge/">Merge rights sets</a></li>
				<li><a href="<?php echo BASE . ADMIN; ?>rights/add/">Add new rights set</a></li>
			</ul>
		</div>
		<div class="span-6 last">
			<h3>Comments</h3>
			<img src="/images/icons/comment.png" alt="" />
			<ul>
				<li><a href="<?php echo BASE . ADMIN; ?>comments/">View comments</a></li>
				<li><a href="<?php echo BASE . ADMIN; ?>comments/review/">Review new comments</a> (0)</li>
				<li><a href="<?php echo BASE . ADMIN; ?>comments/reply/">Reply to comments</a></li>
			</ul>
		</div>
	</div>

	<form action="<?php echo BASE . ADMIN; ?>search/" id="search" method="post">
		<h2>Search</h2>
		<input type="text" name="search" style="width: 50em; font-size: .9em; margin-left: 0;" /> <input type="submit" value="Search" /><br />
		<div>
			<span class="switch">&#9656;</span> <a href="#" class="show" style="line-height: 2.5em;">Show options</a><br />
			<div class="reveal">
				<table>
					<tr>
						<td class="right">Tags:</td>
						<td class="quiet">
							<input type="text" name="tags" style="width: 30em;" /><br />
							Tip: Use the boolean operators AND, OR, and NOT.
						</td>
					</tr>
					<tr>
						<td class="right">Rights set:</td>
						<td class="quiet">
							<?php echo $alkaline->showRights('rights'); ?>
						</td>
					</tr>
					<tr>
						<td class="right">Date taken:</td>
						<td class="quiet">
							between <input type="text" class="date" name="taken_begin" style="width: 10em;" />
							and <input type="text" class="date" name="taken_end" style="width: 10em;" />
						</td>
					</tr>
					<tr>
						<td class="right">Date uploaded:</td>
						<td class="quiet">
							between <input type="text" class="date" name="uploaded_begin" style="width: 10em;" />
							and <input type="text" class="date" name="uploaded_end" style="width: 10em;" />
						</td>
					</tr>
					<tr>
						<td class="right">Location:</td>
						<td class="quiet">
							within
							<select name="location_proximity">
								<option>10</option>
								<option>25</option>
								<option>100</option>
								<option>500</option>
							</select>
							miles of 
							<input type="text" name="location" style="width: 15em;" />
						</td>
					</tr>
					<tr>
						<td class="right" style="padding-top: 7px;">Primary color:</td>
						<td>
							<select name="color_type">
								<option value="is">is</option>
								<option value="is_not">is not</option>
							</select>
							<select name="color">
								<option></option>
								<option value="grey">grey</option>
								<option value="blue">blue</option>
								<option value="red">red</option>
								<option value="yellow">yellow</option>
								<option value="green">green</option>
								<option value="purple">purple</option>
								<option value="orange">orange</option>
							</select>
						</td>
					</tr>
					<tr>
						<td class="right">Views:</td>
						<td>
							<select name="views_operator">
								<option value="greater">&#8805;</option>
								<option value="less">&#8804;</option>
								<option value="equal">&#0061;</option>
							</select>
							<input type="text" name="views" style="width: 4em;" />
						</td>
					</tr>
					<tr>
						<td class="right" style="padding-top: 7px;">Orientation:</td>
						<td class="quiet">
							<select name="orientation">
								<option value="">All</option>
								<option value="portrait">Portrait</option>
								<option value="landscape">Landscape</option>
								<option value="square">Square</option>
							</select>
						</td>
					</tr>
					<tr>
						<td class="right" style="padding-top: 7px;">Privacy level:</td>
						<td class="quiet">
							<select name="privacy">
								<option value="">All</option>
								<option value="public">Public</option>
								<option value="protected">Protected</option>
								<option value="private">Private</option>
							</select>
						</td>
					</tr>
					<tr>
						<td class="right" style="padding-top: 7px;">Publication status:</td>
						<td class="quiet">
							<select name="published">
								<option value="">All</option>
								<option value="published">Published</option>
								<option value="unpublished">Unpublished</option>
							</select>
						</td>
					</tr>
					<tr>
						<td class="right" style="padding-top: 7px;">Sort results by:</td>
						<td>
							<select name="sort">
								<option value="taken">Date taken</option>
								<option value="uploaded">Date uploaded</option>
								<option value="updated">Date last updated</option>
								<option value="title">Photo title</option>
								<option value="views">Views</option>
							</select>
							<select name="sort_direction">
								<option value="DESC">Descending</option>
								<option value="ASC">Ascending</option>
							</select>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</form>
	
	<div style="float: right; margin-top: 2em;"><a href="" class="nu"><span class="button">&#0043;</span>Upload photos</a></div>
	
	<h2>Photos</h2>

	<hr />
	
	<?php
	
	foreach($photos->photos as $photo){
		?>
		<a href="<?php echo BASE . ADMIN . 'photo/' . $photo['photo_id']; ?>/">
			<img src="<?php echo $photo['photo_src_square']; ?>" alt="" title="<?php echo $photo['photo_title']; ?>" />
		</a>
		<?php
	}
	
	?>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>