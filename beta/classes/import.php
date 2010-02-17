<?php

class Import extends Alkaline{
	public $photo_ids;
	public $count;
	
	public function __construct($photos){
		parent::__construct();
		
		require_once(PATH . FUNCTIONS . 'text.php');
				
		// Prepare input
		convertToArray($photos);
		
		$this->photo_ids = array();
		foreach($photos as $photo){
			// Check to see if file exists
			if(!file_exists($file)){
				$this->photo_ids[] = $this->importPhoto($photos);
			}
		}
		
		$this->count = count($this->photo_ids);
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	private function importPhoto($file){
		require_once(PATH . FUNCTIONS . 'image.php');

		// Add photo to database
		$photo_ext = imageExt($file);
		$filename = substr(strrchr($file, '/'), 1);
		
		$query = 'INSERT INTO photos (photo_ext, photo_name, photo_uploaded) VALUES ("' . $photo_ext . '", "' . addslashes($filename) . '", "' . date('Y-m-d H:i:s') . '");';
		$this->db->exec($query);
		$photo_id = $this->db->lastInsertId();
		
		// Copy photo to archive
		copy($file, PATH . PHOTOS . $photo_id . '.' . $photo_ext);

		// Generate photo thumbnails based on sizes in database
		$query = $this->db->prepare('SELECT * FROM sizes');
		$query->execute();
		$sizes = $query->fetchAll();
				
		foreach($sizes as $size){
			$size_height = $size['size_height'];
			$size_width = $size['size_width'];
			$size_type = $size['size_type'];
			$size_prepend = $size['size_prepend'];
			$size_append = $size['size_append'];
			switch($size_type){
				case 'fill':
					imageScaleFill(PATH . PHOTOS . $photo_id . '.' . $photo_ext, PATH . PHOTOS . $size_prepend . $photo_id . $size_append . '.' . $photo_ext, $size_height, $size_width, IMG_QUAL, $photo_ext);
					break;
				case 'max':
					imageScaleMax(PATH . PHOTOS . $photo_id . '.' . $photo_ext, PATH . PHOTOS . $size_prepend . $photo_id . $size_append . '.' . $photo_ext, $size_height, $size_width, IMG_QUAL, $photo_ext);
					break;
			}
		}

		// Read EXIF data
		$file = PATH . PHOTOS . $photo_id . '.' . $photo_ext;
		$exif = @exif_read_data($file, 0, true, false);

		// If EXIF data exists, add each key (group), name, value to database
		if(count($exif) > 0){
			$inserts = array();
			foreach($exif as $key => $section){
			    foreach($section as $name => $value){
					$query = 'INSERT INTO exifs (photo_id, exif_key, exif_name, exif_value) VALUES (' . $photo_id . ', "' . addslashes($key) . '", "' . addslashes($name) . '", "' . addslashes(serialize($value)) . '")';
					$this->db->exec($query);
			    }
			}
		}
	}
}

?>