<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

Find::clearMemory();
	
define('TAB', 'library');
define('TITLE', 'Alkaline Search');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<h1>Search</h1>

<form action="<?php echo BASE . ADMIN; ?>results/" method="post">
	<p>
		<input type="text" name="search" style="width: 50em; margin-left: 0;" /> <input type="submit" value="Search" />
	</p>

	<p>
		<span class="switch">&#9656;</span> <a href="#" class="show" style="line-height: 2.5em;">Show options</a>
	</p>

	<table class="reveal">
		<tr>
			<td class="right pad">Tags:</td>
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
			<td class="right" style="padding-top: 7px;">Dominant color:</td>
			<td>
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
</form>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>