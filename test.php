<?php

require_once('config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
// echo $alkaline->sanitize('sdh98*(*H@jaskl\' ""ASDkasjhd _askljd-daskj91(*!)*&2kjsd');

$image = new Imagick(PATH . 'photos/203.jpg');
var_dump($image->getSize());

?>