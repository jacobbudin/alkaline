<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$alkaline->setCallback();

$photo_ids = new Find();

$photo_ids->recentMemory();
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
			$photo_ids->_ratio(null, 1);
			break;
		case 'landscape':
			$photo_ids->_ratio(1, null);
			break;
		case 'square':
			$photo_ids->_ratio(1, 1);
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
$photo_ids->saveMemory();

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


?>