<?php

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

if(!empty($_FILES)){
	$filename = $_FILES['user_file']['name'][0];
	$tmp_file = $_FILES['user_file']['tmp_name'][0];
	copy($tmp_file, $alkaline->correctWinPath(PATH . SHOEBOX . $filename));
	unlink($tmp_file);
	exit();
}

define('TAB', 'library');
define('TITLE', 'Alkaline Upload');
require_once(PATH . ADMIN . 'includes/header.php');

?>

<div class="span-24 last">
	<div class="span-5 colborderr">
		<h2 id="shoebox_link"><a href="<?php echo BASE . ADMIN; ?>shoebox<?php echo URL_CAP; ?>"><img src="/images/icons/shoebox.png" alt="" /> Shoebox &#9656;</a></h2>
		
		<div id="progress">
		</div>
		
		<hr />
		
		<h3>Status</h3>
		
		<p>You have uploaded <span id="upload_count_text">0 files</span>.</p>
	</div>
	<div class="span-18 colborderl last">
		<h1>Upload</h1>
		<form enctype="multipart/form-data" action="" method="post" style="padding-top: 1em;">
			<p>
				<img src="/images/upload_box.png" alt="" style="position: absolute; z-index: -25;" />
				<input type="file" multiple="multiple" id="upload" style="width: 100%; padding: 310px 0 54px 50px; z-index: 100;" />
			</p>
		</form>
	</div>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>