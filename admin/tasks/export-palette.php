<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalineapp.com/
*/

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true);

$image_id = intval($_REQUEST['image_id']);
$format = strtolower(strip_tags(strval($_REQUEST['format'])));

// Get image
$images = new Image($image_id);
$title = $images->images[0]['image_title'];
$rgb_colors = unserialize($images->images[0]['image_colors']);

// Convert RGB image colors to HEX values
function rgb2hex(){
	$str = '';
	foreach(func_get_args() as $c){
		$str .= substr(sprintf('0%x',$c), -2);
	}
	return $str;
}

$hex_colors = array();

$i = 1;
foreach($rgb_colors as $rgb => $percent){
	$rgb = explode(',', $rgb);
	$hex = rgb2hex($rgb[0], $rgb[1], $rgb[2]);
	$hex_colors[] = array($hex, 'Color #' . $i++);
}

// Convert to format
if($format == 'ase'){
	/** 
	* Make an Adobe Swatch Exchange file 
	* 
	* @param	array 
	* @return	string 
	* @see		http://www.colourlovers.com/web/blog/2007/11/08/color-palettes-in-adobe-swatch-exchange-ase 
	* @author	Chris Williams - http://www.colourlovers.com 
	* @version	2.0 

	* This script uses the Multibyte String extension: http://www.php.net/manual/en/book.mbstring.php 

	* MIT License 

	* Copyright (c) 2011 Chris Williams - http://www.colourlovers.com 

	* Permission is hereby granted, free of charge, to any person obtaining a copy 
	* of this software and associated documentation files (the 'Software'), to deal 
	* in the Software without restriction, including without limitation the rights 
	* to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
	* copies of the Software, and to permit persons to whom the Software is 
	* furnished to do so, subject to the following conditions: 

	* The above copyright notice and this permission notice shall be included in 
	* all copies or substantial portions of the Software. 

	* THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR 
	* IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, 
	* FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE 
	* AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER 
	* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, 
	* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN 
	* THE SOFTWARE. 
	*/ 
	function mkASE($palettes) { 
		$internal_encoding = mb_internal_encoding(); 
		mb_internal_encoding('UTF-8'); 

		ob_start(); 

		$totalColors = $numPalettes = 0; 

		foreach ($palettes as $palette) { 
			$totalColors += count($palette['colors']); 
			++$numPalettes; 
		} 

		echo 'ASEF'; # File signature 
		echo pack('n*',1,0); # Version 
		echo pack('N',$totalColors + ($numPalettes * 2)); # Total number of blocks 

		foreach ($palettes as $palette) { 
			echo pack('n',0xC001); # Group start 

			# Length of this block - see below 

			$title	= (mb_convert_encoding($palette['title'],'UTF-16BE','UTF-8') . pack('n',0)); 
			$buffer = pack('n',(strlen($title) / 2)); # Length of the group title 
			$buffer .= $title; # Group title 

			echo pack('N',strlen($buffer)); # Length of this block 
			echo $buffer; 

			foreach ($palette['colors'] as $color) { 
				echo pack('n',1); # Color entry 

				# Length of this block - see below 

				$title	= (mb_convert_encoding($color[1],'UTF-16BE','UTF-8') . pack('n',0)); 
				$buffer = pack('n',(strlen($title) / 2)); # Length of the title 
				$buffer .= $title; # Title 

				# Colors 
				list ($r,$g,$b) = array_map('intval',sscanf($color[0],'%2x%2x%2x')); 
				$r /= 255; 
				$g /= 255; 
				$b /= 255; 

				$buffer .= 'RGB '; 
				$buffer .= strrev(pack('f',$r)); 
				$buffer .= strrev(pack('f',$g)); 
				$buffer .= strrev(pack('f',$b)); 
				$buffer .= pack('n',0); # Color type - 0x00 'Global' 

				echo pack('N',strlen($buffer)); # Length of this block 
				echo $buffer; 
			} 
			echo pack('n',0xC002); # Group end 

			echo pack('N',0); # Length of 'Group end' block, which is 0 
		} 

		$return = ob_get_contents(); 
		ob_end_clean(); 

		mb_internal_encoding($internal_encoding); 

		return $return; 
	} 

	/* 
	Colors from the Palettes: 
		Vintage Modern		  http://www.colourlovers.com/palette/110225/Vintage_Modern 
		Thought Provoking	  http://www.colourlovers.com/palette/694737/Thought_Provoking 

	Licensed under Creative Commons: http://creativecommons.org/licenses/by-nc-sa/3.0/ 
*/

	$palettes = array ( 
		array ( 
			'title'		=> $title, 
			'colors'	=> $hex_colors
		), 
	); 

	$palette = mkASE($palettes);
	
	$encoding = 'binary';
	$mime = 'application/octet-stream';
}
elseif($format == 'css'){
	$palette = '';
	$i = 1;
	foreach($hex_colors as $color){
		$palette .= '.color' . $i++ . ' { background-color: #' . $color[0] . '; }' . "\n";
	}
	
	$encoding = 'ascii';
	$mime = 'text/x-c';
}
else{
	exit();
}

if(ini_get('zlib.output_compression')){ ini_set('zlib.output_compression', 'Off'); }
header('Pragma: public'); // required
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Cache-Control: private', false); // required for certain browsers
header('Content-Disposition: attachment; filename="' . $image_id . '-color_palette.' . $format . '";');
// Send Content-Transfer-Encoding HTTP header
// (use binary to prevent files from being encoded/messed up during transfer)
header('Content-Transfer-Encoding: ' . $encoding);
header('Content-Length: ' . $bytes);
header('Content-Type: ' . $mime);
header('Content-Description: File Transfer');
echo $palette;

?>