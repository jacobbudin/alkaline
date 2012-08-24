<?php

/*
// Alkaline
// Copyright (c) 2010-2012 by Budin Ltd. Some rights reserved.
// http://www.alkalineapp.com/
*/

/**
 * @author Budin Ltd. <contact@budinltd.com>
 * @copyright Copyright (c) 2010-2012, Budin Ltd.
 * @version 1.0
 */

class Thumbnail extends Alkaline{
	public $library;
	public $thumbnail;
	protected $path;
	public $quality;
	public $ext;
	public $file;
	
	/**
	 * Initiates Thumbnail class
	 *
	 * @param file $file Filename
	 */
	public function __construct($file=null){
		parent::__construct();
		
		$file = parent::correctWinPath($file);
		$ext = Image::getExt($file);
		
		$this->quality = $this->returnConf('thumb_compress_tol');
		if(empty($this->quality)){ $this->quality = 100; }
		
		if(class_exists('Imagick', false) and ($this->returnConf('thumb_imagick') or in_array($ext, array('pdf', 'svg')))){
			$this->library = 'imagick';
			$this->thumbnail = new Imagick($file);
		}
		else{
			$this->library = 'gd';
			require_once(PATH . CLASSES . 'phpthumb/ThumbLib.inc.php');
			$this->thumbnail = PhpThumbFactory::create($file, array('jpegQuality' => $this->quality));
		}
		
		$this->file = $file;
		$this->ext = $ext;
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
			$this->thumbnail->resize($width, $height);
		}
		elseif($this->library == 'imagick'){
			$size = Image::getSize($this->file, $this->ext);
			
			$width_orig = $size['width'];
			$height_orig = $size['height'];
			
			if(($width_orig <= $width) and ($height_orig <= $height)){
				switch($ext){
					case 'jpg':
						return true;
					case 'png':
						return true;
					case 'gif':
						return true;
				}
			}
			
			$ratio_orig = $width_orig / $height_orig;
			$ratio = $width / $height;

			if($ratio_orig > $ratio){ $height = $width / $ratio_orig; }
			else{ $width = $height * $ratio_orig; }
			
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
					$this->thumbnail->readImage($this->file);
					$this->ext = 'png';
					$this->thumbnail->setImageFormat('png');
					break;
				case 'svg':
					$res = $this->thumbnail->getImageResolution();
					$x_ratio = $res['x'] / $this->thumbnail->getImageWidth();
					$y_ratio = $res['y'] / $this->thumbnail->getImageHeight();
					$this->thumbnail->removeImage();
					$this->thumbnail->setResolution($width * $x_ratio, $height * $y_ratio);
					$this->thumbnail->readImage($this->file);
					$this->ext = 'png';
					$this->thumbnail->setImageFormat('png');
					break;
			}
		}
		
		return $this->thumbnail;
	}
	
	public function adaptiveResize($width, $height){
		if($this->library == 'gd'){
			$this->thumbnail->adaptiveResize($width, $height);
		}
		elseif($this->library == 'imagick'){
			$size = Image::getSize($this->file, $this->ext);
			
			$width_orig = $size['width'];
			$height_orig = $size['height'];
			
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
					$this->thumbnail->readImage($this->file);
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
					$this->thumbnail->readImage($this->file);
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
	 * Watermark image after ->save()
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
			
			list($width, $height) = getimagesize($this->path);
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
		
		$pos_x = intval($pos_x);
		$pos_y = intval($pos_y);
		
		return array($pos_x, $pos_y);
	}
	
	/**
	 * Copy original image's metadata to thumbnail after ->save()
	 *
	 * @return void
	 */
	public function metadata(){
		getimagesize($this->file, $info);
		
		if(isset($info['APP13'])){
			$iptc = iptcparse($info["APP13"]);
			
			$utf8seq = chr(0x1b) . chr(0x25) . chr(0x47);
			$length = strlen($utf8seq);
			$data = chr(0x1C) . chr(1) . chr('090') . chr($length >> 8) . chr($length & 0xFF) . $utf8seq;

			foreach($iptc as $tag => $string){
				if(is_array($string)){
					$string = $string[0];
				}
				if(empty($string)){
					continue;
				}
				$class = substr($tag, 0, 1);
			    $tag = substr($tag, 2);
			    $data .= $this->iptc_make_tag($class, $tag, $string);
			}

			$img = imagecreatefromjpeg($this->path);
		    imagejpeg($img, $this->path, 100); 
		    imagedestroy($img);

			$content = iptcembed($data, $this->path);
			@unlink($this->path);
			file_put_contents($this->path, $content);
		}
	}
	
	/**
	 * Helper function for metadata()
	 *
	 * @param string $rec 
	 * @param string $data 
	 * @param string $value 
	 * @return string
	 */
	public function iptc_make_tag($rec, $data, $value){
	    $length = strlen($value);
	    $retval = chr(0x1C) . chr($rec) . chr($data);

	    if($length < 0x8000){
	        $retval .= chr($length >> 8) .  chr($length & 0xFF);
	    }
	    else{
	        $retval .= chr(0x80) . 
	                   chr(0x04) . 
	                   chr(($length >> 24) & 0xFF) . 
	                   chr(($length >> 16) & 0xFF) . 
	                   chr(($length >> 8) & 0xFF) . 
	                   chr($length & 0xFF);
	    }

	    return $retval . $value;
	}
}

?>