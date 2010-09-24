<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$photo_ids = new Find();
$photo_ids->page(1,100);
$photo_ids->find();

$photos = new Photo($photo_ids->photo_ids);
$photos->getImgUrl('square');

define('TAB', 'features');
define('TITLE', 'Alkaline Features');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div class="span-24 last">
	<div class="span-5 colborderr">
		<h2><a href="<?php echo BASE . ADMIN; ?>tags/"><img src="/images/icons/tags.png" alt="" /> Tags &#9656;</a></h2>
		<h2><a href="<?php echo BASE . ADMIN; ?>piles/"><img src="/images/icons/piles.png" alt="" /> Piles &#9656;</a></h2>
		<h2><a href="<?php echo BASE . ADMIN; ?>comments/"><img src="/images/icons/comments.png" alt="" /> Comments &#9656;</a></h2>
		<h2><a href="<?php echo BASE . ADMIN; ?>pages/"><img src="/images/icons/pages.png" alt="" /> Pages &#9656;</a></h2>
		<h2><a href="<?php echo BASE . ADMIN; ?>rights/"><img src="/images/icons/rights.png" alt="" /> Rights &#9656;</a></h2>
	</div>
	<div class="span-18 colborderl last">
		<h1>Actions</h1>
	</div>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>