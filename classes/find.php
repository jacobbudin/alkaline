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

class Find extends Alkaline{
	private $call;
	public $image_ids;
	public $image_ids_after;
	public $image_ids_before;
	public $image_count;
	public $image_count_result;
	public $image_offset_length;
	public $image_order;
	public $image_first;
	public $image_first_reverse;
	public $image_last;
	public $image_last_reverse;
	public $page;
	public $page_begin;
	public $page_count;
	public $page_limit;
	public $page_limit_current;
	public $page_limit_first;
	public $page_next;
	public $page_next_uri;
	public $page_previous;
	public $page_previous_uri;
	public $sets;
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
	protected $sql_params;
	protected $sql_order_by;
	protected $sql_where;
	
	/**
	 * Initiates Find class
	 *
	 * @param string|array|int $image_ids Limit results to select image IDs
	 * @param string $auto_guest Set guest access restrictions
	 * @param string $process_request Automatically employ the $_REQUEST array to issue methods (for searches)
	 */
	public function __construct($image_ids=null, $auto_guest=true, $process_request=true){
		parent::__construct();
		
		// Store data to object
		$this->call = array();
		$this->image_ids = array();
		$this->page = 1;
		$this->page_limit = LIMIT;
		$this->page_limit_first = LIMIT;
		$this->sql = 'SELECT images.image_id AS image_id';
		$this->sql_conds = array();
		$this->sql_limit = '';
		$this->sql_sorts = array();
		$this->sql_from = '';
		$this->sql_tables = array('images');
		$this->sql_join = '';
		$this->sql_join_type = '';
		$this->sql_join_tables = array();
		$this->sql_join_on = array();
		$this->sql_group_by = ' GROUP BY images.image_id';
		$this->sql_having = '';
		$this->sql_injection = '';
		$this->sql_having_fields = array();
		$this->sql_params = array();
		$this->sql_order_by = '';
		$this->sql_where = '';
		
		// Optional "starter image set"
		if(!empty($image_ids)){
			$image_ids = parent::convertToIntegerArray($image_ids);
			$this->sql_conds[] = 'images.image_id IN (' . implode(', ', $image_ids) . ')';
		}
		
		if($auto_guest == true){
			// Guest access
			if(isset($_SESSION['alkaline']['guest'])){
				$this->privacy(2);
				if(!empty($_SESSION['alkaline']['guest']['guest_sets'])){
					$this->sets(intval($_SESSION['alkaline']['guest']['guest_sets']));
				}
			}
		}
		
		if($process_request == true){
			// Process browser requests
			$_REQUEST = array_map('strip_tags', $_REQUEST);

			// Smart search
			if(!empty($_REQUEST['act'])){
				$this->_smart($_REQUEST['act']);
			}

			// Title and description
			if(!empty($_REQUEST['q'])){
				$this->_search($_REQUEST['q']);
			}

			// Tags
			if(!empty($_REQUEST['tags'])){
				$this->_tags($_REQUEST['tags']);
			}

			// Rights set
			if(!empty($_REQUEST['rights'])){
				$this->_rights(intval($_REQUEST['rights']));
			}

			// Date taken
			if(!empty($_REQUEST['taken_begin']) or !empty($_REQUEST['taken_end'])){
				$this->_taken($_REQUEST['taken_begin'], $_REQUEST['taken_end']);
			}

			// Date uploaded
			if(!empty($_REQUEST['uploaded_begin']) or !empty($_REQUEST['uploaded_end'])){
				$this->_uploaded($_REQUEST['uploaded_begin'], $_REQUEST['uploaded_end']);
			}

			// Location
			if(!empty($_REQUEST['location'])){
				$this->_location($_REQUEST['location'], $_REQUEST['location_proximity']);
			}

			// Primary color
			if(!empty($_REQUEST['color'])){
				switch($_REQUEST['color']){
					case 'blue':
						$this->_hsl(170, 235, 1, 100, 1, 100);
						break;
					case 'red':
						$this->_hsl(345, 10, 1, 100, 1, 100);
						break;
					case 'yellow':
						$this->_hsl(50, 75, 1, 100, 1, 100);
						break;
					case 'green':
						$this->_hsl(75, 170, 1, 100, 1, 100);
						break;
					case 'purple':
						$this->_hsl(235, 300, 1, 100, 1, 100);
						break;
					case 'orange':
						$this->_hsl(10, 50, 1, 100, 1, 100);
						break;
					case 'brown':
						$this->_hsl(null, null, null, null, 1, 20);
						break;
					case 'pink':
						$this->_hsl(300, 345, 1, 100, 1, 100);
						break;
					default:
						break;
				}
			}

			// Views
			if(!empty($_REQUEST['views'])){
				switch($_REQUEST['views_operator']){
					case 'greater':
						$this->_views($_REQUEST['views'], null);
						break;
					case 'less':
						$this->_views(null, $_REQUEST['views']);
						break;
					case 'equal':
						$this->_views($_REQUEST['views'], $_REQUEST['views']);
						break;
				}
			}

			// Orientation
			if(!empty($_REQUEST['orientation'])){
				switch($_REQUEST['orientation']){
					case 'portrait':
						$this->_ratio(1, null, null);
						break;
					case 'landscape':
						$this->_ratio(null, 1, null);
						break;
					case 'square':
						$this->_ratio(null, null, 1);
						break;
				}
			}

			// Privacy
			if(!empty($_REQUEST['privacy'])){
				switch($_REQUEST['privacy']){
					case 'public':
						$this->_privacy(1);
						break;
					case 'protected':
						$this->_privacy(2);
						break;
					case 'private':
						$this->_privacy(3);
						break;
				}
			}

			// Published
			if(!empty($_REQUEST['published'])){
				switch($_REQUEST['published']){
					case 'published':
						$this->_published(true);
						break;
					case 'unpublished':
						$this->_published(false);
						break;
				}
			}

			// Sort
			if(!empty($_REQUEST['sort'])){
				switch($_REQUEST['sort']){
					case 'taken':
						$this->_sort('images.image_taken', $_REQUEST['sort_direction']);
						$this->_notnull('images.image_taken');
						break;
					case 'published':
						$this->_sort('images.image_published', $_REQUEST['sort_direction']);
						$this->_notnull('images.image_published');
						break;
					case 'uploaded':
						$this->_sort('images.image_uploaded', $_REQUEST['sort_direction']);
						break;
					case 'updated':
						$this->_sort('images.image_updated', $_REQUEST['sort_direction']);
						$this->_notnull('images.image_updated');
						break;
					case 'title':
						$this->_sort('images.image_title', $_REQUEST['sort_direction']);
						$this->_notnull('images.image_title');
						break;
					case 'views':
						$this->_sort('images.image_views', $_REQUEST['sort_direction']);
						break;
					default:
						break;
				}
			}
		}
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	/**
	 * Save methods (by appending an underscore to the method name) to memory for saving methods to sets, saved searches, etc.
	 *
	 * @param string $method Method name
	 * @param array $arguments Method arguments
	 * @return mixed
	 */
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
			$arguments = null;
		}
		
