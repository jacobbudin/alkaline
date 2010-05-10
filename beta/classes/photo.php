<?php

class Photo extends Alkaline{
	public $photos;
	public $photo_ids;
	public $photo_count;
	protected $sql;
	
	public function __construct($photos){
		parent::__construct();
		
		require_once(PATH . FUNCTIONS . 'text.php');
		
		// Prepare input
		convertToArray($photos);
		
		$photo_ids = array();
		
		foreach($photos as $key => $value){
			if(preg_match('/^\//', $value)){
				require_once(PATH . FUNCTIONS . 'image.php');
				
				$import = true;
				$file = $value;
				
				// Verify file exists
				if(file_exists($file)){
					// Add photo to database
					$photo_ext = imageExt($file);
					$filename = substr(strrchr($file, '/'), 1);

					$query = 'INSERT INTO photos (photo_ext, photo_name, photo_uploaded) VALUES ("' . $photo_ext . '", "' . addslashes($filename) . '", "' . date('Y-m-d H:i:s') . '");';
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
		
		// Prepare input
		convertToIntegerArray($photo_ids);
		
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
		
		if($import){
			$this->sizePhoto();
			$this->exifPhoto();
		}
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	// Generate photo thumbnails based on sizes in database
	public function sizePhoto(){
		require_once(PATH . FUNCTIONS . 'image.php');
		
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
						imageScaleFill($this->photos[$i]['photo_file'], PATH . PHOTOS . $size_prepend . $this->photos[$i]['photo_id'] . $size_append . '.' . $this->photos[$i]['photo_ext'], $size_height, $size_width, IMG_QUAL, $this->photos[$i]['photo_ext']);
						break;
					case 'max':
						imageScaleMax($this->photos[$i]['photo_file'], PATH . PHOTOS . $size_prepend . $this->photos[$i]['photo_id'] . $size_append . '.' . $this->photos[$i]['photo_ext'], $size_height, $size_width, IMG_QUAL, $this->photos[$i]['photo_ext']);
						break;
				}
			}
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