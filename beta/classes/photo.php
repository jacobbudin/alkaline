<?php

class Photo extends Alkaline{
	public $db;
	public $photos = array();
	public $comments;
	public $tags;
	public $photo_columns;
	public $photo_count;
	public $photo_import_ids = array();
	public $photo_ids = array();
	public $user;
	protected $sql;
	
	public function __construct($photo_ids=null){
		parent::__construct();
		
		// User attribution
		$this->user['user_id'] = DEFAULT_USER_ID;
		
		// Input handling
		if(is_object($photo_ids)){
			$photo_ids = $photo_ids->photo_ids;
		}
		
		$this->photo_ids = parent::convertToIntegerArray($photo_ids);
		
		// Error checking
		if(empty($photo_ids)){
			return false;
		}
		
		// Retrieve photos from database
		$this->sql = ' WHERE (photos.photo_id = ' . implode(' OR photos.photo_id = ', $this->photo_ids) . ')';
		
		$query = $this->db->prepare('SELECT * FROM photos' . $this->sql . ';');
		$query->execute();
		$photos = $query->fetchAll();
		
		// Ensure photos array correlates to photo_ids array
		foreach($this->photo_ids as $photo_id){
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
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function import($files){
		if(empty($files)){
			return false;
		}
		
		$files = $this->convertToArray($files);
		
		foreach($files as $file){
			if(!file_exists($file)){
				return false;
			}
		
			$filename = $this->getFilename($file);
		
			// Add photo to database
			$photo_ext = $this->getExt($file);
			$photo_mime = $this->getMIME($file);
			
			$query = 'INSERT INTO photos (user_id, photo_ext, photo_mime, photo_name, photo_uploaded) VALUES (' . $this->user['user_id'] . ', "' . $photo_ext . '", "' . $photo_mime . '", "' . addslashes($filename) . '", "' . date('Y-m-d H:i:s') . '");';
			$this->db->exec($query);
			
			$photo_id = intval($this->db->lastInsertId());
			$photo_ids[] = $photo_id;

			// Copy photo to archive, delete original from shoebox
			copy($file, PATH . PHOTOS . $photo_id . '.' . $photo_ext);
			@unlink($file);
		}
		
		// Store initial photo_ids array
		$exisiting_photo_ids = $this->photo_ids;
		
		// Construct object anew
		self::__construct($photo_ids);
		
		// Process imported photos
		$this->findColors();
		$this->readEXIF();
		$this->readIPTC();
		$this->sizePhoto();
		
		// Combine existing and imported photo_ids arrays
		if(!empty($exisiting_photo_ids)){
			$this->photo_ids = array_merge($exisiting_photo_ids, $this->photo_ids);
		}
		
		// Merge with previous photo_ids
		self::__construct($this->photo_ids);
	}
	
	// USER ATTRIBUTION
	public function attachUser($user){
		$this->user = $user->user;
	}
	
	// Generate photo thumbnails based on sizes in database
	public function sizePhoto($photos=null){
		if(empty($photos)){
			$photos = $this->photos;
			$photo_count = $this->photo_count;
		}
		else{
			$photo_count = count($photos);
		}
		
		// Look up sizes in database
		$query = $this->db->prepare('SELECT * FROM sizes');
		$query->execute();
		$sizes = $query->fetchAll();
		
		// Generate thumbnails
		for($i = 0; $i < $photo_count; ++$i){
			foreach($sizes as $size){
				$size_height = $size['size_height'];
				$size_width = $size['size_width'];
				$size_type = $size['size_type'];
				$size_prepend = $size['size_prepend'];
				$size_append = $size['size_append'];
				switch($size_type){
					case 'fill':
						$this->imageFill($photos[$i]['photo_file'], PATH . PHOTOS . $size_prepend . $photos[$i]['photo_id'] . $size_append . '.' . $photos[$i]['photo_ext'], $size_height, $size_width, null, $photos[$i]['photo_ext']);
						break;
					case 'scale':
						$this->imageScale($photos[$i]['photo_file'], PATH . PHOTOS . $size_prepend . $photos[$i]['photo_id'] . $size_append . '.' . $photos[$i]['photo_ext'], $size_height, $size_width, null, $photos[$i]['photo_ext']);
						break;
					default:
						return false; break;
				}
			}
		}
	}
	
	// UPDATE PHOTO TABLE
	public function updateFields($array, $overwrite=true){
		// Error checking
		if(!is_array($array)){
			return false;
		}
		
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
				elseif($key == 'photo_geo'){
					$geo = new Geo($value);
					if($geo->city['country_name'] == 'United States'){
						$fields[] = $key . ' = "' . $geo->city['city_name'] . ', ' . $geo->city['city_state'] .', ' . $geo->city['country_name'] . '"';
					}
					else{
						$fields[] = $key . ' = "' . $geo->city['city_name'] . ', ' . $geo->city['country_name'] . '"';
					}
					$fields[] = 'photo_geo_lat = ' . $geo->city['city_lat'];
					$fields[] = 'photo_geo_long = '. $geo->city['city_long'];
					
				}
				else{
					$fields[] = $key . ' = "' . addslashes($value) . '"';
				}
			}
			
			// Set photo_updated field to now
			$fields[] = 'photo_updated = "' . date('Y-m-d H:i:s') . '"';
			$sql = implode(', ', $fields);
			
			// Update table
			$this->db->exec('UPDATE photos SET ' . $sql . ' WHERE photo_id = ' . $this->photos[$i]['photo_id'] . ';');
		}
	}
	
