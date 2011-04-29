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

class Post extends Alkaline{
	public $citations;
	public $comments;
	public $posts = array();
	public $post_ids;
	public $post_count = 0;
	public $user;
	public $versions;
	protected $sql;
	
	/**
	 * Initiates post object
	 *
	 * @param array|int|string $post_ids post IDs (use Find class to locate them)
	 */
	public function __construct($post_ids=null){
		parent::__construct();
		
		// Reset post array
		$this->posts = array();
		
		// Input handling
		if(is_object($post_ids)){
			$post_ids = $post_ids->ids;
		}
		
		$this->post_ids = parent::convertToIntegerArray($post_ids);
		
		// Error checking
		$this->sql = ' WHERE (posts.post_id IS NULL)';
		
		if(count($this->post_ids) > 0){
			// Retrieve posts from database
			$this->sql = ' WHERE (posts.post_id IN (' . implode(', ', $this->post_ids) . '))';
			
			$query = $this->prepare('SELECT * FROM posts' . $this->sql . ';');
			$query->execute();
			$posts = $query->fetchAll();
		
			// Ensure posts array correlates to post_ids array
			foreach($this->post_ids as $post_id){
				foreach($posts as $post){
					if($post_id == $post['post_id']){
						$this->posts[] = $post;
					}
				}
			}
		
			// Store post count as integer
			$this->post_count = count($this->posts);
		
			// Attach additional fields
			for($i = 0; $i < $this->post_count; ++$i){
				$title_url = $this->makeURL($this->posts[$i]['post_title']);
				if(empty($title_url) or (URL_RW != '/')){
					$this->posts[$i]['post_uri_rel'] = BASE . 'post' . URL_ID . $this->posts[$i]['post_id'] . URL_RW;
				}
				else{
					$this->posts[$i]['post_uri_rel'] = BASE . 'post' . URL_ID . $this->posts[$i]['post_id'] . '-' . $title_url . URL_RW;
				}
				
				$this->posts[$i]['post_uri'] = LOCATION . $this->posts[$i]['post_uri_rel'];
				
				if($this->returnConf('comm_enabled') != true){
					$this->posts[$i]['post_comment_disabled'] = 1;
				}
				elseif($this->returnConf('comm_close') == true){
					if((time() - strtotime($this->images[$i]['image_published'])) > $this->returnConf('comm_close_time')){
						$this->images[$i]['image_comment_disabled'] = 1;
					}
				}
			}
		}
	}
	
	public function __destruct(){
		parent::__destruct();
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
		
		$this->posts = $orbit->hook('post', $this->posts, $this->posts);
	}
	
	/**
	 * Increase post_views field by 1
	 *
	 * @return void
	 */
	public function updateViews(){
		for($i = 0; $i < $this->post_count; ++$i){
			$this->posts[$i]['post_views']++;
			$this->exec('UPDATE posts SET post_views = ' . $this->posts[$i]['post_views'] . ' WHERE post_id = ' . $this->posts[$i]['post_id'] . ';');
		}
	}
	
	/**
	 * Attribute actions to user
	 *
	 * @param User $user User object
	 * @return void
	 */
	public function attachUser($user){
		$this->user = $user;
	}
	
	/**
	 * Deletes posts
	 *
	 * @param bool Delete permanently (and therefore cannot be recovered)
	 * @return void
	 */
	public function delete($permanent=false){
		if($permanent === true){
			$this->deleteRow('posts', $this->post_ids);
		
			// Delete row
			$query = 'DELETE FROM versions WHERE post_id = ' . implode(' OR post_id = ', $this->post_ids) . ';';
			$query = 'DELETE FROM citations WHERE post_id = ' . implode(' OR post_id = ', $this->post_ids) . ';';
		
			if(!$this->exec($query)){
				return false;
			}
		}
		else{
			$query = $this->prepare('UPDATE comments SET comment_deleted = ? WHERE post_id = ' . implode(' OR post_id = ', $this->post_ids) . ';');
			$query->execute(array(date('Y-m-d H:i:s')));
			
			$fields = array('post_deleted' => date('Y-m-d H:i:s'));
			$this->updateFields($fields);
		}
		
		return true;
	}
	
