<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalineapp.com/
*/

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$alkaline->setCallback();

// Preference: page_limit
if(!$max = $user->returnPref('page_limit')){
	$max = 100;
}

$photo_ids = new Find();
$photo_ids->page(null, $max);
$photo_ids->find();
$photo_ids->saveMemory();

$photos = new Photo($photo_ids->photo_ids);
$photos->getImgUrl('square');

define('TAB', 'library');
define('TITLE', 'Alkaline Search Results');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div class="actions"><a href="<?php echo BASE . ADMIN . 'features' . URL_ACT . 'bulk' . URL_RW; ?>">Bulk edit</a> <a href="<?php echo BASE . ADMIN; ?>piles<?php echo URL_ACT; ?>build<?php echo URL_RW; ?>">Build pile</a> <a href="<?php echo BASE . ADMIN; ?>library<?php echo URL_CAP; ?>">New search</a></div>

<h1>Search Results (<?php echo number_format($photo_ids->photo_count); ?>)</h1>

<?php

if($photo_ids->photo_count_result > 0){
	?>
	<p>
	<?php
	for($i = 0; $i < $photos->photo_count; ++$i){
		?>
		<a href="<?php echo BASE . ADMIN . 'photo' . URL_ID . $photos->photos[$i]['photo_id'] . URL_RW; ?>"><img src="<?php echo $photos->photos[$i]['photo_src_square']; ?>" alt="" title="<?php echo $photos->photos[$i]['photo_title']; ?>" class="frame" /></a>
		<?php
	}
	?>
	</p>
	<?php
	if($photo_ids->page_count > 1){
		?>
		<p>
			<?php
			if(!empty($photo_ids->page_previous)){
				for($i = 1; $i <= $photo_ids->page_previous; ++$i){
					echo '<a href="' . BASE . ADMIN . 'search' . URL_PAGE . $i . URL_RW . '" class="page_no">' . number_format($i) . '</a>';
				}
			}
			?>
			<span class="page_no">Page <?php echo $photo_ids->page; ?> of <?php echo $photo_ids->page_count; ?></span>
			<?php
			if(!empty($photo_ids->page_next)){
				for($i = $photo_ids->page_next; $i <= $photo_ids->page_count; ++$i){
					echo '<a href="' . BASE . ADMIN . 'search' . URL_PAGE . $i . URL_RW . '" class="page_no">' . number_format($i) . '</a>';
				}
			}
			?>
		</p>
		<?php
	}
}

require_once(PATH . ADMIN . 'includes/footer.php');

?>