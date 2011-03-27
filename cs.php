<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalineapp.com/
*/

class AlkalineCS{
	const build = 2;
	const copyright = 'Powered by <a href="http://www.alkalineapp.com/">Alkaline</a>. Copyright &copy; 2010-2011 by <a href="http://www.budinltd.com/">Budin Ltd.</a> All rights reserved.';
	const version = 'Beta 1';
	
	public $compatible;
	
	public $ic_version;
	public $php_version;
	public $php_extensions;
	public $phpinfo;
	Public $php_pdo_drivers;
	
	function __construct(){
		$this->compatible = true;
		$this->php_version = phpversion();
		if(function_exists('ioncube_loader_version')){
			$this->ic_version = ioncube_loader_version();
			$this->ic_version = intval(preg_replace('#^([0-9]+)\..*#si', '\\1', $this->ic_version));
		}
		$this->php_extensions = get_loaded_extensions();
		ob_start();
		phpinfo();
		$this->phpinfo = ob_get_contents(); 
		ob_end_clean();
		$php_pdo_drivers = @PDO::getAvailableDrivers();
		foreach($php_pdo_drivers as $driver){
			switch($driver){
				case 'odbc':
					// $this->php_pdo_drivers[] = 'Microsoft SQL Server';
					break;
				case 'mysql':
					$this->php_pdo_drivers[] = 'MySQL';
					break;
				case 'pgsql':
					$this->php_pdo_drivers[] = 'PostgreSQL';
					break;
				case 'sqlite':
					$this->php_pdo_drivers[] = 'SQLite';
					break;
				default:
					break;
			}
		}
	}
	
	public function isExt($ext, $req=true){
		if(in_array($ext, $this->php_extensions)){
			return true;
		}
		else{
			if($req == true){
				$this->compatible = false;
			}
			return false;
		}
	}
	
	public function isVer($pat, $str){
		if(preg_match($pat, $str)){
			return true;
		}
		else{
			$this->compatible = false;
			return false;
		}
	}
	
	public function isThere($str){
		if(stripos($this->phpinfo, $str)){
			return true;
		}
	}
	
	public function isNet(){
		$ip = gethostbyname('http://www.alkalineapp.com/');
		if(!empty($ip)){
			return true;
		}
		else{
			$this->compatible = false;
			return false;
		}
	}
	
	public function boolToHTML($bool, $positive=null, $negative=null){
		if(empty($positive)){
			$positive = '<strong>Installed</strong>';
		}
		if(empty($negative)){
			$negative = '<strong>Not installed</strong>';
		}
		
		if($bool === true){
			echo '<td class="center middle quiet" style="width: 20%">' . $positive . '</td>';
		}
		elseif($bool === false){
			echo '<td class="center middle quiet" style="width: 20%">' . $negative . '</td>';
		}
		else{
			echo '<td class="center middle quiet" style="width: 20%"><strong>Unknown</strong></td>';
		}
	}
	
	public function boolToIMG($bool){
		if($bool === true){
			echo '<td class="center middle" style="width: 2%"><img src="http://www.alkalineapp.com/remote/cs/images/positive.png" alt="" /></td>';
		}
		elseif($bool === false){
			echo '<td class="center middle" style="width: 2%"><img src="http://www.alkalineapp.com/remote/cs/images/negative.png" alt="" /></td>';
		}
		else{
			echo '<td class="center middle" style="width: 2%"><img src="http://www.alkalineapp.com/remote/cs/images/unknown.png" alt="" /></td>';
		}
	}
	
