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
	
	<div id="view">
		<a href="" class="selected"><img src="<?php echo BASE . IMAGES; ?>icons/grid.png" alt="" /> Grid view</a> <a href=""><img src="<?php echo BASE . IMAGES; ?>icons/list.png" alt="" /> List view</a>
	</div>
	
	<h2>Photos</h2>
	
	<?php $alkaline->viewNotification(); ?>
	
	<h3>Search</h3>

	<form id="search" method="get">
		<input type="text" name="find_text" style="width: 50%; font-size: .9em; margin-left: 0;" /> <input type="submit" value="Search" /><br />
		<a href="" style="line-height: 2.5em;">Advanced search</a>
	</form><br />
	
	<h3>Library <span class="small quiet">(<?php echo $photo_ids->photo_count_result; ?>)</span></h3>
	
	<div id="photos" class="span-17 last">
		<?php
		for($i = 0; $i < $photos->photo_count; ++$i){
			echo '<a href="#" id="photo-' . $photos->photos[$i]['photo_id'] . '" class="photo"><img src="' . $photos->photos[$i]['photo_src_square'] . '" alt="" class="admin_thumb" /></a>';
			echo '<div id="photo-' . $photos->photos[$i]['photo_id'] . '-blurb" class="blurb"></div>';
			echo "\n\n";
		}
		?>
	</div>
	
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>