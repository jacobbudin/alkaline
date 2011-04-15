<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalineapp.com/
*/

error_reporting(E_ALL & ~E_DEPRECATED);

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$version_id = intval($_POST['version_id']);
$version = $alkaline->getRow('versions', $version_id);

$comparison = $alkaline->compare($version['version_text_raw'], $_POST['text_raw']);

function charsToBlanks($str){
	$len = strlen($str);
	$str = array_fill(0, ceil($len / 2), '&#0160;');
	$str = ' &#0160; ' . implode(' ', $str);
	if((1&$len)){ $str .= ' '; }
	return $str;
}

$comparison_mod = preg_replace('#<ins>(.*?)</ins>#esi', "charsToBlanks('\\1')", $comparison);

if(empty($comparison)){
	exit();
}

?>

<div class="span-24 comparison_split last">
	<div class="span-10 append-1 prepend-1 comparison_left">
		<?php echo $comparison_mod; ?>
	</div>
	<div class="span-10 append-1 prepend-1 last comparison_right">
		<?php echo $comparison; ?>
	</div>
</div>