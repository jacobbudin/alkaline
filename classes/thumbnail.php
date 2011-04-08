<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalineapp.com/
*/

/**
 * @author Budin Ltd. <contact@budinltd.com>
 * @copyright Copyright (c) 2010-2011, Budin Ltd.
 * @version 1.0
 */

class Thumbnail extends Alkaline{
	public $library;
	public $thumbnail;
	protected $path;
	public $quality;
	public $ext;
	
	/**
	 * Initiates Thumbnail class
	 *
	 * @param file $file Filename
	 */
	public function __construct($file=null){
		parent::__construct();
		
		$file = parent::correctWinPath($file);
		
		$this->quality = $this->returnConf('thumb_compress_tol');
		if(class_exists('Imagick', false) and ($this->returnConf('thumb_imagick') or in_array($ext, array('pdf', 'svg')))){
			$this->library = 'imagick';
			$this->thumbnail = new Imagick($file);
		}
		else{
			$this->library = 'gd';
			require_once('phpthumb/ThumbLib.inc.php');
			$this->thumbnail = PhpThumbFactory::create($file, array('jpegQuality' => $this->quality, 'resizeUp' => true));
		}
		
		$this->ext = Image::getExt($file);
	}
	
	public function __destruct(){
		if($this->library == 'imagick'){
			$this->thumbnail->clear();
			$this->thumbnail->destroy();
		}
		
		parent::__destruct();
	}
	
	public function resize($width, $height){
		if($this->library == 'gd'){
			$this->thumbnail->resize($height, $width);
		}
		elseif($this->library == 'imagick'){
			switch($this->ext){
				case 'jpg':
					$this->thumbnail->setImageCompression(Imagick::COMPRESSION_JPEG); 
					$this->thumbnail->setImageCompressionQuality($this->quality);
					$this->thumbnail->thumbnailImage($width, $height);
					break;
				case 'png':
					$this->thumbnail->thumbnailImage($width, $height);
					break;
				case 'gif':
					$this->thumbnail->thumbnailImage($width, $height);
					break;
				case 'pdf':
					$res = $this->thumbnail->getImageResolution();
					$x_ratio = $res['x'] / $this->thumbnail->getImageWidth();
					$y_ratio = $res['y'] / $this->thumbnail->getImageHeight();
					$this->thumbnail->removeImage();
					$this->thumbnail->setResolution($width * $x_ratio, $height * $y_ratio);
					$this->thumbnail->readImage($src);
					$this->ext = 'png';
					$this->thumbnail->setImageFormat('png');
					break;
				case 'svg':
					$res = $this->thumbnail->getImageResolution();
					$x_ratio = $res['x'] / $this->thumbnail->getImageWidth();
					$y_ratio = $res['y'] / $this->thumbnail->getImageHeight();
					$this->thumbnail->removeImage();
					$this->thumbnail->setResolution($width * $x_ratio, $height * $y_ratio);
					$this->thumbnail->readImage($src);
					$this->ext = 'png';
					$this->thumbnail->setImageFormat('png');
					break;
			}
		}
		
		return $this->thumbnail;
	}
	
