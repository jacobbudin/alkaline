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
$orbit = new Orbit;

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

if(isset($_GET['success']) and ($_GET['success'] == 1)){
	header('Location: ' . LOCATION . BASE . ADMIN . 'shoebox' . URL_CAP);
	exit();
}

$orbit->hook('shoebox');

define('TAB', 'upload');
define('TITLE', 'Alkaline Upload');
require_once(PATH . ADMIN . 'includes/header.php');

// cliqcliq Quickpic support
if(preg_match('#iphone|ipad#si', $_SERVER['HTTP_USER_AGENT']) and !isset($_GET['success'])){
	?>
	<script type="text/javascript">
		launchQuickpic('<?php echo sha1(PATH . BASE . DB_DSN . DB_TYPE); ?>');
	</script>
	<?php
}

?>

<div class="actions"><a href="<?php echo BASE . ADMIN . 'shoebox' . URL_CAP; ?>"><button>Go to shoebox</button></a></div>
<h1><img src="<?php echo BASE . ADMIN; ?>images/icons/upload.png" alt="" /> Upload</h1>

<div class="span-24 last">
	<div class="span-18 append-1">
		<form enctype="multipart/form-data" action="" method="post" style="padding-top: 1em;">
			<img src="<?php echo BASE . ADMIN; ?>images/upload_box.png" alt="" style="position: absolute; z-index: -25;" />
			<div style="height: 380px; margin-bottom: 1.5em;">
				<input type="file" multiple="multiple" id="upload" />
			</div>
		</form>
	</div>
	<div class="span-5 append-top last">
		<h3>Status</h3>
		<p>You have uploaded <span id="upload_count_text">0 files</span>.</p>
		
		<h3>File size limit</h3>
		<p>
			<?php
			
			$sizes = array('post_max_size', 'upload_max_filesize', 'memory_limit');
			$sizes = array_map('ini_get', $sizes);
			$sizes = array_map(array($alkaline, 'convertToBytes'), $sizes);
			sort($sizes);
			
			echo str_replace(array('000000000', '000000', '000000'), array('GB', 'MB', 'KB'), $sizes[0]);
			
			?>
			<span class="quiet">(<a href="http://www.alkalineapp.com/guide/faq/#file-size-limit-uploads">Why?</a>)</span>
		</p>
		
		<h3>Instructions</h3>
		<p>Drag images from a folder on your computer or directly from most applications into the grey retaining area. You can also drag and drop text files to create new posts.</p>
	
		<p>You can also browse your computer and select the files you wish to upload by clicking the &#8220;Choose File&#8221; button.</p>
	
		<p>Once you&#8217;ve finished uploading, go to your <a href="<?php echo BASE . ADMIN . 'shoebox' . URL_CAP; ?>">shoebox</a> to process your files.</p>
	</div>
</div>

<?php

require_once(PATH . ADMIN . 'includes/footer.php');

?>