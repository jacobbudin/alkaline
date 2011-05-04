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

class Image extends Alkaline{
	public $comments;
	public $db;
	public $images = array();
	public $pages;
	public $related;
	public $sets;
	public $sizes;
	public $tags;
	public $tag_count;
	public $image_columns;
	public $image_count = 0;
	public $image_import_ids = array();
	public $image_ids = array();
	public $user;
	
	protected $sql;
	
	/**
	 * Initiates Image object
	 *
	 * @param array|int|string $image_ids Image IDs (use Find class to locate them)
	 */
	public function __construct($image_ids=null){
		parent::__construct();
		
		// Reset image array
		$this->images = array();
		
		// Input handling
		if(is_object($image_ids)){
			$last_modified = $image_ids->last_modified;
			$image_ids = $image_ids->ids;
		}
		
		$this->image_ids = parent::convertToIntegerArray($image_ids);
		
		// Error checking
		$this->sql = ' WHERE (images.image_id IS NULL)';
		
		// Cache
		require_once('cache_lite/Lite.php');
		
		// Set a few options
		$options = array(
		    'cacheDir' => PATH . CACHE,
		    'lifeTime' => 3600
		);

		// Create a Cache_Lite object
		$cache = new Cache_Lite($options);
		
		if(($images = $cache->get('images:' . implode(',', $this->image_ids), 'images')) && !empty($last_modified) && ($cache->lastModified() > $last_modified)){
			$this->images = unserialize($images);
		}
		else{
			if(count($this->image_ids) > 0){
				// Retrieve images from database
				$this->sql = ' WHERE (images.image_id IN (' . implode(', ', $this->image_ids) . '))';

				$query = $this->prepare('SELECT * FROM images' . $this->sql . ';');
				$query->execute();
				$images = $query->fetchAll();

				// Ensure images array correlates to image_ids array
				foreach($this->image_ids as $image_id){
					foreach($images as $image){
						if($image_id == $image['image_id']){
							$this->images[] = $image;
						}
					}
				}

				// Store image count as integer
				$image_count = count($this->images);

				// Attach additional fields
				for($i = 0; $i < $image_count; ++$i){
					$this->images[$i]['image_file'] = parent::correctWinPath(PATH . IMAGES . $this->images[$i]['image_id'] . '.' . $this->images[$i]['image_ext']);
					$this->images[$i]['image_src'] = BASE . IMAGES . $this->images[$i]['image_id'] . '.' . $this->images[$i]['image_ext'];
					$title_url = $this->makeURL($this->images[$i]['image_title']);
					if(empty($title_url) or (URL_RW != '/')){
						$this->images[$i]['image_uri_rel'] = BASE . 'image' . URL_ID . $this->images[$i]['image_id'] . URL_RW;
					}
					else{
						$this->images[$i]['image_uri_rel'] = BASE . 'image' . URL_ID . $this->images[$i]['image_id'] . '-' . $title_url . URL_RW;
					}

					$this->images[$i]['image_uri'] = LOCATION . $this->images[$i]['image_uri_rel'];

					if($this->returnConf('comm_enabled') != true){
						$this->images[$i]['image_comment_disabled'] = 1;
					}
					elseif($this->returnConf('comm_close') == true){
						if((time() - strtotime($this->images[$i]['image_published'])) > $this->returnConf('comm_close_time')){
							$this->images[$i]['image_comment_disabled'] = 1;
						}
					}
				}
			}
			
		    $cache->save(serialize($this->images));
		}
		
		// Store image count as integer
		$this->image_count = count($this->images);
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
		
		$this->images = $orbit->hook('image', $this->images, $this->images);
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
		$image_ids = array();
		
		foreach($files as $file){
			if(!file_exists($file)){
				return false;
			}
		
			$filename = $this->getFilename($file);
		
			// Add image to database
			$image_ext = $this->getExt($file);
			$image_mime = $this->getMIME($file);
			$image_size = $this->getSize($file, $image_ext);
			
			// Configuration: default rights set
			if($this->returnConf('rights_default')){
				$right_id = $this->returnConf('rights_default_id');
			}
			
			$fields = array('user_id' => @$this->user['user_id'],
				'right_id' => @$right_id,
				'image_ext' => $image_ext,
				'image_mime' => $image_mime,
				'image_name' => $filename,
				'image_uploaded' => date('Y-m-d H:i:s'),
				'image_height' => $image_size['height'],
				'image_width' => $image_size['width']);
			
			$image_id = $this->addRow($fields, 'images');
			$image_ids[] = $image_id;

			// Copy image to archive, delete original from shoebox
			copy($file, parent::correctWinPath(PATH . IMAGES . $image_id . '.' . $image_ext));
			@unlink($file);
		}
		
		// Store initial image_ids array
		$existing_image_ids = $this->image_ids;
		
		// Construct object anew
		self::__construct($image_ids);
		
		// Process imported images
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
		
		$this->sizeImage();
		
		// Combine existing and imported image_ids arrays
		if(!empty($existing_image_ids)){
			$this->image_ids = array_merge($existing_image_ids, $this->image_ids);
		}
		
		// Merge with previous image_ids
		self::__construct($this->image_ids);
	}
	
