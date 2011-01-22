<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalinenapp.com/
*/

/**
 * @author Budin Ltd. <contact@budinltd.com>
 * @copyright Copyright (c) 2010-2011, Budin Ltd.
 * @version 1.0
 */

class Photo extends Alkaline{
	public $db;
	public $photos = array();
	public $comments;
	public $pages;
	public $piles;
	public $tags;
	public $photo_columns;
	public $photo_count;
	public $photo_import_ids = array();
	public $photo_ids = array();
	public $user;
	protected $sql;
	
	/**
	 * Initiates Photo object
	 *
	 * @param array|int|string $photo_ids Photo IDs (use Find class to locate them)
	 */
	public function __construct($photo_ids=null){
		parent::__construct();
		
		// Reset photo array
		$this->photos = array();
		
		// Input handling
		if(is_object($photo_ids)){
			$photo_ids = $photo_ids->photo_ids;
		}
		
		$this->photo_ids = parent::convertToIntegerArray($photo_ids);
		
		// Error checking
		$this->sql = ' WHERE (photos.photo_id IS NULL)';
		
		if(count($this->photo_ids) > 0){
			// Retrieve photos from database
			$this->sql = ' WHERE (photos.photo_id IN (' . implode(', ', $this->photo_ids) . '))';
			
			$query = $this->prepare('SELECT * FROM photos' . $this->sql . ';');
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
				$this->photos[$i]['photo_file'] = parent::correctWinPath(PATH . PHOTOS . $this->photos[$i]['photo_id'] . '.' . $this->photos[$i]['photo_ext']);
				$this->photos[$i]['photo_src'] = BASE . PHOTOS . $this->photos[$i]['photo_id'] . '.' . $this->photos[$i]['photo_ext'];
			}
		}
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	/**
	 * Perform Object hook
	 *
	 * @param Object $orbit 
	 * @return void
	 */
	public function hook($orbit=null){
		if(!is_object($orbit)){
			$orbit = new Orbit;
		}
		
		$this->photos = $orbit->hook('photo', $this->photos, $this->photos);
	}
	
	/**
	 * Attribute actions to user
	 *
	 * @param User $user User object
	 * @return void
	 */
	public function attachUser($user){
		$this->user = $user->user;
	}
	
	/**
	 * Import files into image library
	 *
	 * @param array|string $files Full path to image files
	 * @return void
	 */
	public function import($files){
		if(empty($files)){
			return false;
		}
		
		$files = $this->convertToArray($files);
		$photo_ids = array();
		
		foreach($files as $file){
			if(!file_exists($file)){
				return false;
			}
		
			$filename = $this->getFilename($file);
		
			// Add photo to database
			$photo_ext = $this->getExt($file);
			$photo_mime = $this->getMIME($file);
			$photo_size = $this->getSize($file);
			
			// Configuration: default rights set
			if($this->returnConf('rights_default')){
				$right_id = $this->returnConf('rights_default_id');
			}
			
			$fields = array('user_id' => @$this->user['user_id'],
				'right_id' => @$right_id,
				'photo_ext' => $photo_ext,
				'photo_mime' => $photo_mime,
				'photo_name' => $filename,
				'photo_uploaded' => date('Y-m-d H:i:s'),
				'photo_height' => $photo_size['height'],
				'photo_width' => $photo_size['width']);
			
			$photo_id = $this->addRow($fields, 'photos');
			$photo_ids[] = $photo_id;

			// Copy photo to archive, delete original from shoebox
			copy($file, parent::correctWinPath(PATH . PHOTOS . $photo_id . '.' . $photo_ext));
			@unlink($file);
		}
		
		// Store initial photo_ids array
		$existing_photo_ids = $this->photo_ids;
		
		// Construct object anew
		self::__construct($photo_ids);
		
		// Process imported photos
		$this->findColors();
		
		if($this->returnConf('shoe_exif')){
			$this->readEXIF();
		}
		
		if($this->returnConf('shoe_iptc')){
			$this->readIPTC();
		}
		
		if($this->returnConf('shoe_geo')){
			$this->readGeo();
		}
		
		$this->sizePhoto();
		
		// Combine existing and imported photo_ids arrays
		if(!empty($existing_photo_ids)){
			$this->photo_ids = array_merge($existing_photo_ids, $this->photo_ids);
		}
		
		// Merge with previous photo_ids
		self::__construct($this->photo_ids);
	}
	
	/**
	 * Generate photo thumbnails based on sizes in database (and apply watermark if selected)
	 *
	 * @param array $photos Photo array
	 * @param array|int $size_ids Size IDs (else all sizes)
	 * @return void
	 */
	public function sizePhoto($photos=null, $size_ids=null){
		if(empty($photos)){
			$photos = $this->photos;
			$photo_count = $this->photo_count;
		}
		else{
			$photo_count = count($photos);
		}
		
		if(!empty($size_ids)){
			$size_ids = parent::convertToIntegerArray($size_ids);
		}
		
		// Look up sizes in database
		$query = $this->prepare('SELECT * FROM sizes');
		$query->execute();
		$sizes = $query->fetchAll();
		
		// Generate thumbnails
		for($i = 0; $i < $photo_count; ++$i){
			foreach($sizes as $size){
				$size_id = $size['size_id'];
				
				if(!empty($size_ids)){
					if(!in_array($size_id, $size_ids)){
						continue;
					}
				}
				
				$size_height = $size['size_height'];
				$size_width = $size['size_width'];
				$size_type = $size['size_type'];
				$size_prepend = $size['size_prepend'];
				$size_append = $size['size_append'];
				$size_watermark = $size['size_watermark'];
				$size_dest = parent::correctWinPath(PATH . PHOTOS . $size_prepend . $photos[$i]['photo_id'] . $size_append . '.' . $photos[$i]['photo_ext']);
				
				switch($size_type){
					case 'fill':
						$this->imageFill($photos[$i]['photo_file'], $size_dest, $size_height, $size_width, null, $photos[$i]['photo_ext']);
						break;
					case 'scale':
						$this->imageScale($photos[$i]['photo_file'], $size_dest, $size_height, $size_width, null, $photos[$i]['photo_ext']);
						break;
					default:
						return false; break;
				}
				
				if($this->returnConf('thumb_watermark') and ($size_watermark == 1)){
					$watermark = parent::correctWinPath(PATH . 'watermark.png');
					$this->watermark($size_dest, $size_dest, $watermark, null, null, null, $photos[$i]['photo_ext']);
				}
			}
		}
	}
	
