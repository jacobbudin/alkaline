<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

if(empty($_GET) and empty($_POST)){
	
	define('TITLE', 'Alkaline Search');
	require_once(PATH . ADMIN . 'includes/header.php');
	require_once(PATH . ADMIN . 'includes/library.php');

	?>
	
	<h1>Search</h1>

	<form action="<?php echo BASE . ADMIN; ?>search/" method="post">
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
	</form>
	
	<?php

	require_once(PATH . ADMIN . 'includes/footer.php');
	
}
else{
	$photo_ids = new Find();
	$photo_ids->page(1, 100);
	
	// SANITIZE SEARCH QUERY
	$_GET = array_map('strip_tags', $_GET);
	$_POST = array_map('strip_tags', $_POST);

	// Smart search
	if(!empty($_GET['smart'])){
		$photo_ids->_smart($_GET['smart']);
	}

	// Title and description
	if(!empty($_POST['search'])){
		$photo_ids->_search($_POST['search']);
	}

	// Tags
	if(!empty($_POST['tags'])){
		$photo_ids->_tags($_POST['tags']);
	}

	// Rights set
	if(!empty($_POST['rights'])){
		$photo_ids->_rights(intval($_POST['rights']));
	}

	// Date taken
	if(!empty($_POST['taken_begin']) or !empty($_POST['taken_end'])){
		$photo_ids->_taken($_POST['taken_begin'], $_POST['taken_end']);
	}

	// Date uploaded
	if(!empty($_POST['uploaded_begin']) or !empty($_POST['uploaded_end'])){
		$photo_ids->_uploaded($_POST['uploaded_begin'], $_POST['uploaded_end']);
	}

	// Location
	if(!empty($_POST['location'])){
		// NA
	}

	// Primary color
	if(!empty($_POST['color'])){
		switch($_POST['color']){
			case 'grey':
				break;
			case 'blue':
				break;
			case 'red':
				break;
			case 'yellow':
				break;
			case 'green':
				break;
			case 'purple':
				break;
			case 'orange':
				break;
		}
		switch($_POST['color_type']){
			case 'is':
				break;
			case 'is_not':
				break;
		}
	}

	// Views
	if(!empty($_POST['views'])){
		switch($_POST['views_operator']){
			case 'greater':
				$photo_ids->_views($_POST['views'], null);
				break;
			case 'less':
				$photo_ids->_views(null, $_POST['views']);
				break;
			case 'equal':
				$photo_ids->_views($_POST['views'], $_POST['views']);
				break;
		}
	}

	// Orientation
	if(!empty($_POST['orientation'])){
		switch($_POST['orientation']){
			case 'portrait':
				$photo_ids->ratio(null, 1);
				break;
			case 'landscape':
				$photo_ids->ratio(1, null);
				break;
			case 'square':
				$photo_ids->ratio(1, 1);
				break;
		}
	}

	// Privacy
	if(!empty($_POST['privacy'])){
		switch($_POST['privacy']){
			case 'public':
				$photo_ids->_privacy(1);
				break;
			case 'protected':
				$photo_ids->_privacy(2);
				break;
			case 'private':
				$photo_ids->_privacy(3);
				break;
		}
	}

	// Published
	if(!empty($_POST['published'])){
		switch($_POST['published']){
			case 'published':
				$photo_ids->_published(true);
				break;
			case 'unpublished':
				$photo_ids->_published(false);
				break;
		}
	}

	// Sort
	if(!empty($_POST['sort'])){
		switch($_POST['sort']){
			case 'taken':
				break;
			case 'uploaded':
				break;
			case 'updated':
				break;
			case 'title':
				break;
			case 'views':
				break;
		}
	}
	
	$photo_ids->exec();
	
	$photo_ids->getMemory();

	$photos = new Photo($photo_ids->photo_ids);
	$photos->getImgUrl('square');
	
	define('TITLE', 'Alkaline Search Results');
	require_once(PATH . ADMIN . 'includes/header.php');
	require_once(PATH . ADMIN . 'includes/library_wide.php');

	?>

	<div style="float: right;"><a href="" class="nu"><span class="button">&#0131;</span>Build pile</a> &#160; <a href="" class="nu"><span class="button">&#0187;</span>View comments</a></div>

	<h1>Search Results (<?php echo $photo_ids->photo_count_result; ?>)</h1>

	<?php

	if($photo_ids->photo_count_result > 0){
		?>
		<p>
		<?php
		for($i = 0; $i < $photos->photo_count; ++$i){
			?>
			<a href="<?php echo BASE . ADMIN . 'photo/' . $photos->photos[$i]['photo_id']; ?>/"><img src="<?php echo $photos->photos[$i]['photo_src_square']; ?>" alt="" title="<?php echo $photos->photos[$i]['photo_title']; ?>" class="frame" /></a>
			<?php
		}
		?>
		</p>
		<?php
	}

	require_once(PATH . ADMIN . 'includes/footer.php');
}

?>