	public function noteRAM(){
		$ram = ini_get('memory_limit');
		$value = substr($ram, 1, -1);
		$unit = substr($ram, -1, 1);
		
		if($unit == 'm'){
			$unit = 'MB';
			if($value < 16){ return 'Your Web server has insufficient RAM allocated to PHP processes.'; $this->compatible = false; }
			if($value >= 16){ $res = '1.5 megapixel'; }
			if($value >= 32){ $res = '3 megapixel'; }
			if($value >= 48){ $res = '4.5 megapixel'; }
			if($value >= 64){ $res = '6 megapixel'; }
			if($value >= 128){ $res = '12 megapixel'; }
			if($value >= 256){ $res = '24 megapixel'; }
			if($value >= 512){ $res = '48 megapixel'; }
			if($value >= 1024){ $res = '96 megapixel'; }
		}
		if($unit == 'g'){
			if($value >= 1){ $res = '96 megapixel'; }
		}
		
		if(empty($res)){ return false; }
		
		return '<p class="quiet"><em>Note: Your Web server allocates ' . $value . ' ' . $unit . ' to PHP processes&#8212;enough to process images of up to approx. ' . $res . ' resolution.</em></p>';
	}
}

$test = new AlkalineCS();

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Alkaline Compatibility Suite</title>
	<link rel="stylesheet" href="http://www.alkalineapp.com/remote/cs/css/blueprint/screen.css" type="text/css" media="screen, projection" />
	<link rel="stylesheet" href="http://www.alkalineapp.com/remote/cs/css/blueprint/print.css" type="text/css" media="print" />	
	<!--[if lt IE 8]><link rel="stylesheet" href="http://www.alkalineapp.com/remote/cs/css/blueprint/ie.css" type="text/css" media="screen, projection" /><![endif]-->
	<link rel="stylesheet" href="http://www.alkalineapp.com/remote/cs/css/alkaline.css" type="text/css" media="screen, projection" />
	<link rel="shortcut icon" href="http://www.alkalineapp.com/remote/cs/favicon.ico" />