	public function adaptiveResize($width, $height){
		if($this->library == 'gd'){
			$this->thumbnail->adaptiveResize($height, $width);
		}
		elseif($this->library == 'imagick'){
			switch($this->ext){
				case 'jpg':
					$this->thumbnail->setImageCompression(Imagick::COMPRESSION_JPEG); 
					$this->thumbnail->setImageCompressionQuality($this->quality);
					$this->thumbnail->cropThumbnailImage($width, $height);
					break;
				case 'png':
					$this->thumbnail->cropThumbnailImage($width, $height);
					break;
				case 'gif':
					$this->thumbnail->cropThumbnailImage($width, $height);
					break;
				case 'pdf':
					$res = $this->thumbnail->getImageResolution();
					$x_ratio = $res['x'] / $this->thumbnail->getImageWidth();
					$y_ratio = $res['y'] / $this->thumbnail->getImageHeight();
					$this->thumbnail->removeImage();
					$this->thumbnail->setResolution($width_orig * $x_ratio, $height_orig * $y_ratio);
					$this->thumbnail->readImage($src);
					$this->thumbnail->setImageFormat('png');
					$this->ext = 'png';
					$this->thumbnail->cropThumbnailImage($width, $height);
					break;
				case 'svg':
					$res = $this->thumbnail->getImageResolution();
					$x_ratio = $res['x'] / $this->thumbnail->getImageWidth();
					$y_ratio = $res['y'] / $this->thumbnail->getImageHeight();
					$this->thumbnail->removeImage();
					$this->thumbnail->setResolution($width_orig * $x_ratio, $height_orig * $y_ratio);
					$this->thumbnail->readImage($src);
					$this->thumbnail->setImageFormat('png');
					$this->ext = 'png';
					$this->thumbnail->cropThumbnailImage($width, $height);
					break;
			}
		}
		
		return $this->thumbnail;
	}
	
	public function save($path){
		$this->path = parent::correctWinPath($path);
		
		if($this->library == 'gd'){
			$this->thumbnail->save($this->path);
		}
		elseif($this->library == 'imagick'){
			$this->thumbnail->writeImage($this->path);	
		}
		
		return $this->thumbnail;
	}
	
	/**
	 * Watermark image
	 *
	 * @param string $watermark Watermark full path
	 * @param int $margin Margin (in pixels)
	 * @param string $position Watermark position
	 * @return Thumbnail
	 */
	public function watermark($watermark, $margin=null, $position=null){
		if(empty($margin)){ $margin = $this->returnConf('thumb_watermark_margin'); }
		if(empty($position)){ $position = $this->returnConf('thumb_watermark_pos'); }
		
		$watermark = parent::correctWinPath($watermark);
		
		// Check to see if watermark exists
		if(!file_exists($watermark)){ $this->addNote('Watermark file could not be found', 'error'); return; }
		
		if($this->library == 'imagick'){
			$image = new Imagick($this->path);
			$image_watermark = new Imagick($watermark);
			
			list($width, $height) = getimagesize($src);
			list($width_watermark, $height_watermark) = getimagesize($watermark);
			
			switch($this->ext){
				case 'jpg':
					$image->setImageCompression(Imagick::COMPRESSION_JPEG); 
					$image->setImageCompressionQuality($this->quality);
					break;
				case 'png':
					break;
				case 'gif':
					break;
			}
			
			if((($height_watermark + ($margin * 2)) > $height) or (($width_watermark + ($margin * 2)) > $width)){ return false; break; }
			
			list($pos_x, $pos_y) = $this->watermarkPosition($height, $width, $height_watermark, $width_watermark, $margin, $position);
			
			$image->compositeImage($image_watermark, Imagick::COMPOSITE_DEFAULT, $pos_x, $pos_y);
			$image->flattenImages();
			
			$image->writeImage($this->path);
			$image->clear();
			$image->destroy();
			return true;
		}
		elseif($this->library == 'gd'){
			$watermark = imagecreatefrompng($watermark);

			imagealphablending($watermark, false);
		    imagesavealpha($watermark, true);

			$width_watermark = imagesx($watermark);
			$height_watermark = imagesy($watermark);
			
			switch($this->ext){
				case 'jpg':
					$image = imagecreatefromjpeg($this->path);
					imagealphablending($image, true);
				
					$width = imagesx($image);
					$height = imagesy($image);
				
					if((($height_watermark + ($margin * 2)) > $height) or (($width_watermark + ($margin * 2)) > $width)){ return false; break; }
					
					list($pos_x, $pos_y) = $this->watermarkPosition($height, $width, $height_watermark, $width_watermark, $margin, $position);
				
					imagecopy($image, $watermark, $pos_x, $pos_y, 0, 0, $width_watermark, $height_watermark);
					imagedestroy($watermark);
					imagejpeg($image, $this->path, $this->quality);
					imagedestroy($image);
				
					return true;
					break;
				case 'png':
					$image = imagecreatefrompng($this->path);
					imagealphablending($image, true);
					
					$quality_tmp = floor((1 / $this->quality) * 95);
					
					$width = imagesx($image);
					$height = imagesy($image);
				
					if((($height_watermark + ($margin * 2)) > $height) or (($width_watermark + ($margin * 2)) > $width)){ return false; break; }
				
					list($pos_x, $pos_y) = $this->watermarkPosition($height, $width, $height_watermark, $width_watermark, $margin, $position);
				
					imagecopy($image, $watermark, $pos_x, $pos_y, 0, 0, $width_watermark, $height_watermark);
					imagedestroy($watermark);
					imagepng($image, $this->path, $quality_tmp);
					imagedestroy($image);
				
					return true;
					break;
				case 'gif':
					$image = imagecreatefromgif($this->path);
					
					$width = imagesx($image);
					$height = imagesy($image);
				
					if((($height_watermark + ($margin * 2)) > $height) or (($width_watermark + ($margin * 2)) > $width)){ return false; break; }
				
					list($pos_x, $pos_y) = $this->watermarkPosition($height, $width, $height_watermark, $width_watermark, $margin, $position);
				
					$image_temp = imagecreatetruecolor($width, $height);
					imagecopy($image_temp, $image, 0, 0, 0, 0, $width, $height);
					$image = $image_temp;
					imagealphablending($image, true);
				
					imagecopy($image, $watermark, $pos_x, $pos_y, 0, 0, $width_watermark, $height_watermark);
					imagedestroy($watermark);
					imagegif($image, $this->path, $this->quality);
					imagedestroy($image);
				
					return true;
					break;
				default:
					return false;
					break;
			}
		}
	}
	