	/**
	 * Generate image thumbnails based on sizes in database (and apply watermark if selected)
	 *
	 * @param array $images Image array
	 * @param array|int $size_ids Size IDs (else all sizes)
	 * @return void
	 */
	public function sizeImage($images=null, $size_ids=null){
		if(empty($images)){
			$images = $this->images;
			$image_count = $this->image_count;
		}
		else{
			$image_count = count($images);
		}
		
		if(!empty($size_ids)){
			$size_ids = parent::convertToIntegerArray($size_ids);
		}
		
		// Look up sizes in database
		$query = $this->prepare('SELECT * FROM sizes');
		$query->execute();
		$sizes = $query->fetchAll();
		
		// Generate thumbnails
		for($i = 0; $i < $image_count; ++$i){
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
				$size_label = $size['size_label'];
				$size_dest = parent::correctWinPath(PATH . IMAGES . $size_prepend . $images[$i]['image_id'] . $size_append . '.' . $images[$i]['image_ext']);
				
				if(in_array($images[$i]['image_ext'], array('pdf', 'svg'))){
					$size_dest = $this->changeExt($size_dest, 'png');
				}
				
				switch($size_type){
					case 'fill':
						$thumbnail = new Thumbnail($images[$i]['image_file']);
						$thumbnail->adaptiveResize($size_width, $size_height);
						$thumbnail->save($size_dest);
						break;
					case 'scale':
						$thumbnail = new Thumbnail($images[$i]['image_file']);
						$thumbnail->resize($size_width, $size_height);
						$thumbnail->save($size_dest);
						break;
					default:
						return false; break;
				}
				
				if($this->returnConf('thumb_watermark') and ($size_watermark == 1)){
					$watermark = parent::correctWinPath(PATH . WATERMARKS . $size_label . '.png');
					if(!file_exists($watermark)){
						$watermark = parent::correctWinPath(PATH . WATERMARKS . 'watermark.png');
					}
					$thumbnail->watermark($watermark);
				}
				
				unset($thumbnail);
			}
		}
	}
	
	/**
	 * Update image table
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
		
		$array_original = $array;
				
		for($i = 0; $i < $this->image_count; ++$i){
			 $array = $array_original;
			
			// Verify each key has changed; if not, unset the key
			foreach($array as $key => $value){
				if($array[$key] == $this->images[$i][$key]){
					unset($array[$key]);
				}
				if(!empty($this->images[$i][$key]) and ($overwrite === false)){
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
				if($key == 'image_published'){
					if(empty($value)){
						$fields[$key] = null;
					}
					elseif(trim(strtolower($value)) == 'now'){
						$value = date('Y-m-d H:i:s');
						$fields[$key] = $value;
					}
					else{
						$value = str_ireplace(' on ', ', ', $value);
						$value = str_ireplace(' at ', ', ', $value);
						$value = strtotime($value);
						if($value !== false){
							$value = date('Y-m-d H:i:s', $value);
						}
						else{
							$this->addNote('The image&#8217;s publish date could not be determined, and was left unpublished.', 'error');
							$value = '';
						}
						$fields[$key] = $value;
					}
				}
				elseif($key == 'image_geo'){
					$geo = new Geo($value);
					if(!empty($geo->city)){
						if($geo->city['country_name'] == 'United States'){
							$fields['image_geo'] = $geo->city['city_name'] . ', ' . $geo->city['city_state'] .', ' . $geo->city['country_name'];
						}
						else{
							$fields['image_geo'] = $geo->city['city_name'] . ', ' . $geo->city['country_name'];
						}
					}
					elseif(!empty($geo->raw)){
						$fields['image_geo'] = ucwords($geo->raw);
					}
					else{
						$fields['image_geo'] = '';
					}
					
					if(!empty($geo->lat) and !empty($geo->long)){
						$fields['image_geo_lat'] = $geo->lat;
						$fields['image_geo_long'] = $geo->long;
					}
				}
				else{
					$fields[$key] = $value;
				}
			}
			
			// Set image_modified field to now
			$fields['image_modified'] = date('Y-m-d H:i:s');
			
			$columns = array_keys($fields);
			$values = array_values($fields);

			// Add row to database
			$query = $this->prepare('UPDATE images SET ' . implode(' = ?, ', $columns) . ' = ? WHERE image_id = ' . $this->images[$i]['image_id'] . ';');
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
		
		if($tag_count > 0){
			for($j=0; $j<$tag_count; ++$j){
				$sql_params[':tag' . $j] = $tags[$j];
			}
		
			$sql_param_keys = array_keys($sql_params);
			
			$query = $this->prepare('SELECT tags.tag_parents FROM tags WHERE tags.tag_name = ' . implode(' OR tags.tag_name = ', $sql_param_keys) . ';');

			$query->execute($sql_params);
			$tags_db = $query->fetchAll();

			$tags_db_parents = array();

			foreach($tags_db as $tag_db){
				if(!empty($tag_db['tag_parents'])){
					$tag_db['tag_parents'] = unserialize($tag_db['tag_parents']);
					if(is_array($tag_db['tag_parents'])){
						foreach($tag_db['tag_parents'] as $tag){
							$tags[] = $tag;
						}
					}
				}
			}

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
			$tags_db_names = array();
		
			foreach($tags_db as $tag_db){
				$tags_db_names[] = $tag_db['tag_name'];
			}
		}
		
		
		// Tags to update to all images
		$tags_to_update = $tags;
		
		// Get current tags
		$this->getTags(true);
		
		$affected_image_ids = array();
		
		// Loop through images
		for($i = 0; $i < $this->image_count; ++$i){
			// Duplicate tags so unsetting doesn't affect loop
			$tags = $tags_to_update;
			
			// Verify tags have changed; if not, unset the key
			foreach($this->tags as $tag){
				if($tag['image_id'] == $this->images[$i]['image_id']){
					$tag_key = array_search($tag['tag_name'], $tags);
					if($tag_key !== false){
						unset($tags[$tag_key]);
						continue;
					}
					else{
						$query = 'DELETE FROM links WHERE image_id = ' . $tag['image_id'] . ' AND tag_id = ' . $tag['tag_id'] . ';';
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
			
			$affected_image_ids[] = $this->images[$i]['image_id'];
			
			foreach($tags as $tag){
				$key = array_search($tag, $tags_db_names);
				if($key !== false){
					$query = 'INSERT INTO links (image_id, tag_id) VALUES (' . $this->images[$i]['image_id'] . ', ' . $tags_db[$key]['tag_id'] . ');';
					$this->exec($query);
				}
				else{
					$query = $this->prepare('INSERT INTO tags (tag_name) VALUES (:tag);');
					$query->execute(array(':tag' => $tag));
					$tag_id = intval($this->db->lastInsertId(TABLE_PREFIX . 'tags_tag_id_seq'));
					
					$tags_db[] = array('tag_id' => $tag_id, 'tag_name' => $tag);
					$tags_db_names[] = $tag;
					
					$query = 'INSERT INTO links (image_id, tag_id) VALUES (' . $this->images[$i]['image_id'] . ', ' . $tag_id . ');';
					$this->exec($query);
				}
			}
		}
		
		if(count($affected_image_ids) > 0){
			$now = date('Y-m-d H:i:s');
			$image_tags = implode('; ', $tags_to_update);
			$query = $this->prepare('UPDATE images SET image_modified = :image_modified, image_tags = :image_tags, image_tag_count = :image_tag_count WHERE image_id = :image_id;');
			foreach($affected_image_ids as $image_id){
				$query->execute(array(':image_modified' => $now, ':image_tags' => $image_tags, ':image_tag_count' => count($tags_to_update), ':image_id' => $image_id));
			}
		}
		
		$this->updateRelated();
		
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
		if(empty($tags)){
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
	
		$query = $this->prepare('SELECT tags.tag_parents FROM tags WHERE tags.tag_name = ' . implode(' OR tags.tag_name = ', $sql_param_keys) . ';');
		
		$query->execute($sql_params);
		$tags_db = $query->fetchAll();
		
		$tags_db_parents = array();
		
		foreach($tags_db as $tag_db){
			if(!empty($tag_db['tag_parents'])){
				$tag_db['tag_parents'] = unserialize($tag_db['tag_parents']);
				if(is_array($tag_db['tag_parents'])){
					foreach($tag_db['tag_parents'] as $tag){
						$tags[] = $tag;
					}
				}
			}
		}
		
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
		
		$query = $this->prepare('SELECT tags.tag_id, tags.tag_name, tags.tag_parents FROM tags WHERE tags.tag_name = ' . implode(' OR tags.tag_name = ', $sql_param_keys) . ';');
		
		$query->execute($sql_params);
		$tags_db = $query->fetchAll();
		$tags_db_ids = array();
		$tags_db_names = array();
		
		foreach($tags_db as $tag_db){
			$tags_db_ids[] = $tag_db['tag_id'];
			$tags_db_names[] = $tag_db['tag_name'];
		}
		
		// Tags to add to all images
		$tags_to_add = $tags;
		
		// Get current tags
		$this->getTags(true);
		
		$affected_image_ids = array();
		
		// Loop through images
		for($i = 0; $i < $this->image_count; ++$i){
			// Duplicate tags so unsetting doesn't affect loop
			$tags = $tags_to_add;
			
			// Verify tags have changed; if not, unset the key
			foreach($this->tags as $tag){
				if($tag['image_id'] == $this->images[$i]['image_id']){
					$tag_key = array_search($tag['tag_name'], $tags, true);
					if($tag_key !== false){
						unset($tags[$tag_key]);
					}
				}
			}
			
			$tags = array_merge($tags);
			
			// If no tags have changed, break
			if(count($tags) == 0){
				continue;
			}
			
			$affected_image_ids[] = $this->images[$i]['image_id'];
			
			foreach($tags as $tag){
				$key = array_search($tag, $tags_db_names);
				if($key !== false){
					$query = 'INSERT INTO links (image_id, tag_id) VALUES (' . $this->images[$i]['image_id'] . ', ' . $tags_db[$key]['tag_id'] . ');';
					$this->exec($query);
				}
				else{
					$query = $this->prepare('INSERT INTO tags (tag_name) VALUES (:tag);');
					$query->execute(array(':tag' => $tag));
					$tag_id = intval($this->db->lastInsertId(TABLE_PREFIX . 'tags_tag_id_seq'));
					
					$tags_db[] = array('tag_id' => $tag_id, 'tag_name' => $tag);
					$tag_db_ids[] = $tag_id;
					$tags_db_names[] = $tag;
					
					$query = 'INSERT INTO links (image_id, tag_id) VALUES (' . $this->images[$i]['image_id'] . ', ' . $tag_id . ');';
					$this->exec($query);
				}
			}
		}
		
		if(count($affected_image_ids) > 0){
			$now = date('Y-m-d H:i:s');
			
			$affected_images = new Image($affected_image_ids);
			$affected_images->getTags();
			
			$query = $this->prepare('UPDATE images SET image_modified = :image_modified, image_tags = :image_tags, image_tag_count = :image_tag_count WHERE image_id = :image_id;');
			
			foreach($affected_images->images as $image){
				$query->execute(array(':image_modified' => $now, ':image_tags' => implode('; ', $image['image_tags_array']), ':image_tag_count' => count($image['image_tags']), ':image_id' => $image['image_id']));
			}
			
			// $now = date('Y-m-d H:i:s');
			// $query = $this->prepare('UPDATE images SET image_modified = :image_modified WHERE image_id = :image_id;');
			// foreach($affected_image_ids as $image_id){
			// 	$query->execute(array(':image_modified' => $now, ':image_id' => $image_id));
			// }
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
		
		// Get current tags
		$this->getTags(true);
		
		$tags_db_ids = array();
		$affected_image_ids = array();
		
		for($i = 0; $i < $this->image_count; ++$i){
			// Verify tags have changed; if not, unset the key
			foreach($this->tags as $tag){
				if($tag['image_id'] == $this->images[$i]['image_id']){
					$tag_key = array_search($tag['tag_name'], $tags);
					if($tag_key !== false){			
						$query = 'DELETE FROM links WHERE image_id = ' . $tag['image_id'] . ' AND tag_id = ' . $tag['tag_id'] . ';';
						$this->exec($query);
						$tags_db_ids[] = $tag['tag_id'];
						$affected_image_ids[] = $this->images[$i]['image_id'];
					}
				}
			}
		}
		
		if(count($affected_image_ids) > 0){
			$now = date('Y-m-d H:i:s');
			
			$affected_images = new Image($affected_image_ids);
			$affected_images->getTags();
			
			$query = $this->prepare('UPDATE images SET image_modified = :image_modified, image_tags = :image_tags, image_tag_count = :image_tag_count WHERE image_id = :image_id;');
			
			foreach($affected_images->images as $image){
				$query->execute(array(':image_modified' => $now, ':image_tags' => implode('; ', $image['image_tags_array']), ':image_tag_count' => count($image['image_tags']), ':image_id' => $image['image_id']));
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
	public function getSize($file, $ext=null){
		// Error checking
		if(empty($file)){
			return false;
		}
		
		if(empty($ext)){ $ext = self::getExt($src); }
		
		$size = array();
		
		// ImageMagick version
		if(class_exists('Imagick', false) and ($this->returnConf('thumb_imagick') or in_array($ext, array('pdf', 'svg')))){
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
	 * Generate Colorkey data
	 *
	 * @param array $images 
	 * @return void
	 */
	public function findColors($images=null){
		if(empty($images)){
			$images = $this->images;
			$image_count = $this->image_count;
		}
		else{
			$image_count = count($images);
		}
		for($i = 0; $i < $image_count; ++$i){		
			$dest = preg_replace('/(.*[0-9]+)(\..+)/', '$1-tmp$2', $images[$i]['image_file']);
			
			$thumbnail = new Thumbnail($images[$i]['image_file']);
			$thumbnail->resize(50, 50);
			$thumbnail->save($dest);
		
			switch($images[$i]['image_ext']){
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
			
			$fields = array(':image_colors' => serialize($rgbs),
				':image_color_r' => $rgb_dom_r,
				':image_color_g' => $rgb_dom_g,
				':image_color_b' => $rgb_dom_b,
				':image_color_h' => $hsl_dom_h,
				':image_color_s' => $hsl_dom_s,
				':image_color_l' => $hsl_dom_l);
		
			$query = $this->prepare('UPDATE images SET image_colors = :image_colors, image_color_r = :image_color_r, image_color_g = :image_color_g, image_color_b = :image_color_b, image_color_h = :image_color_h, image_color_s = :image_color_s, image_color_l = :image_color_l WHERE image_id = ' . $images[$i]['image_id'] . ';');
			return $query->execute($fields);
		}
	}
	
	/**
	 * Read and import EXIF data from images
	 *
	 * @param array $images 
	 * @return void
	 */
	public function readEXIF($images=null){
		if(!function_exists('exif_read_data')){ return false; }
		
		if(empty($images)){
			$images = $this->images;
			$image_count = $this->image_count;
		}
		else{
			$image_count = count($images);
		}
		
		for($i = 0; $i < $image_count; ++$i){
			// Read EXIF data
			$exif = @exif_read_data($images[$i]['image_file'], 0, true, false);
			
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
						
						$fields = array('image_id' => $images[$i]['image_id'],
							'exif_key' => $key,
							'exif_name' => $name,
							'exif_value' => serialize($value));
						
						$this->addRow($fields, 'exifs');
						
						// Check for date taken, insert to images table
						if(($key == 'IFD0') and ($name == 'DateTime')){
							$query = $this->prepare('UPDATE images SET image_taken = :image_taken WHERE image_id = ' . $images[$i]['image_id'] . ';');
							$query->execute(array(':image_taken' => date('Y-m-d H:i:s', strtotime($value))));
						}
				    }
				}
			}
		}
	}
	
	/**
	 * Read and import IPTC data from images (for tags)
	 *
	 * @param array $images 
	 * @return void
	 */
	public function readIPTC($images=null){
		if(empty($images)){
			$images = $this->images;
			$image_count = $this->image_count;
		}
		else{
			$image_count = count($images);
		}
		
		for($i = 0; $i < $image_count; ++$i){
			// Read IPTC data
			$size = getimagesize($images[$i]['image_file'], $info);
			
			if(isset($info['APP13']))
			{
				// Parse IPTC data
			    $iptc = iptcparse($info['APP13']);
				
				$title = (!empty($iptc["2#005"][0])) ? $iptc["2#005"][0] : '';
				$title = (!empty($iptc["2#105"][0])) ? $iptc["2#105"][0] : $title;
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
			
			$fields = array('image_title' => @$title,
				'image_description_raw' => @$description,
				'image_description' => $this->nl2br(@$description));
			
			$image = new Image($images[$i]['image_id']);
			$image->updateFields($fields, false);
			$image->addTags($tags);
		}
		
		return true;
	}
	
	/**
	 * Read and import geolocation data (via any mix of EXIF and IPTC) from images
	 *
	 * @param array $images 
	 * @return void
	 */
	public function readGeo($images=null){
		if(empty($images)){
			$images = $this->images;
			$image_count = $this->image_count;
		}
		else{
			$image_count = count($images);
		}
		
		for($i = 0; $i < $image_count; ++$i){
			$found_exif = 0;
			
			if(function_exists('exif_read_data')){
				// Read EXIF data
				$exif = @exif_read_data($images[$i]['image_file'], 0, true, false);
				
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
			$size = getimagesize($images[$i]['image_file'], $info);
			
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
			
			$fields = array('image_geo' => @$geo,
				'image_geo_lat' => @$geo_lat,
				'image_geo_long' => @$geo_long);
		
			$image = new Image($images[$i]['image_id']);
			$image->updateFields($fields, false);
		}
	}
	
	/**
	 * Increase image_views field by 1
	 *
	 * @return void
	 */
	public function updateViews(){
		for($i = 0; $i < $this->image_count; ++$i){
			$this->images[$i]['image_views']++;
			$this->exec('UPDATE images SET image_views = ' . $this->images[$i]['image_views'] . ' WHERE image_id = ' . $this->images[$i]['image_id'] . ';');
		}
	}
	
	/**
	 * Delete images (and remove image from images, comments, exifs, and links tables)
	 * 
	 * @param bool Delete permanently (and therefore cannot be recovered)
	 * @return void
	 */
	public function delete($permanent=false){
		if($permanent === true){
			$this->deSizeImage(true);
		
			for($i = 0; $i < $this->image_count; ++$i){
				@$this->exec('DELETE FROM images WHERE image_id = ' . $this->images[$i]['image_id'] . ';');
				@$this->exec('DELETE FROM comments WHERE image_id = ' . $this->images[$i]['image_id'] . ';');
				@$this->exec('DELETE FROM exifs WHERE image_id = ' . $this->images[$i]['image_id'] . ';');
				@$this->exec('DELETE FROM links WHERE image_id = ' . $this->images[$i]['image_id'] . ';');
			}
		}
		else{
			$query = $this->prepare('UPDATE comments SET comment_deleted = ? WHERE image_id = ' . implode(' OR image_id = ', $this->image_ids) . ';');
			$query->execute(array(date('Y-m-d H:i:s')));
			
			$fields = array('image_deleted' => date('Y-m-d H:i:s'));
			$this->updateFields($fields);
		}
		
		$this->getSets();
		
		foreach($this->sets as $set){
			$a_set = new Set($set['set_id']);
			$a_set->rebuild();
		}
		
		return true;
	}
	
	/**
	 * Recover images (and comments also deleted at same time)
	 * 
	 * @return bool
	 */
	public function recover(){
		for($i = 0; $i < $this->image_count; ++$i){
			$query = $this->prepare('UPDATE comments SET comment_deleted = ? WHERE image_id = ' . $this->images[$i]['image_id'] . ' AND comment_deleted = ' . $this->images[$i]['image_deleted'] . ';');
			$query->execute(array(null));
		}
		
		$fields = array('image_deleted' => null);
		$this->updateFields($fields);
		
		$this->getSets();
		
		foreach($this->sets as $set){
			$a_set = new Set($set['set_id']);
			$a_set->rebuild();
		}
		
		return true;
	}
	
	/**
	 * Generate image URLs for images
	 *
	 * @param array|string $sizes Size titles (or else all sizes)
	 * @return void
	 */
	public function getSizes($sizes=null){
		$sizes = $this->convertToArray($sizes);
		
		if(empty($sizes)){
			$sizes = array();
		}
		
		// Cache
		require_once('cache_lite/Lite.php');
		
		// Set a few options
		$options = array(
		    'cacheDir' => PATH . CACHE,
		    'lifeTime' => 3600
		);

		// Create a Cache_Lite object
		$cache = new Cache_Lite($options);
		
		if($sizes = $cache->get('sizes:' . implode(',', $sizes), 'sizes')){
			$sizes = unserialize($sizes);
		}
		else{
			// Find size's prefix and suffix
			if(!empty($sizes)){
				$sizes = array_map('strtolower', $sizes);
				$sizes_count = count($sizes);
			
				$sizes_new = array();
			
				foreach($sizes as $size){
					$sizes_new[] = $size;
					$sizes_new[] = $size;
				}
			
				$sizes = $sizes_new;
			
				$value_slots = array_fill(0, $sizes_count, '?');
			
				$query = $this->prepare('SELECT * FROM sizes WHERE LOWER(size_title) = ' . implode(' OR LOWER(size_title) = ', $value_slots) . ' OR LOWER(size_label) = ' . implode(' OR LOWER(size_label) = ', $value_slots) . ' ORDER BY (size_width*size_height) DESC');
				$query->execute($sizes);
			}
			else{
				$query = $this->prepare('SELECT * FROM sizes ORDER BY (size_width*size_height) DESC');
				$query->execute();
			}
			
			$sizes = $query->fetchAll();
			$cache->save(serialize($sizes));
		}
		
		$this->sizes = array();
		
		for($j=0; $j < $this->image_count; $j++){
			foreach($sizes as $size){
				$size['image_id'] = $this->images[$j]['image_id'];
				$image_ext = $this->images[$j]['image_ext'];
				
				$size_label = 'image_src_' . strtolower($size['size_label']);
				$size_img_label = 'image_img_' . strtolower($size['size_label']);
				$size_prepend = $size['size_prepend'];
				$size_append = $size['size_append'];
				$size_type = $size['size_type'];
				$size_width = $size['size_width'];
				$size_height = $size['size_height'];

				if(in_array($image_ext, array('pdf', 'svg'))){
					$image_ext = 'png';
				}
				
				$size['size_src'] = BASE . IMAGES . $size_prepend . $this->images[$j]['image_id'] . $size_append . '.' . $image_ext;
				
				$width = $size['size_width'];
				$height = $size['size_height'];

			    $this->images[$j][$size_label] = BASE . IMAGES . $size_prepend . $this->images[$j]['image_id'] . $size_append . '.' . $image_ext;
			    $this->images[$j][$size_img_label] = '<img src="' . BASE . IMAGES . $size_prepend . $this->images[$j]['image_id'] . $size_append . '.' . $image_ext . ' alt="" />';
				
				$width_orig = $this->images[$j]['image_width'];
				$height_orig = $this->images[$j]['image_height'];
				
				if($size_type == 'scale'){
					if(($width_orig <= $width) and ($height_orig <= $height)){
						switch($image_ext){
							case 'jpg':
								$size['size_height'] = $this->images[$j]['image_height'];
								$size['size_width'] = $this->images[$j]['image_width'];
								break;
							case 'png':
								$size['size_height'] = $this->images[$j]['image_height'];
								$size['size_width'] = $this->images[$j]['image_width'];
								break;
							case 'gif':
								$size['size_height'] = $this->images[$j]['image_height'];
								$size['size_width'] = $this->images[$j]['image_width'];
								break;
						}
					}

					$ratio_orig = $width_orig / $height_orig;
					$ratio = $width / $height;

					if($ratio_orig > $ratio){ $height = $width / $ratio_orig; }
					else{ $width = $height * $ratio_orig; }
					
					$size['size_height'] = floor($height);
					$size['size_width'] = floor($width);
				}
				elseif($size_type == 'fill'){
					if($size_height < $height){
						$size['size_height'] = $size_height;
					}
					else{
						$size['size_height'] = $height;
					}
					
					if($size_width < $width){
						$size['size_width'] = $size_width;
					}
					else{
						$size['size_width'] = $width;
					}
				}
				$this->sizes[] = $size;
			}
		}
		
		return $this->sizes;
	}
	
	/**
	 * Get EXIF data and append to image array
	 *
	 * @return array Associative array of EXIFs
	 */
	public function getEXIF(){
		$query = $this->prepare('SELECT exifs.* FROM exifs, images' . $this->sql . ' AND images.image_id = exifs.image_id;');
		$query->execute();
		$exifs = $query->fetchAll();
		
		foreach($exifs as $exif){
			$image_id = intval($exif['image_id']);
			$key = array_search($image_id, $this->image_ids);
			if(@$image_id = $this->image_ids[$key]){
				@$this->images[$key]['image_exif_' . strtolower($exif['exif_key']) . '_' . strtolower($exif['exif_name'])] = unserialize($exif['exif_value']);
			}
		}
		
		return $exifs;
	}
	
	/**
	 * Get image tags and append to image array
	 *
	 * @param bool $show_hidden_tags Include hidden tags
	 * @return array Associative array of tags
	 */
	public function getTags($show_hidden_tags=false){
		// Sort by tag name
		if($this->returnConf('tag_alpha')){
			$query = $this->prepare('SELECT tags.tag_name, tags.tag_id, images.image_id FROM tags, links, images' . $this->sql . ' AND tags.tag_id = links.tag_id AND links.image_id = images.image_id ORDER BY tags.tag_name;');
		}
		// Sort by order added
		else{
			$query = $this->prepare('SELECT tags.tag_name, tags.tag_id, images.image_id FROM tags, links, images' . $this->sql . ' AND tags.tag_id = links.tag_id AND links.image_id = images.image_id ORDER BY links.link_id;');
		}
		$query->execute();
		$tags = $query->fetchAll();
		
		if($show_hidden_tags === true){
			$this->tags = $tags;
		}
		else{
			foreach($tags as $tag){
				if($tag['tag_name'][0] != '!'){
					$this->tags[] = $tag;
				}
			}
		}
		
		$this->tag_count = count($this->tags);
		
		// Attach additional fields
		for($i = 0; $i < $this->tag_count; ++$i){
			$title_url = $this->makeURL($this->tags[$i]['tag_name']);
			if(empty($title_url) or (URL_RW != '/')){
				$this->tags[$i]['tag_uri_rel'] = BASE . 'tag' . URL_ID . $this->tags[$i]['tag_id'] . URL_RW;
			}
			else{
				$this->tags[$i]['tag_uri_rel'] = BASE . 'tag' . URL_ID . $this->tags[$i]['tag_id'] . '-' . $title_url . URL_RW;
			}
			
			$this->tags[$i]['tag_uri'] = LOCATION . $this->tags[$i]['tag_uri_rel'];
		}
		
		foreach($this->images as &$image){
			$image['image_tags_array'] = array();
		}
		
		foreach($tags as $tag){
			$image_id = intval($tag['image_id']);
			$key = array_search($image_id, $this->image_ids);
			if($image_id = $this->image_ids[$key]){
				$this->images[$key]['image_tags_array'][] = $tag['tag_name'];
			}
		}
		
		return $tags;
	}
	
	/**
	 * Get rights set data and append to image array
	 *
	 * @return array Associative array of rights sets
	 */
	public function getRights(){
		$query = $this->prepare('SELECT rights.*, images.image_id FROM rights, images' . $this->sql . ' AND rights.right_id = images.right_id AND rights.right_deleted IS NULL;');
		$query->execute();
		$rights = $query->fetchAll();
		
		foreach($rights as $right){
			$image_id = intval($right['image_id']);
			$key = array_search($image_id, $this->image_ids);
			if($image_id = $this->image_ids[$key]){
				foreach($right as $right_key => $right_value){
					$this->images[$key][$right_key] = $right_value;
				}
			}
		}
		
		return $rights;
	}
	
	/**
	 * Get sets data and append to image array
	 *
	 * @return array Associative array of sets
	 */
	public function getSets(){
		$set_ids = new Find('sets');
		$set_ids->find();
		
		$sets = new Set($set_ids);
		
		foreach($sets->sets as &$set){
			$set_images = explode(', ', $set['set_images']);
			foreach($this->image_ids as $image_id){
				if(in_array($image_id, $set_images)){
					$set['image_id'] = $image_id;
					$this->sets[] = $set;
				}
			}
		}
		
		return $this->sets;
	}
	
	/**
	 * Get users data and append to image array
	 *
	 * @return void
	 */
	public function getUsers(){
		$ids = array();
		
		for($i = 0; $i < $this->image_count; ++$i){
			$ids[] = $this->images[$i]['user_id'];
		}
		
		$ids = array_unique($ids);
		
		$users = $this->getTable('users', $ids);
		
		$user_ids = array();
		
		foreach($users as $user){
			$user_ids[] = $user['user_id'];
		}
		
		$no_save = array('user_key', 'user_pass', 'user_pass_salt');
		
		for($i = 0; $i < $this->image_count; ++$i){
			$key = array_search($this->images[$i]['user_id'], $user_ids);
			foreach($users[$key] as $field => $value){
				if(in_array($field, $no_save)){ continue; }
				$this->images[$i][$field] = $users[$key][$field];
			}
		}
		
		return $this->users;
	}
	
	/**
	 * Get pages data and append to image array
	 *
	 * @return array Associative array of pages
	 */
	public function getPages(){
		$page_ids = new Find('pages');
		$page_ids->find();
		
		$pages = new Page($page_ids);
		
		foreach($pages->pages as &$page){
			$page_images = $page['page_images'];
			if(empty($page_images)){ continue; }
			
			$page_images = explode(', ', $page_images);
			foreach($this->image_ids as $image_id){
				if(in_array($image_id, $page_images)){
					$page['image_id'] = $image_id;
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
			$values = range($start, $start+$this->image_count);
		}
		else{
			$values = range($start, $start-$this->image_count);
		}
		
		for($i = 0; $i < $this->image_count; ++$i){
			$this->images[$i]['image_numeric'] = $values[$i];
			$this->images[$i]['image_alpha'] = ucwords($this->numberToWords($values[$i]));
		}
	}
	
	/**
	 * Get Colorkey data and append <canvas> to image array
	 *
	 * @param int $width Width (in pixels)
	 * @param int $height Height (in pixels) 
	 * @return void
	 */
	public function getColorkey($width=null, $height=null){
		// Error handling
		if(!isset($width)){ $width = 300; }
		if(!isset($height)){ $height = 40; }
		
		for($i = 0; $i < $this->image_count; ++$i){
			$image_colors = unserialize($this->images[$i]['image_colors']);
			
			if(empty($image_colors)){ $this->images[$i]['image_colorkey'] = ''; continue; }

			$image_colors_colors = array();
			$image_colors_percents = array();

			foreach($image_colors as $color => $percent){
				$image_colors_colors[] = $color;
				$image_colors_percents[] = $percent;
			}

			$image_colors_colors = json_encode($image_colors_colors);
			$image_colors_percents = json_encode($image_colors_percents);
			
			$this->images[$i]['image_colorkey'] = '<div class="colorkey_data none">
				<div class="colors">' . $image_colors_colors . '</div>
				<div class="percents">' . $image_colors_percents . '</div>
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
		
		// Store image comment fields
		foreach($this->images as &$image){
			if($i == $frequency){
				if(empty($image['image_sequence'])){
					$image['image_sequence'] = $label;
				}
				else{
					$image['image_sequence'] .= ' ' . $label;
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
	 * @param bool Published (true) or all (false)
	 * @param bool Inline responses (responses directly follow) or force chronological (false)
	 * @return array Associative array of comments
	 */
	public function getComments($published=true, $inline_responses=true){
		if($published == true){
			$query = $this->prepare('SELECT * FROM comments, images' . $this->sql . ' AND comments.image_id = images.image_id AND  comments.comment_deleted IS NULL AND comments.comment_status > 0 ORDER BY comments.comment_created ASC;');
		}
		else{
			$query = $this->prepare('SELECT * FROM comments, images' . $this->sql . ' AND comments.image_id = images.image_id AND comments.comment_deleted IS NULL ORDER BY comments.comment_created ASC;');
		}		
		$query->execute();
		$this->comments = $query->fetchAll();
		
		$comment_count = count($this->comments);
		
		foreach($this->comments as &$comment){
			if(!empty($comment['comment_author_avatar'])){
				$comment['comment_author_avatar'] = '<img src="' . $comment['comment_author_avatar'] . '" alt="" />';
			}
			$comment['comment_created'] = parent::formatTime($comment['comment_created']);
		}
		
		// Convert to inline
		if($inline_responses == true){
			$comments = array();
			for($i=0; $i < $comment_count; $i++){
				if(empty($this->comments[$i]['comment_response'])){
					$comments[$this->comments[$i]['comment_id']] = array();
					$comments[$this->comments[$i]['comment_id']][] = $this->comments[$i];
				}
				else{
					$comments[$this->comments[$i]['comment_response']][] = $this->comments[$i];
				}
			}
			
			$this->comments = array();
			
			foreach($comments as $key => $value){
				foreach($value as $comment){
					$this->comments[] = $comment;
				}
			}
		}
		
		// Store image comment fields
		for($i = 0; $i < $this->image_count; ++$i){
			$this->images[$i]['image_comment_text'] = '<textarea id="comment_' . $this->images[$i]['image_id'] . '_text" name="comment_' . $this->images[$i]['image_id'] . '_text" class="comment_text"></textarea>';
			
			$this->images[$i]['image_comment_author_name'] = '<input type="text" id="comment_' . $this->images[$i]['image_id'] . '_author_name" name="comment_' . $this->images[$i]['image_id'] . '_author_name" class="comment_author_name" />';
			
			$this->images[$i]['image_comment_author_email'] = '<input type="text" id="comment_' . $this->images[$i]['image_id'] . '_author_email" name="comment_' . $this->images[$i]['image_id'] . '_author_email" class="comment_author_email" />';
			
			$this->images[$i]['image_comment_author_uri'] = '<input type="text" id="comment_' . $this->images[$i]['image_id'] . '_author_uri" name="comment_' . $this->images[$i]['image_id'] . '_author_uri" class="comment_author_uri" />';
		
			$this->images[$i]['image_comment_submit'] = '<input type="hidden" name="image_id" value="' . $this->images[$i]['image_id'] . '" /><input type="submit" id="" name="" class="comment_submit" value="Submit comment" />';
		}
		
		return $this->comments;
	}
	
	/**
	 * Find related images
	 *
	 * @param int $limit Number of images to retrieve
	 * @return Image
	 */
	public function getRelated($limit=null){
		$ids = array();
		
		foreach($this->images as $image){
			$ids = array_merge($ids, explode(', ', $image['image_related']));
		}
		
		$ids = array_unique($ids);
		$ids = array_slice($ids, 0, $limit);
		
		$this->related = new Image($ids);
		
		return $this->related;
	}
	
	/**
	 * Update related images
	 *
	 * @param int $limit Number of images to find 
	 * @return void
	 */
	public function updateRelated($limit=100){
		$this->getTags();
		
		$now = date('Y-m-d H:i:s');

		$query = $this->prepare('UPDATE images SET image_modified = :image_modified, image_related = :image_related, image_related_hash = :image_related_hash WHERE image_id = :image_id;');
		
		// Check to see if recently updated
		for($i=0; $i < $this->image_count; $i++){ 
			$image_related_hash = md5($this->images[$i]['image_tags']);
			if($image_related_hash != $this->images[$i]['image_related_hash']){
				$image_related = array();
				
				$related_image_ids = new Find('images');
				$related_image_ids->anyTags($this->images[$i]['image_tags_array']);
				$related_image_ids->page(1, $limit);
				$related_image_ids->find();
				
				$key = array_search($this->images[$i]['image_id'], $related_image_ids->ids);
				
				if($key !== false){
					unset($related_image_ids->ids[$key]);
				}
				
				$ids = array_merge($related_image_ids->ids);
				
				$related_images = new Image($ids);
				$related_images->getTags();
				
				foreach($related_images->images as $image){
					$image_related[$image['image_id']] = count(array_intersect($this->images[$i]['image_tags_array'], $image['image_tags_array']));
				}
				
				arsort($image_related);
				
				$image_related = implode(', ', array_keys($image_related));
				
				$query->execute(array(':image_modified' => $now, ':image_related' => $image_related, ':image_related_hash' => $image_related_hash, ':image_id' => $this->images[$i]['image_id']));
			}
		}
	}
	
	/**
	 * Delete image thumbnail files
	 *
	 * @param string $original Delete original image
	 * @param array|true $save_labels Save these thumbnail labels, or true to save all
	 * @return void
	 */
	public function deSizeImage($original=false, $save_labels=null){
		// Open image directory
		$dir = parent::correctWinPath(PATH . IMAGES);
		$handle = opendir($dir);
		$images = array();
		
		$save_me = array();
		
		if(!empty($save_labels)){
			$sizes = $this->getTable('sizes');
			foreach($sizes as $size){
				if(($save_labels === true) or in_array($size['size_label'], $save_labels)){
					for($i = 0; $i < $this->image_count; ++$i){
						$save_me[] = $size['size_prepend'] . $this->images[$i]['image_id'] . $size['size_append'] . '.' . $this->images[$i]['image_ext'];
					}
				}
			}
		}
		
		while($filename = readdir($handle)){
			for($i = 0; $i < $this->image_count; ++$i){
				// Find image thumnails
				if(preg_match('/^((.*[\D]+' . $this->images[$i]['image_id'] . '|' . $this->images[$i]['image_id'] . '[\D]+.*|.*[\D]+' . $this->images[$i]['image_id'] . '[\D]+.*)\..+)$/', $filename)){
					if(!in_array($filename, $save_me)){
						$images[] = $dir . $filename;
					}
				}
				if($original === true){
					if(preg_match('/^' . $this->images[$i]['image_id'] . '\..+$/', $filename)){
						$images[] = $dir . $filename;
					}
				}
			}
	    }
		
		closedir($handle);
		
		// Delete image thumbnails
		foreach($images as $image){
			unlink($image);
		}
	}
	
	/**
	 * Format time
	 *
	 * @param string $format Same format as date();
	 * @return void
	 */
	public function formatTime($format=null){
		foreach($this->images as &$image){
			$image['image_taken_format'] = parent::formatTime($image['image_taken'], $format);
			$image['image_uploaded_format'] = parent::formatTime($image['image_uploaded'], $format);
			$image['image_published_format'] = parent::formatTime($image['image_published'], $format);
			$image['image_modified_format'] = parent::formatTime($image['image_modified'], $format);
		}
	}
}

?>