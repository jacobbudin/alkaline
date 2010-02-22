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
		
		$this->sql = ' WHERE photo_id = ' . implode(' OR photo_id = ', $photos);
		
		$query = $this->db->prepare('SELECT * FROM photos' . $this->sql . ';');
		$query->execute();
		$this->photos = $query->fetchAll();
		
		$this->photo_ids = array();
		foreach($this->photos as $photo){
			$this->photo_ids[] = $photo['photo_id'];
		}
		
		$this->photo_count = count($this->photos);
	}
	
	public function __destruct(){
		parent::__destruct();
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
	
}

?>