<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$photo_ids = new Find();
$photo_ids->page(1,100);

// MANAGE SEARCH QUERY
array_map('strip_tags', $_GET);
array_map('strip_tags', $_POST);

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
	// NA
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
			break;
		case 'landscape':
			break;
		case 'square':
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

$photos = new Photo($photo_ids->photo_ids);
$photos->getImgUrl('square');

define('TITLE', 'Alkaline Search');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="module" class="container">
	<h1>Search</h1>
	<p>Your query matched <?php $alkaline->echoCount($photo_ids->photo_count_result, 'photo'); ?>. <a href="<?php echo BASE . ADMIN; ?>library/">Start over.</a></p>
</div>

<?php

if($photo_ids->photo_count_result > 0){
	?>
	<div id="results" class="container">
	
		<div style="float: right;"><a href="" class="nu"><span class="button">&#0131;</span>Build pile</a> &#160; <a href="" class="nu"><span class="button">&#0187;</span>View comments</a></div>
	
		<h2>Results</h2>

		<hr />
	
		<?php
		for($i = 0; $i < $photos->photo_count; ++$i){
			?>
			<a href="<?php echo BASE . ADMIN . 'photo/' . $photos->photos[$i]['photo_id']; ?>/">
				<img src="<?php echo $photos->photos[$i]['photo_src_square']; ?>" alt="" title="<?php echo $photos->photos[$i]['photo_title']; ?>" />
			</a>
			<?php
		}
		?>
	
	</div>
	<?php
}

require_once(PATH . ADMIN . 'includes/footer.php');

?>