<?php

class Find extends Alkaline{
	private $memory;
	public $photo_ids;
	public $photo_ids_after;
	public $photo_ids_before;
	public $photo_count;
	public $photo_count_result;
	public $photo_offset_length;
	public $page;
	public $page_begin;
	public $page_count;
	public $page_first;
	public $page_limit;
	public $page_next;
	public $page_previous;
	public $piles;
	public $tags;
	public $with;
	protected $sql;
	protected $sql_conds;
	protected $sql_limit;
	protected $sql_sorts;
	protected $sql_from;
	protected $sql_tables;
	protected $sql_join;
	protected $sql_join_type;
	protected $sql_join_tables;
	protected $sql_join_on;
	protected $sql_group_by;
	protected $sql_having;
	protected $sql_having_fields;
	protected $sql_order_by;
	protected $sql_where;
	
	public function __construct(){
		parent::__construct();
		
		// Store data to object
		$this->memory = array();
		$this->photo_ids = array();
		$this->page = 1;
		$this->page_limit = LIMIT;
		$this->sql = 'SELECT photos.photo_id';
		$this->sql_conds = array();
		$this->sql_limit = '';
		$this->sql_sorts = array();
		$this->sql_from = '';
		$this->sql_tables = array('photos');
		$this->sql_join = '';
		$this->sql_join_type = '';
		$this->sql_join_tables = array();
		$this->sql_join_on = array();
		$this->sql_group_by = ' GROUP BY photos.photo_id';
		$this->sql_having = '';
		$this->sql_injection = '';
		$this->sql_having_fields = array();
		$this->sql_order_by = '';
		$this->sql_where = '';
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	// SAVE TO MEMORY
	public function __call($method, $arguments){
		// Error checking
		if(substr($method, 0, 1) != '_'){
			return false;
		}
		
		// Determine real method
		$method = substr($method, 1);
		
		// Error checking
		if(!method_exists($this, $method)){
			return false;
		}
		if(@count($arguments) < 1){
			$arguments = array();
		}
		
		// Execute method
		if(call_user_method_array($method, $this, $arguments)){
			// Remove unsaveable methods
			$nosave_methods = array('page', 'privacy');
			
			if(in_array($method, $nosave_methods)){
				return;
			}
			
			// Prepare for memory
			foreach($arguments as &$arg){
				if(is_string($arg)){
					$arg = '\'' . addslashes($arg) . '\'';
				}
			}
		
			// Save to memory
			$this->memory[] = '$this->' . $method . '(' . @implode(', ', $arguments) . '); ';
		}
	}
	
	public function __toString(){
        return implode(', ', $this->photo_ids);
    }

	// FIND BY DATE TAKEN
	public function taken($begin=null, $end=null){
		// Error checking
		if(empty($begin) and empty($end)){ return false; }
		
		// Set begin date
		if(!empty($begin)){
			if(is_int($begin)){ $begin = strval($begin); }
			if(strlen($begin) == 4){ $begin .= '-01-01'; }
			$begin = date('Y-m-d', strtotime($begin));
			$this->sql_conds[] = 'photos.photo_taken >= "' . $begin . '"';
		}
		
		// Set end date
		if(!empty($end)){
			if(is_int($end)){ $end = strval($end); }
			if(strlen($end) == 4){ $end .= '-01-01'; }
			$end = date('Y-m-d', strtotime($end));
			$this->sql_conds[] = 'photos.photo_taken <= "' . $end . '"';
		}
		
		return true;
	}
	
	// FIND BY DATE UPLOADED
	public function uploaded($begin=null, $end=null){
		// Error checking
		if(empty($begin) and empty($end)){ return false; }
		
		// Set begin date
		if(!empty($begin)){
			if(is_int($begin)){ $begin = strval($begin); }
			if(strlen($begin) == 4){ $begin .= '-01-01'; }
			$begin = date('Y-m-d', strtotime($begin));
			$this->sql_conds[] = 'photos.photo_uploaded >= "' . $begin . '"';
		}
		
		// Set end date
		if(!empty($end)){
			if(is_int($end)){ $end = strval($end); }
			if(strlen($end) == 4){ $end .= '-01-01'; }
			$end = date('Y-m-d', strtotime($end));
			$this->sql_conds[] = 'photos.photo_uploaded <= "' . $end . '"';
		}
		
		return true;
	}
	
	// FIND BY VIEWS
	public function views($min=null, $max=null){
		// Error checking
		if(empty($max) and empty($min)){ return false; }
		
		$min = intval($min);
		$max = intval($max);
		
		// Set maximum views
		if(!empty($max)){
			$this->sql_conds[] = 'photos.photo_views <= ' . $max;
		}
		
		// Set minimum views
		if(!empty($min)){
			$this->sql_conds[] = 'photos.photo_views >= ' . $min;
		}
		
		return true;
	}
	
	// FIND BY TAGS
	public function tags($tags=null){
		// Error checking
		if(empty($tags)){ return false; }
		
		if(!preg_match('/(NOT|OR|AND)/', $tags)){
			$pieces = array($tags, 'AND');
		}
		else{
			$pieces = preg_split('/(NOT|OR|AND)/', $tags, null, PREG_SPLIT_DELIM_CAPTURE);
		}
		$pieces = array_map('trim', $pieces);

		$any = array();
		$any_count = count(array_keys($pieces, 'OR'));
		$all = array();
		$not = array();

		for($i = 0; $i < count($pieces); ++$i){
			if((@$pieces[$i - 1] == 'NOT') and !in_array($pieces[$i], $any) and !in_array($pieces[$i], $all) and !in_array($pieces[$i], $not) and !empty($pieces[$i])){
				$not[] = $pieces[$i];
			}
			if(((@$pieces[$i + 1] == 'OR') or (@$pieces[$i - 1] == 'OR')) and !in_array($pieces[$i], $any) and !in_array($pieces[$i], $any) and !in_array($pieces[$i], $all) and !in_array($pieces[$i], $not) and !empty($pieces[$i])){
				$any[] = $pieces[$i];
			}
			if(((@$pieces[$i + 1] == 'AND') or (@$pieces[$i - 1] == 'AND') or (@$pieces[$i + 1] == 'NOT')) and !in_array($pieces[$i], $any) and !in_array($pieces[$i], $all) and !in_array($pieces[$i], $not) and !empty($pieces[$i])){
				$all[] = $pieces[$i];
			}
		}
		
		$this->anyTags($any, $any_count);
		$this->allTags($all);
		$this->notTags($not);
		
		return true;
	}
	
	protected function anyTags($tags=null, $count=1){
		// Error checking
		if(empty($tags)){ return false; }
		
		parent::convertToArray($tags);
		
		// Find tags in database
		if(is_int($tags[0])){
			parent::convertToIntegerArray($tags);
			$query = $this->db->prepare('SELECT tags.tag_id FROM tags WHERE tags.tag_id = ' . implode(' OR tags.tag_id = ', $tags) . ';');
		}
		else{
			$query = $this->db->prepare('SELECT tags.tag_id FROM tags WHERE tags.tag_name = "' . implode('" OR tags.tag_name = "', $tags) . '";');
		}
		
		$query->execute();
		$this->tags = $query->fetchAll();
		
		$tag_ids = array();	
		foreach($this->tags as $tag){
			$tag_ids[] = $tag['tag_id'];
		}
		
		// Join tables
		$this->sql_join_on[] = 'photos.photo_id = links.photo_id';
		$this->sql_join_tables[] = 'links';
		$this->sql_join_type = 'INNER JOIN';
		
		$this->sql_having_fields[] = 'COUNT(*) >= ' . intval($count);
		
		// Set tags to find
		$this->sql_conds[] = '(links.tag_id = ' . implode(' OR links.tag_id = ', $tag_ids) . ')';
		
		return true;
	}
	
	protected function allTags($tags=null){
		// Error checking
		if(empty($tags)){ return false; }
		
		$tag_count = count($tags);
		
		parent::convertToArray($tags);
		
		// Find photos with these tags in database
		if(is_int($tags[0])){
			parent::convertToIntegerArray($tags);
			$query = $this->db->prepare('SELECT photos.photo_id FROM photos, links WHERE photos.photo_id = links.photo_id AND (links.tag_id = ' . implode(' OR links.tag_id = ', $tags) . ');');
		}
		else{
			$query = $this->db->prepare('SELECT photos.photo_id FROM photos, links, tags WHERE photos.photo_id = links.photo_id AND links.tag_id = tags.tag_id AND (tags.tag_name = "' . implode('" OR tags.tag_name = "', $tags) . '");');
		}
		$query->execute();
		$this->photos = $query->fetchAll();
		
		// Compile photo IDs
		$include_photo_ids = array();	
		foreach($this->photos as $photo){
			if(array_key_exists($photo['photo_id'], $include_photo_ids)){
				$include_photo_ids[$photo['photo_id']]++;
			}
			else{
				$include_photo_ids[$photo['photo_id']] = 1;
			}
		}
		foreach($include_photo_ids as $photo_id => $count){
			if($count < $tag_count){
				unset($include_photo_ids[$photo_id]);
			}
		}
		$include_photo_ids = array_keys($include_photo_ids);
		
		// Set fields to search
		$this->sql_conds[] = 'photos.photo_id IN (' . implode(', ', $include_photo_ids) . ')';
		
		return true;
	}
	
	protected function notTags($tags=null){
		// Error checking
		if(empty($tags)){ return false; }
		
		parent::convertToArray($tags);
		
		// Find photos with these tags in database
		if(is_int($tags[0])){
			parent::convertToIntegerArray($tags);
			$query = $this->db->prepare('SELECT photos.photo_id FROM photos, links WHERE photos.photo_id = links.photo_id AND (links.tag_id = ' . implode(' OR links.tag_id = ', $tags) . ');');
		}
		else{
			$query = $this->db->prepare('SELECT photos.photo_id FROM photos, links, tags WHERE photos.photo_id = links.photo_id AND links.tag_id = tags.tag_id AND (tags.tag_name = "' . implode('" OR tags.tag_name = "', $tags) . '");');
		}
		$query->execute();
		$this->photos = $query->fetchAll();
		
		// Compile photo IDs
		$exclude_photo_ids = array();	
		foreach($this->photos as $photo){
			$exclude_photo_ids[] = $photo['photo_id'];
		}
		$exclude_photo_ids = array_unique($exclude_photo_ids);
		
		// Set fields to search
		$this->sql_conds[] = 'photos.photo_id NOT IN (' . implode(', ', $exclude_photo_ids) . ')';
		
		return true;
	}
	
	// FIND BY PILE
	public function pile($pile=null){
		// Error checking
		if(empty($pile)){ return false; }
		
		// Determine input type
		if(is_string($pile)){
			$query = $this->db->prepare('SELECT pile_call FROM piles WHERE LOWER(pile_title) LIKE "' . strtolower($pile) . '" LIMIT 0, 1;');
		}
		elseif(is_int($pile)){
			$query = $this->db->prepare('SELECT pile_call FROM piles WHERE pile_id = ' . $pile . ' LIMIT 0, 1;');
		}
		else{
			return false;
		}
		
		$query->execute();
		$piles = $query->fetchAll();
		
		// Call stored functions
		if(!eval($piles[0]['pile_call'])){
			return false;
		}
		
		return true;
	}
	
	// FIND BY MEMORY
	public function memory(){
		if(!eval($this->recentMemory())){
			return false;
		}
		
		return true;
	}
	
	// FIND BY RIGHTS SET
	public function rights($right=null){
		// Error checking
		if(empty($right)){ return false; }
		
		// Determine input type
		if(is_string($right)){
			$query = $this->db->prepare('SELECT right_id FROM rights WHERE LOWER(right_title) LIKE "' . strtolower($right) . '" LIMIT 0, 1;');
		}
		elseif(is_int($right)){
			$query = $this->db->prepare('SELECT right_id FROM rights WHERE right_id = ' . $right . ' LIMIT 0, 1;');
		}
		else{
			return false;
		}
		
		$query->execute();
		$rights = $query->fetchAll();
		
		if(@count($rights) != 1){
			return false;
		}
		
		$right = $rights[0];
		
		$this->sql_conds[] = 'photos.right_id = ' . $right['right_id'];
		
		return true;
	}
	
	// FIND BY USER
	public function user($users=null){
		// Error checking
		if(empty($users)){ return false; }
		
		$users = parent::convertToIntegerArray($users);
		
		$users_sql = array();
		
		foreach($users as $user){
			$users_sql[] = 'photos.user_id = ' . $user;
		}
		
		$this->sql_conds[] = '(' . implode(' OR ', $users_sql) . ')';
		
		return true;
	}
	
	// FIND BY SEARCH
	public function search($search=null){
		// Error checking
		if(empty($search)){ return false; }
		
		// Prepare input
		$search_lower = strtolower($search);
		
		// Set fields to search
		$sql = '(';
		$sql .= 'LOWER(photos.photo_title) LIKE "%' . $search_lower . '%" OR ';
		$sql .= 'LOWER(photos.photo_description) LIKE "%' . $search_lower . '%"';
		$sql .= ')';
		$this->sql_conds[] = $sql;
		
		return true;
	}
	
	// FIND BY PRIVACY LEVEL
	public function privacy($privacy=null, $all=false){
		// Error checking
		if(empty($privacy)){ return false; }
		
		// Convert strings
		if(is_string($privacy)){
			$levels = array('public' => 1, 'protected' => 2, 'private' => 3);
			if(array_key_exists($privacy, $levels)){
				$privacy = $levels[$privacy];
			}
			else{
				return false;
			}
		}
		
		// Set fields to search
		if($all == true){
			$this->sql_conds[] = 'photos.photo_privacy <= ' . $privacy;
		}
		else{
			$this->sql_conds[] = 'photos.photo_privacy = ' . $privacy;
		}
		
		return true;
	}
	
	// FIND BY COLOR (HSL)
	public function hsl($h, $s, $l){
		// Error checking
		if(empty($h) and empty($s) and empty($l)){ return false; }
		
		$h_tolerance = 40;
		$s_tolerance = 50;
		$l_tolerance = 75;
		
		// H - Hue
		if(!empty($h)){
			$h = intval($h);
			if(($h < $h_tolerance) or ($h > (360 - $h_tolerance))){
				if($h < $h_tolerance){
					$h_top = $h;
					$h_bottom = 360 + ($h - $h_tolerance);
				}
				if($h > (360 - $h_tolerance)){
					$h_top = ($h + $h_tolerance) - 360;
					$h_bottom = $h;
				}
				$this->sql_conds[] = '(photos.photo_color_h <= ' . $h_top . ' OR photos.photo_color_h >= ' . $h_bottom . ')';
			}
			else{
				$this->sql_conds[] = 'photos.photo_color_h >= ' . ($h - $h_tolerance);
				$this->sql_conds[] = 'photos.photo_color_h <= ' . ($h + $h_tolerance);
			}
		}
		
		// S - Saturation
		if(!empty($s)){
			$s = intval($s);
			$this->sql_conds[] = 'photos.photo_color_s >= ' . ($s - $s_tolerance);
			$this->sql_conds[] = 'photos.photo_color_s <= ' . ($s + $s_tolerance);
		}
		
		// L - Lightness
		if(!empty($l)){
			$l = intval($l);
			$this->sql_conds[] = 'photos.photo_color_l >= ' . ($l - $l_tolerance);
			$this->sql_conds[] = 'photos.photo_color_l <= ' . ($l + $l_tolerance);
		}
		
		return true;
	}
	
	// FIND BY PUBLISHED
	public function published($published=true){
		$now = date('Y-m-d H:i:s');
		
		if($published == true){
			$this->sql_conds[] = 'photos.photo_published < "' . $now . '"';
		}
		if($published == false){
			$this->sql_conds[] = '(photos.photo_published > "' . $now . '" OR photo_published IS NULL OR photo_published = "")';
		}
		
		return true;
	}
	
	// FIND BY IMAGE RATIO
	public function ratio($min=null, $max=null){
		if(empty($min) and empty($max)){
			return false;
		}
		
		if(!empty($min)){
			$min = intval($min);
			$this->sql_conds[] = '(photos.photo_width / photos.photo_height) <= ' . $min;
		}
		if(!empty($max)){
			$max = intval($max);
			$this->sql_conds[] = '(photos.photo_width / photos.photo_height) >= ' . $min;
		}
		
		return true;
	}
	
	// SMART SEARCH
	protected function smart($kind){
		if(empty($kind)){
			return false;
		}
		
		switch($kind){
			case 'untagged':
				// Join tables
				$this->sql_join_on[] = 'photos.photo_id = links.photo_id';
				$this->sql_join_tables[] = 'links';
				$this->sql_join_type = 'LEFT OUTER JOIN';
				
				// Set tags to find
				$this->sql_conds[] = 'links.link_id IS NULL';
				break;
			case 'tags':
				$this->_allTags(@intval($_GET['id']));
				break;
			case 'piles':
				$this->_pile(@intval($_GET['id']));
				break;
			case 'rights':
				$this->_rights(@intval($_GET['id']));
				break;
			default:
				return false;
				break;
		}
		
		return true;
	}
	
	// FIND PAGE BY PHOTO ID
	public function with($photo_id){
		// Error checking
		if(empty($photo_id)){ return false; }
		if(!$photo_id = intval($photo_id)){ return false; }
		
		$this->with = $photo_id;
		
		return true;
	}
	
	// PAGINATE RESULTS
	public function page($page, $limit=null, $first=null){
		// Error checking
		if(empty($page)){ return false; }
		if($page == 0){ return false; }
		if(empty($limit)){ $limit = LIMIT; }
		if(empty($first)){ $first = $limit; }
		
		// Store data to object
		$this->page = intval($page);
		$this->page_limit = intval($limit);
		$this->page_first = intval($first);
		
		// Set SQL limit
		if($page == 1){ $limit = $first; }
		$this->page_begin = (($page - 1) * $limit) - $limit + $first;
		$this->sql_limit = ' LIMIT ' . $this->page_begin . ', ' . $limit;
		
		return true;
	}
	
	// OFFSET PHOTOS
	public function offset($length){
		// Error checking
		if(!($length = intval($length))){ return false; }
		
		$this->photo_offset_length = $length;
	}
	
	// SORT RESULTS
	public function sort($column, $sort='ASC'){
		// Error checking
		if(empty($column)){ return false; }
		
		$column = strtolower($column);
		$sort = strtoupper($sort);
		
		// More error checking
		if(($sort != 'ASC') and ($sort != 'DESC')){
			return false;
		}
		
		// Set column, sort
		$this->sql_sorts[] = $column . ' ' . $sort;
		
		return true;
	}
	
	// EXECUTE QUERY
	public function exec(){
		// Prepare SQL
		$this->sql_from = ' FROM ' . implode(', ', $this->sql_tables);

		if(count($this->sql_conds) > 0){
			$this->sql_where = ' WHERE ' . implode(' AND ', $this->sql_conds);
		}
		if(count($this->sql_sorts) > 0){
			$this->sql_order_by = ' ORDER BY ' . implode(', ', $this->sql_sorts);
		}
		if((count($this->sql_join_on) > 0) and (count($this->sql_join_tables) > 0) and (!empty($this->sql_join_type))){
			$this->sql_join = ' ' . $this->sql_join_type . ' ' . implode(', ', $this->sql_join_tables) . ' ON ' . implode(', ', $this->sql_join_on);
		}
		if(count($this->sql_having_fields) > 0){
			$this->sql_having = ' HAVING ' . implode(', ', $this->sql_having_fields);
		}

		// Prepare query without limit
		$this->sql .= $this->sql_from . $this->sql_join . $this->sql_where . $this->sql_group_by . $this->sql_having . $this->sql_order_by;
		
		// Execute query without limit
		$query = $this->db->prepare($this->sql);
		$query->execute();
		$photos = $query->fetchAll();
		
		// Grab photos.photo_ids of results
		$photo_ids = array();
		foreach($photos as $photo){
			$photo_ids[] = intval($photo['photo_id']);
		}
		
		// Determine number of photos
		$this->photo_count = count($photos);
		
		// Determine where "with" photo id is placed in pages
		if(!empty($this->with)){
			$key = array_search($this->with, $photo_ids);
			if($key === false){
				return false;
			}
			
			if(empty($this->page_limit)){ $this->page_limit = LIMIT; }
			if(empty($this->page_first)){ $this->page_first = $this->page_limit; }
			
			if($key < $this->page_first){
				$page = 1;
			}
			else{
				$page = intval(ceil((($key + 1) - $this->page_first) / $this->page_limit) + 1);
			}
			
			$this->page($page, $this->page_limit, $this->page_first);
		}
		
		// Determine pagination
		if(!empty($this->page)){
			$this->page_count = ceil(($this->photo_count - $this->page_first) / $this->page_limit) + 1;
			if($this->page < $this->page_count){
				$this->page_next = $this->page + 1;
			}
			if($this->page > 1){
				$this->page_previous = $this->page - 1;
			}
		}
		
		// Add order, limit
		$this->sql .= $this->sql_limit;
		
		// Execute query with order, limit
		$query = $this->db->prepare($this->sql);
		$query->execute();
		$photos = $query->fetchAll();
		
		// Grab photos.photo_ids of results
		$this->photo_ids = array();
		foreach($photos as $photo){
			$this->photo_ids[] = intval($photo['photo_id']);
		}
		
		// Count photos
		$this->photo_count_result = count($this->photo_ids);
		
		// Determine offset photos
		if(!empty($this->page_limit)){
			if(!empty($this->photo_offset_length)){
				$offset = $this->page_begin - $this->photo_offset_length;
				$this->photo_ids_before = array_slice($photo_ids, $offset, $this->photo_offset_length);
				
				if($this->page == 1){
					$offset = $this->page_begin + $this->page_first;
				}
				else{
					$offset = $this->page_begin + $this->page_limit;
				}
				$this->photo_ids_after = array_slice($photo_ids, $offset, $this->photo_offset_length);
			}
			else{
				$this->photo_ids_before = array_slice($photo_ids, 0, $this->page_begin);
				
				if($this->page == 1){
					$offset = $this->page_begin + $this->page_first;
				}
				else{
					$offset = $this->page_begin + $this->page_limit;
				}
				$this->photo_ids_after = array_slice($photo_ids, $offset);
			}
			
		}
		
		// Return photos.photo_ids
		return $this->photo_ids;
	}
	
	// SEARCH MEMORY
	// Retrieve memory
	public function getMemory(){
		if(count($this->memory) > 0){
			return false;
		}
		
		return implode(' ', $this->memory);
	}
	
	
	// Save memory
	public function saveMemory(){
		if(count($this->memory) < 1){
			return false;
		}
		
		$_SESSION['recent_memory'] = $this->memory;
		
		return true;
	}
	
	// Most recent saved memory
	public function recentMemory(){
		if(empty($_SESSION['recent_memory'])){
			return false;
		}
		
		return implode(' ', $_SESSION['recent_memory']);
	}
	
	// Clear memory
	public function clearMemory(){
		unset($_SESSION['recent_memory']);
		
		return true;
	}
}

?>