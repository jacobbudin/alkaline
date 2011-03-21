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

if(!empty($_GET['act']) or !empty($_REQUEST['q'])){
	Find::clearMemory();
}

$image_ids = new Find('images');
$image_ids->page(null, $max);
$image_ids->memory();
$image_ids->find();
$image_ids->saveMemory();

$images = new Image($image_ids);
$images->getSizes('square');

define('TAB', 'library');
define('TITLE', 'Alkaline Search Results');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div class="actions"><a href="<?php echo BASE . ADMIN . 'features' . URL_ACT . 'bulk' . URL_RW; ?>">Bulk edit</a> <a href="<?php echo BASE . ADMIN; ?>sets<?php echo URL_ACT; ?>build<?php echo URL_RW; ?>">Build set</a> <a href="<?php echo BASE . ADMIN; ?>library<?php echo URL_CAP; ?>">New search</a></div>

<h1>Search Results (<?php echo number_format($image_ids->count); ?>)</h1>

<?php

if($image_ids->count_result > 0){
	?>
	<p>
	<?php
	for($i = 0; $i < $images->image_count; ++$i){
		?>
		<a href="<?php echo BASE . ADMIN . 'image' . URL_ID . $images->images[$i]['image_id'] . URL_RW; ?>"><img src="<?php echo $images->images[$i]['image_src_square']; ?>" alt="" title="<?php echo $images->images[$i]['image_title']; ?>" class="frame" /></a>
		<?php
	}
	?>
	</p>
	<?php
	if($image_ids->page_count > 1){
		?>
		<p>
			<?php
			if(!empty($image_ids->page_previous)){
				for($i = 1; $i <= $image_ids->page_previous; ++$i){
					$page_uri = 'page_' . $i . '_uri';
					echo '<a href="' . $image_ids->$page_uri  .'" class="page_no">' . number_format($i) . '</a>';
				}
			}
			?>
			<span class="page_no">Page <?php echo $image_ids->page; ?> of <?php echo $image_ids->page_count; ?></span>
			<?php
			if(!empty($image_ids->page_next)){
				for($i = $image_ids->page_next; $i <= $image_ids->page_count; ++$i){
					$page_uri = 'page_' . $i . '_uri';
					echo '<a href="' . $image_ids->$page_uri  .'" class="page_no">' . number_format($i) . '</a>';
				}
			}
			?>
		</p>
		<?php
	}
}

require_once(PATH . ADMIN . 'includes/footer.php');

?>