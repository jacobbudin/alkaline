<?php

require_once('./../piston.php');
require_once(PATH . CLASSES . 'piston.php');
require_once(PATH . CLASSES . 'import.php');

$piston = new Piston;

$photos = $piston->seekPhotos(PATH . SHOEBOX);

foreach($photos as $photo){
	new Import($photo);
}

echo '<br /><br /><br />Done.';

?>