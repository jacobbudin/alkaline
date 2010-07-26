<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

if(!empty($_POST['photo_ids'])){
	$photo_ids = explode(',', $_POST['photo_ids']);
	$alkaline->convertToIntegerArray($photo_ids);
	foreach($photo_ids as $photo_id){
		$photo = new Photo($photo_id);
		$fields = array('photo_title' => $_POST['photo-' . $photo_id . '-title'],
			'photo_description' => $_POST['photo-' . $photo_id . '-description'],
			'photo_published' => $_POST['photo-' . $photo_id . '-published'],
			'photo_geo' => $_POST['photo-' . $photo_id . '-geo']);
		$photo->updateFields($fields);
	}
	$alkaline->addNotification('Your changes have been successfully saved.', 'success');
	header('Location: ' . BASE . ADMIN . 'photos/');
	exit();
}

$photo_ids = new Find();
$photo_ids->page(1,100);
$photo_ids->exec();

$photos = new Photo($photo_ids->photo_ids);
$photos->getImgUrl('square');

$alkaline->injectJS('photos');

define('TITLE', 'Alkaline Library');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="module" class="container" style="background-image: url(/images/icons/library.png);">
	<h1>Library</h1>
	<p>Your library contains <?php echo $photo_ids->photo_count_result; ?> photos. <a href="<?php echo BASE . ADMIN; ?>upload/">Upload photos.</a></p>
</div>

<div id="library" class="container">
	<div class="span-17 append-1">
		<h2>Search</h2>

		<form id="search" method="get">
			<p>
				<input type="text" name="search" style="width: 40%; font-size: .9em; margin-left: 0;" /> <input type="submit" value="Search" /><br />
				&#9656; <a href="" style="line-height: 2.5em;">Show options</a>
			</p>
		</form>
	
		<h2>Photos</h2>
	
		<hr />
		
		<?php
		for($i = 0; $i < $photos->photo_count; ++$i){
			?>
			<a href="#" id="photo-<?php echo $photos->photos[$i]['photo_id']; ?>" class="photo">
				<img src="<?php echo $photos->photos[$i]['photo_src_square']; ?>" alt="" class="admin_thumb" />
			</a>
			<?php
		}
	?>
	</div>
	<div id="library_features" class="span-5 last">
		<h2>Features</h2>
		<ul>
			<li>
				<strong>Piles</strong><br />
				Group photos together
			</li>
			<li>
				<strong>Tags</strong><br />
				Link photos to keywords
			</li>
			<li>
				<strong>Rights</strong><br />
				Manage copyrights
			</li>
			<li>
				<strong>Comments</strong><br />
				Moderate comments
			</li>
		</ul>
	</div>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>