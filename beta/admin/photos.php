<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');
require_once(PATH . CLASSES . 'find.php');
require_once(PATH . CLASSES . 'photo.php');
require_once(PATH . CLASSES . 'user.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$photo_ids = new Find();
$photo_ids->page(1,100);
$photo_ids->exec();

$photos = new Photo($photo_ids->photo_ids);
$photos->addImgUrl('square');

define('TITLE', 'Alkaline Photos');
define('COLUMNS', '19');
define('WIDTH', '750');

$alkaline->injectJS('photos');

require_once(PATH . ADMIN . 'includes/header.php');

?>

<div id="content" class="span-<?php echo COLUMNS - 2; ?> prepend-1 append-1 last">
	<form id="search" method="get" style="float: right; text-align: right; margin-top: 5px;">
		<input type="text" name="find_text" style="width: 200px; font-size: .9em;" /> <input type="submit" value="Search" /><br />
		<a href="" style="line-height: 2.5em;">Advanced search</a>
	</form>
	
	<h2>Photos</h2>
	
	<h3>Library</h3>
	
	<?php $alkaline->viewNotification(); ?>
	
	<p>Your library includes <?php echo $photo_ids->photo_count_result; ?> photos.</p>
	
	<div id="photos" class="span-17 last">
		<?php
		for($i = 0; $i < $photos->photo_count; ++$i){
			echo '<div class="span-2"><a href="#" id="photo-' . $photos->photos[$i]['photo_id'] . '" class="photo"><img src="' . $photos->photos[$i]['photo_src_square'] . '" alt="" class="admin_thumb" /></a>';
			echo '<div id="photo-' . $photos->photos[$i]['photo_id'] . '-blurb" class="blurb"></div>';
			echo '</div>' . "\n\n";
		}
		?>
	</div>
	
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>