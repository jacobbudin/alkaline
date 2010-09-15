<?php

require_once('./config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$geo = new Geo('');
$hint = $geo->hint('back');
var_dump($hint);

// $alkaline = new Alkaline;
// var_dump($alkaline->emptyDirectory(PATH . SHOEBOX));

// $alkaline = new Alkaline;

// $alkaline->updateCount('comments', 'photos', 'photo_comment_count', '203');

// $alkaline->emptyDirectory(PATH . SHOEBOX);

// Photo::watermark(PATH . PHOTOS . '244_m.jpg', PATH . PHOTOS . '244_w.jpg', PATH . 'watermark.png');

// print_r(ini_get_all());

?>

<!-- <img src="<?php echo '/photos/244_w.jpg'; ?>" />-->