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
	exit();
}

define('TAB', 'library');
define('TITLE', 'Alkaline Upload');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div class="span-24 last">
	<div class="span-16 append-1">
		<h1>Upload</h1>
		<form enctype="multipart/form-data" action="" method="post" style="padding-top: 1em;">
			<p>
				<img src="/images/upload_box.png" alt="" style="position: absolute; z-index: -25;" />
				<input type="file" multiple="multiple" id="upload" style="width: 100%; padding: 310px 0 54px 50px; z-index: 100;" />
			</p>
		</form>
	</div>
	<div class="span-7 last">
		<h2><a href="<?php echo BASE . ADMIN; ?>shoebox/"><img src="/images/icons/shoebox.png" alt="" /> Shoebox &#9656;</a></h2>
		
		<hr />
		
		<div id="progress">
		</div>
		
		<p><em>Tip: Please wait until your photos have finished uploading before proceeding to your shoebox.</em></p>
		
		<p>You have uploaded <span id="upload_count_text">0 files</span>.</p>
	</div>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>