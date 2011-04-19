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

// cliqcliq Quickpic support
if(isset($_REQUEST['context']) and ($_REQUEST['context'] == sha1(PATH . BASE . DB_DSN . DB_TYPE))){
	header('Content-Type: application/x-plist');
	
	$file = $_FILES['upload_file'];
	move_uploaded_file($file['tmp_name'], $alkaline->correctWinPath(PATH . SHOEBOX . $file['name']));
	
	echo '<?xml version="1.0" encoding="UTF-8"?>';
	?>
	<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
	<plist version="1.0">
	<dict>
	<key>success</key>
	<true/>
	</dict>
	</plist>
	<?php
}

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

// cliqcliq Quickpic support
if(preg_match('#iphone|ipad#si', $_SERVER['HTTP_USER_AGENT'])){
	?>
	<script type="text/javascript">
		launchQuickpic('<?php echo sha1(PATH . BASE . DB_DSN . DB_TYPE); ?>');
	</script>
	<?php
}

?>

<div class="span-24 last">
	<div class="span-18 colborder">
			<div class="actions">Done uploading? <a href="<?php echo BASE . ADMIN . 'shoebox' . URL_CAP; ?>"><button>Go to shoebox</button></a></div>
		<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/upload.png" alt="" /> Upload</h1>
		<form enctype="multipart/form-data" action="" method="post" style="padding-top: 1em;">
			<?php if(preg_match('#webkit#si', $_SERVER['HTTP_USER_AGENT'])){ ?>
				<img src="<?php echo BASE . ADMIN; ?>images/upload_box.png" alt="" style="position: absolute; z-index: -25;" />
				<div style="height: 380px; margin-bottom: 1.5em;">
					<input type="file" multiple="multiple" id="upload" style="width: 100%; padding: 310px 0 54px 50px;" />
				</div>
			<?php } else{ ?>
				<p><input type="file" multiple="multiple" id="upload" /></p>
			<?php } ?>
		</form>
	</div>
	<div class="span-5 last">
		<div id="progress">
		</div>
		
		<h3>Status</h3>
		<p>You have uploaded <span id="upload_count_text">0 files</span>.</p>
		
		<?php if(stripos($_SERVER['HTTP_USER_AGENT'], 'webkit')){ ?>		
			<h3>Instructions</h3>
			<p>Drag images from a folder on your computer or from most applications including Aperture, Bridge, iPhoto, and Lightroom into the grey retaining area. You can also drag text files to create new posts.</p>
		
			<p>If you prefer, you can also browse your files and select the ones you wish to upload by clicking the &#8220;Choose File&#8221; button.</p>
		
			<p>Once you&#8217;ve finished uploading, go to your <a href="<?php echo BASE . ADMIN . 'shoebox' . URL_CAP; ?>">shoebox</a> to process your files.</p>
		<?php } ?>
	</div>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>