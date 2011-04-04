<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalineapp.com/
*/

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true, 'upload');

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
		<h2 id="h2_shoebox"><a href="<?php echo BASE . ADMIN; ?>shoebox<?php echo URL_CAP; ?>"><img src="<?php echo BASE . ADMIN; ?>images/icons/shoebox.png" alt="" /> Shoebox</a></h2>
		
		<div id="progress">
		</div>
		
		<hr />
		
		<h3>Status</h3>
		<p>You have uploaded <span id="upload_count_text">0 files</span>.</p>
		
		<?php if(stripos($_SERVER['HTTP_USER_AGENT'], 'webkit')){ ?>		
			<h3>Instructions</h3>
			<p>Drag images from a folder on your computer or from most applications including Aperture, Bridge, iPhoto, and Lightroom into the grey retaining area.</p>
		
			<p>If you prefer, you can also browse your files and select the ones you wish to upload by clicking the &#8220;Choose File&#8221; button.</p>
		
			<p>Once you&#8217;ve finished uploading, go to your <a href="<?php echo BASE . ADMIN . 'shoebox' . URL_CAP; ?>">Shoebox</a> to process your images.</p>
		<?php } ?>
	</div>
	<div class="span-18 colborderl last">
		<h1>Upload</h1>
		<form enctype="multipart/form-data" action="" method="post" style="padding-top: 1em;">
			<?php if(stripos($_SERVER['HTTP_USER_AGENT'], 'webkit')){ ?>
				<img src="<?php echo BASE . ADMIN; ?>images/upload_box.png" alt="" style="position: absolute; z-index: -25;" />
				<div style="height: 380px; margin-bottom: 1.5em;">
					<input type="file" multiple="multiple" id="upload" style="width: 100%; padding: 310px 0 54px 50px;" />
				</div>
			<?php } else{ ?>
				<input type="file" multiple="multiple" id="upload" />
			<?php } ?>
		</form>
	</div>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>