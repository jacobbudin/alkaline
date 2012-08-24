<?php

/*
// Alkaline
// Copyright (c) 2010-2012 by Budin Ltd. Some rights reserved.
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

$comparison = $alkaline->compare($version['version_title'] . "\n\n" . $version['version_text_raw'], $_POST['title'] . "\n\n" . $_POST['text_raw']);

// Bold title
$comparison = preg_replace('#(.*?)\n#si', '<strong>\\1</strong>', $comparison, 1);

function charsToBlanks($str){
	$paras = substr_count($str, "\n");
	$len = strlen($str);
	$str = ' &#0160; ' . str_repeat(' &#0160;', ceil($len / 2));
	$str .= str_repeat('<br />', $paras);
	if((1&$len)){ $str .= ' '; }
	
	return $str;
}

$comparison_mod = preg_replace('#<ins>(.*?)</ins>#esi', "charsToBlanks('\\1')", $comparison);

if(empty($comparison)){
	exit();
}

?>

<div class="span-24 comparison_split last">
	<div class="actions append-1 append-bottom">
		<a href="#revert" id="version-<?php echo $version['version_id']; ?>"><button>Revert to previous version</button></a>
	</div>
	<div class="span-24 last">
		<div class="span-10 append-1 prepend-1 comparison_left">
			<?php echo $comparison_mod; ?>
		</div>
		<div class="span-10 append-1 prepend-1 last comparison_right">
			<?php echo $comparison; ?>
		</div>
	</div>
</div>