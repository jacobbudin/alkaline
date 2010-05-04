<?php

class Import extends Photo{
	public $photo_ids;
	public $photo_count;
	
	public function __construct($files){
		Alkaline::__construct();
		
		require_once(PATH . FUNCTIONS . 'image.php');
		require_once(PATH . FUNCTIONS . 'text.php');
		
		// Prepare input
		convertToArray($files);
		
		$this->photo_ids = array();
		
		// Add photos
		foreach($files as $file){
			// Add photo to database
			$photo_ext = imageExt($file);
			$filename = substr(strrchr($file, '/'), 1);
		
			$query = 'INSERT INTO photos (photo_ext, photo_name, photo_uploaded) VALUES ("' . $photo_ext . '", "' . addslashes($filename) . '", "' . date('Y-m-d H:i:s') . '");';
			$this->db->exec($query);
			$photo_id = $this->db->lastInsertId();
			$this->photo_ids[] = $photo_id;
		
			// Copy photo to archive
			copy($file, PATH . PHOTOS . $photo_id . '.' . $photo_ext);
		}
		
		parent::__construct($this->photo_ids);
		parent::sizePhoto();
		parent::exifPhoto();
		
		$this->photo_count = count($this->photo_ids);
		
		return $this->photo_count;
	}
	
	public function __destruct(){
		parent::__destruct();
	}
}

?>