	/**
	 * Recover posts (and comments also deleted at same time)
	 * 
	 * @return bool
	 */
	public function recover(){
		for($i = 0; $i < $this->post_count; ++$i){
			$query = $this->prepare('UPDATE comments SET comment_deleted = ? WHERE post_id = ' . $this->posts[$i]['post_id'] . ' AND comment_deleted = ' . $this->posts[$i]['post_deleted'] . ';');
			$query->execute(array(null));
		}
		
		$fields = array('post_deleted' => null);
		$this->updateFields($fields);
		
		return true;
	}
	
	/**
	 * Update post table
	 *
	 * @param array $array Associative array of columns and fields
	 * @param bool $overwrite 
	 * @param bool $version If post_text_raw changed, create a new version
	 * @return void
	 */
	public function updateFields($array, $overwrite=true, $version=true){
		// Error checking
		if(!is_array($array)){
			return false;
		}
		
		$array_original = $array;
		
		for($i = 0; $i < $this->post_count; ++$i){
			$array = $array_original;
			
			$post_title = $array['post_title'];
			$post_text_raw = $array['post_text_raw'];
			
			// Verify each key has changed; if not, unset the key
			foreach($array as $key => $value){
				if($array[$key] == $this->posts[$i][$key]){
					unset($array[$key]);
				}
				if(!empty($this->posts[$i][$key]) and ($overwrite === false)){
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
				if($key == 'post_published'){
					if(empty($value)){
						$fields[$key] = null;
					}
					elseif(strtolower($value) == 'now'){
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
							$this->addNote('The post&#8217;s publish date could not be determined, and was left unpublished.', 'error');
							$value = '';
						}
						$fields[$key] = $value;
					}
				}
				elseif($key == 'post_geo'){
					$geo = new Geo($value);
					if(!empty($geo->city)){
						if($geo->city['country_name'] == 'United States'){
							$fields['post_geo'] = $geo->city['city_name'] . ', ' . $geo->city['city_state'] .', ' . $geo->city['country_name'];
						}
						else{
							$fields['post_geo'] = $geo->city['city_name'] . ', ' . $geo->city['country_name'];
						}
					}
					elseif(!empty($geo->raw)){
						$fields['post_geo'] = ucwords($geo->raw);
					}
					else{
						$fields['post_geo'] = '';
					}
					
					if(!empty($geo->lat) and !empty($geo->long)){
						$fields['post_geo_lat'] = $geo->lat;
						$fields['post_geo_long'] = $geo->long;
					}
				}
				else{
					$fields[$key] = $value;
				}
			}
			
			// Set post_modified field to now
			$fields['post_modified'] = date('Y-m-d H:i:s');
			
			// Create version
			if(!empty($fields['post_text_raw']) and (($fields['post_text_raw'] != $this->posts[$i]['post_text_raw']) or ($fields['post_title'] != $this->posts[$i]['post_title'])) and ($version == true)){
				similar_text($fields['post_text_raw'], $this->posts[$i]['post_text_raw'], $version_similarity);
				$version_fields = array('post_id' => $this->posts[$i]['post_id'],
					'user_id' => $this->user->user['user_id'],
					'version_title' => $post_title,
					'version_text_raw' => $post_text_raw,
					'version_created' => date('Y-m-d H:i:s'),
					'version_similarity' => round($version_similarity));
				$this->addRow($version_fields, 'versions');
			}
			
			$columns = array_keys($fields);
			$values = array_values($fields);
			
			// Add row to database
			$query = $this->prepare('UPDATE posts SET ' . implode(' = ?, ', $columns) . ' = ? WHERE post_id = ' . $this->posts[$i]['post_id'] . ';');
			if(!$query->execute($values)){
				return false;
			}
			
			// Update object
			foreach($fields as $row => $value){
				$this->posts[$i][$row] = $value;
			}
			
		}
		
		$this->updateCitations();
		$this->updateRelated();
		
		return true;
	}
	