		// Execute method
		call_user_func_array(array($this, $method), $arguments);
		
		// Remove unsaveable methods
		$nosave_methods = array('page', 'privacy');
		
		if(in_array($method, $nosave_methods)){
			return;
		}
			
		// Save to memory
		$this->call[] = array($method => $arguments);
	}
	
	/**
	 * Translate $this->image_ids array to comma-separated string
	 *
	 * @return string Comma-separated image IDs
	 */
	public function __toString(){
        return implode(', ', $this->image_ids);
    }

	/**
	 * Perform Orbit hook
	 *
	 * @param Orbit $orbit 
	 * @return void
	 */
	public function hook($orbit=null){
		if(!is_object($orbit)){
			$orbit = new Orbit;
		}
		
		$this->image_ids = $orbit->hook('find', $this->image_ids, $this->image_ids);
	}

	/**
	 * Find by date taken
	 *
	 * @param string $begin Date begin
	 * @param string $end Date end
	 * @return bool True if successful
	 */
	public function taken($begin=null, $end=null){
		// Error checking
		if(empty($begin) and empty($end)){ return false; }
		
		// Set begin date
		if(!empty($begin)){
			if(is_int($begin)){ $begin = strval($begin); }
			if(strlen($begin) == 4){ $begin .= '-01-01'; }
			$begin = date('Y-m-d', strtotime($begin));
			$this->sql_conds[] = 'images.image_taken >= :image_taken_begin';
			$this->sql_params[':image_taken_begin'] = $begin . ' 00:00:00';
		}
		
		// Set end date
		if(!empty($end)){
			if(is_int($end)){ $end = strval($end); }
			if(strlen($end) == 4){ $end .= '-01-01'; }
			$end = date('Y-m-d', strtotime($end));
			$this->sql_conds[] = 'images.image_taken <= :image_taken_end';
			$this->sql_params[':image_taken_end'] = $end . ' 23:59:59"';
		}
		
		return true;
	}
	
	/**
	 * Find by date uploaded
	 *
	 * @param string $begin Date begin
	 * @param string $end Date end
	 * @return bool True if successful
	 */
	public function uploaded($begin=null, $end=null){
		// Error checking
		if(empty($begin) and empty($end)){ return false; }
		
		// Set begin date
		if(!empty($begin)){
			if(is_int($begin)){ $begin = strval($begin); }
			if(strlen($begin) == 4){ $begin .= '-01-01'; }
			$begin = date('Y-m-d', strtotime($begin));
			$this->sql_conds[] = 'images.image_uploaded >= :image_uploaded_begin';
			$this->sql_params[':image_uploaded_begin'] = $begin . ' 00:00:00';
		}
		
		// Set end date
		if(!empty($end)){
			if(is_int($end)){ $end = strval($end); }
			if(strlen($end) == 4){ $end .= '-01-01'; }
			$end = date('Y-m-d', strtotime($end));
			$this->sql_conds[] = 'images.image_uploaded <= :image_uploaded_end';
			$this->sql_params[':image_uploaded_end'] = $end . ' 23:59:59"';
		}
		
		return true;
	}
	
	/**
	 * Find by number of views
	 *
	 * @param string $min Minimum views
	 * @param string $max Maximum views
	 * @return bool True if successful
	 */
	public function views($min=null, $max=null){
		// Error checking
		if(empty($max) and empty($min)){ return false; }
		
		$min = intval($min);
		$max = intval($max);
		
		// Set maximum views
		if(!empty($max)){
			$this->sql_conds[] = 'images.image_views <= ' . $max;
		}
		
		// Set minimum views
		if(!empty($min)){
			$this->sql_conds[] = 'images.image_views >= ' . $min;
		}
		
		return true;
	}
	
	/**
	 * Find by tag search
	 *
	 * @param string $tags Tag search, can include boolean operators
	 * @return bool True if successful
	 */
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
		
		$any_count = count($any) - count(array_keys($pieces, 'OR'));
		
		$this->anyTags($any, $any_count);
		$this->allTags($all);
		$this->notTags($not);
		
		return true;
	}
	
	/**
	 * Find by string|int|array joined by OR
	 *
	 * @param string $tags Tags to search for
	 * @param string $count Minimum number of tags to find
	 * @return bool True if successful
	 */
	protected function anyTags($tags=null, $count=1){
		// Error checking
		if(empty($tags)){ return false; }
		
		parent::convertToArray($tags);
		
		// Find tags in database
		if(intval($tags[0])){
			parent::convertToIntegerArray($tags);
			$query = $this->prepare('SELECT tags.tag_id FROM tags WHERE tags.tag_id = ' . implode(' OR tags.tag_id = ', $tags) . ';');
			$query->execute();
		}
		else{
			$sql_params = array();
			$tag_count = count($tags);
			
			// Grab tag IDs
			for($j=0; $j<$tag_count; ++$j){
				$sql_params[':tag' . $j] = '%' . strtolower($tags[$j]) . '%';
			}
			
			$sql_param_keys = array_keys($sql_params);
			
			$query = $this->prepare('SELECT tags.tag_id FROM tags WHERE LOWER(tags.tag_name) LIKE ' . implode(' OR LOWER(tags.tag_name) LIKE ', $sql_param_keys) . ';');
			$query->execute($sql_params);
		}
		
		$this->tags = $query->fetchAll();
		
		$tag_ids = array();	
		foreach($this->tags as $tag){
			$tag_ids[] = $tag['tag_id'];
		}
		
		// Join tables
		$this->sql_join_on[] = 'images.image_id = links.image_id';
		$this->sql_join_tables[] = 'links';
		$this->sql_join_type = 'INNER JOIN';
		
		$this->sql_having_fields[] = 'COUNT(*) >= ' . intval($count);
		
		// Set tags to find
		$this->sql_conds[] = '(links.tag_id = ' . implode(' OR links.tag_id = ', $tag_ids) . ')';
		
		return true;
	}
	
	/**
	 * Find by tags joined by AND
	 *
	 * @param string|int|array $tags Tags to search for
	 * @return bool True if successful
	 */
	protected function allTags($tags=null){
		// Error checking
		if(empty($tags)){ return false; }
		
		$tag_count = count($tags);
		
		parent::convertToArray($tags);
		
		// Find images with these tags in database
		if(intval($tags[0])){
			parent::convertToIntegerArray($tags);
			$query = $this->prepare('SELECT images.image_id FROM images, links WHERE images.image_id = links.image_id AND (links.tag_id = ' . implode(' OR links.tag_id = ', $tags) . ');');
			$query->execute();
		}
		else{
			$sql_params = array();
			$tag_count = count($tags);
			
			// Grab tag IDs
			for($j=0; $j<$tag_count; ++$j){
				$sql_params[':tag' . $j] = '%' . strtolower($tags[$j]) . '%';
			}
			
			$sql_param_keys = array_keys($sql_params);
			
			$query = $this->prepare('SELECT images.image_id FROM images, links, tags WHERE images.image_id = links.image_id AND links.tag_id = tags.tag_id AND (LOWER(tags.tag_name) LIKE ' . implode(' OR LOWER(tags.tag_name) LIKE ', $sql_param_keys) . ');');
			$query->execute($sql_params);
		}
		
		$this->images = $query->fetchAll();
		
		// Comset image IDs
		$include_image_ids = array();	
		foreach($this->images as $image){
			if(array_key_exists($image['image_id'], $include_image_ids)){
				$include_image_ids[$image['image_id']]++;
			}
			else{
				$include_image_ids[$image['image_id']] = 1;
			}
		}
		foreach($include_image_ids as $image_id => $count){
			if($count < $tag_count){
				unset($include_image_ids[$image_id]);
			}
		}
		$include_image_ids = array_keys($include_image_ids);
		
		// Set fields to search
		if(count($include_image_ids) > 0){
			$this->sql_conds[] = 'images.image_id IN (' . implode(', ', $include_image_ids) . ')';
		}
		else{
			$this->sql_conds[] = 'images.image_id IN (NULL)';
		}
		
		return true;
	}
	
	/**
	 * Find by tags joined by NOT
	 *
	 * @param string|int|array $tags Tags to search for
	 * @return bool True if successful
	 */
	protected function notTags($tags=null){
		// Error checking
		if(empty($tags)){ return false; }
		
		parent::convertToArray($tags);
		
		// Find images with these tags in database
		if(intval($tags[0])){
			parent::convertToIntegerArray($tags);
			$query = $this->prepare('SELECT images.image_id FROM images, links WHERE images.image_id = links.image_id AND (links.tag_id = ' . implode(' OR links.tag_id = ', $tags) . ');');
			$query->execute();
		}
		else{
			$sql_params = array();
			$tag_count = count($tags);
			
			// Grab tag IDs
			for($j=0; $j<$tag_count; ++$j){
				$sql_params[':tag' . $j] = '%' . strtolower($tags[$j]) . '%';
			}
			
			$sql_param_keys = array_keys($sql_params);
			
			$query = $this->prepare('SELECT images.image_id FROM images, links, tags WHERE images.image_id = links.image_id AND links.tag_id = tags.tag_id AND (LOWER(tags.tag_name) LIKE ' . implode(' OR LOWER(tags.tag_name) LIKE ', $sql_param_keys) . ');');
			$query->execute($sql_params);
		}
		$this->images = $query->fetchAll();
		
		// Comset image IDs
		$exclude_image_ids = array();	
		foreach($this->images as $image){
			$exclude_image_ids[] = $image['image_id'];
		}
		$exclude_image_ids = array_unique($exclude_image_ids);
		
		if(count($exclude_image_ids) > 0){
			$this->sql_conds[] = 'images.image_id NOT IN (' . implode(', ', $exclude_image_ids) . ')';
		}
		
		return true;
	}
	
	/**
	 * Find by set
	 *
	 * @param int|string $set Set ID or set title
	 * @return void
	 */
	public function sets($set=null){
		// Error checking
		if(empty($set)){ return false; }
		if(intval($set)){ $set = intval($set); }
		
		// Determine input type
		if(is_string($set)){
			$query = $this->prepare('SELECT set_id, set_call, set_type, set_images, set_image_count FROM sets WHERE LOWER(set_title) LIKE :set_title_lower LIMIT 0, 1;');
			$query->execute(array(':set_title_lower' => strtolower($set)));
		}
		elseif(is_int($set)){
			$query = $this->prepare('SELECT set_id, set_call, set_type, set_images, set_image_count FROM sets WHERE set_id = ' . $set . ' LIMIT 0, 1;');
			$query->execute();
		}
		else{
			return false;
		}
		
		$sets = $query->fetchAll();
		
		if(@count($sets) != 1){
			return false;
		}
		
		$set = $sets[0];
		
		// If auto, apply stored functions
		if($set['set_type'] == 'auto'){
			$image_ids = new Find(null, false, false);
			$image_ids->memory(unserialize($set['set_call']));
			$image_ids->find();
			
			$set_images = implode(', ', $image_ids->image_ids);
			
			// Update set if images have changed
			if($set_images != $set['set_images']){
				$fields = array('set_image_count' => $image_ids->image_count,
					'set_images' => $set_images);
				$this->updateRow($fields, 'sets', $set['set_id'], false);
			}
			
			if(!empty($image_ids->image_ids)){
				$this->sql_conds[] = 'images.image_id IN (' . implode(', ', $image_ids->image_ids) . ')';
			}
			else{
				$this->sql_conds[] = 'images.image_id IN (NULL)';
			}
		}
		
		// If static, use stored image IDs
		elseif($set['set_type'] == 'static'){
			if(!empty($set['set_images'])){
				$this->sql_conds[] = 'images.image_id IN (' . $set['set_images'] . ')';
				$this->image_order = $this->convertToIntegerArray($set['set_images']);
			}
			else{
				$this->sql_conds[] = 'images.image_id IN (NULL)';
			}
		}
	}
	
	// MEMORY
	
	/**
	 * Recall memory
	 *
	 * @param array $call set_call field, else uses recent memory
	 * @return bool True if successful
	 */
	public function memory($call=null){
		if(empty($call)){
			if(!$call = $this->recentMemory()){
				return false;
			}
		}
		
		foreach($call as $ring){
			$method = key($ring);
			$arguments = $ring[$method];
			call_user_func_array(array($this, $method), $arguments);
		}
		
		return true;
	}
	
	/**
	 * Find by rights set
	 *
	 * @param int|string $right Right ID or right title
	 * @return void
	 */
	public function rights($right=null){
		// Error checking
		if(empty($right)){ return false; }
		if(intval($right)){ $right = intval($right); }
		
		// Determine input type
		if(is_string($right)){
			$query = $this->prepare('SELECT right_id FROM rights WHERE LOWER(right_title) LIKE :lower_right_title LIMIT 0, 1;');
			$query->execute(array(':lower_right_title' => strtolower($right)));
		}
		elseif(is_int($right)){
			$query = $this->prepare('SELECT right_id FROM rights WHERE right_id = ' . $right . ' LIMIT 0, 1;');
			$query->execute();
		}
		else{
			return false;
		}
		
		$rights = $query->fetchAll();
		
		if(@count($rights) != 1){
			return false;
		}
		
		$right = $rights[0];
		
		$this->sql_conds[] = 'images.right_id = ' . $right['right_id'];
		
		return true;
	}
	
	/**
	 * Find by user (who uploaded the image)
	 *
	 * @param int|array $users User IDs
	 * @return bool True if successful
	 */
	public function user($users=null){
		// Error checking
		if(empty($users)){ return false; }
		
		$users = parent::convertToIntegerArray($users);
		
		$users_sql = array();
		
		foreach($users as $user){
			$users_sql[] = 'images.user_id = ' . $user;
		}
		
		$this->sql_conds[] = '(' . implode(' OR ', $users_sql) . ')';
		
		return true;
	}
	
	/**
	 * Find by search (image title, image description, image geography, and image tags)
	 *
	 * @param string $search 
	 * @return bool True if successful
	 */
	public function search($search=null){
		// Error checking
		if(empty($search)){ return false; }
		
		// Prepare input
		$search_lower = strtolower($search);
		$search_lower = preg_replace('#\s#', '%', $search_lower);
		$search_lower = '%' . $search_lower . '%';
		
		// Search title, description
		$query = $this->prepare('SELECT images.image_id FROM images WHERE (LOWER(images.image_title) LIKE :image_title_lower OR LOWER(images.image_description) LIKE :image_description_lower OR LOWER(images.image_geo) LIKE :image_geo_lower)');
		$query->execute(array(':image_title_lower' => $search_lower, ':image_description_lower' => $search_lower, ':image_geo_lower' => $search_lower));
		$images = $query->fetchAll();
		
		$image_ids = array();
		
		foreach($images as $image){
			$image_ids[] = $image['image_id'];
		}
		
		// Search tags
		$query = $this->prepare('SELECT images.image_id FROM images, links, tags WHERE images.image_id = links.image_id AND links.tag_id = tags.tag_id AND (LOWER(tags.tag_name) LIKE :tag_name_lower);');
		$query->execute(array(':tag_name_lower' => $search_lower));
		
		$images = $query->fetchAll();
		
		foreach($images as $image){
			$image_ids[] = $image['image_id'];
		}
		
		if(count($image_ids) > 0){
			$this->sql_conds[] = 'images.image_id IN (' . implode(', ', $image_ids) . ')';
		}
		else{
			$this->sql_conds[] = 'images.image_id IS NULL';
		}
		
		return true;
	}
	
	/**
	 * Find by privacy levels
	 *
	 * @param int|string $privacy Privacy ID or string
	 * @param string $all Also include all images of lower privacy levels
	 * @return bool True if successful
	 */
	public function privacy($privacy=null, $all=false){
		// Error checking
		if(empty($privacy)){ return false; }
		if(intval($privacy)){ $privacy = intval($privacy); }
	
		// Guest, admin checking
		$user = new User;
		
		if(!empty($_SESSION['alkaline']['guest'])){
			$privacy = 2;
			$all = false;
		}
		
		// Convert strings
		if(is_string($privacy)){
			$privacy = strtolower($privacy);
			$levels = array('public' => 1, 'protected' => 2, 'private' => 3);
			if(array_key_exists($privacy, $levels)){
				$privacy = $levels[$privacy];
			}
			else{
				return false;
			}
			
			// Set fields to search
			if($all == true){
				$this->sql_conds[] = 'images.image_privacy <= ' . $privacy;
			}
			else{
				$this->sql_conds[] = 'images.image_privacy = ' . $privacy;
			}
		}
		elseif(is_integer($privacy)){
			// Set fields to search
			if($all == true){
				$this->sql_conds[] = 'images.image_privacy <= ' . $privacy;
			}
			else{
				$this->sql_conds[] = 'images.image_privacy = ' . $privacy;
			}
			
		}
		elseif(is_array($privacy)){
			parent::convertToIntegerArray($privacy);
			
			// Set fields to search
			$this->sql_conds[] = 'images.image_privacy IN (' . implode(', ', $privacy) . ')';
		}
		else{
			return false;
		}
		
		return true;
	}
	
	/**
	 * Find by color (HSL)
	 *
	 * @param string $h_min 
	 * @param string $h_max 
	 * @param string $s_min 
	 * @param string $s_max 
	 * @param string $l_min 
	 * @param string $l_max 
	 * @return bool True if successful
	 */
	public function hsl($h_min, $h_max, $s_min, $s_max, $l_min, $l_max){
		// Error checking
		if(!isset($h_min) and !isset($h_max) and !isset($s_min) and !isset($s_max) and !isset($l_min) and !isset($l_max)){ return false; }
		
		// H - Hue
		if(isset($h_min) and isset($h_max)){
			
			if($h_min > $h_max){
				$this->sql_conds[] = '(images.image_color_h <= ' . intval($h_max) . ' OR images.image_color_h >= ' . intval($h_min) . ')';
			}
			else{
				$this->sql_conds[] = 'images.image_color_h >= ' . intval($h_min);
				$this->sql_conds[] = 'images.image_color_h <= ' . intval($h_max);
			}
		}
		
		// S - Saturation
		if(isset($s_min) and isset($s_max)){
			$this->sql_conds[] = 'images.image_color_s >= ' . intval($s_min);
			$this->sql_conds[] = 'images.image_color_s <= ' . intval($s_max);
		}
		
		// L - Lightness
		if(isset($l_min) and isset($l_max)){
			$this->sql_conds[] = 'images.image_color_l >= ' . intval($l_min);
			$this->sql_conds[] = 'images.image_color_l <= ' . intval($l_max);
		}
		
		return true;
	}
	
	/**
	 * Find by publish status
	 *
	 * @param bool $published
	 * @return void
	 */
	public function published($published=true){
		$now = date('Y-m-d H:i:s');
		
		if($published == true){
			$this->sql_conds[] = 'images.image_published < :image_published';
			$this->sql_params[':image_published'] = $now;
		}
		if($published == false){
			$this->sql_conds[] = '(images.image_published > :image_published OR image_published IS NULL)';
			$this->sql_params[':image_published'] = $now;
		}
	}
	
	/**
	 * Find by image ratio (width/height)
	 *
	 * @param string|float|int $min Minimum ratio (0)
	 * @param string|float|int $max Maximum ratio (infinite)
	 * @param string|float|int $equal Search for precise ratio (1 = square)
	 * @return bool True if successful
	 */
	public function ratio($min=null, $max=null, $equal=null){
		if(empty($min) and empty($max) and empty($equal)){
			return false;
		}
		
		if(!empty($min)){
			$min = floatval($min);
			if($this->db_type == 'pgsql'){
				$this->sql_conds[] = '(CAST(images.image_width AS FLOAT) / CAST(images.image_height AS FLOAT)) < ' . $min;
			}
			else{
				$this->sql_conds[] = '(images.image_width / images.image_height) < ' . $min;
			}
		}
		if(!empty($max)){
			$max = floatval($max);
			if($this->db_type == 'pgsql'){
				$this->sql_conds[] = '(CAST(images.image_width AS FLOAT) / CAST(images.image_height AS FLOAT)) >' . $max;
			}
			else{
				$this->sql_conds[] = '(images.image_width / images.image_height) > ' . $max;
			}
		}
		if(!empty($equal)){
			$equal = floatval($equal);
			if($this->db_type == 'pgsql'){
				$this->sql_conds[] = '(CAST(images.image_width AS FLOAT) / CAST(images.image_height AS FLOAT)) = ' . $equal;
			}
			else{
				$this->sql_conds[] = '(images.image_width / images.image_height) = ' . $equal;
			}
		}
		
		return true;
	}
	
	/**
	 * Find by pages
	 *
	 * @param int|array $id Page IDs
	 * @return bool True if successful
	 */
	public function pages($id=null){
		if(empty($id)){ return false; }
		if(!intval($id)){ return false; }
		
		$id = intval($id);
		
		$pages = $this->getTable('pages', $id);
		
		$image_ids = array();
		
		foreach($pages as $page){
			$image_ids_on_page = explode(', ', $page['page_images']);
			foreach($image_ids_on_page as $image_id){
				$image_ids[] = $image_id;
			}
		}
		
		$image_ids = array_unique($image_ids);
		
		if(count($image_ids) > 0){
			$this->sql_conds[] = 'images.image_id IN (' . implode(', ', $image_ids) . ')';
		}
		else{
			$this->sql_conds[] = 'images.image_id IN (NULL)';
		}
		
		return true;
	}
	
	/**
	 * Find by EXIF value
	 *
	 * @param string $value EXIF value
	 * @param string $name EXIF name (key)
	 * @return bool True if successful
	 */
	public function exifs($value, $name=null){
		if(empty($value)){ return false; }
		
		// Add EXIFs to find
		$this->sql_tables[] = 'exifs';
		$this->sql_conds[] = 'exifs.image_id = images.image_id';
		
		// Search EXIFs
		if(empty($name)){
			$this->sql_conds[] = '(LOWER(exifs.exif_value) LIKE :exif_value)';
			$this->sql_params[':exif_value'] = '%' . strtolower($value) . '%';
		}
		else{
			$this->sql_conds[] = '(LOWER(exifs.exif_value) LIKE :exif_value AND LOWER(exifs.exif_name) LIKE :exif_name)';
			$this->sql_params[':exif_name'] = '%' . strtolower($name) . '%';
			$this->sql_params[':exif_value'] = '%' . strtolower($value) . '%';
		}
		
		return true;
	}
	
	/**
	 * Find by guest simulation
	 *
	 * @param int $id Guest ID
	 * @return bool True if successful
	 */
	public function guest($id=null){
		if(empty($id)){ return false; }
		
		$guest = $this->getRow('guests', $id);
		
		if($guest === false){
			return false;
		}
		
		if(empty($guest['guest_sets'])){
			$this->privacy('protected');
		}
		else{
			$this->sets(intval($guest['guest_sets']));
			$this->privacy('protected');
		}
		
		return true;
	}
	
	/**
	 * Specialized smart searches, use GET[id] values where necessary
	 *
	 * @param string $kind Untagged, unpublished, displayed, updated, nonpublic, untitled, views, tags, guests, sets, me, users, rights, pages
	 * @return bool True if successful
	 */
	protected function smart($kind){
		if(empty($kind)){
			return false;
		}
		
		switch($kind){
			case 'untagged':
				// Join tables
				$this->sql_join_on[] = 'images.image_id = links.image_id';
				$this->sql_join_tables[] = 'links';
				$this->sql_join_type = 'LEFT OUTER JOIN';
				
				// Set tags to find
				$this->sql_conds[] = 'links.link_id IS NULL';
				break;
			case 'unpublished':
				$this->_published(false);
				break;
			case 'displayed':
				$this->_published(true);
				$this->_privacy('public');
				break;
			case 'updated':
				$this->_sort('image_updated', 'DESC');
				break;
			case 'nonpublic':
				$this->_privacy(array(2, 3));
				break;
			case 'untitled':
				$this->sql_conds[] = 'images.image_title IS NULL';
				break;
			case 'views':
				$this->_sort('image_views', 'DESC');
				break;
			case 'tags':
				$this->_allTags(@intval($_GET['id']));
				break;
			case 'guests':
				$this->_guest(@intval($_GET['id']));
				break;
			case 'sets':
				$this->_sets(@intval($_GET['id']));
				break;
			case 'me':
				$this->_user(@intval($_SESSION['alkaline']['user']['user_id']));
				break;
			case 'users':
				$this->_user(@intval($_GET['id']));
				break;
			case 'rights':
				$this->_rights(@intval($_GET['id']));
				break;
			case 'pages':
				$this->_pages(@intval($_GET['id']));
				break;
			default:
				return false;
				break;
		}
		
		return true;
	}
	
	/**
	 * Locate page that contains a particular image ID
	 *
	 * @param int $image_id Image ID
	 * @return bool True if successful
	 */
	public function with($image_id){
		// Error checking
		if(empty($image_id)){ return false; }
		if(!$image_id = intval($image_id)){ return false; }
		
		$this->with = $image_id;
		
		return true;
	}
	
	/**
	 * Paginate results
	 *
	 * @param int $page Page number
	 * @param int $limit Number of images per page
	 * @param int $first Number of images on the first page (if different)
	 * @return bool True if successful
	 */
	public function page($page=null, $limit=null, $first=null){
		// Error checking
		if($limit === 0){ return false; }
		if(empty($page)){
			if(!empty($_REQUEST['page'])){ $page = intval($_REQUEST['page']); }
			else{ $page = 1; }
		}
		else{
			$page = intval($page);
		}
		if($page < 1){ return false; }
		if(empty($limit)){ $limit = LIMIT; }
		if(empty($first)){ $first = $limit; }
		
		// Store data to object
		$this->page = $page;
		$this->page_limit = intval($limit);
		$this->page_limit_first = intval($first);
		
		// Set SQL limit
		if($page == 1){ $this->page_limit_curent = $this->page_limit_first; }
		else{ $this->page_limit_curent = $this->page_limit; }
		
		$this->page_begin = (($page - 1) * $this->page_limit_curent) - $this->page_limit_curent + $this->page_limit_first;
		$this->sql_limit = ' LIMIT ' . $this->page_begin . ', ' . $this->page_limit_curent;
		
		return true;
	}
	
	/**
	 * Set number of offset images (images that appear just before and after the requested page)
	 *
	 * @param int $length Number of images
	 * @return bool True if successful
	 */
	public function offset($length){
		// Error checking
		if(!($length = intval($length))){ return false; }
		
		$this->image_offset_length = $length;
	}
	
	/**
	 * Find by location
	 *
	 * @param string $geo City name or latitude, longitude
	 * @param string $radius Search radius (in miles)
	 * @return bool True if successful
	 */
	public function location($geo, $radius){
		$place = new Geo($geo);
		
		if(!($radius = floatval($radius))){ return false; }
		
		$lat = $place->city['city_lat'];
		$long = $place->city['city_long'];
		
		$this->sql_conds[] = 'image_geo_lat <= ' . ceil($lat + $radius);
		$this->sql_conds[] = 'image_geo_lat >= ' . ceil($lat - $radius);
		$this->sql_conds[] = 'image_geo_long <= ' . ceil($long + $radius);
		$this->sql_conds[] = 'image_geo_long >= ' . ceil($long - $radius);
		$this->sql_conds[] = '3959 * acos(cos(radians(' . $lat . ')) * cos(radians(image_geo_lat)) * cos(radians(image_geo_long) - radians(' . $long . ')) + sin(radians(' . $lat . ')) * sin(radians(image_geo_lat))) <= ' . $radius;
		
		return true;
	}
	
	/**
	 * Sort results by image column
	 *
	 * @param string $column Image column
	 * @param string $sort Sort order (ASC or DESC)
	 * @return bool True if successful
	 */
	public function sort($column, $sort='ASC'){
		// Error checking
		if(empty($column)){ return false; }
		
		$column = $this->sanitize($column);
		
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
	
	/**
	 * Find by image fields not null
	 *
	 * @param string $field Image field
	 * @return bool True if successful
	 */
	public function notnull($field){
		if(empty($field)){ return false; }
		
		$field = $this->sanitize($field);
		
		$this->sql_conds[] = $field . ' IS NOT NULL';
		
		return true;
	}
	
	/**
	 * Execute Find class to determine class variables
	 *
	 * @return array Image IDs
	 */
	public function find(){
		// Prepare SQL
		$this->sql_from = ' FROM ' . implode(', ', $this->sql_tables);

		if(count($this->sql_conds) > 0){
			$this->sql_where = ' WHERE ' . implode(' AND ', $this->sql_conds);
		}
		
		if(count($this->sql_sorts) > 0){
			$this->sql_order_by = ' ORDER BY ' . implode(', ', $this->sql_sorts);
			if(($this->db_type == 'pgsql') or ($this->db_type == 'mssql')){
				$sql_sorts = str_ireplace(' ASC', '', $this->sql_sorts);
				$sql_sorts = str_ireplace(' DESC', '', $this->sql_sorts);
				$this->sql_group_by .= ', ' . implode(', ', $sql_sorts);
			}
		}
		elseif(empty($this->image_order)){
			$this->sql_order_by = ' ORDER BY images.image_uploaded DESC';
			if(($this->db_type == 'pgsql') or ($this->db_type == 'mssql')){
				$this->sql_group_by .= ', images.image_uploaded';
			}
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
		$query = $this->prepare($this->sql);
		$query->execute($this->sql_params);
		$images = $query->fetchAll();
		
		// Grab images.image_ids of results
		$image_ids = array();
		foreach($images as $image){
			$image_ids[] = intval($image['image_id']);
		}
		
		// Determine number of images
		$this->image_count = count($images);
		
		// Determine where "with" image id is placed in pages
		if(!empty($this->with)){
			$key = array_search($this->with, $image_ids);
			if($key === false){
				return false;
			}
			
			if(empty($this->page_limit)){ $this->page_limit = LIMIT; }
			if(empty($this->page_limit_first)){ $this->page_limit_first = $this->page_limit; }
			
			if($key < $this->page_limit_first){
				$page = 1;
			}
			else{
				$page = intval(ceil((($key + 1) - $this->page_limit_first) / $this->page_limit) + 1);
			}
			
			$this->page($page, $this->page_limit, $this->page_limit_first);
		}
		
		// Determine pagination
		if(!empty($this->page)){
			$this->page_count = ceil(($this->image_count - $this->page_limit_first) / $this->page_limit) + 1;
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
		$query = $this->prepare($this->sql);
		$query->execute($this->sql_params);
		$images = $query->fetchAll();
		
		// Grab images.image_ids of results
		$this->image_ids = array();
		foreach($images as $image){
			$this->image_ids[] = intval($image['image_id']);
		}
		
		if(!empty($this->image_order)){
			$replacement_image_ids = array();
			foreach($this->image_order as $image_id){
				if(in_array($image_id, $this->image_ids)){
					$replacement_image_ids[] = $image_id;
				}
			}
			
			$replacement_append_image_ids = array();
			foreach($this->image_ids as $image_id){
				if(!in_array($image_id, $this->image_order)){
					$replacement_append_image_ids[] = $image_id;
				}
			}
			
			$this->image_ids = array_merge($replacement_image_ids, $replacement_append_image_ids);
		}
		
		// Count images
		$this->image_count_result = count($this->image_ids);
		
		// Determine offset images
		if(!empty($this->page_limit)){
			if(!empty($this->image_offset_length)){
				$offset = $this->page_begin - $this->image_offset_length;
				
				if($offset < 0){
					$length = $this->image_offset_length + $offset;
					$offset = 0;
				}
				else{
					$length = $this->image_offset_length;
				}
				
				$this->image_ids_before = array_slice($image_ids, $offset, $length, true);
				$this->image_ids_before = array_reverse($this->image_ids_before);
				
				if($this->page == 1){
					$offset = $this->page_begin + $this->page_limit_first;
				}
				else{
					$offset = $this->page_begin + $this->page_limit;
				}
				
				$this->image_ids_after = array_slice($image_ids, $offset, $this->image_offset_length, true);
			}
			else{
				$this->image_ids_before = array_slice($image_ids, 0, $this->page_begin, true);
				
				if($this->page == 1){
					$offset = $this->page_begin + $this->page_limit_first;
				}
				else{
					$offset = $this->page_begin + $this->page_limit;
				}
				$this->image_ids_after = array_slice($image_ids, $offset, null, true);
			}
		}
		
		// Determine keys of images
		$this->image_first = $this->page_begin + 1;
		$this->image_last = $this->page_begin + $this->page_limit;
		$this->image_first_reverse = $this->image_count - $this->image_first + 1;
		$this->image_last_reverse = $this->page_begin + $this->page_limit;
		
		// Determine URLs of image pages
		if(!empty($this->page_next)){
			$this->page_next_uri = $this->magicURL($this->page_next);
		}
		
		if(!empty($this->page_previous)){
			$this->page_previous_uri = $this->magicURL($this->page_previous);
		}
		
		// Return images.image_ids
		return $this->image_ids;
	}
	
	// SEARCH MEMORY
	
	/**
	 * Save memory (after executing)
	 *
	 * @return bool True if successful
	 */
	public function saveMemory(){
		if(count($this->call) < 1){
			return false;
		}
		
		$_SESSION['alkaline']['search']['request'] = $_REQUEST;
		$_SESSION['alkaline']['search']['call'] = $this->call;
		$_SESSION['alkaline']['search']['image_ids'] = $this->image_ids;
		
		return true;
	}
	
	/**
	 * Execute recent memory
	 *
	 * @return string|false
	 */
	public function recentMemory(){
		if(empty($_SESSION['alkaline']['search']['call'])){
			return false;
		}
		
		return $_SESSION['alkaline']['search']['call'];
	}
	
	/**
	 * Clear the memory
	 *
	 * @return void
	 */
	public function clearMemory(){
		unset($_SESSION['alkaline']['search']['request']);
		unset($_SESSION['alkaline']['search']['call']);
		unset($_SESSION['alkaline']['search']['image_ids']);
	}
}

?>