<?php

class Photo extends Alkaline{
	public $photos;
	public $comments;
	public $photo_columns;
	public $photo_count;
	public $photo_ids;
	public $user;
	protected $sql;
	
	public function __construct($photos){
		parent::__construct();
		
		if(is_object($photos)){
			$photos = $photos->photo_ids;
		}
		
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
					$photo_mime = $this->imageMime($file);
					$photo_color = $this->imageColor($file);
					$filename = substr(strrchr($file, '/'), 1);
					
					$query = 'INSERT INTO photos (user_id, photo_ext, photo_mime, photo_name, photo_colors, photo_uploaded) VALUES (' . $this->user['user_id'] . ', "' . $photo_ext . '", "' . $photo_mime . '", "' . addslashes($filename) . '", "' . addslashes(serialize($photo_color)) . '", "' . date('Y-m-d H:i:s') . '");';
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
		
		$this->photo_ids = $photo_ids;
		
		$this->sql = ' WHERE (photos.photo_id = ' . implode(' OR photos.photo_id = ', $photo_ids) . ')';
		
		$query = $this->db->prepare('SELECT * FROM photos' . $this->sql . ';');
		$query->execute();
		$photos = $query->fetchAll();
		
		$this->photos = array();
		
		
		foreach($photo_ids as $photo_id){
			foreach($photos as $photo){
				if($photo_id == $photo['photo_id']){
					$this->photos[] = $photo;
				}
			}
		}
		
		
		// Store photo count as integer
		$this->photo_count = count($this->photos);
		
		// Store photo_file
		for($i = 0; $i < $this->photo_count; ++$i){
			$this->photos[$i]['photo_file'] = PATH . PHOTOS . $this->photos[$i]['photo_id'] . '.' . $this->photos[$i]['photo_ext'];
		}
		
		if(@$import){
			$this->sizePhoto();
			$this->exifPhoto();
			$this->readIPTC();
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
	
	// UPDATE PHOTO TABLE
	public function updateFields($array, $overwrite=true){
		for($i = 0; $i < $this->photo_count; ++$i){
			// Verify each key has changed; if not, unset the key
			foreach($array as $key => $value){
				if($array[$key] == $this->photos[$i][$key]){
					unset($array[$key]);
				}
				if(!empty($this->photos[$i][$key]) and ($overwrite === false)){
					unset($array[$key]);
				}
			}
			
			// If no keys have changed, break
			if(count($array) == 0){
				continue;
			}
			
			$fields = array();
			
			// Prepare input
			foreach($array as $key => $value){
				if($key == 'photo_published'){
					if(empty($value)){
						$fields[] = $key . ' = NULL';
					}
					elseif(strtolower($value) == 'now'){
						$value = date('Y-m-d H:i:s');
						$fields[] = $key . ' = "' . $value . '"';
					}
					else{
						$value = str_ireplace(' at ', ', ', $value);
						$value = date('Y-m-d H:i:s', strtotime($value));
						$fields[] = $key . ' = "' . $value . '"';
					}
				}
				else{
					$fields[] = $key . ' = "' . $value . '"';
				}
			}
			
			// Set photo_updated field to now
			$fields[] = 'photo_updated = "' . date('Y-m-d H:i:s') . '"';
			$sql = implode(', ', $fields);
			
			// Update table
			$this->db->exec('UPDATE photos SET ' . $sql . ' WHERE photo_id = ' . $this->photos[$i]['photo_id'] . ';');
		}
	}
	
	// Detemine image extension
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
	
	// Detemine image MIME type
	private function imageMime($file){
		$type = exif_imagetype($file);
		switch($type){
			case 1:
				return 'image/gif'; break;
			case 2:
				return 'image/jpeg'; break;
			case 3:
				return 'image/png'; break;
			default:
				return false; break;
		}
	}
	
	// Fill image
	private function imageFill($src, $dest, $height, $width, $quality=null, $ext=null){
		if(empty($quality)){ $quality = IMG_QUAL; }
		if(empty($ext)){ $ext = self::imageExt($src); }
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
	
	// Scale image
	private function imageScale($src, $dest, $height, $width, $quality=null, $ext=null){
		if(empty($quality)){ $quality = IMG_QUAL; }
		if(empty($ext)){ $ext = self::imageExt($src); }
		
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
	
	// Create Colorstrip data
	private function imageColor($src, $ext=null){
		if(empty($ext)){ $ext = $this->imageExt($src); }
		
		$dest = preg_replace('/(.*[0-9]+)(\..+)/', '$1-temp$2', $src);
		
		self::imageScale($src, $dest, 50, 50, 100, $ext);
		
		switch($ext){
			case 'jpg':
				$image = imagecreatefromjpeg($dest);
				break;
			case 'png':
				$image = imagecreatefrompng($dest);
				break;
			case 'gif':
				$image = imagecreatefromgif($dest);
				break;
			default:
				return false;
				break;
		}
		
		$colors = array();
		
		$width = imagesx($image);
		$height = imagesy($image);
		
		for($x = 0; $x < $width; ++$x){
			for($y = 0; $y < $height; ++$y){
				$rgb = imagecolorat($image, $x, $y);
				$r = ($rgb >> 16) & 0xFF;
				$g = ($rgb >> 8) & 0xFF;
				$b = $rgb & 0xFF;
				$diff = abs($r - $g) + abs($r - $b) + abs($g - $b);
				
				$color_present = false;
				
				// See if it's in the same color class
				for($i = 0; $i < count($colors); ++$i){
					if((abs($colors[$i]['r'] - $r) < COLOR_TOLERANCE) and (abs($colors[$i]['g'] - $g) < COLOR_TOLERANCE) and (abs($colors[$i]['b'] - $b) < COLOR_TOLERANCE)){
						//If a more saturated color comes along in same color class, replace color
						if($diff > $colors[$i]['diff']){
							$colors[$i]['r'] = $r;
							$colors[$i]['g'] = $g;
							$colors[$i]['b'] = $b;
						}
						
						// Add one to count
						$colors[$i]['count']++;
						$color_present = true;
						break;
					}
				}
				
				if($color_present === false){
					$colors[] = array('r' => $r, 'g' => $g, 'b' => $b, 'diff' => $diff, 'count' => 1);
				}
			}
		}
		
		$diffs = array();
		
		foreach($colors as $key => $row){
		    $diffs[$key] = $row['diff'];
		}
		
		array_multisort($diffs, SORT_DESC, $colors);
		
		$colors = array_slice($colors, 0, PALETTE_SIZE);
		
		$counts = 0;
		
		for($i = 0; $i < count($colors); ++$i){
			$colors[$i]['count'] = intval(pow($colors[$i]['count'], .35));
			$counts += $colors[$i]['count'];
		}
		
		$rgbs = array();
		
		$total = 0;
		$rgb_last = '';
		
		foreach($colors as $color){
			$rgb = $color['r'] . ',' . $color['g'] . ',' . $color['b'];
			$percent = strval(round((($color['count'] / $counts) * 100), 1));
			$total += $percent;
			$rgbs[$rgb] = $percent;
			$rgb_last = $rgb;
		}
		
		if($total != 100){
			$remaining = 100 - $total;
			$rgbs[$rgb_last] += strval($remaining);
		}
		
		imagedestroy($image);
		unlink($dest);
		
		return $rgbs;
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
	
	public function readIPTC(){
		for($i = 0; $i < $this->photo_count; ++$i){
			// Read IPTC data
			$size = getimagesize($this->photos[$i]['photo_file'], $info);
			
			if(isset($info['APP13']))
			{
				// Parse IPTC data
			    $iptc = iptcparse($info['APP13']);
				
				$title = (!empty($iptc["2#105"][0])) ? $iptc["2#105"][0] : '';
				$description = (!empty($iptc["2#120"][0])) ? $iptc["2#120"][0] : '';
				$tags = (!empty($iptc["2#025"][0])) ? $iptc['2#025'] : array();
				$city = (!empty($iptc["2#090"][0])) ? $iptc["2#090"][0] : '';
				$state = (!empty($iptc["2#095"][0])) ? $iptc["2#095"][0] : '';
				$country = (!empty($iptc["2#101"][0])) ? $iptc["2#101"][0] : '';
			}
			
			// Clean input
			$title = trim($title);
			$description = trim($description);
			$tags = array_unique(array_map('trim', $tags));
			
			// If there are tags, add them (IPTC keywords)
			if(count($tags) > 0){
				
				// Find tags that already exist, add links
				$query = $this->db->prepare('SELECT tags.tag_name, tags.tag_id FROM tags WHERE LOWER(tags.tag_name) LIKE "' . implode('" OR LOWER(tags.tag_name) LIKE "', $tags) . '";');
				$query->execute();
				$tags_db = $query->fetchAll();
				
				foreach($tags_db as $tag){
					// Remove from tags input
					$tag_key = array_search($tag['tag_name'], $tags);
					unset($tags[$tag_key]);
					
					// Add link
					$query = 'INSERT INTO links (photo_id, tag_id) VALUES (' . $this->photos[$i]['photo_id'] . ', ' . $tag['tag_id'] . ');';
					$this->db->exec($query);
				}
				
				// For tags that don't exist, add tags and links
				foreach($tags as $tag){
					// Add tag
					$query = 'INSERT INTO tags (tag_name) VALUES ("' . $tag . '");';
					$this->db->exec($query);
					$tag_id = $this->db->lastInsertId();
					
					// Add link
					$query = 'INSERT INTO links (photo_id, tag_id) VALUES (' . $this->photos[$i]['photo_id'] . ', ' . $tag_id . ');';
					$this->db->exec($query);
				}
			}
			
			if(!empty($city)){
				// Require geography class
				require_once('geo.php');
				
				$place = $city;
				
				if(!empty($state)){
					$place .= ', ' . $state;
				}
				
				if(!empty($country)){
					$place .= ', ' . $country;
				}
				
				// Locate place
				if($place = new Geo($place)){
					$geo = $place->city['city_name'];
					if(!empty($place->city['city_state'])){
						$geo .= ', ' . $place->city['city_state'];
					}
					$geo .= ', ' . $place->city['country_name'];
					$geo_lat = $place->city['city_lat'];
					$geo_long = $place->city['city_long'];
				}
			}
			
			$fields = array('photo_title' => $title,
				'photo_description' => $description,
				'photo_geo' => $geo,
				'photo_geo_lat' => $geo_lat,
				'photo_geo_long' => $geo_long);
			
			$photo = new Photo($this->photos[$i]['photo_id']);
			$photo->updateFields($fields, false);
		}
		
		return true;
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
			@$this->db->exec('DELETE FROM exifs WHERE photo_id = ' . $this->photos[$i]['photo_id'] . ';');
			@$this->db->exec('DELETE FROM links WHERE photo_id = ' . $this->photos[$i]['photo_id'] . ';');
		}
	}
	
	// Generate image URLs for images
	public function getImgUrl($size){
		$photo_src = 'photo_src_' . $size;
		
		// Find size's prefix and suffix
		$query = $this->db->prepare('SELECT size_prepend, size_append FROM sizes WHERE size_title = "' . $size . '"');
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
	public function getExif(){
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
	
	// Retrieve image tags
	public function getTags(){
		$query = $this->db->prepare('SELECT tags.tag_name, photos.photo_id FROM tags, links, photos' . $this->sql . ' AND tags.tag_id = links.tag_id AND links.photo_id = photos.photo_id;');
		$query->execute();
		$tags = $query->fetchAll();
		
		foreach($tags as $tag){
			$photo_id = intval($tag['photo_id']);
			$key = array_search($photo_id, $this->photo_ids);
			if($photo_id = $this->photo_ids[$key]){
				if(empty($this->photos[$key]['photo_tags'])){
					@$this->photos[$key]['photo_tags'] = $tag['tag_name'];
				}
				else{
					$this->photos[$key]['photo_tags'] .= ', ' . $tag['tag_name']; 
				}
			}
		}
	}
	
	// Retrieve image comments
	public function getComments(){
		$query = $this->db->prepare('SELECT * FROM comments, photos' . $this->sql . ' AND comments.photo_id = photos.photo_id;');
		$query->execute();
		$comments = $query->fetchAll();
		
		$this->comments = array();
		
		foreach($this->photo_ids as $photo_id){
			foreach($comments as $comment){
				if($photo_id == $comment['photo_id']){
					$this->comments[] = $comment;
				}
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
	
	public function formatTime($format=null){
		for($i = 0; $i < $this->photo_count; ++$i){
			parent::formatTime($this->photos[$i]['photo_uploaded'], $format);
			parent::formatTime($this->photos[$i]['photo_published'], $format);
			parent::formatTime($this->photos[$i]['photo_updated'], $format);
		}
	}
}

?>