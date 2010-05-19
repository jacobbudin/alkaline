<?php

class Photo extends Alkaline{
	public $photos;
	public $photo_ids;
	public $photo_count;
	public $user;
	protected $sql;
	
	public function __construct($photos){
		parent::__construct();
		parent::convertToArray($photos);
		
		$this->user['user_id'] = DEFAULT_USER_ID;
		
		$photo_ids = array();
		
		foreach($photos as $key => $value){
			if(preg_match('/^\//', $value)){
				$import = true;
				$file = $value;
				
				// Verify file exists
				if(file_exists($file)){
					// Add photo to database
					$photo_ext = $this->imageExt($file);
					$filename = substr(strrchr($file, '/'), 1);
					
					$query = 'INSERT INTO photos (user_id, photo_ext, photo_name, photo_uploaded) VALUES (' . $this->user['user_id'] . ', "' . $photo_ext . '", "' . addslashes($filename) . '", "' . date('Y-m-d H:i:s') . '");';
					$this->db->exec($query);
					$photo_id = $this->db->lastInsertId();
					$photo_ids[] = $photo_id;

					// Copy photo to archive, delete original from shoebox
					copy($file, PATH . PHOTOS . $photo_id . '.' . $photo_ext);
					unlink($file);
				}
			}
			else{
				$import = false;
				$photo_ids[] = $value;
			}
		}
		
		parent::convertToIntegerArray($photo_ids);
		
		$this->sql = ' WHERE photo_id = ' . implode(' OR photo_id = ', $photo_ids);
		
		$query = $this->db->prepare('SELECT * FROM photos' . $this->sql . ';');
		$query->execute();
		$this->photos = $query->fetchAll();
		
		$this->photo_ids = array();
		foreach($this->photos as $photo){
			$this->photo_ids[] = $photo['photo_id'];
		}
		
		$this->photo_count = count($this->photos);
		
		for($i = 0; $i < $this->photo_count; ++$i){
			$this->photos[$i]['photo_file'] = PATH . PHOTOS . $this->photos[$i]['photo_id'] . '.' . $this->photos[$i]['photo_ext'];
		}
		
		if(@$import){
			$this->sizePhoto();
			$this->exifPhoto();
		}
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function attachUser($user){
		$this->user = $user->user;
	}
	
	// Generate photo thumbnails based on sizes in database
	public function sizePhoto(){
		// Look up sizes in database
		$query = $this->db->prepare('SELECT * FROM sizes');
		$query->execute();
		$sizes = $query->fetchAll();
		
		// Generate thumbnails
		for($i = 0; $i < $this->photo_count; ++$i){
			foreach($sizes as $size){
				$size_height = $size['size_height'];
				$size_width = $size['size_width'];
				$size_type = $size['size_type'];
				$size_prepend = $size['size_prepend'];
				$size_append = $size['size_append'];
				switch($size_type){
					case 'fill':
						$this->imageFill($this->photos[$i]['photo_file'], PATH . PHOTOS . $size_prepend . $this->photos[$i]['photo_id'] . $size_append . '.' . $this->photos[$i]['photo_ext'], $size_height, $size_width, null, $this->photos[$i]['photo_ext']);
						break;
					case 'scale':
						$this->imageScale($this->photos[$i]['photo_file'], PATH . PHOTOS . $size_prepend . $this->photos[$i]['photo_id'] . $size_append . '.' . $this->photos[$i]['photo_ext'], $size_height, $size_width, null, $this->photos[$i]['photo_ext']);
						break;
					default:
						return false; break;
				}
			}
		}
	}
	
	public function updateFields($array){
		for($i = 0; $i < $this->photo_count; ++$i){
			$fields = array();
			foreach($array as $key => $value){
				$fields[] = $key . ' = "' . $value . '"';
			}
			$sql = implode(', ', $fields);
			$this->db->exec('UPDATE photos SET ' . $sql . ' WHERE photo_id = ' . $this->photos[$i]['photo_id'] . ';');
		}
	}
	
	private function imageExt($file){
		$type = exif_imagetype($file);
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
	
	private function imageFill($src, $dest, $height, $width, $quality=null, $ext=null){
		if(empty($quality)){ $quality = IMG_QUAL; }
		if(empty($ext)){ $ext = $this->imageExt($src); }
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
					imagecopyresampled($image_p, $image, 0, 0, 0, $pixel, $width, $height * (1 / $ratio_orig), $width_orig, $height_orig);
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
					imagecopyresampled($image_p, $image, 0, 0, 0, $pixel, $width, $height * (1 / $ratio_orig), $width_orig, $height_orig);
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
					imagecopyresampled($image_p, $image, 0, 0, 0, $pixel, $width, $height * (1 / $ratio_orig), $width_orig, $height_orig);
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
	
	private function imageScale($src, $dest, $height, $width, $quality=null, $ext=null){
		if(empty($quality)){ $quality = IMG_QUAL; }
		if(empty($ext)){ $ext = $this->imageExt($src); }
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
	
	public function exifPhoto(){
		for($i = 0; $i < $this->photo_count; ++$i){
			// Read EXIF data
			$exif = @exif_read_data($this->photos[$i]['photo_file'], 0, true, false);

			// If EXIF data exists, add each key (group), name, value to database
			if(count($exif) > 0){
				$inserts = array();
				foreach($exif as $key => $section){
				    foreach($section as $name => $value){
						$query = 'INSERT INTO exifs (photo_id, exif_key, exif_name, exif_value) VALUES (' . $this->photos[$i]['photo_id'] . ', "' . addslashes($key) . '", "' . addslashes($name) . '", "' . addslashes(serialize($value)) . '")';
						$this->db->exec($query);
				    }
				}
			}
		}
	}
	
	// Increase photos.photo_views by 1
	public function updateViews(){
		for($i = 0; $i < $this->photo_count; ++$i){
			$this->photos[$i]['photo_views']++;
			$this->db->exec('UPDATE photos SET photo_views = ' . $this->photos[$i]['photo_views'] . ' WHERE photo_id = ' . $this->photos[$i]['photo_id'] . ';');
		}
	}
	
	// Delete photos
	public function delete(){
		$this->deSizePhoto();
		for($i = 0; $i < $this->photo_count; ++$i){
			@$this->db->exec('DELETE FROM photos WHERE photo_id = ' . $this->photos[$i]['photo_id'] . ';');
			@$this->db->exec('DELETE FROM exif WHERE photo_id = ' . $this->photos[$i]['photo_id'] . ';');
			@$this->db->exec('DELETE FROM links WHERE photo_id = ' . $this->photos[$i]['photo_id'] . ';');
		}
	}
	
	// Generate image URLs for images
	public function addImgUrl($size){
		$photo_src = 'photo_src_' . $size;
		
		// Find size's prefix and suffix
		$query = $this->db->prepare('SELECT size_prepend, size_append FROM sizes WHERE size_name = "' . $size . '"');
		$query->execute();
		$sizes = $query->fetchAll();
				
		foreach($sizes as $size){
			$size_prepend = $size['size_prepend'];
			$size_append = $size['size_append'];
		}
		
		// Attach photo_src_ to photos array
		for($i = 0; $i < $this->photo_count; ++$i){
		    $this->photos[$i][$photo_src] = BASE . PHOTOS . $size_prepend . $this->photos[$i]['photo_id'] . $size_append . '.' . $this->photos[$i]['photo_ext'];
		}
	}
	
	// Generate EXIF for images
	public function addExif(){
		$query = $this->db->prepare('SELECT * FROM exifs' . $this->sql . ';');
		$query->execute();
		$exifs = $query->fetchAll();
		
		foreach($exifs as $exif){
			$photo_id = intval($exif['photo_id']);
			$key = array_search($photo_id, $this->photo_ids);
			if($photo_id = $this->photo_ids[$key]){
				@$this->photos[$key]['photo_exif_' . strtolower($exif['exif_key']) . '_' . strtolower($exif['exif_name'])] = unserialize($exif['exif_value']);
			}
		}
	}
	
	// Delete photo thumbnails
	public function deSizePhoto(){
		// Open photo directory
		$dir = PATH . PHOTOS;
		$handle = opendir($dir);
		$photos = array();
		
		while($filename = readdir($handle)){
			for($i = 0; $i < $this->photo_count; ++$i){
				// Find photo thumnails
				if(preg_match('/^((.*[\D]+' . $this->photos[$i]['photo_id'] . '|' . $this->photos[$i]['photo_id'] . '[\D]+.*|.*[\D]+' . $this->photos[$i]['photo_id'] . '[\D]+.*)\..+)$/', $filename)){
					$photos[] = $dir . $filename;
				}
			}
	    }
		
		closedir($handle);
		
		// Delete photo thumbnails
		foreach($photos as $photo){
			unlink($photo);
		}
		return true;
	}
	
}

?>