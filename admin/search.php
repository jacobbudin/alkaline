<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$alkaline->setCallback();

$photo_ids = new Find();
$photo_ids->page(1, 100);

// SANITIZE SEARCH QUERY
$_GET = array_map('strip_tags', $_GET);
$_POST = array_map('strip_tags', $_POST);

if(empty($_GET) and empty($_POST)){
	$photo_ids->memory();
}

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
	$photo_ids->_location($_POST['location'], $_POST['location_proximity']);
}

// Primary color
if(!empty($_POST['color'])){
	switch($_POST['color']){
		case 'blue':
			$photo_ids->_hsl(170, 235, 1, 100, 1, 100);
			break;
		case 'red':
			$photo_ids->_hsl(345, 10, 1, 100, 1, 100);
			break;
		case 'yellow':
			$photo_ids->_hsl(50, 75, 1, 100, 1, 100);
			break;
		case 'green':
			$photo_ids->_hsl(75, 170, 1, 100, 1, 100);
			break;
		case 'purple':
			$photo_ids->_hsl(235, 300, 1, 100, 1, 100);
			break;
		case 'orange':
			$photo_ids->_hsl(10, 50, 1, 100, 1, 100);
			break;
		case 'brown':
			$photo_ids->_hsl(null, null, null, null, 1, 20);
			break;
		case 'pink':
			$photo_ids->_hsl(300, 345, 1, 100, 1, 100);
			break;
		default:
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
			$photo_ids->_ratio(1, null, null);
			break;
		case 'landscape':
			$photo_ids->_ratio(null, 1, null);
			break;
		case 'square':
			$photo_ids->_ratio(null, null, 1);
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
			$photo_ids->_sort('photos.photo_taken', $_POST['sort_direction']);
			$photo_ids->_notnull('photos.photo_taken');
			break;
		case 'published':
			$photo_ids->_sort('photos.photo_published', $_POST['sort_direction']);
			$photo_ids->_notnull('photos.photo_published');
			break;
		case 'uploaded':
			$photo_ids->_sort('photos.photo_uploaded', $_POST['sort_direction']);
			break;
		case 'updated':
			$photo_ids->_sort('photos.photo_updated', $_POST['sort_direction']);
			$photo_ids->_notnull('photos.photo_updated');
			break;
		case 'title':
			$photo_ids->_sort('photos.photo_title', $_POST['sort_direction']);
			$photo_ids->_notnull('photos.photo_title');
			break;
		case 'views':
			$photo_ids->_sort('photos.photo_views', $_POST['sort_direction']);
			break;
	}
}

$photo_ids->find();
$photo_ids->saveMemory();

$photos = new Photo($photo_ids->photo_ids);
$photos->getImgUrl('square');

define('TAB', 'library');
define('TITLE', 'Alkaline Search Results');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div class="actions"><a href="<?php echo BASE . ADMIN; ?>piles/build/">Build pile</a> <a href="<?php echo BASE . ADMIN; ?>library/">New search</a></div>

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

?>