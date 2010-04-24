<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title><?php if(defined('TITLE')){ echo TITLE; } else{ echo 'Alkaline'; } ?></title>
	<link rel="stylesheet" href="<?php echo BASE . CSS; ?>blueprint/screen.css" type="text/css" media="screen, projection" />
	<link rel="stylesheet" href="<?php echo BASE . CSS; ?>blueprint/print.css" type="text/css" media="print" />	
	<!--[if lt IE 8]><link rel="stylesheet" href="<?php echo BASE . CSS; ?>blueprint/ie.css" type="text/css" media="screen, projection" /><![endif]-->
	<link rel="stylesheet" href="<?php echo BASE . CSS; ?>alkaline.css" type="text/css" media="screen, projection" />
	<link rel="stylesheet" href="<?php echo BASE . CSS; ?>smoothness/jquery-ui-1.8.custom.css" type="text/css" media="screen, projection" />
	<style type="text/css">
		.container { width: <?php if(defined('WIDTH')){ echo WIDTH; } else{ echo '750'; } ?>px; }	
		#content { background-image: url(<?php echo BASE . 'images/block-'; if(defined('WIDTH')){ echo WIDTH; } else{ echo '750'; } ?>.png); }
	</style>
	<script src="<?php echo BASE . JS; ?>jquery/jquery-1.4.1.min.js" type="text/javascript"></script>
	<script src="<?php echo BASE . JS; ?>jquery/jquery-ui-1.8.custom.min.js" type="text/javascript"></script>
	<script type="text/javascript">
		var photo_ids;
		var photo_count;
		var progress;
		var progress_step;
		
		function photoArray(data){
			photo_ids = data;
			photo_count = photo_ids.length;
			progress = 0;
			progress_step = 100 / photo_ids.length;
			for(photo_id in photo_ids){
				$.post("<?php echo BASE . ADMIN; ?>tasks/rebuild-all.php", { photo_id: photo_ids[photo_id] }, function(data){ updateProgress(); } );
			}
		}
		
		function updateProgress(){
			progress += progress_step;
			progress_int = parseInt(progress);
			$("#progress").progressbar({ value: progress_int });
			if(progress == 100){
				$.post("<?php echo BASE . ADMIN; ?>tasks/add-notification.php", { message: "Your photo library&#8217;s thumbnails have been rebuilt.", type: "success" }, function(data){ redirect(); } );
			}
		}
		
		function redirect(){
			window.location = "<?php echo BASE . ADMIN; ?>";
		}
		
		$(document).ready(function(){
			$("#progress").hide(0);
			$("a.task").click(function(event){
				$("#tasks").slideUp(500);
				$("#progress").delay(500).slideDown(500);
				$("#progress").progressbar({ value: 0 });
				$.ajax({
					url: "<?php echo BASE . ADMIN; ?>tasks/rebuild-all.php",
					cache: false,
					error: function(data){ alert(data); },
					dataType: "json",
					success: function(data){ photoArray(data); }
				});
				event.preventDefault();
			});
		});
	</script>
</head>
<body>
	<div class="container">
		<div id="header" class="span-<?php echo COLUMNS; ?> last">
			<p style="float: left;"><a href="<?php echo BASE . ADMIN; ?>"><img src="<?php echo BASE . IMAGES; ?>shutter.png" alt="" /></a></p>
			<?php
			if($user->perm()){
			?>
				<div id="user">You are logged in as: <?php echo $user->user['user_name']; ?>. &#0160; <a href="<?php echo BASE . 'admin/logout/'; ?>" class="button_red">Logout</a></div>
			<?php
			}
			?>
		</div>