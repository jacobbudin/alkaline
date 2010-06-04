<?php

class Find extends Alkaline{
	public $photo_ids;
	public $photo_count;
	public $photo_count_result;
	public $page;
	public $page_count;
	public $page_limit;
	public $page_next;
	public $page_previous;
	public $piles;
	public $tags;
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
	protected $sql_injection;
	protected $sql_having_fields;
	protected $sql_order_by;
	protected $sql_where;
	
	public function __construct(){
		parent::__construct();
		
		// Store data to object
		$this->photo_ids = array();
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
	
	public function __toString(){
        return implode(', ', $this->photo_ids);
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
		
		// Set maximum views
		if(!empty($max) and is_int($max)){
			$this->sql_conds[] = 'photos.photo_views <= ' . $max;
		}
		
		// Set minimum views
		if(!empty($min) and is_int($min)){
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
			if(@$pieces[$i - 1] == 'NOT'){
				$not[] = $pieces[$i];
			}
			if(((@$pieces[$i + 1] == 'OR') or (@$pieces[$i - 1] == 'OR')) and !in_array($pieces[$i], $any)){
				$any[] = $pieces[$i];
			}
			if(((@$pieces[$i + 1] == 'AND') or (@$pieces[$i - 1] == 'AND')) and !in_array($pieces[$i], $any)){
				$all[] = $pieces[$i];
			}
		}
		
		self::anyTags($any, $any_count);
		self::allTags($all);
		self::notTags($not);
	}
	
	public function anyTags($tags=null, $count=1){
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
		
		$this->sql_having_fields[] = 'COUNT(*) = ' . intval($count);
		
		// Set tags to find
		$this->sql_conds[] = '(links.tag_id = ' . implode(' OR links.tag_id = ', $tag_ids) . ')';
		
		return true;
	}
	
	public function allTags($tags=null){
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
		$include_photo_ids = array();	
		foreach($this->photos as $photo){
			$include_photo_ids[] = $photo['photo_id'];
		}
		
		// Set fields to search
		$this->sql_conds[] = 'photos.photo_id IN (' . implode(', ', $include_photo_ids) . ')';
		
		return true;
	}
	
	public function notTags($tags=null){
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
		
		// Set fields to search
		$this->sql_conds[] = 'photos.photo_id NOT IN (' . implode(', ', $exclude_photo_ids) . ')';
		
		return true;
	}
	
	// FIND BY PILE
	public function pile($pile=null){
		// Error checking
		if(empty($pile)){ return false; }
		
		$query = $this->db->prepare('SELECT pile_sql FROM piles WHERE LOWER(pile_title) LIKE "' . strtolower($pile) . '" LIMIT 0,1;');
		$query->execute();
		$piles = $query->fetchAll();
		
		$this->sql_injection = $piles[0]['pile_sql'];
		
		return true;
	}
	
	// FIND BY SEARCH
	public function search($search=null){
		// Error checking
		if(empty($search)){ return false; }
		
		$search_lower = strtolower($search);
		
		// Set fields to search
		$sql = '(';
		$sql .= 'LOWER(photos.photo_title) LIKE "%' . $search_lower . '%" OR ';
		$sql .= 'LOWER(photos.photo_description) LIKE "%' . $search_lower . '%"';
		$sql .= ')';
		$this->sql_conds[] = $sql;
		
		return true;
	}
	
	// FIND BY PUBLISHED
	public function published($published=true){
		$now = date('Y-m-d H:i:s');
		if($published == true){
			$this->sql_conds[] = 'photos.photo_published < "' . $now . '"';
		}
		if($published == false){
			$this->sql_conds[] = '(photos.photo_published > "' . $now . '" OR photo_published = null)';
		}
	}
	
	// PAGINATE RESULTS
	public function page($page, $limit=LIMIT){
		// Error checking
		if(empty($page)){ return false; }
		if($page == 0){ return false; }
		
		// Store data to object
		$this->page = intval($page);
		$this->page_limit = intval($limit);
		
		// Set SQL limit
		$begin = ($page * $limit) - $limit;
		$this->sql_limit = ' LIMIT ' . $begin . ', ' . $limit;
		
		return true;
	}
	
	public function sort($column, $sort='ASC'){
		// Error checking
		if(empty($column)){ return false; }
		
		$column = strtolower($column);
		$sort = strtoupper($sort);
		if(($sort == 'ASC') or ($sort == 'DESC')){
			$this->sql_sorts[] = $column . ' ' . $sort;
			return true;
		}
		else{
			return false;
		}
	}
	
	// EXECUTE QUERY
	public function exec(){
		// Inject stored SQL
		if(!empty($this->sql_injection)){
			$this->sql = $this->sql_injection;
		}
		// Prepare SQL
		else{
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
			$this->sql .= $this->sql_from . $this->sql_join . $this->sql_where . $this->sql_group_by . $this->sql_having;
		}
		
		// Execute query without limit
		$query = $this->db->prepare($this->sql);
		$query->execute();
		$photos = $query->fetchAll();
		
		// Determine number of photos
		$this->photo_count = count($photos);
		
		// Add order, limit
		$this->sql .= $this->sql_order_by . $this->sql_limit;
		
		// Execute query with order, limit
		$query = $this->db->prepare($this->sql);
		$query->execute();
		$photos = $query->fetchAll();
		
		// Grab photos.photo_ids of results
		$this->photo_ids = array();
		foreach($photos as $photo){
			$this->photo_ids[] = $photo['photo_id'];
		}
		
		// Count photos
		$this->photo_count_result = count($this->photo_ids);
		
		// Determine pagination
		if(!empty($this->page)){
			$this->page_count = ceil($this->photo_count / ($this->page * $this->page_limit));
			if($this->page < $this->page_count){
				$this->page_next = $this->page + 1;
			}
			if($this->page > 1){
				$this->page_previous = $this->page - 1;
			}
		}
		
		// Return photos.photo_ids
		return $this->photo_ids;
	}
}

?>