	/**
	 * Determine watermark position
	 *
	 * @param int $image_height Image height (in pixels)
	 * @param int $image_width Image width (in pixels)
	 * @param int $water_height Watermark height (in pixels)
	 * @param int $water_width Watermark width (in pixels)
	 * @param int $margin Margin (in pixels)
	 * @param string $position 'nw', 'ne', 'sw', '00', 'n0', 's0', '0e', '0w'
	 * @return void
	 */
	private function watermarkPosition($image_height, $image_width, $water_height, $water_width, $margin=null, $position=null){
		if(empty($margin)){ $margin = $this->returnConf('thumb_watermark_margin'); }
		if(empty($position)){ $position = $this->returnConf('thumb_watermark_pos'); }
		switch($position){
			case 'nw':
				$pos_x = $margin;
				$pos_y = $margin;
				break;
			case 'ne':
				$pos_x = $image_width - $water_width - $margin;
				$pos_y = $margin;
				break;
			case 'sw':
				$pos_x = $margin;
				$pos_y = $image_height - $water_height - $margin;
				break;
			case 'se':
				$pos_x = $image_width - $water_width - $margin;
				$pos_y = $image_height - $water_height - $margin;
				break;
			case '00':
				$pos_x = ($image_width / 2) - ($water_width / 2);
				$pos_y = ($image_height / 2) - ($water_height / 2);
				break;
			case 'n0':
				$pos_x = ($image_width / 2) - ($water_width / 2);
				$pos_y = $margin;
				break;
			case 's0':
				$pos_x = ($image_width / 2) - ($water_width / 2);
				$pos_y = $image_height - $water_height - $margin;
				break;
			case '0e':
				$pos_x = $image_width - $water_width - $margin;
				$pos_y = ($image_height / 2) - ($water_height / 2);
				break;
			case '0w':
				$pos_x = $margin;
				$pos_y = ($image_height / 2) - ($water_height / 2);
				break;
			default:
				return false;
				break;
		}
		return array($pos_x, $pos_y);
	}
}

?>