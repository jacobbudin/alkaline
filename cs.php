<?php

/*
// Alkaline
// Copyright (c) 2010 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalinenapp.com/
*/

class AlkalineCS{
	const build = 1;
	const copyright = 'Powered by <a href="http://www.alkalineapp.com/">Alkaline</a>. Copyright &copy; 2010 by <a href="http://www.budinltd.com/">Budin Ltd.</a> All rights reserved.';
	const version = 'Alpha (May 19)';
	
	public $compatible;
	
	public $php_version;
	public $php_extensions;
	public $phpinfo;
	Public $php_pdo_drivers;
	
	function __construct(){
		$this->compatible = true;
		$this->php_version = phpversion();
		$this->php_extensions = get_loaded_extensions();
		ob_start();
		phpinfo();
		$this->phpinfo = ob_get_contents(); 
		ob_end_clean();
		$php_pdo_drivers = @PDO::getAvailableDrivers();
		foreach($php_pdo_drivers as $driver){
			switch($driver){
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
			$positive = 'Installed';
		}
		if(empty($negative)){
			$negative = 'Not installed';
		}
		
		if($bool === true){
			echo '<td class="test_result positive">' . $positive . '</td>';
		}
		elseif($bool === false){
			echo '<td class="test_result negative">' . $negative . '</td>';
		}
		else{
			echo '<td class="test_result unknown">Unknown</td>';
		}
	}
}

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
	<style>
		.container{ width: 670px; }
		body { margin: 5em auto 2em auto; background-image: url(http://www.alkalineapp.com/remote/cs/images/bg.png); background-repeat: repeat-x; font-family: "Lucida Sans Unicode", "Lucida Grande", Verdana, sans-serif; }
		a { color: #222; }
		a:hover, a:active { text-decoration: none; }
		h4 { padding-bottom: 5px; border-bottom: 1px solid #ddd; }
		h5 { margin-bottom: 0; }
		#header { text-align: center; }
		#icon { margin-top: 10px; margin-bottom: 4em; }
		#result { margin-top: 1.5em; font-size: 1.25em; text-align: center; }
		#footer { margin-top: 3em; color: #999; font-size: .9em;  }
		#footer a { color: #999; }
		.test_result { text-align: center; width: 20%; font-size: .9em; font-weight: bold; text-transform: uppercase; }
		.test_result.positive { background-color: #c0ddea; color: #021e2f; }
		.test_result.negative { color: #555; }
		.test_result.unknown { color: #555; }
		.light { text-align: center; font-size: .9em; font-weight: normal; text-transform: none; }
		.result_icon { margin-bottom: 10px; }
	</style>
</head>
<body>
	<div class="container">
		<div id="header" class="span-17 last">
			<img id="icon" src="http://www.alkalineapp.com/remote/cs/images/alkaline.png" alt="" />
		</div>
		<div class="span-17 last">
			<h3>Alkaline Compatibility Suite</h3>
			
			<p>The Alkaline Compatibility Suite tests your Web server to ensure it&#8217;s compatible with <a href="http://www.alkalineapp.com/">Alkaline</a>.</p><br />
			
			<?php $test = new AlkalineCS(); ?>
			
			<h4>Required</h4>
			
			<table>
				<tr>
					<td>
						<h5>Internet connectivity</h5>
						<span class="small quiet">An Internet connection is required to activate Alkaline.</span>
					</td>
					<?php echo $test->boolToHTML($test->isNet(), 'Connected', 'Disconnected'); ?>
				</tr>
				<tr>
					<td>
						<h5>PHP 5.2+</h5>
						<span class="small quiet">PHP allows Alkaline to produce dynamic Web pages.</span>
					</td>
					<?php echo $test->boolToHTML($test->isVer('/^(5\.2|5\.3).+/is', $test->php_version), $test->php_version, $test->php_version); ?>
				</tr>
				<tr>
					<td>
						<h5>PHP PDO support</h5>
						<span class="small quiet">PHP PDO allows Alkaline to connect to various database types.</span>
					</td>
					<?php echo $test->boolToHTML(($test->isExt('PDO') and (count($test->php_pdo_drivers) > 0)), 'Installed<br /><span class="light">(' . implode(', ', $test->php_pdo_drivers) . ')</span>'); ?>
				</tr>
				<tr>
					<td>
						<h5>GD image library</h5>
						<span class="small quiet">GD allows Alkaline to create and manipulate image files.</span>
					</td>
					<?php echo $test->boolToHTML($test->isExt('gd')); ?>
				</tr>
				<tr>
					<td>
						<h5>Zend Optimizer</h5>
						<span class="small quiet">Zend Optimizer is used to verify the authenticity of Alkaline.</span>
					</td>
					<?php echo $test->boolToHTML($test->isExt('Zend Optimizer')); ?>
				</tr>
			</table>
			
			
			<h4>Optional</h4>
			<table>
				<tr>
					<td>
						<h5>Apache mod_rewrite module</h5>
						<span class="small quiet">Apache mod_rewite allows Alkaline to use clean, semantic URLs.</span>
					</td>
					<?php echo $test->boolToHTML($test->isThere('mod_rewrite')); ?>
				</tr>
				<tr>
					<td>
						<h5>PHP EXIF support</h5>
						<span class="small quiet">PHP EXIF allows Alkaline to read EXIF data from your photos.</span>
					</td>
					<?php echo $test->boolToHTML($test->isExt('exif', false)); ?>
				</tr>
				<tr>
					<td>
						<h5>PHP SQLite support</h5>
						<span class="small quiet">PHP SQLite allows Alkaline to operate without a traditional database.</span>
					</td>
					<?php echo $test->boolToHTML($test->isThere('PDO Driver for SQLite 3.x')); ?>
				</tr>
			</table>
			
			<div id="result">
				<?php
				if($test->compatible == true){
					?>
					<img src="http://www.alkalineapp.com/remote/cs/images/check.png" alt="" class="result_icon" /><br />
					Good news, you can install Alkaline here!<br />
					<span style="font-size: .7em;">(What are you waiting for? <a href="http://www.alkalineapp.com/">Purchase and download Alkaline today.</a>)</span>
					<?php
				}
				else{
					?>
					<img src="http://www.alkalineapp.com/remote/cs/images/stop.png" alt="" class="result_icon" /><br />
					Uh-oh, you cannot install Alkaline here.<br />
					<span style="font-size: .7em;">(<a href="http://www.alkalineapp.com/">Learn how to make your Web server compatible with Alkaline.</a>)</span>
					<?php
				}
				?>
			</div>
			
			<div id="footer">
				<?php echo AlkalineCS::copyright; ?>
			</div>
		</div>
	</div>
</body>
</html>