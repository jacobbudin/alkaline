<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$alkaline->setCallback();

$photo_ids = new Find();
$photo_ids->clearMemory();
$photo_ids->page(1,100);
$photo_ids->exec();

$photos = new Photo($photo_ids->photo_ids);
$photos->getImgUrl('square');

define('TAB', 'library');
define('TITLE', 'Alkaline Library');
require_once(PATH . ADMIN . 'includes/header.php');

?>


<h1>Search</h1>

<div class="span-24 last">
	<div class="span-14 append-1">
		<form action="<?php echo BASE . ADMIN; ?>search/" method="post">
			<p style="margin-bottom: 0;">
				<input type="search" name="search" style="width: 30em; margin-left: 0;" results="10" /> <input type="submit" value="Search" />
			</p>

			<p>
				<span class="switch">&#9656;</span> <a href="#" class="show" style="line-height: 2.5em;">Show options</a>
			</p>

			<table class="reveal">
				<tr>
					<td class="right pad"><label for="tags">Tags:</label></td>
					<td class="quiet">
						<input type="text" id="tags" name="tags" style="width: 30em;" /><br />
						Tip: Use the boolean operators AND, OR, and NOT.
					</td>
				</tr>
				<tr>
					<td class="right middle"><label for="rights">Rights set:</label></td>
					<td class="quiet">
						<?php echo $alkaline->showRights('rights'); ?>
					</td>
				</tr>
				<tr>
					<td class="right middle"><label>Date taken:</label></td>
					<td class="quiet">
						between <input type="text" class="date" name="taken_begin" style="width: 10em;" />
						and <input type="text" class="date" name="taken_end" style="width: 10em;" />
					</td>
				</tr>
				<tr>
					<td class="right middle"><label>Date uploaded:</label></td>
					<td class="quiet">
						between <input type="text" class="date" name="uploaded_begin" style="width: 10em;" />
						and <input type="text" class="date" name="uploaded_end" style="width: 10em;" />
					</td>
				</tr>
				<tr>
					<td class="right middle"><label>Location:</label></td>
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
					<td class="right middle"><label for="color">Dominant color:</label></td>
					<td>
						<select id="color" name="color">
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
					<td class="right middle"><label>Views:</label></td>
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
					<td class="right middle"><label for="orientation">Orientation:</label></td>
					<td class="quiet">
						<select id="orientation" name="orientation">
							<option value="">All</option>
							<option value="portrait">Portrait</option>
							<option value="landscape">Landscape</option>
							<option value="square">Square</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="right middle"><label for="privacy">Privacy level:</label></td>
					<td class="quiet">
						<select id="privacy" name="privacy">
							<option value="">All</option>
							<option value="public">Public</option>
							<option value="protected">Protected</option>
							<option value="private">Private</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="right middle"><label for="published">Publication status:</label></td>
					<td class="quiet">
						<select id="published" name="published">
							<option value="">All</option>
							<option value="published">Published</option>
							<option value="unpublished">Unpublished</option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="right middle"><label>Sort results by:</label></td>
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
		</form>
	</div>
	<div class="span-13 last">
		
	</div>
</div>

<h1>Photos (<?php echo $photo_ids->photo_count_result; ?>)</h1>

<p>
	<?php

	foreach($photos->photos as $photo){
		?>
		<a href="<?php echo BASE . ADMIN . 'photo/' . $photo['photo_id']; ?>/">
			<img src="<?php echo $photo['photo_src_square']; ?>" alt="" title="<?php echo $photo['photo_title']; ?>" class="frame" />
		</a>
		<?php
	}
	
	?>
</p>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>