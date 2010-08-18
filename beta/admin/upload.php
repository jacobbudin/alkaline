<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

if(!empty($_FILES)){
	$filename = $_FILES['user_file']['name'][0];
	$tmp_file = $_FILES['user_file']['tmp_name'][0];
	copy($tmp_file, PATH . SHOEBOX . $filename);
	unlink($tmp_file);
	var_dump($_FILES);
}

$blocks = $alkaline->getBlocks();
$block_count = count($blocks);

define('TITLE', 'Alkaline Upload');
require_once(PATH . ADMIN . 'includes/header.php');
require_once(PATH . ADMIN . 'includes/library.php');

?>

<h1>Upload</h1>

<form enctype="multipart/form-data" action="" method="post" style="padding-top: 1em;">
	<p>
		<img src="/images/upload_box.png" alt="" style="position: absolute; z-index: -25;" />
		<input type="file" multiple="multiple" id="upload" style="width: 100%; padding: 310px 0 54px 50px; z-index: 100;" />
	</p>
</form>

<div id="progress">
</div>

<p>
	You have uploaded <span id="upload_count_text">0 files</span>.
</p>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>