	/**
	 * Update photo table
	 *
	 * @param array $array Associative array of columns and fields
	 * @param bool $overwrite 
	 * @return void
	 */
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
						$fields[$key] = null;
					}
					elseif(strtolower($value) == 'now'){
						$value = date('Y-m-d H:i:s');
						$fields[$key] = $value;
					}
					else{
						$value = str_ireplace(' at ', ', ', $value);
						$value = date('Y-m-d H:i:s', strtotime($value));
						$fields[$key] = $value;
					}
				}
				elseif($key == 'photo_geo'){
					$geo = new Geo($value);
					if(!empty($geo->city)){
						if($geo->city['country_name'] == 'United States'){
							$fields['photo_geo'] = $geo->city['city_name'] . ', ' . $geo->city['city_state'] .', ' . $geo->city['country_name'];
						}
						else{
							$fields['photo_geo'] = $geo->city['city_name'] . ', ' . $geo->city['country_name'];
						}
					}
					elseif(!empty($geo->raw)){
						$fields['photo_geo'] = ucwords($geo->raw);
					}
					else{
						$fields['photo_geo'] = '';
					}
					
					if(!empty($geo->lat) and !empty($geo->long)){
						$fields['photo_geo_lat'] = $geo->lat;
						$fields['photo_geo_long'] = $geo->long;
					}
				}
				else{
					$fields[$key] = $value;
				}
			}
			
			// Set photo_updated field to now
			$fields['photo_updated'] = date('Y-m-d H:i:s');
			
			$columns = array_keys($fields);
			$values = array_values($fields);

			// Add row to database
			$query = $this->prepare('UPDATE photos SET ' . implode(' = ?, ', $columns) . ' = ? WHERE photo_id = ' . $this->photos[$i]['photo_id'] . ';');
			if(!$query->execute($values)){
				return false;
			}
		}
		
		return true;
	}
	
	/**
	 * Update tags (update tags and links tables)
	 *
	 * @param array $tags 
	 * @return void
	 */
	public function updateTags($tags){
		// Error checking
		if(!is_array($tags)){
			return false;
		}
		
		// Sanitize input
		$tags = array_map('strip_tags', $tags);
		$tags = array_map('trim', $tags);
		$tags = array_unique($tags);
		
		// Find input tags (and their IDs) in database	
		$sql_params = array();
		$tag_count = count($tags);
		
		for($j=0; $j<$tag_count; ++$j){
			$sql_params[':tag' . $j] = $tags[$j];
		}
		
		$sql_param_keys = array_keys($sql_params);
		
		$query = $this->prepare('SELECT tags.tag_id, tags.tag_name FROM tags WHERE tags.tag_name = ' . implode(' OR tags.tag_name = ', $sql_param_keys) . ';');
		$query->execute($sql_params);
		$tags_db = $query->fetchAll();
		$tags_db_names = array();
		
		foreach($tags_db as $tag_db){
			$tags_db_names[] = $tag_db['tag_name'];
		}
		
		// Get current tags
		$this->getTags();
		
		$affected_photo_ids = array();
		
		// Loop through photos
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
						$this->exec($query);
						
						$query = $this->prepare('SELECT COUNT(*) as count FROM links WHERE tag_id = ' . $tag['tag_id'] . ';');
						$query->execute();
						$tag_exists = $query->fetchAll();
						$tag_count = $tag_exists[0]['count'];
						
						if($tag_count < 1){
							$query = 'DELETE FROM tags WHERE tag_id = ' . $tag['tag_id'] . ';';
							$this->exec($query);
						}
					}
				}
			}
			
			$tags = array_merge($tags);
			
			// If no tags have changed, break
			if(count($tags) == 0){
				continue;
			}
			
			$affected_photo_ids[] = $this->photos[$i]['photo_id'];
			
			foreach($tags as $tag){
				$key = array_search($tag, $tags_db_names);
				if($key !== false){
					$query = 'INSERT INTO links (photo_id, tag_id) VALUES (' . $this->photos[$i]['photo_id'] . ', ' . $tags_db[$key]['tag_id'] . ');';
					$this->exec($query);
				}
				else{
					$query = $this->prepare('INSERT INTO tags (tag_name) VALUES (:tag);');
					$query->execute(array(':tag' => $tag));
					$tag_id = intval($this->db->lastInsertId(TABLE_PREFIX . 'tags_tag_id_seq'));
					
					$tags_db[] = array('tag_id' => $tag_id, 'tag_name' => $tag);
					$tags_db_names[] = $tag;
					
					$query = 'INSERT INTO links (photo_id, tag_id) VALUES (' . $this->photos[$i]['photo_id'] . ', ' . $tag_id . ');';
					$this->exec($query);
				}
			}
		}
		
		if(count($affected_photo_ids) > 0){
			$now = date('Y-m-d H:i:s');
			$query = $this->prepare('UPDATE photos SET photo_updated = :photo_updated WHERE photo_id = :photo_id;');
			foreach($affected_photo_ids as $photo_id){
				$query->execute(array(':photo_updated' => $now, ':photo_id' => $photo_id));
			}
		}
		
		return true;
	}
	
	/**
	 * Add tags
	 *
	 * @param array|string $tags Tags names
	 * @return array Affected tag IDs
	 */
	public function addTags($tags){
		// Error checking
		if(!is_array($tags)){
			return false;
		}
		
		// Sanitize input
		$tags = array_map('strip_tags', $tags);
		$tags = array_map('trim', $tags);
		$tags = array_unique($tags);
		
		// Find input tags (and their IDs) in database	
		$sql_params = array();
		
		$tags = array_merge($tags);
		$tag_count = count($tags);
		
		for($j=0; $j<$tag_count; ++$j){
			$sql_params[':tag' . $j] = $tags[$j];
		}
		
		$sql_param_keys = array_keys($sql_params);
		
		$query = $this->prepare('SELECT tags.tag_id, tags.tag_name FROM tags WHERE tags.tag_name = ' . implode(' OR tags.tag_name = ', $sql_param_keys) . ';');
		$query->execute($sql_params);
		$tags_db = $query->fetchAll();
		$tags_db_ids = array();
		$tags_db_names = array();
		
		foreach($tags_db as $tag_db){
			$tags_db_ids[] = $tag_db['tag_id'];
			$tags_db_names[] = $tag_db['tag_name'];
		}
		
		// Get current tags
		$this->getTags();
		
		$affected_photo_ids = array();
		
		// Loop through photos
		for($i = 0; $i < $this->photo_count; ++$i){
			// Verify tags have changed; if not, unset the key
			foreach($this->tags as $tag){
				if($tag['photo_id'] == $this->photos[$i]['photo_id']){
					$tag_key = array_search($tag['tag_name'], $tags);
					if($tag_key !== false){
						unset($tags[$tag_key]);
						continue;
					}
				}
			}
			
			$tags = array_merge($tags);
			
			// If no tags have changed, break
			if(count($tags) == 0){
				continue;
			}
			
			$affected_photo_ids[] = $this->photos[$i]['photo_id'];
			
			foreach($tags as $tag){
				$key = array_search($tag, $tags_db_names);
				if($key !== false){
					$query = 'INSERT INTO links (photo_id, tag_id) VALUES (' . $this->photos[$i]['photo_id'] . ', ' . $tags_db[$key]['tag_id'] . ');';
					$this->exec($query);
				}
				else{
					$query = $this->prepare('INSERT INTO tags (tag_name) VALUES (:tag);');
					$query->execute(array(':tag' => $tag));
					$tag_id = intval($this->db->lastInsertId(TABLE_PREFIX . 'tags_tag_id_seq'));
					
					$tags_db[] = array('tag_id' => $tag_id, 'tag_name' => $tag);
					$tag_db_ids[] = $tag_id;
					$tags_db_names[] = $tag;
					
					$query = 'INSERT INTO links (photo_id, tag_id) VALUES (' . $this->photos[$i]['photo_id'] . ', ' . $tag_id . ');';
					$this->exec($query);
				}
			}
		}
		
		if(count($affected_photo_ids) > 0){
			$now = date('Y-m-d H:i:s');
			$query = $this->prepare('UPDATE photos SET photo_updated = :photo_updated WHERE photo_id = :photo_id;');
			foreach($affected_photo_ids as $photo_id){
				$query->execute(array(':photo_updated' => $now, ':photo_id' => $photo_id));
			}
		}
		
		$tags_db_ids = array_unique($tags_db_ids);
		
		return $tag_db_ids;
	}
	
	/**
	 * Remove tags
	 *
	 * @param array|string $tags Tags names
	 * @return array Affected tag IDs
	 */
	public function removeTags($tags){
		// Error checking
		if(!is_array($tags)){
			return false;
		}
		
		// Sanitize input
		$tags = array_map('strip_tags', $tags);
		$tags = array_map('trim', $tags);
		$tags = array_unique($tags);
		
		$this->getTags();
		
		$tags_db_ids = array();
		$affected_photo_ids = array();
		
		for($i = 0; $i < $this->photo_count; ++$i){
			// Verify tags have changed; if not, unset the key
			foreach($this->tags as $tag){
				if($tag['photo_id'] == $this->photos[$i]['photo_id']){
					$tag_key = array_search($tag['tag_name'], $tags);
					if($tag_key !== false){			
						$query = 'DELETE FROM links WHERE photo_id = ' . $tag['photo_id'] . ' AND tag_id = ' . $tag['tag_id'] . ';';
						$this->exec($query);
						$tags_db_ids[] = $tag['tag_id'];
						$affected_photo_ids[] = $this->photos[$i]['photo_id'];
					}
				}
			}
		}
		
		if(count($affected_photo_ids) > 0){
			$now = date('Y-m-d H:i:s');
			$query = $this->prepare('UPDATE photos SET photo_updated = :photo_updated WHERE photo_id = :photo_id;');
			foreach($affected_photo_ids as $photo_id){
				$query->execute(array(':photo_updated' => $now, ':photo_id' => $photo_id));
			}
		}
		
		$tags_db_ids = array_unique($tags_db_ids);
		
		return $tags_db_ids;
	}
	
	/**
	 * Determine image extension
	 *
	 * @param string $file Full path to file
	 * @return string|false Extension
	 */
	public function getExt($file){
		// Error checking
		if(empty($file)){
			return false;
		}
		
		if(function_exists('exif_imagetype')){
			$type = exif_imagetype($file);
			
			switch($type){
				case 1:
					return 'gif'; break;
				case 2:
					return 'jpg'; break;
				case 3:
					return 'png'; break;
			}
		}
		
		preg_match('#\.([a-z0-9]*)$#si', $file, $matches);
		$type = $matches[1];
		if($type == 'jpeg'){ return 'jpg'; }
		else{ return $type; }
	}
	
	/**
	 * Determine image MIME type
	 *
	 * @param string $file Full path to file
	 * @return string|false MIME type
	 */
	public function getMIME($file){
		// Error checking
		if(empty($file)){
			return false;
		}
		
		if(function_exists('exif_imagetype')){
			$type = exif_imagetype($file);
			switch($type){
				case 1:
					return 'image/gif'; break;
				case 2:
					return 'image/jpeg'; break;
				case 3:
					return 'image/png'; break;
			}
		}
		
		preg_match('#\.([a-z0-9]*)$#si', $file, $matches);
		$type = $matches[1];
		if($type == 'jpg'){ return 'image/jpeg'; }
		elseif($type == 'svg'){ return 'image/svg+xml'; }
		elseif($type == 'pdf'){ return 'application/pdf'; }
		else{ return 'image/' . $type; }
	}
	
	/**
	 * Determine image dimensions
	 *
	 * @param string $file Full path to file
	 * @return array Associative array with heigh and width keys
	 */
	public function getSize($file){
		// Error checking
		if(empty($file)){
			return false;
		}
		
		$size = array();
		
		// ImageMagick version
		if(class_exists('Imagick', false) and $this->returnConf('thumb_imagick')){
			$image = new Imagick($file);
			$size['width'] = $image->getImageWidth();
			$size['height'] = $image->getImageHeight();
		}
		// GD version
		else{
			$info = getimagesize($file);
			$size['width'] = $info[0];
			$size['height'] = $info[1];
		}
		
		return $size;
	}
	
	/**
	 * Resize image by fill
	 *
	 * @param string $src Source full path
	 * @param string $dest Destination full path
	 * @param int $height 
	 * @param int $width 
	 * @param int $quality 1-100
	 * @param string $ext 
	 * @return bool True if successful
	 */
	private function imageFill($src, $dest, $height, $width, $quality=null, $ext=null){
		if(empty($quality)){ $quality = $this->returnConf('thumb_compress_tol'); }
		if(empty($ext)){ $ext = self::getExt($src); }
		
		$src = parent::correctWinPath($src);
		$dest = parent::correctWinPath($dest);
		
		// ImageMagick version
		if(class_exists('Imagick', false) and $this->returnConf('thumb_imagick')){
			$size = $this->getSize($src);
			$width_orig = $size['width'];
			$height_orig = $size['height'];
			
			$image = new Imagick($src);
			
			switch($ext){
				case 'jpg':
					$image->setImageCompression(Imagick::COMPRESSION_JPEG); 
					$image->setImageCompressionQuality($quality);
					$image->cropThumbnailImage($width, $height);
					break;
				case 'png':
					$image->cropThumbnailImage($width, $height);
					break;
				case 'gif':
					$image->cropThumbnailImage($width, $height);
					break;
				case 'pdf':
					$res = $image->getImageResolution();
					$x_ratio = $res['x'] / $image->getImageWidth();
					$y_ratio = $res['y'] / $image->getImageHeight();
					$image->removeImage();
					$image->setResolution($width_orig * $x_ratio, $height_orig * $y_ratio);
					$image->readImage($src);
					$image->setImageFormat('png');
					$image->cropThumbnailImage($width, $height);
					$dest = $this->changeExt($dest, 'png');
					break;
				case 'svg':
					$res = $image->getImageResolution();
					$x_ratio = $res['x'] / $image->getImageWidth();
					$y_ratio = $res['y'] / $image->getImageHeight();
					$image->removeImage();
					$image->setResolution($width_orig * $x_ratio, $height_orig * $y_ratio);
					$image->readImage($src);
					$image->setImageFormat('png');
					$image->cropThumbnailImage($width, $height);
					$dest = $this->changeExt($dest, 'png');
					break;
			}
			
			$image->writeImage($dest);
			$image->clear();
			$image->destroy();
			return true;
		}
		// GD version
		else{
			list($width_orig, $height_orig) = getimagesize($src);

			$ratio_orig = $width_orig / $height_orig;
			$ratio = $width / $height;
			
			switch($ext){
				case 'jpg':
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
					$quality_tmp = floor((1 / $quality) * 95);
				
					if($ratio_orig > $ratio){
						$image_p = imagecreatetruecolor($width, $height);
						$image = imagecreatefrompng($src);
						$pixel = ($width_orig - $height_orig) / 2;
						imagecopyresampled($image_p, $image, 0, 0, $pixel, 0, $width * $ratio_orig, $height, $width_orig, $height_orig);
						imagepng($image_p, $dest, $quality_tmp);
					}
					else{
						$image_p = imagecreatetruecolor($width, $height);
						$image = imagecreatefrompng($src);
						$pixel = ($height_orig - $width_orig) / 2;
						imagecopyresampled($image_p, $image, 0, 0, 0, $pixel, $width, $height * (1 / $ratio_orig), $width_orig, $height_orig);
						imagepng($image_p, $dest, $quality_tmp);
					}

					imagedestroy($image);
					imagedestroy($image_p);
					return true;
					break;
				case 'gif':
					if($ratio_orig > $ratio){
						$image_p = imagecreatetruecolor($width, $height);
						$image = imagecreatefromgif($src);
						$pixel = ($width_orig - $height_orig) / 2;
						imagecopyresampled($image_p, $image, 0, 0, $pixel, 0, $width * $ratio_orig, $height, $width_orig, $height_orig);
						imagegif($image_p, $dest);
					}
					else{
						$image_p = imagecreatetruecolor($width, $height);
						$image = imagecreatefromgif($src);
						$pixel = ($height_orig - $width_orig) / 2;
						imagecopyresampled($image_p, $image, 0, 0, 0, $pixel, $width, $height * (1 / $ratio_orig), $width_orig, $height_orig);
						imagegif($image_p, $dest);
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
	}
	
	/**
	 * Resize image by scale
	 *
	 * @param string $src Source full path
	 * @param string $dest Destination full path
	 * @param int $height 
	 * @param int $width 
	 * @param int $quality 1-100
	 * @param string $ext 
	 * @return bool True if successful
	 */
	private function imageScale($src, $dest, $height, $width, $quality=null, $ext=null){
		if(empty($quality)){ $quality = $this->returnConf('thumb_compress_tol'); }
		if(empty($ext)){ $ext = self::getExt($src); }
		
		$src = parent::correctWinPath($src);
		$dest = parent::correctWinPath($dest);
		
		// ImageMagick version
		if(class_exists('Imagick', false) and $this->returnConf('thumb_imagick')){
			$image = new Imagick($src);
			
			$size = $this->getSize($src);
			$width_orig = $size['width'];
			$height_orig = $size['height'];
			
			if(($width_orig <= $width) and ($height_orig <= $height)){
				switch($ext){
					case 'jpg':
						copy($src, $dest);
						break;
					case 'png':
						copy($src, $dest);
						break;
					case 'gif':
						copy($src, $dest);
						break;
				}
			}

			$ratio_orig = $width_orig / $height_orig;
			$ratio = $width / $height;

			if($ratio_orig > $ratio){ $height = $width / $ratio_orig; }
			else{ $width = $height * $ratio_orig; }
			
			switch($ext){
				case 'jpg':
					$image->setImageCompression(Imagick::COMPRESSION_JPEG); 
					$image->setImageCompressionQuality($quality);
					$image->thumbnailImage($width, $height);
					break;
				case 'png':
					$image->thumbnailImage($width, $height);
					break;
				case 'gif':
					$image->thumbnailImage($width, $height);
					break;
				case 'pdf':
					$res = $image->getImageResolution();
					$x_ratio = $res['x'] / $image->getImageWidth();
					$y_ratio = $res['y'] / $image->getImageHeight();
					$image->removeImage();
					$image->setResolution($width * $x_ratio, $height * $y_ratio);
					$image->readImage($src);
					$image->setImageFormat('png');
					$dest = $this->changeExt($dest, 'png');
					break;
				case 'svg':
					$res = $image->getImageResolution();
					$x_ratio = $res['x'] / $image->getImageWidth();
					$y_ratio = $res['y'] / $image->getImageHeight();
					$image->removeImage();
					$image->setResolution($width * $x_ratio, $height * $y_ratio);
					$image->readImage($src);
					$image->setImageFormat('png');
					$dest = $this->changeExt($dest, 'png');
					break;
			}
			
			$image->writeImage($dest);
			$image->clear();
			$image->destroy();
			return true;
		}
		// GD version
		else{
			list($width_orig, $height_orig) = getimagesize($src);
		
			if(($width_orig <= $width) and ($height_orig <= $height)){
				copy($src, $dest);
				return true;	
			}

			$ratio_orig = $width_orig / $height_orig;
			$ratio = $width / $height;

			if($ratio_orig > $ratio){ $height = $width / $ratio_orig; }
			else{ $width = $height * $ratio_orig; }
			
			switch($ext){
				case 'jpg':
					$image_p = imagecreatetruecolor($width, $height);
					$image = imagecreatefromjpeg($src);
					
					imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
					imagejpeg($image_p, $dest, $quality);
				
					imagedestroy($image);
					imagedestroy($image_p);
					
					return true;
					break;
				case 'png':
					$quality_tmp = floor((1 / $quality) * 95);
					
					$image_p = imagecreatetruecolor($width, $height);
					$image = imagecreatefrompng($src);
					
					imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
					imagepng($image_p, $dest, $quality_tmp);

					imagedestroy($image);
					imagedestroy($image_p);
					
					return true;
					break;
				case 'gif':
					$image_p = imagecreatetruecolor($width, $height);
					$image = imagecreatefromgif($src);
					
					imagecopyresampled($image_p, $image, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
					imagegif($image_p, $dest);

					imagedestroy($image);
					imagedestroy($image_p);
					
					return true;
					break;
				default:
					return false;
					break;
			}
		}
	}
	
	/**
	 * Generate Colorkey data
	 *
	 * @param array $photos 
	 * @return void
	 */
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
					$rgb = imagecolorat($image, $x, $y);
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
			
			// Calculate dominant color (RGB)
			
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
			
			$R = ($rgb_dom_r / 255);
			$G = ($rgb_dom_g / 255);
			$B = ($rgb_dom_b / 255);

			$min = min($R, $G, $B);
			$max = max($R, $G, $B);
			$delta = $max - $min;

			$V = $max;

			if($delta == 0){ 
				$H = 0;
				$S = 0;
			} 
			else{ 
				$S = $delta / $max;

				$del_R = ((($max - $R ) / 6) + ($delta / 2 )) / $delta;
				$del_G = ((($max - $G ) / 6) + ($delta / 2 )) / $delta;
				$del_B = ((($max - $B ) / 6) + ($delta / 2 )) / $delta;

				if($R == $max){ $H = $del_B - $del_G; }
				elseif($G == $max){ $H = ( 1 / 3 ) + $del_R - $del_B; }
				elseif($B == $max){ $H = ( 2 / 3 ) + $del_G - $del_R; }

				if($H<0){ $H++; }
				if($H>1){ $H--; }
			} 

			$hsl_dom_h = round($H * 360);
			$hsl_dom_s = round($S * 100);
			$hsl_dom_l = round($V * 100);
			
			$fields = array(':photo_colors' => serialize($rgbs),
				':photo_color_r' => $rgb_dom_r,
				':photo_color_g' => $rgb_dom_g,
				':photo_color_b' => $rgb_dom_b,
				':photo_color_h' => $hsl_dom_h,
				':photo_color_s' => $hsl_dom_s,
				':photo_color_l' => $hsl_dom_l);
		
			$query = $this->prepare('UPDATE photos SET photo_colors = :photo_colors, photo_color_r = :photo_color_r, photo_color_g = :photo_color_g, photo_color_b = :photo_color_b, photo_color_h = :photo_color_h, photo_color_s = :photo_color_s, photo_color_l = :photo_color_l WHERE photo_id = ' . $photos[$i]['photo_id'] . ';');
			return $query->execute($fields);
		}
	}
	
	/**
	 * Read and import EXIF data from images
	 *
	 * @param array $photos 
	 * @return void
	 */
	public function readEXIF($photos=null){
		if(!function_exists('exif_read_data')){ return false; }
		
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
			if((count($exif) > 0) and is_array($exif)){
				$inserts = array();
				foreach(@$exif as $key => $section){
				    foreach($section as $name => $value){
						// Check for empty EXIF data entries
						if(is_string($value)){
							$value = trim($value);
						}
						if(empty($value)){
							continue;
						}
						
						$fields = array('photo_id' => $photos[$i]['photo_id'],
							'exif_key' => $key,
							'exif_name' => $name,
							'exif_value' => serialize($value));
						
						$this->addRow($fields, 'exifs');
						
						// Check for date taken, insert to photos table
						if(($key == 'IFD0') and ($name == 'DateTime')){
							$query = $this->prepare('UPDATE photos SET photo_taken = :photo_taken WHERE photo_id = ' . $photos[$i]['photo_id'] . ';');
							$query->execute(array(':photo_taken' => date('Y-m-d H:i:s', strtotime($value))));
						}
				    }
				}
			}
		}
	}
	
	/**
	 * Read and import IPTC data from images (for tags)
	 *
	 * @param array $photos 
	 * @return void
	 */
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
				
				$sql_params = array();
				$tag_count = count($tags);

				// Grab tag IDs
				for($j=0; $j<$tag_count; ++$j){
					$sql_params[':tag' . $j] = $tags[$j];
				}

				$sql_param_keys = array_keys($sql_params);
				
				// Find tags that already exist, add links
				$query = $this->prepare('SELECT tags.tag_name, tags.tag_id FROM tags WHERE LOWER(tags.tag_name) LIKE ' . implode(' OR LOWER(tags.tag_name) LIKE ', $sql_param_keys) . ';');
				$query->execute($sql_params);
				$tags_db = $query->fetchAll();
				
				foreach($tags_db as $tag){
					// Remove from tags input
					$tag_key = array_search($tag['tag_name'], $tags);
					unset($tags[$tag_key]);
					
					// Add link
					$query = 'INSERT INTO links (photo_id, tag_id) VALUES (' . $photos[$i]['photo_id'] . ', ' . $tag['tag_id'] . ');';
					$this->exec($query);
				}
				
				// For tags that don't exist, add tags and links
				if(count($tags) > 0){
					foreach($tags as $tag){
						// Error checking
						if(empty($tag)){ continue; }
						
						// Add tag
						$query = $this->prepare('INSERT INTO tags (tag_name) VALUES (:tag);');
						$query->execute(array(':tag' => $tag));
						$tag_id = intval($this->db->lastInsertId(TABLE_PREFIX . 'tags_tag_id_seq'));
					
						// Add link
						$query = 'INSERT INTO links (photo_id, tag_id) VALUES (' . $photos[$i]['photo_id'] . ', ' . $tag_id . ');';
						$this->exec($query);
					}
				}
			}
			
			$fields = array('photo_title' => @$title,
				'photo_description' => @$description);
			
			$photo = new Photo($photos[$i]['photo_id']);
			$photo->updateFields($fields, false);
		}
		
		return true;
	}
	
	/**
	 * Read and import geolocation data (via any mix of EXIF and IPTC) from images
	 *
	 * @param array $photos 
	 * @return void
	 */
	public function readGeo($photos=null){
		if(empty($photos)){
			$photos = $this->photos;
			$photo_count = $this->photo_count;
		}
		else{
			$photo_count = count($photos);
		}
		
		for($i = 0; $i < $photo_count; ++$i){
			$found_exif = 0;
			
			if(function_exists('exif_read_data')){
				// Read EXIF data
				$exif = @exif_read_data($photos[$i]['photo_file'], 0, true, false);
				
				// If EXIF data exists, add each key (group), name, value to database
				if((count($exif) > 0) and is_array($exif)){
					$inserts = array();
					foreach(@$exif as $key => $section){
					    foreach($section as $name => $value){
							// Check for geo data
							if(($key == 'GPS') and ($name == 'GPSLatitude')){
								$lat_d = $value[0];
							
								$lat_m = $value[1];
								$lat_m = explode('/', $lat_m);
								$lat_m = $lat_m[0] / $lat_m[1];
							
								$lat_s = $value[2];
								$lat_s = explode('/', $lat_s);
								$lat_s = $lat_s[0] / $lat_s[1];
							
								$found_exif++;
							}
							if(($key == 'GPS') and ($name == 'GPSLatitudeRef')){
								if(strtolower($value) == 's'){
									$lat_d = 0 - $lat_d;
									$lat_m = 0 - $lat_m;
									$lat_s = 0 - $lat_s;
								}
								$found_exif++;
							}
							if(($key == 'GPS') and ($name == 'GPSLongitude')){
								$long_d = $value[0];
							
								$long_m = $value[1];
								$long_m = explode('/', $long_m);
								$long_m = $long_m[0] / $long_m[1];
							
								$long_s = $value[2];
								$long_s = explode('/', $long_s);
								$long_s = $long_s[0] / $long_s[1];
							
								$found_exif++;
							}
							if(($key == 'GPS') and ($name == 'GPSLongitudeRef')){
								if(strtolower($value) == 'w'){
									$long_d = 0 - $long_d;
									$long_m = 0 - $long_m;
									$long_s = 0 - $long_s;
								}
								$found_exif++;
							}
					    }
					}
				}
			
				// Did it find all 4 EXIF GPS tags?
				if($found_exif == 4){
					$geo_lat = $lat_d + ($lat_m / 60) + ($lat_s / 3600);
					$geo_long = $long_d + ($long_m / 60) + ($long_s / 3600);
				}
			}
			
			// Read IPTC data
			$size = getimagesize($photos[$i]['photo_file'], $info);
			
			if(isset($info['APP13']))
			{
				// Parse IPTC data
			    $iptc = iptcparse($info['APP13']);
				
				$city = (!empty($iptc["2#090"][0])) ? $iptc["2#090"][0] : '';
				$state = (!empty($iptc["2#095"][0])) ? $iptc["2#095"][0] : '';
				$country = (!empty($iptc["2#101"][0])) ? $iptc["2#101"][0] : '';
			}
			
			// Determine if there's geo data in IPTC
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
				$place = new Geo($place);
			}
			elseif($found_exif == 4){
				// Require geography class
				require_once('geo.php');
				
				$place = new Geo(strval($geo_lat) . ', ' . strval($geo_long));
			}
			
			if(!empty($place->city)){
				$geo = $place->city['city_name'];
				if(!empty($place->city['city_state'])){
					$geo .= ', ' . $place->city['city_state'];
				}
				$geo .= ', ' . $place->city['country_name'];
			}
			elseif(!empty($place->raw)){
				$geo = $place->raw;
			}
			
			if(!empty($place->lat) or !empty($place->long)){
				$geo_lat = $place->lat;
				$geo_long = $place->long;
			}
			
			$fields = array('photo_geo' => @$geo,
				'photo_geo_lat' => @$geo_lat,
				'photo_geo_long' => @$geo_long);
		
			$photo = new Photo($photos[$i]['photo_id']);
			$photo->updateFields($fields, false);
		}
	}
	
	/**
	 * Increase photo_views field by 1
	 *
	 * @return void
	 */
	public function updateViews(){
		for($i = 0; $i < $this->photo_count; ++$i){
			$this->photos[$i]['photo_views']++;
			$this->exec('UPDATE photos SET photo_views = ' . $this->photos[$i]['photo_views'] . ' WHERE photo_id = ' . $this->photos[$i]['photo_id'] . ';');
		}
	}
	
	/**
	 * Delete photos (and remove photo from photos, comments, exifs, and links tables)
	 *
	 * @return void
	 */
	public function delete(){
		$this->deSizePhoto(true);
		for($i = 0; $i < $this->photo_count; ++$i){
			@$this->exec('DELETE FROM photos WHERE photo_id = ' . $this->photos[$i]['photo_id'] . ';');
			@$this->exec('DELETE FROM comments WHERE photo_id = ' . $this->photos[$i]['photo_id'] . ';');
			@$this->exec('DELETE FROM exifs WHERE photo_id = ' . $this->photos[$i]['photo_id'] . ';');
			@$this->exec('DELETE FROM links WHERE photo_id = ' . $this->photos[$i]['photo_id'] . ';');
		}
	}
	
	/**
	 * Generate image URLs for images
	 *
	 * @param array|string $sizes Size titles (or else all sizes)
	 * @return void
	 */
	public function getImgUrl($sizes=null){
		$sizes = $this->convertToArray($sizes);
		
		// Find size's prefix and suffix
		if(!empty($size)){
			$sizes = array_map('strtolower', $sizes);
			$value_slots = array_fill(0, count($sizes)*2, '?');
			$query = $this->prepare('SELECT size_label, size_prepend, size_append FROM sizes WHERE LOWER(size_title) = ' . implode(' OR size_title = ', $value_slots) . ' OR LOWER(size_label) = ' . implode(' OR size_label = ', $value_slots));
			$query->execute($sizes);
		}
		else{
			$query = $this->prepare('SELECT size_label, size_prepend, size_append, size_title FROM sizes');
			$query->execute();
		}
		
		$sizes = $query->fetchAll();
		
		foreach($sizes as $size){
			$size_label = 'photo_src_' . strtolower($size['size_label']);
			$size_img_label = 'photo_img_' . strtolower($size['size_label']);
			$size_prepend = $size['size_prepend'];
			$size_append = $size['size_append'];
			
			// Attach photo_src_ to photos array
			for($i = 0; $i < $this->photo_count; ++$i){
				$photo_ext = $this->photos[$i]['photo_ext'];

				if(in_array($photo_ext, array('pdf', 'svg'))){
					$photo_ext = 'png';
				}
				
			    $this->photos[$i][$size_label] = BASE . PHOTOS . $size_prepend . $this->photos[$i]['photo_id'] . $size_append . '.' . $photo_ext;
			    $this->photos[$i][$size_img_label] = '<img src="' . BASE . PHOTOS . $size_prepend . $this->photos[$i]['photo_id'] . $size_append . '.' . $photo_ext . ' alt="" />';
			}
		}
		
		return $sizes;
	}
	
	/**
	 * Get EXIF data and append to photo array
	 *
	 * @return array Associative array of EXIFs
	 */
	public function getEXIF(){
		$query = $this->prepare('SELECT exifs.* FROM exifs, photos' . $this->sql . ' AND photos.photo_id = exifs.photo_id;');
		$query->execute();
		$exifs = $query->fetchAll();
		
		foreach($exifs as $exif){
			$photo_id = intval($exif['photo_id']);
			$key = array_search($photo_id, $this->photo_ids);
			if(@$photo_id = $this->photo_ids[$key]){
				@$this->photos[$key]['photo_exif_' . strtolower($exif['exif_key']) . '_' . strtolower($exif['exif_name'])] = unserialize($exif['exif_value']);
			}
		}
		
		return $exifs;
	}
	
	/**
	 * Get image tags and append to photo array
	 *
	 * @return array Associative array of tags
	 */
	public function getTags(){
		// Sort by tag name
		if($this->returnConf('tag_alpha')){
			$query = $this->prepare('SELECT tags.tag_name, tags.tag_id, photos.photo_id FROM tags, links, photos' . $this->sql . ' AND tags.tag_id = links.tag_id AND links.photo_id = photos.photo_id ORDER BY tags.tag_name;');
		}
		// Sort by order added
		else{
			$query = $this->prepare('SELECT tags.tag_name, tags.tag_id, photos.photo_id FROM tags, links, photos' . $this->sql . ' AND tags.tag_id = links.tag_id AND links.photo_id = photos.photo_id ORDER BY links.link_id;');
		}
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
		
		return $tags;
	}
	
	/**
	 * Get rights set data and append to photo array
	 *
	 * @return array Associative array of rights sets
	 */
	public function getRights(){
		$query = $this->prepare('SELECT rights.*, photos.photo_id FROM rights, photos' . $this->sql . ' AND rights.right_id = photos.right_id;');
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
		
		return $rights;
	}
	
	/**
	 * Get piles data and append to photo array
	 *
	 * @return array Associative array of piles
	 */
	public function getPiles(){
		$piles = $this->getTable('piles');
		
		foreach($piles as &$pile){
			$pile_photos = explode(', ', $pile['pile_photos']);
			foreach($this->photo_ids as $photo_id){
				if(in_array($photo_id, $pile_photos)){
					$pile['photo_id'] = $photo_id;
					$this->piles[] = $pile;
				}
			}
		}
		
		return $this->piles;
	}
	
	/**
	 * Get pages data and append to photo array
	 *
	 * @return array Associative array of pages
	 */
	public function getPages(){
		$pages = $this->getTable('pages');
		
		foreach($pages as &$page){
			$page_photos = $page['page_photos'];
			if(empty($page_photos)){ continue; }
			
			$page_photos = explode(', ', $page_photos);
			foreach($this->photo_ids as $photo_id){
				if(in_array($photo_id, $page_photos)){
					$page['photo_id'] = $photo_id;
					$this->pages[] = $page;
				}
			}
		}
		
		return $this->pages;
	}
	
	/**
	 * Get word and numerical sequencing of images
	 *
	 * @param int $start First number on page
	 * @param bool $asc Sequence order (false if DESC)
	 * @return void
	 */
	public function getSeries($start=null, $asc=true){
		if(!isset($start)){
			$start = 1;
		}
		else{
			$start = intval($start);
		}
		
		if($asc === true){
			$values = range($start, $start+$this->photo_count);
		}
		else{
			$values = range($start, $start-$this->photo_count);
		}
		
		for($i = 0; $i < $this->photo_count; ++$i){
			$this->photos[$i]['photo_numeric'] = $values[$i];
			$this->photos[$i]['photo_alpha'] = ucwords($this->numberToWords($values[$i]));
		}
	}
	
	/**
	 * Get Colorkey data and append <canvas> to photo array
	 *
	 * @param int $width Width (in pixels)
	 * @param int $height Height (in pixels) 
	 * @return void
	 */
	public function getColorkey($width=null, $height=null){
		// Error handling
		if(!isset($width)){ $width = 300; }
		if(!isset($height)){ $height = 40; }
		
		for($i = 0; $i < $this->photo_count; ++$i){
			$photo_colors = unserialize($this->photos[$i]['photo_colors']);
			
			if(empty($photo_colors)){ $this->photos[$i]['photo_colorkey'] = ''; continue; }

			$photo_colors_colors = array();
			$photo_colors_percents = array();

			foreach($photo_colors as $color => $percent){
				$photo_colors_colors[] = $color;
				$photo_colors_percents[] = $percent;
			}

			$photo_colors_colors = json_encode($photo_colors_colors);
			$photo_colors_percents = json_encode($photo_colors_percents);
			
			$this->photos[$i]['photo_colorkey'] = '<div class="colorkey_data none">
				<div class="colors">' . $photo_colors_colors . '</div>
				<div class="percents">' . $photo_colors_percents . '</div>
			</div>
			<canvas width="' . intval(@$width) . '" height="' . intval(@$height) . '" class="colorkey"></canvas>';
		}
	}
	
	/**
	 * Add string notation to particular sequence, good for CSS columns
	 *
	 * @param string $label String notation
	 * @param int $frequency 
	 * @param bool $start_first True if first image should be selected and begin sequence
	 * @return void
	 */
	public function addSequence($label, $frequency, $start_first=false){
		if($start_first === false){
			$i = 1;
		}
		else{
			$i = $frequency;
		}
		
		// Store photo comment fields
		foreach($this->photos as &$photo){
			if($i == $frequency){
				if(empty($photo['photo_sequence'])){
					$photo['photo_sequence'] = $label;
				}
				else{
					$photo['photo_sequence'] .= ' ' . $label;
				}
				$i = 1;
			}
			else{
				$i++;
			}
		}
		
		return true;
	}
		
	/**
	 * Get comments data, append comment <input> HTML data
	 *
	 * @return array Associative array of comments
	 */
	public function getComments(){
		$query = $this->prepare('SELECT * FROM comments, photos' . $this->sql . ' AND comments.photo_id = photos.photo_id;');
		$query->execute();
		$this->comments = $query->fetchAll();
		
		foreach($this->comments as &$comment){
			if(!empty($comment['comment_author_avatar'])){
				$comment['comment_author_avatar'] = '<img src="' . $comment['comment_author_avatar'] . '" alt="" />';
			}
			$comment['comment_created'] = parent::formatTime($comment['comment_created']);
		}
		
		// Store photo comment fields
		for($i = 0; $i < $this->photo_count; ++$i){
			$this->photos[$i]['photo_comment_text'] = '<textarea id="comment_' . $this->photos[$i]['photo_id'] . '_text" name="comment_' . $this->photos[$i]['photo_id'] . '_text" class="comment"></textarea>';
			
			$this->photos[$i]['photo_comment_author_name'] = '<input type="text" id="comment_' . $this->photos[$i]['photo_id'] . '_author_name" name="comment_' . $this->photos[$i]['photo_id'] . '_author_name" class="comment_author_name" />';
			
			$this->photos[$i]['photo_comment_author_email'] = '<input type="text" id="comment_' . $this->photos[$i]['photo_id'] . '_author_email" name="comment_' . $this->photos[$i]['photo_id'] . '_author_email" class="comment_author_email" />';
			
			$this->photos[$i]['photo_comment_author_uri'] = '<input type="text" id="comment_' . $this->photos[$i]['photo_id'] . '_author_uri" name="comment_' . $this->photos[$i]['photo_id'] . '_author_uri" class="comment_author_uri" />';
		
			$this->photos[$i]['photo_comment_submit'] = '<input type="hidden" name="comment_id" value="' . $this->photos[$i]['photo_id'] . '" /><input type="submit" id="" name="" class="comment_submit" value="Submit comment" />';
		}
		
		return $this->comments;
	}
	
	/**
	 * Delete photo thumbnail files
	 *
	 * @param string $original Delete original photo
	 * @return void
	 */
	public function deSizePhoto($original=false){
		// Open photo directory
		$dir = parent::correctWinPath(PATH . PHOTOS);
		$handle = opendir($dir);
		$photos = array();
		
		while($filename = readdir($handle)){
			for($i = 0; $i < $this->photo_count; ++$i){
				// Find photo thumnails
				if(preg_match('/^((.*[\D]+' . $this->photos[$i]['photo_id'] . '|' . $this->photos[$i]['photo_id'] . '[\D]+.*|.*[\D]+' . $this->photos[$i]['photo_id'] . '[\D]+.*)\..+)$/', $filename)){
					$photos[] = $dir . $filename;
				}
				if($original === true){
					if(preg_match('/^' . $this->photos[$i]['photo_id'] . '\..+$/', $filename)){
						$photos[] = $dir . $filename;
					}
				}
			}
	    }
		
		closedir($handle);
		
		// Delete photo thumbnails
		foreach($photos as $photo){
			unlink($photo);
		}
	}
	
	/**
	 * Format time
	 *
	 * @param string $format Same format as date();
	 * @return void
	 */
	public function formatTime($format=null){
		for($i = 0; $i < $this->photo_count; ++$i){
			$this->photos[$i]['photo_taken'] = parent::formatTime($this->photos[$i]['photo_taken'], $format);
			$this->photos[$i]['photo_uploaded'] = parent::formatTime($this->photos[$i]['photo_uploaded'], $format);
			$this->photos[$i]['photo_published'] = parent::formatTime($this->photos[$i]['photo_published'], $format);
			$this->photos[$i]['photo_updated'] = parent::formatTime($this->photos[$i]['photo_updated'], $format);
		}
	}
	
	/**
	 * Watermark image
	 *
	 * @param string $src Source full path
	 * @param string $dest Destination full path
	 * @param string $watermark Watermark full path
	 * @param int $margin Margin (in pixels)
	 * @param string $position Watermark position
	 * @param int $quality 1-100
	 * @param string $ext Extension
	 * @return void
	 */
	private function watermark($src, $dest, $watermark, $margin=null, $position=null, $quality=null, $ext=null){
		if(empty($margin)){ $margin = $this->returnConf('thumb_watermark_margin'); }
		if(empty($position)){ $position = $this->returnConf('thumb_watermark_pos'); }
		if(empty($quality)){ $quality = $this->returnConf('thumb_compress_tol'); }
		if(empty($ext)){ $ext = self::getExt($src); }
		
		$src = parent::correctWinPath($src);
		$dest = parent::correctWinPath($dest);
		$watermark = parent::correctWinPath($watermark);
		
		if(class_exists('Imagick', false) and $this->returnConf('thumb_imagick')){
			$image = new Imagick($src);
			$image_watermark = new Imagick($watermark);
			
			list($width, $height) = getimagesize($src);
			list($width_watermark, $height_watermark) = getimagesize($watermark);
			
			switch($ext){
				case 'jpg':
					$image->setImageCompression(Imagick::COMPRESSION_JPEG); 
					$image->setImageCompressionQuality($quality);
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
			
			$image->writeImage($dest);
			$image->clear();
			$image->destroy();
			return true;
		}
		else{
			$watermark = imagecreatefrompng($watermark);

			imagealphablending($watermark, false);
		    imagesavealpha($watermark, true);

			$width_watermark = imagesx($watermark);
			$height_watermark = imagesy($watermark);
			
			switch($ext){
				case 'jpg':
					$image = imagecreatefromjpeg($src);
					imagealphablending($image, true);
				
					$width = imagesx($image);
					$height = imagesy($image);
				
					if((($height_watermark + ($margin * 2)) > $height) or (($width_watermark + ($margin * 2)) > $width)){ return false; break; }
					
					list($pos_x, $pos_y) = $this->watermarkPosition($height, $width, $height_watermark, $width_watermark, $margin, $position);
				
					imagecopy($image, $watermark, $pos_x, $pos_y, 0, 0, $width_watermark, $height_watermark);
					imagedestroy($watermark);
					imagejpeg($image, $dest, $quality);
					imagedestroy($image);
				
					return true;
					break;
				case 'png':
					$image = imagecreatefrompng($src);
					imagealphablending($image, true);
					
					$quality_tmp = floor((1 / $quality) * 95);
					
					$width = imagesx($image);
					$height = imagesy($image);
				
					if((($height_watermark + ($margin * 2)) > $height) or (($width_watermark + ($margin * 2)) > $width)){ return false; break; }
				
					list($pos_x, $pos_y) = $this->watermarkPosition($height, $width, $height_watermark, $width_watermark, $margin, $position);
				
					imagecopy($image, $watermark, $pos_x, $pos_y, 0, 0, $width_watermark, $height_watermark);
					imagedestroy($watermark);
					imagepng($image, $dest, $quality_tmp);
					imagedestroy($image);
				
					return true;
					break;
				case 'gif':
					$image = imagecreatefromgif($src);
					
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
	
	/**
	 * Determine watermark position
	 *
	 * @param int $photo_height Photo height (in pixels)
	 * @param int $photo_width Photo width (in pixels)
	 * @param int $water_height Watermark height (in pixels)
	 * @param int $water_width Watermark width (in pixels)
	 * @param int $margin Margin (in pixels)
	 * @param string $position 'nw', 'ne', 'sw', '00', 'n0', 's0', '0e', '0w'
	 * @return void
	 */
	private function watermarkPosition($photo_height, $photo_width, $water_height, $water_width, $margin=null, $position=null){
		if(empty($margin)){ $margin = $this->returnConf('thumb_watermark_margin'); }
		if(empty($position)){ $position = $this->returnConf('thumb_watermark_pos'); }
		switch($position){
			case 'nw':
				$pos_x = $margin;
				$pos_y = $margin;
				break;
			case 'ne':
				$pos_x = $photo_width - $water_width - $margin;
				$pos_y = $margin;
				break;
			case 'sw':
				$pos_x = $margin;
				$pos_y = $photo_height - $water_height - $margin;
				break;
			case 'se':
				$pos_x = $photo_width - $water_width - $margin;
				$pos_y = $photo_height - $water_height - $margin;
				break;
			case '00':
				$pos_x = ($photo_width / 2) - ($water_width / 2);
				$pos_y = ($photo_height / 2) - ($water_height / 2);
				break;
			case 'n0':
				$pos_x = ($photo_width / 2) - ($water_width / 2);
				$pos_y = $margin;
				break;
			case 's0':
				$pos_x = ($photo_width / 2) - ($water_width / 2);
				$pos_y = $photo_height - $water_height - $margin;
				break;
			case '0e':
				$pos_x = $photo_width - $water_width - $margin;
				$pos_y = ($photo_height / 2) - ($water_height / 2);
				break;
			case '0w':
				$pos_x = $margin;
				$pos_y = ($photo_height / 2) - ($water_height / 2);
				break;
			default:
				return false;
				break;
		}
		return array($pos_x, $pos_y);
	}
}

?>