	// UPDATE TAGS & LINKS TABLES
	public function updateTags($tags){		
		// Error checking
		if(!is_array($tags)){
			return false;
		}
		
		// Sanitize input
		$tags = array_map('strip_tags', $tags);
		$tags = array_map('trim', $tags);
		$tags = array_unique($tags);
		
		$this->getTags();
		
		for($i = 0; $i < $this->photo_count; ++$i){
			// Verify tags have changed; if not, unset the key
			foreach($this->tags as $tag){
				if($tag['photo_id'] == $this->photos[$i]['photo_id']){
					$tag_key = array_search($tag['tag_name'], $tags);
					if($tag_key !== false){
						unset($tags[$tag_key]);
						continue;
					}
					else{
						$query = 'DELETE FROM links WHERE photo_id = ' . $tag['photo_id'] . ' AND tag_id = ' . $tag['tag_id'] . ';';
						$this->db->exec($query);
						
						$query = $this->db->prepare('SELECT COUNT(*) as count FROM links WHERE tag_id = ' . $tag['tag_id'] . ';');
						$query->execute();
						$tag_exists = $query->fetchAll();
						$tag_count = $tag_exists[0]['count'];
						
						if($tag_count < 1){
							$query = 'DELETE FROM tags WHERE tag_id = ' . $tag['tag_id'] . ';';
							$this->db->exec($query);
						}
					}
				}
			}
			
			// If no tags have changed, break
			if(count($tags) == 0){
				continue;
			}
			
			// Grab tag IDs
			$tags = array_map('addslashes', $tags);
			
			$query = $this->db->prepare('SELECT tags.tag_id, tags.tag_name FROM tags WHERE tags.tag_name = "' . implode('" OR tags.tag_name = "', $tags) . '";');
			$query->execute();
			$tags_db = $query->fetchAll();
			
			$tags = array_map('stripslashes', $tags);
			
			foreach($tags as $tag){
				$found = false;
				foreach($tags_db as $tag_db){
					if($tag == $tag_db['tag_name']){
						$found = true;
						$query = 'INSERT INTO links (photo_id, tag_id) VALUES (' . $this->photos[$i]['photo_id'] . ', ' . $tag_db['tag_id'] . ');';
						$this->db->exec($query);
						continue;
					}
				}
				if($found === false){
					$query = 'INSERT INTO tags (tag_name) VALUES ("' . addslashes($tag) . '");';
					$this->db->exec($query);
					$tag_id = intval($this->db->lastInsertId());
					
					$query = 'INSERT INTO links (photo_id, tag_id) VALUES (' . $this->photos[$i]['photo_id'] . ', ' . $tag_id . ');';
					$this->db->exec($query);	
				}
			}
			
			// Update table
			$this->db->exec('UPDATE photos SET photo_updated = "' . date('Y-m-d H:i:s') . '" WHERE photo_id = ' . $this->photos[$i]['photo_id'] . ';');
		}
	}
	
