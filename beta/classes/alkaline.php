<?php

class Alkaline{
	protected $db;
	protected $photos_cols;
	
	public function __construct(){
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

		if(session_id() == ''){ session_start(); }
		
		$this->db = new PDO(DB_DSN, DB_USER, DB_PASS, array(PDO::ATTR_PERSISTENT => true));
	}
	
	public function __destruct(){
		$this->db = null;
	}
	
	// Seek EXIF data for images
	public function seekExif(){
		$query = $this->db->prepare('SELECT * FROM exifs' . $this->sql . ';');
		$query->execute();
		$exifs = $query->fetchAll();
		
		foreach($exifs as $exif){
			$photo_id = intval($exif['photo_id']);
			$key = array_search($photo_id, $this->photo_ids);
			if($photo_id = $this->photo_ids[$key]){
				$this->photos[$key]['exif'][$exif['exif_key']][$exif['exif_name']] = unserialize($exif['exif_value']);
			}
		}
	}
	
	// Seek compatible photos in a directory
	public function seekPhotos($dir){
		// Open shoebox directory
		if($handle = opendir($dir)){
			$photos = array();
			while($filename = readdir($handle)){
				// Find files with proper extensions
				if(preg_match('([a-zA-Z0-9]+\.(' . IMG_EXT . '){1,1})', $filename)){
					$photos[] = $dir . $filename;
				}
		    }
		    closedir($handle);
			return $photos;
		}
		else{ return false; }
	}
	
	// Find and store column fields into variables
	private function seekCols(){
		$query = $this->db->prepare('SELECT * FROM photos LIMIT 0,1;');
		$query->execute();
		$this->photos = $query->fetchAll();
		
		$this->photos_cols = array();
		foreach($this->photos as $photo){
			foreach($photo as $key => $value){
				$this->photos_cols[] = $key;
			}
		}
	}
}

?>