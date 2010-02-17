<?php

function imageExt($img){
	$type = exif_imagetype($img);
	switch($type){
		case 1:
			return 'gif'; break;
		case 2:
			return 'jpg'; break;
		case 3:
			return 'png'; break;
		default:
			return false; break;
	}
}

function imageScaleMax($src, $dest, $height, $width, $quality, $ext=null){
	if(empty($ext)){ $ext = imageExt($src); }
	switch($ext){
		case 'jpg':
			list($width_orig, $height_orig) = getimagesize($src);
	
			$ratio_orig = $width_orig / $height_orig;
			$ratio = $width / $height;
	
			if($ratio_orig > $ratio){ $height = $width / $ratio_orig; }
			else{ $width = $height * $ratio_orig; }
	
			$image_p = imagecreatetruecolor($width, $height);
			$image = imagecreatefromjpeg($src);
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
			imagejpeg($image_p, $dest, $quality);
	
			imagedestroy($image);
			imagedestroy($image_p);
			return true;
			break;
		case 'png':
			list($width_orig, $height_orig) = getimagesize($src);
	
			$ratio_orig = $width_orig / $height_orig;
			$ratio = $width / $height;
	
			if($ratio_orig > $ratio){ $height = $width / $ratio_orig; }
			else{ $width = $height * $ratio_orig; }
	
			$image_p = imagecreatetruecolor($width, $height);
			$image = imagecreatefrompng($src);
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
			imagepng($image_p, $dest, $quality);
	
			imagedestroy($image);
			imagedestroy($image_p);
			return true;
			break;
		case 'gif':
			list($width_orig, $height_orig) = getimagesize($src);
	
			$ratio_orig = $width_orig / $height_orig;
			$ratio = $width / $height;
	
			if($ratio_orig > $ratio){ $height = $width / $ratio_orig; }
			else{ $width = $height * $ratio_orig; }
	
			$image_p = imagecreatetruecolor($width, $height);
			$image = imagecreatefromgif($src);
			imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
			imagegif($image_p, $dest, $quality);
	
			imagedestroy($image);
			imagedestroy($image_p);
			return true;
			break;
		default:
			return false;
			break;
	}
}

function imageScaleFill($src, $dest, $height, $width, $quality, $ext=null){
	if(empty($ext)){ $ext = imageExt($src); }
	switch($ext){
		case 'jpg':
			list($width_orig, $height_orig) = getimagesize($src);
	
			$ratio_orig = $width_orig / $height_orig;
			$ratio = $width / $height;
	
			if($ratio_orig > $ratio){
				$image_p = imagecreatetruecolor($width, $height);
				$image = imagecreatefromjpeg($src);
				$pixel = ($width_orig - $height_orig) / 2;
				imagecopyresampled($image_p, $image, 0, 0, $pixel, 0, $width * $ratio_orig, $height, $width_orig, $height_orig);
				imagejpeg($image_p, $dest, $quality);
			}
			else{
				$image_p = imagecreatetruecolor($width, $height);
				$image = imagecreatefromjpeg($src);
				$pixel = ($height_orig - $width_orig) / 2;
				imagecopyresampled($image_p, $image, 0, 0, 0, $pixel, $width, $height * $ratio_orig, $width_orig, $height_orig);
				imagejpeg($image_p, $dest, $quality);
			}
	
			imagedestroy($image);
			imagedestroy($image_p);
			return true;
			break;
		case 'png':
			list($width_orig, $height_orig) = getimagesize($src);
	
			$ratio_orig = $width_orig / $height_orig;
			$ratio = $width / $height;
	
			if($ratio_orig > $ratio){
				$image_p = imagecreatetruecolor($width, $height);
				$image = imagecreatefrompng($src);
				$pixel = ($width_orig - $height_orig) / 2;
				imagecopyresampled($image_p, $image, 0, 0, $pixel, 0, $width * $ratio_orig, $height, $width_orig, $height_orig);
				imagepng($image_p, $dest, $quality);
			}
			else{
				$image_p = imagecreatetruecolor($width, $height);
				$image = imagecreatefrompng($src);
				$pixel = ($height_orig - $width_orig) / 2;
				imagecopyresampled($image_p, $image, 0, 0, 0, $pixel, $width, $height * $ratio_orig, $width_orig, $height_orig);
				imagepng($image_p, $dest, $quality);
			}
	
			imagedestroy($image);
			imagedestroy($image_p);
			return true;
			break;
		case 'gif':
			list($width_orig, $height_orig) = getimagesize($src);
	
			$ratio_orig = $width_orig / $height_orig;
			$ratio = $width / $height;
	
			if($ratio_orig > $ratio){
				$image_p = imagecreatetruecolor($width, $height);
				$image = imagecreatefromgif($src);
				$pixel = ($width_orig - $height_orig) / 2;
				imagecopyresampled($image_p, $image, 0, 0, $pixel, 0, $width * $ratio_orig, $height, $width_orig, $height_orig);
				imagegif($image_p, $dest, $quality);
			}
			else{
				$image_p = imagecreatetruecolor($width, $height);
				$image = imagecreatefromgif($src);
				$pixel = ($height_orig - $width_orig) / 2;
				imagecopyresampled($image_p, $image, 0, 0, 0, $pixel, $width, $height * $ratio_orig, $width_orig, $height_orig);
				imagegif($image_p, $dest, $quality);
			}
	
			imagedestroy($image);
			imagedestroy($image_p);
			return true;
			break;
		default:
			return false;
			break;
	}
}

?>