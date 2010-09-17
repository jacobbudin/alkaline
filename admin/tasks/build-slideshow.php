<?php

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;

$photo_ids = new Find;
$photo_ids->privacy('public', true);
$photo_ids->exec();

$photos = new Photo($photo_ids);
$photos->getImgUrl('medium');
echo json_encode($photos);

?>