</head>
<body>
	<div id="header_holder">
		<div class="container">
			<div id="header" class="span-24 last">
				<div class="span-12 append-1">
					<a href="http://www.alkalineapp.com/"><img src="http://www.alkalineapp.com/remote/cs/images/shutter.png" alt="Alkaline" /></a>
				</div>
				<div id="panels" class="span-11 last">
				</div>
			</div>
			<div id="navigation" class="span-24 last">
				<ul>
					<li><a href="" class="selected">Compatibility Suite</a></li>
				</ul>
			</div>
		</div>
	</div>
	<div class="container">
		<div id="content" class="span-24 last">
			<h1>Alkaline Compatibility Suite</h1>
			
			<p>The Alkaline Compatibility Suite tests your Web server to ensure it&#8217;s compatible with <a href="http://www.alkalineapp.com/">Alkaline</a>.</p>
			
			<h2>Required</h2>
			
			<table>
				<tr>
					<?php $result = $test->isNet(); echo $test->boolToIMG($result); ?>
					<td>
						<strong>Internet connectivity</strong><br />
						<span class="quiet">An Internet connection is required.</span>
					</td>
					<?php echo $test->boolToHTML($result, '<strong>Connected</strong><br />(via ' . $_SERVER['SERVER_NAME'] . ')', '<strong>Disconnected</strong>'); ?>
				</tr>
				<tr>
					<?php $result = $test->isExt('gd'); echo $test->boolToIMG($result); ?>
					<td>
						<strong>GD image library</strong><br />
						<span class="quiet">GD allows Alkaline to create and manipulate raster image files (GIF, JPG, and PNG).</span>
					</td>
					<?php echo $test->boolToHTML($result); ?>
				</tr>
				<tr>
					<?php $result = ($test->isExt('ionCube Loader') and !empty($test->ic_version) and ($test->ic_version >= 4)); echo $test->boolToIMG($result); ?>
					<td>
						<strong>ionCube Loader 4</strong><br />
						<span class="quiet">ionCube Loader is used to verify the authenticity of Alkaline.</span>
					</td>
					<?php echo $test->boolToHTML($result); ?>
				</tr>
				<tr>
					<?php $result = $test->isVer('/^(5\.2|5\.3).+/is', $test->php_version); echo $test->boolToIMG($result); ?>
					<td>
						<strong>PHP 5.2+</strong><br />
						<span class="quiet">PHP allows Alkaline to produce dynamic Web pages.</span>
					</td>
					<?php echo $test->boolToHTML($result, '<strong>' . $test->php_version . '</strong>', '<strong>' . $test->php_version . '</strong>'); ?>
				</tr>
				<tr>
					<?php $result = $test->isExt('json'); echo $test->boolToIMG($result); ?>
					<td>
						<strong>PHP JSON support</strong><br />
						<span class="quiet">PHP JSON allows Alkaline to provide real-time functionality.</span>
					</td>
					<?php echo $test->boolToHTML($result); ?>
				</tr>
				<tr>
					<?php $result = ($test->isExt('PDO') and (count($test->php_pdo_drivers) > 0)); echo $test->boolToIMG($result); ?>
					<td>
						<strong>PHP PDO support</strong><br />
						<span class="quiet">PHP PDO allows Alkaline to connect to various database types.</span>
					</td>
					<?php echo $test->boolToHTML($result, '<strong>Installed</strong><br />(' . implode(', ', $test->php_pdo_drivers) . ')'); ?>
				</tr>
				<tr>
					<?php $result = $test->isExt('SimpleXML'); echo $test->boolToIMG($result); ?>
					<td>
						<strong>PHP SimpleXML support</strong><br />
						<span class="quiet">PHP SimpleXML allows Alkaline to process new themes and extensions.</span>
					</td>
					<?php echo $test->boolToHTML($result); ?>
				</tr>
			</table>
			
			<h2>Optional</h2>
			
			<table>
				<?php if($test->isThere('Apache/')){ ?>
					<tr>
						<?php $result = $test->isThere('mod_rewrite'); echo $test->boolToIMG($result); ?>
						<td>
							<strong>Apache mod_rewrite module</strong><br />
							<span class="quiet">Apache mod_rewite allows Alkaline to use clean, semantic URLs.</span>
						</td>
						<?php echo $test->boolToHTML($result); ?>
					</tr>
				<?php } ?>
				<tr>
					<?php $result = $test->isExt('imagick', false); echo $test->boolToIMG($result); ?>
					<td>
						<strong>ImageMagick library</strong><br />
						<span class="quiet">ImageMagick allows Alkaline to create and manipulate vector image files (PDF and SVG).</span>
					</td>
					<?php echo $test->boolToHTML($result); ?>
				</tr>
				<tr>
					<?php $result = $test->isExt('exif', false); echo $test->boolToIMG($result); ?>
					<td>
						<strong>PHP EXIF support</strong><br />
						<span class="quiet">PHP EXIF allows Alkaline to read EXIF data from your images.</span>
					</td>
					<?php echo $test->boolToHTML($result); ?>
				</tr>
				<tr>
					<?php $result = $test->isThere('PDO Driver for SQLite 3.x'); echo $test->boolToIMG($result); ?>
					<td>
						<strong>PHP SQLite support</strong><br />
						<span class="quiet">PHP SQLite allows Alkaline to operate without a conventional database.</span>
					</td>
					<?php echo $test->boolToHTML($result); ?>
				</tr>
			</table>
			
			<?php echo $test->noteRAM(); ?>
			
			<p class="center large">
				<?php
				if($test->compatible == true){
					?>
					<img src="http://www.alkalineapp.com/remote/cs/images/success.png" alt="" /><br />
					<strong>Good news, you can install Alkaline here!</strong><br />
					<span class="quiet small">What are you waiting for? <a href="http://www.alkalineapp.com/buy/">Purchase and download Alkaline today.</a></span>
					<?php
				}
				else{
					?>
					<img src="http://www.alkalineapp.com/remote/cs/images/failure.png" alt="" /><br />
					<strong>Uh-oh, you cannot install Alkaline here.</strong><br />
					<span class="quiet small"><a href="http://www.alkalineapp.com/">Learn how to make your Web server compatible with Alkaline.</a></span>
					<?php
				}
				?>
			</p>

			<hr />
		
			<div id="footer" class="span-24 last">
				<img src="http://www.alkalineapp.com/remote/cs/images/icon.png" alt="" /> Powered by <a href="http://www.alkalineapp.com/compatibility/">Alkaline</a>. Copyright &#0169; 2010-2011 by <a href="http://www.budinltd.com/">Budin Ltd.</a> All rights reserved.
			</div>
		</div>
	</div>
</body>
</html>