	/**
	 * Format time
	 *
	 * @param string $format Same format as date();
	 * @return void
	 */
	public function formatTime($format=null){
		foreach($this->posts as &$post){
			$post['post_created_format'] = parent::formatTime($post['post_created'], $format);
			$post['post_modified_format'] = parent::formatTime($post['post_modified'], $format);
			$post['post_published_format'] = parent::formatTime($post['post_published'], $format);
		}
	}
	
	
	/**
	 * Add string notation to particular sequence, good for CSS columns
	 *
	 * @param string $label String notation
	 * @param int $frequency 
	 * @param bool $start_first True if first post should be selected and begin sequence
	 * @return void
	 */
	public function addSequence($label, $frequency, $start_first=false){
		if($start_first === false){
			$i = 1;
		}
		else{
			$i = $frequency;
		}
		
		// Store post comment fields
		foreach($this->posts as &$post){
			if($i == $frequency){
				if(empty($post['post_sequence'])){
					$post['post_sequence'] = $label;
				}
				else{
					$post['post_sequence'] .= ' ' . $label;
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
	 * Get word and numerical sequencing of posts
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
			$values = range($start, $start+$this->post_count);
		}
		else{
			$values = range($start, $start-$this->post_count);
		}
		
		for($i = 0; $i < $this->post_count; ++$i){
			$this->posts[$i]['post_numeric'] = $values[$i];
			$this->posts[$i]['post_alpha'] = ucwords($this->numberToWords($values[$i]));
		}
	}
	
	/**
	 * Find related posts
	 *
	 * @param int $limit Number of posts to retrieve
	 * @return Post
	 */
	public function getRelated($limit=null){
		$ids = array();
		
		foreach($this->posts as $post){
			$ids = array_merge($ids, explode(', ', $post['post_related']));
		}
		
		$ids = array_unique($ids);
		$ids = array_slice($ids, 0, $limit);
		
		$this->related = new Post($ids);
		
		return $this->related;
	}
	
	/**
	 * Update related posts
	 *
	 * @param int $limit Number of posts to find 
	 * @return void
	 */
	public function updateRelated($limit=100){
		$now = date('Y-m-d H:i:s');

		$query = $this->prepare('UPDATE posts SET post_modified = :post_modified, post_tags = :post_tags, post_related = :post_related, post_related_hash = :post_related_hash WHERE post_id = :post_id;');
		
		$stop_words = array('a', 'about', 'above', 'above', 'across', 'after', 'afterwards', 'again', 'against', 'all', 'almost', 'alone', 'along', 'already', 'also','although','always','am','among', 'amongst', 'amoungst', 'amount', 'an', 'and', 'another', 'any','anyhow','anyone','anything','anyway', 'anywhere', 'are', 'around', 'as', 'at', 'back','be','became', 'because','become','becomes', 'becoming', 'been', 'before', 'beforehand', 'behind', 'being', 'below', 'beside', 'besides', 'between', 'beyond', 'bill', 'both', 'bottom','but', 'by', 'call', 'can', 'cannot', 'cant', 'co', 'con', 'could', 'couldnt', 'cry', 'de', 'describe', 'detail', 'do', 'done', 'dont', 'down', 'due', 'during', 'each', 'eg', 'eight', 'either', 'eleven','else', 'elsewhere', 'empty', 'enough', 'etc', 'even', 'ever', 'every', 'everyone', 'everything', 'everywhere', 'except', 'few', 'fifteen', 'fify', 'fill', 'find', 'fire', 'first', 'five', 'for', 'former', 'formerly', 'forty', 'found', 'four', 'from', 'front', 'full', 'further', 'get', 'give', 'go', 'had', 'has', 'hasnt', 'have', 'he', 'hence', 'her', 'here', 'hereafter', 'hereby', 'herein', 'hereupon', 'hers', 'herself', 'him', 'himself', 'his', 'how', 'however', 'hundred', 'i', 'ie', 'if', 'in', 'inc', 'indeed', 'interest', 'into', 'is', 'it', 'its', 'itself', 'keep', 'last', 'lets', 'latter', 'latterly', 'least', 'less', 'ltd', 'made', 'many', 'may', 'me', 'meanwhile', 'might', 'mill', 'mine', 'more', 'moreover', 'most', 'mostly', 'move', 'much', 'must', 'my', 'myself', 'name', 'namely', 'neither', 'never', 'nevertheless', 'next', 'nine', 'no', 'nobody', 'none', 'noone', 'nor', 'not', 'nothing', 'now', 'nowhere', 'of', 'off', 'often', 'on', 'once', 'one', 'only', 'onto', 'or', 'other', 'others', 'otherwise', 'our', 'ours', 'ourselves', 'out', 'over', 'own','part', 'per', 'perhaps', 'please', 'put', 'rather', 're', 'same', 'see', 'seem', 'seemed', 'seeming', 'seems', 'serious', 'several', 'she', 'should', 'show', 'side', 'since', 'sincere', 'six', 'sixty', 'so', 'some', 'somehow', 'someone', 'something', 'sometime', 'sometimes', 'somewhere', 'still', 'such', 'system', 'take', 'ten', 'than', 'that', 'the', 'their', 'them', 'themselves', 'then', 'thence', 'there', 'thereafter', 'thereby', 'therefore', 'therein', 'thereupon', 'these', 'they', 'thickv', 'thin', 'third', 'this', 'those', 'though', 'three', 'through', 'throughout', 'thru', 'thus', 'to', 'together', 'too', 'top', 'toward', 'towards', 'twelve', 'twenty', 'two', 'un', 'under', 'until', 'up', 'upon', 'us', 'very', 'via', 'was', 'we', 'well', 'were', 'what', 'whatever', 'when', 'whence', 'whenever', 'where', 'whereafter', 'whereas', 'whereby', 'wherein', 'whereupon', 'wherever', 'whether', 'which', 'while', 'whither', 'who', 'whoever', 'whole', 'whom', 'whose', 'why', 'will', 'with', 'within', 'without', 'would', 'yet', 'you', 'your', 'yours', 'yourself', 'yourselves', 'the');
		
		// Check to see if recently updated
		for($i=0; $i < $this->post_count; $i++){
			// Compute tags
			$post_text_raw = strip_tags($this->posts[$i]['post_text_raw']);
			preg_match_all('#[A-Z]+[A-Za-z\']*(\s[A-Z]+[A-Za-z\']*)*#s', $post_text_raw, $matches);
			preg_match_all('#[a-z]{7,}#s', $post_text_raw, $matches2);
			
			$post_tags = array();
			
			foreach($matches[0] as $match){
				$match = explode(' ', $match);
				if(in_array(strtolower($match[0]), $stop_words)){
					unset($match[0]);
					if((count($match) == 2) and in_array(strtolower($match[1]), $stop_words)){
						$match = '';
					}
				}
				if(is_array($match)){
					$match = implode(' ', $match);
				}
				$match_temp = $match;
				$match_temp = strtolower(str_replace('\'', '', $match_temp));
				if(!in_array($match_temp, $stop_words)){
					$match = preg_replace('#\'s$#si', '', $match);
					$post_tags[] = $match;
				}
			}
			
			foreach($matches2[0] as $match){
				$match_temp = $match;
				$match_temp = strtolower(str_replace('\'', '', $match_temp));
				if(!in_array($match_temp, $stop_words)){
					if(!preg_match('#ly$#si', $match)){
						$post_tags[] = $match;
					}
				}
			}
			
			$post_tag_count = count($post_tags);
			
			for($m=0; $m < $post_tag_count; $m++){
				if(empty($post_tags[$m])){
					unset($post_tags[$m]);
				}
			}
			
			$post_tags = array_merge(array_unique($post_tags));
			
			$post_related_hash = md5(implode('; ', $post_tags));
			if($post_related_hash != $this->posts[$i]['post_related_hash']){
				$post_related = array();
				
				$related_post_ids = new Find('posts');
				$related_post_ids->anyTags($post_tags);
				$related_post_ids->page(1, $limit);
				$related_post_ids->find();
				
				$key = array_search($this->posts[$i]['post_id'], $related_post_ids->ids);
				
				if($key !== false){
					unset($related_post_ids->ids[$key]);
				}
				
				$ids = array_merge($related_post_ids->ids);
				
				$related_posts = new Post($ids);
				
				foreach($related_posts->posts as $post){
					$post_related[$post['post_id']] = count(array_intersect(explode('; ', $this->posts[$i]['post_tags']), explode('; ', $post['post_tags'])));
				}
				
				arsort($post_related);
				
				$post_related = implode(', ', array_keys($post_related));
				
				$query->execute(array(':post_modified' => $now, ':post_related' => $post_related, ':post_related_hash' => $post_related_hash, ':post_tags' => implode('; ', $post_tags), ':post_id' => $this->posts[$i]['post_id']));
			}
		}
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
			$query = $this->prepare('SELECT * FROM comments, posts' . $this->sql . ' AND comments.post_id = posts.post_id AND comments.comment_deleted IS NULL AND comments.comment_status > 0 ORDER BY comments.comment_created ASC;');
		}
		else{
			$query = $this->prepare('SELECT * FROM comments, posts' . $this->sql . ' AND comments.post_id = posts.post_id AND comments.comment_deleted IS NULL ORDER BY comments.comment_created ASC;');
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
		
		// Store post comment fields
		for($i = 0; $i < $this->post_count; ++$i){
			$this->posts[$i]['post_comment_text'] = '<textarea id="comment_' . $this->posts[$i]['post_id'] . '_text" name="comment_' . $this->posts[$i]['post_id'] . '_text" class="comment_text"></textarea>';
			
			$this->posts[$i]['post_comment_author_name'] = '<input type="text" id="comment_' . $this->posts[$i]['post_id'] . '_author_name" name="comment_' . $this->posts[$i]['post_id'] . '_author_name" class="comment_author_name" />';
			
			$this->posts[$i]['post_comment_author_email'] = '<input type="text" id="comment_' . $this->posts[$i]['post_id'] . '_author_email" name="comment_' . $this->posts[$i]['post_id'] . '_author_email" class="comment_author_email" />';
			
			$this->posts[$i]['post_comment_author_uri'] = '<input type="text" id="comment_' . $this->posts[$i]['post_id'] . '_author_uri" name="comment_' . $this->posts[$i]['post_id'] . '_author_uri" class="comment_author_uri" />';
		
			$this->posts[$i]['post_comment_submit'] = '<input type="hidden" name="post_id" value="' . $this->posts[$i]['post_id'] . '" /><input type="submit" id="" name="" class="comment_submit" value="Submit comment" />';
		}
		
		return $this->comments;
	}
	
	/**
	 * Get users data and save to object
	 *
	 * @return array
	 */
	public function getUsers(){
		$ids = array();
		
		for($i = 0; $i < $this->post_count; ++$i){
			$ids[] = $this->posts[$i]['user_id'];
		}
		
		$ids = array_unique($ids);
		
		$users = $this->getTable('users', $ids);
		
		$user_ids = array();
		
		foreach($users as $user){
			$user_ids[] = $user['user_id'];
		}
		
		$no_save = array('user_key', 'user_pass', 'user_pass_salt');
		
		for($i = 0; $i < $this->post_count; ++$i){
			$key = array_search($this->posts[$i]['user_id'], $user_ids);
			foreach($users[$key] as $field => $value){
				if(in_array($field, $no_save)){ continue; }
				$this->posts[$i][$field] = $users[$key][$field];
			}
		}
		
		return $this->users;
	}
	
	/**
	 * Get version data and save to object
	 *
	 * @return array Array of version data
	 */
	public function getVersions(){
		$query = $this->prepare('SELECT versions.* FROM versions, posts' . $this->sql . ' AND versions.post_id = posts.post_id ORDER BY versions.version_created DESC;');
		$query->execute();
		$this->versions = $query->fetchAll();
		
		return $this->versions;
	}
	
	/**
	 * Get citation data and save to object
	 *
	 * @return array Array of version data
	 */
	public function getCitations(){
		$query = $this->prepare('SELECT citations.* FROM citations, posts' . $this->sql . ' AND citations.post_id = posts.post_id;');
		$query->execute();
		$this->citations = $query->fetchAll();
		
		$citation_count = count($this->citations);
		
		for($i=0; $i < $citation_count; $i++){
			$domain = $this->siftDomain($this->citations[$i]['citation_uri_requested']);
			if(file_exists(PATH . CACHE . 'citations/favicons/' . $this->makeFilenameSafe($domain) . '.png')){
				$this->citations[$i]['citation_favicon_uri'] = LOCATION . BASE . CACHE . 'citations/favicons/' . $this->makeFilenameSafe($domain) . '.png';
			}
		}
		
		return $this->citations;
	}
	
	/**
	 * Import files as posts
	 *
	 * @param array|string $files Full path to post files
	 * @return void
	 */
	public function import($files){
		if(empty($files)){
			return false;
		}
		
		$files = $this->convertToArray($files);
		$post_ids = array();
		
		foreach($files as $file){
			if(!file_exists($file)){
				return false;
			}
		
			// Add post to database
			$post_title = substr($this->changeExt(trim($this->getFilename($file)), ''), 0, -1);
			$post_title = $this->makeUnicode($post_title);
			$post_title_url = $this->makeURL($post_title);

			$post_text_raw = file_get_contents($file);
			$post_text_raw = $this->makeUnicode($post_text_raw);
			$post_text = $post_text_raw;

			// Configuration: post_markup
			if($this->returnConf('web_markup')){
				$orbit = new Orbit;
				$post_markup_ext = $this->returnConf('web_markup_ext');
				$post_text = $orbit->hook('markup_' . $post_markup_ext, $post_text_raw, $post_text);
			}
			else{
				$post_markup_ext = '';
				$post_text = $this->nl2br($post_text_raw);
			}

			$post_images = implode(', ', $this->findIDRef($post_text));

			$post_words = $this->countWords($post_text_raw, 0);
			
			$now = date('Y-m-d H:i:s');
			
			$post_published = '';
			if($this->user->returnPref('post_pub') === true){
				$post_published = $now;
			}
			
			$post_created = '';
			// Post created time
			if($filemtime = filemtime($file)){
				$post_created = date('Y-m-d H:i:s', $filemtime);
			}

			$fields = array('user_id' => @$this->user->user['user_id'],
				'post_title' => $post_title,
				'post_title_url' => $post_title_url,
				'post_text_raw' => $post_text_raw,
				'post_markup' => $post_markup_ext,
				'post_images' => $post_images,
				'post_text' => $post_text,
				'post_created' => $post_created,
				'post_published' => $post_published,
				'post_words' => $post_words);
			
			$post_id = $this->addRow($fields, 'posts');
			
			// Create version
			if(is_integer($post_id) and ($post_id > 0)){
				$version_fields = array('post_id' => $post_id,
					'user_id' => $this->user->user['user_id'],
					'version_title' => $post_title,
					'version_text_raw' => $post_text_raw,
					'version_created' => $now);
				$this->addRow($version_fields, 'versions');
			}
			
			$post_ids[] = $post_id;
			
			// Delete file
			@unlink($file);
		}
		
		// Store initial post_ids array
		$existing_post_ids = $this->post_ids;
		
		// Construct object anew
		self::__construct($post_ids);
		
		// Combine existing and imported post_ids arrays
		if(!empty($existing_post_ids)){
			$this->post_ids = array_merge($existing_post_ids, $this->post_ids);
		}
		
		// Merge with previous post_ids
		self::__construct($this->post_ids);
	}
	
	/**
	 * Update citations
	 *
	 * @return void
	 */
	public function updateCitations(){
		$this->getCitations();
		
		$citations = array();
		$to_delete = array();
		
		foreach($this->citations as $citation){
			$citations[$citation['post_id']][] = $citation['citation_uri_requested'];
			$key = array_search($citation['post_id'], $this->post_ids);
			if($key !== false){
				if(strpos($this->posts[$key]['post_text_raw'], $citation['citation_uri_requested']) === false){
					$to_delete[] = $citation['citation_id'];
				}
			}
		}
		
		foreach($this->posts as $post){
			preg_match_all('#href="(.*?)"#si', $post['post_text_raw'], $matches);
			foreach($matches[1] as $match){
				if(isset($citations[$post['post_id']])){
					if(in_array($matches[1], $citations[$post['post_id']])){ continue; }
				}
				$this->loadCitation($match, 'post_id', $post['post_id']);
			}
		}
		
		if(count($to_delete) > 0){
			$this->deleteRow('citations', $to_delete);
		}
	}
}

?>