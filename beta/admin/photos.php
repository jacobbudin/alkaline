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
	
	<h2>Photos</h2>
	
	<?php $alkaline->viewNotification(); ?>
	
	<h3>Search</h3>

	<form id="search" method="get">
		<input type="text" name="find_text" style="width: 50%; font-size: .9em; margin-left: 0;" /> <input type="submit" value="Search" /><br />
		<a href="" style="line-height: 2.5em;">Advanced search</a>
	</form><br />
	
	<div id="view">
		<a href="#" class="<?php if($user->view_type == 'grid'){ echo 'selected'; } ?>" id="grid"><img src="<?php echo BASE . IMAGES; ?>icons/grid.png" alt="" title="Grid view" /></a> <a href="#" class="<?php if($user->view_type == 'list'){ echo 'selected'; } ?>" id="list"><img src="<?php echo BASE . IMAGES; ?>icons/list.png" alt="" title="List view" /></a>
	</div>
	
	<h3>Library <span class="small quiet">(<?php echo $photo_ids->photo_count_result; ?>)</span></h3>
	
	<div id="photos" class="span-17 last">
		<?php
		switch($user->view_type){
			case 'grid': 
				for($i = 0; $i < $photos->photo_count; ++$i){
					echo '<a href="#" id="photo-' . $photos->photos[$i]['photo_id'] . '" class="photo"><img src="' . $photos->photos[$i]['photo_src_square'] . '" alt="" class="admin_thumb" /></a>';
					echo '<div id="photo-' . $photos->photos[$i]['photo_id'] . '-blurb" class="blurb"><p class="wide-form"><strong>Title:</strong><br /><input type="text" name="photo-' . $photos->photos[$i]['photo_id'] . '-title" /></p><p class="wide-form"><strong>Description:</strong><br /><textarea name="photo-' . $photos->photos[$i]['photo_id'] . '-description"></textarea></p><p class="wide-form"><strong>Tags:</strong><br /><input type="text" name="photo-' . $photos->photos[$i]['photo_id'] . '-tags" /></p><p class="center"><input id="photo_ids" type="hidden" name="photo_ids" value="' . $photos->photos[$i]['photo_id'] . '" /><input id="save" type="submit" value="Save changes" /></p></div>';
					echo "\n\n";
				}
				break;
			case 'list': 
				for($i = 0; $i < $photos->photo_count; ++$i){
					echo '<p id="photo-' . $photos->photos[$i]['photo_id'] . '"><div class="span-3 center"><img src="' . $photos->photos[$i]['photo_src_square'] . '" alt="" class="admin_thumb" /></div><div class="span-14 last"><p><strong>Title:</strong><br /><input type="text" name="photo-' . $photos->photos[$i]['photo_id'] . '-title" /></p><p><strong>Description:</strong><br /><textarea name="photo-' . $photos->photos[$i]['photo_id'] . '-description"></textarea></p><p><strong>Tags:</strong><br /><input type="text" name="photo-' . $photos->photos[$i]['photo_id'] . '-tags" /></p></div></p>';
					echo '<div id="photo-' . $photos->photos[$i]['photo_id'] . '-blurb" class="blurb"></div>';
					echo "\n\n";
				}
				?>
				<p class="center">
					<input id="photo_ids" type="hidden" name="photo_ids" value="" />
					<input id="save" type="submit" value="Save changes" />
				</p>
				<?php
				break;
		}
		?>
	</div>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>