	// Detemine image extension
	public function getExt($file){
		// Error checking
		if(empty($file)){
			return false;
		}
		
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
	public function getMIME($file){
		// Error checking
		if(empty($file)){
			return false;
		}
		
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
		if(empty($ext)){ $ext = self::getExt($src); }
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
		if(empty($ext)){ $ext = self::getExt($src); }
		
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
	
	// Create Colorkey data
	public function findColors($photos=null){
		if(empty($photos)){
			$photos = $this->photos;
			$photo_count = $this->photo_count;
		}
		else{
			$photo_count = count($photos);
		}
		for($i = 0; $i < $photo_count; ++$i){		
			$dest = preg_replace('/(.*[0-9]+)(\..+)/', '$1-tmp$2', $photos[$i]['photo_file']);
		
			self::imageScale($photos[$i]['photo_file'], $dest, 50, 50, 100, $photos[$i]['photo_ext']);
		
			switch($photos[$i]['photo_ext']){
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
					$rgb = imagecolorat($kmage, $x, $y);
					$r = ($rgb >> 16) & 0xFF;
					$g = ($rgb >> 8) & 0xFF;
					$b = $rgb & 0xFF;
					$diff = abs($r - $g) + abs($r - $b) + abs($g - $b);
				
					$color_present = false;
				
					// See if it's in the same color class
					for($k = 0; $k < count($colors); ++$k){
						if((abs($colors[$k]['r'] - $r) < COLOR_TOLERANCE) and (abs($colors[$k]['g'] - $g) < COLOR_TOLERANCE) and (abs($colors[$k]['b'] - $b) < COLOR_TOLERANCE)){
							//If a more saturated color comes along in same color class, replace color
							if($diff > $colors[$k]['diff']){
								$colors[$k]['r'] = $r;
								$colors[$k]['g'] = $g;
								$colors[$k]['b'] = $b;
							}
						
							// Add one to count
							$colors[$k]['count']++;
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
		
			for($j = 0; $j < count($colors); ++$j){
				$colors[$j]['count'] = intval(pow($colors[$j]['count'], .35));
				$counts += $colors[$j]['count'];
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
				$rgbs[$rgb_last] = strval(floatval(round($rgbs[$rgb_last], 1)));
			}
		
			imagedestroy($image);
			unlink($dest);
			
			$rgb_dom_percent = 0;
			foreach($rgbs as $rgb => $percent){
				if($percent > $rgb_dom_percent){
					$rgb_dom = $rgb;
				}
			}
			
			$rgb_dom = explode(',', $rgb_dom);
			$rgb_dom_r = $rgb_dom[0];
			$rgb_dom_g = $rgb_dom[1];
			$rgb_dom_b = $rgb_dom[2];
			
			$query = 'UPDATE photos SET photo_colors = "' . addslashes(serialize($rgbs)) . '", photo_color_r = ' . $rgb_dom_r . ', photo_color_g = ' . $rgb_dom_g . ', photo_color_b = ' . $rgb_dom_b . ' WHERE photo_id = ' . $photos[$i]['photo_id'] . ';';
			$this->db->exec($query);
			
			return true;
		}
	}
	
	public function readEXIF($photos=null){
		if(empty($photos)){
			$photos = $this->photos;
			$photo_count = $this->photo_count;
		}
		else{
			$photo_count = count($photos);
		}
		
		for($i = 0; $i < $photo_count; ++$i){
			// Read EXIF data
			$exif = @exif_read_data($photos[$i]['photo_file'], 0, true, false);
			
			// If EXIF data exists, add each key (group), name, value to database
			if(count($exif) > 0){
				$inserts = array();
				foreach($exif as $key => $section){
				    foreach($section as $name => $value){
						$query = 'INSERT INTO exifs (photo_id, exif_key, exif_name, exif_value) VALUES (' . $photos[$i]['photo_id'] . ', "' . addslashes($key) . '", "' . addslashes($name) . '", "' . addslashes(serialize($value)) . '");';
						$this->db->exec($query);
						
						// Check for date taken, insert to photos table
						if(($key == 'IFD0') and ($name == 'DateTime')){
							$query = 'UPDATE photos SET photo_taken = "' . date('Y-m-d H:i:s', strtotime($value)) . '" WHERE photo_id = ' . $photos[$i]['photo_id'] . ';';
							$this->db->exec($query);
						}
				    }
				}
			}
		}
	}
	
	public function readIPTC($photos=null){
		if(empty($photos)){
			$photos = $this->photos;
			$photo_count = $this->photo_count;
		}
		else{
			$photo_count = count($photos);
		}
		
		for($i = 0; $i < $photo_count; ++$i){
			// Read IPTC data
			$size = getimagesize($photos[$i]['photo_file'], $info);
			
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
				
				// Clean input
				$title = trim($title);
				$description = trim($description);
				$tags = array_unique(array_map('trim', $tags));
			}
			
			// If there are tags, add them (IPTC keywords)
			if(@count($tags) > 0){
				
				// Find tags that already exist, add links
				$query = $this->db->prepare('SELECT tags.tag_name, tags.tag_id FROM tags WHERE LOWER(tags.tag_name) LIKE "' . implode('" OR LOWER(tags.tag_name) LIKE "', $tags) . '";');
				$query->execute();
				$tags_db = $query->fetchAll();
				
				foreach($tags_db as $tag){
					// Remove from tags input
					$tag_key = array_search($tag['tag_name'], $tags);
					unset($tags[$tag_key]);
					
					// Add link
					$query = 'INSERT INTO links (photo_id, tag_id) VALUES (' . $photos[$i]['photo_id'] . ', ' . $tag['tag_id'] . ');';
					$this->db->exec($query);
				}
				
				// For tags that don't exist, add tags and links
				if(count($tags) > 0){
					foreach($tags as $tag){
						// Add tag
						$query = 'INSERT INTO tags (tag_name) VALUES ("' . $tag . '");';
						$this->db->exec($query);
						$tag_id = $this->db->lastInsertId();
					
						// Add link
						$query = 'INSERT INTO links (photo_id, tag_id) VALUES (' . $photos[$i]['photo_id'] . ', ' . $tag_id . ');';
						$this->db->exec($query);
					}
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
			
			$fields = array('photo_title' => @$title,
				'photo_description' => @$description,
				'photo_geo' => @$geo,
				'photo_geo_lat' => @$geo_lat,
				'photo_geo_long' => @$geo_long);
			
			$photo = new Photo($photos[$i]['photo_id']);
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
		$query = $this->db->prepare('SELECT tags.tag_name, tags.tag_id, photos.photo_id FROM tags, links, photos' . $this->sql . ' AND tags.tag_id = links.tag_id AND links.photo_id = photos.photo_id;');
		$query->execute();
		$tags = $query->fetchAll();
		$this->tags = $tags;
		
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
	
	// Retrieve image rights
	public function getRights(){
		$query = $this->db->prepare('SELECT rights.*, photos.photo_id FROM rights, photos' . $this->sql . ' AND rights.right_id = photos.right_id;');
		$query->execute();
		$rights = $query->fetchAll();
		
		foreach($rights as $right){
			$photo_id = intval($right['photo_id']);
			$key = array_search($photo_id, $this->photo_ids);
			if($photo_id = $this->photo_ids[$key]){
				foreach($right as $right_key => $right_value){
					$this->photos[$key][$right_key] = $right_value;
				}
			}
		}
	}
	
	// Retrieve image comments
	public function getComments(){
		$query = $this->db->prepare('SELECT * FROM comments, photos' . $this->sql . ' AND comments.photo_id = photos.photo_id;');
		$query->execute();
		$this->comments = $query->fetchAll();
		
		foreach($this->comments as &$comment){
			if(!empty($comment['comment_author_avatar'])){
				$comment['comment_author_avatar'] = '<img src="' . $comment['comment_author_avatar'] . '" alt="" />';
			}
		}
		
		// Store photo comment fields
		for($i = 0; $i < $this->photo_count; ++$i){
			$this->photos[$i]['photo_comment_text'] = '<textarea id="comment_' . $this->photos[$i]['photo_id'] . '_text" name="comment_' . $this->photos[$i]['photo_id'] . '_text" class="comment"></textarea>';
			
			$this->photos[$i]['photo_comment_author_name'] = '<input type="text" id="comment_' . $this->photos[$i]['photo_id'] . '_author_name" name="comment_' . $this->photos[$i]['photo_id'] . '_author_name" class="comment_author_name" />';
			
			$this->photos[$i]['photo_comment_author_email'] = '<input type="text" id="comment_' . $this->photos[$i]['photo_id'] . '_author_email" name="comment_' . $this->photos[$i]['photo_id'] . '_author_email" class="comment_author_email" />';
			
			$this->photos[$i]['photo_comment_author_url'] = '<input type="text" id="comment_' . $this->photos[$i]['photo_id'] . '_author_url" name="comment_' . $this->photos[$i]['photo_id'] . '_author_url" class="comment_author_url" />';
		
			$this->photos[$i]['photo_comment_submit'] = '<input type="hidden" name="comment_id" value="' . $this->photos[$i]['photo_id'] . '" /><input type="submit" id="" name="" class="comment_submit" value="Submit comment" />';
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
	
	public function watermark($src, $dest, $watermark, $quality=null, $ext=null){
		if(empty($quality)){ $quality = IMG_QUAL; }
		if(empty($ext)){ $ext = self::getExt($src); }
		
		$watermark = imagecreatefrompng($watermark);
	
		imagealphablending($watermark, false);
	    imagesavealpha($watermark, true);
	
		$width_watermark = imagesx($watermark);
		$height_watermark = imagesy($watermark);
		
		// 
		
		switch($ext){
			case 'jpg':
				$image = imagecreatefromjpeg($src);
				imagealphablending($image, true);
				
				$width = imagesx($image);
				$height = imagesy($image);
				
				$pos_x = $width - $width_watermark - WATERMARK_MARGIN;
				$pos_y = $height - $height_watermark - WATERMARK_MARGIN;
				
				imagecopy($image, $watermark, $pos_x, $pos_y, 0, 0, $width_watermark, $height_watermark);
				imagedestroy($watermark);
				imagejpeg($image, $dest, $quality);
				imagedestroy($image);
				
				return true;
				break;
			case 'png':
				$image = imagecreatefrompng($src);
				
				$width = imagesx($image);
				$height = imagesy($image);
				
				$pos_x = $width - $width_watermark - WATERMARK_MARGIN;
				$pos_y = $height - $height_watermark - WATERMARK_MARGIN;
				
				imagecopy($image, $watermark, $pos_x, $pos_y, 0, 0, $width_watermark, $height_watermark);
				imagedestroy($watermark);
				imagepng($image, $dest, $quality);
				imagedestroy($image);
				
				return true;
				break;
			case 'gif':
				$image = imagecreatefromgif($src);
				
				$width = imagesx($image);
				$height = imagesy($image);
				
				$pos_x = $width - $width_watermark - WATERMARK_MARGIN;
				$pos_y = $height - $height_watermark - WATERMARK_MARGIN;
				
				$image_tamp = imagecreatetruecolor($width, $height);
				imagecopy($image_temp, $image, 0, 0, 0, 0, $width, $height);
				$image = $image_tamp;
				
				imagecopy($image, $watermark, $pos_x, $pos_y, 0, 0, $width_watermark, $height_watermark);
				imagedestroy($watermark);
				imagegif($image, $dest, $quality);
				imagedestroy($image);
				
				return true;
				break;
			default:
				return false;
				break;
		}
	}
}

?>