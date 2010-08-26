<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$alkaline->setCallback();

$photo_ids = new Find();
$photo_ids->page(1,100);
$photo_ids->exec();

$photos = new Photo($photo_ids->photo_ids);
$photos->getImgUrl('square');

define('TAB', 'library');
define('TITLE', 'Alkaline Library');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<h1>Photos (<?php echo $photo_ids->photo_count_result; ?>)</h1>

<p>
	<?php

	foreach($photos->photos as $photo){
		?>
		<a href="<?php echo BASE . ADMIN . 'photo/' . $photo['photo_id']; ?>/">
			<img src="<?php echo $photo['photo_src_square']; ?>" alt="" title="<?php echo $photo['photo_title']; ?>" class="frame" />
		</a>
		<?php
	}
	
	?>
</p>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>