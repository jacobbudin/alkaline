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

class Page extends Alkaline{
	public $images;
	public $page_ids;
	public $page_count = 0;
	public $pages;
	
	protected $sql;
	
	/**
	 * Initiate Page object
	 *
	 * @param int|array|string $page Page search (IDs or page title)
	 */
	public function __construct($page_ids=null){
		parent::__construct();
		
		// Repage page array
		$this->pages = array();
		
		// Input handling
		if(is_object($page_ids)){
			$last_modified = $page_ids->last_modified;
			$page_ids = $page_ids->ids;
		}
		
		$this->page_ids = parent::convertToIntegerArray($page_ids);
		
		// Error checking
		$this->sql = ' WHERE (pages.page_id IS NULL)';
		
		// Cache
		require_once('cache_lite/Lite.php');
		
		// Set a few options
		$options = array(
		    'cacheDir' => PATH . CACHE,
		    'lifeTime' => 3600
		);

		// Create a Cache_Lite object
		$cache = new Cache_Lite($options);
		
		if(($pages = $cache->get('pages:' . implode(',', $this->page_ids), 'pages')) && !empty($last_modified) && ($cache->lastModified() > $last_modified)){
			$this->pages = unserialize($pages);
		}
		else{
			if(count($this->page_ids) > 0){
				// Retrieve pages from database
				$this->sql = ' WHERE (pages.page_id IN (' . implode(', ', $this->page_ids) . '))';
			
				$query = $this->prepare('SELECT * FROM pages' . $this->sql . ';');
				$query->execute();
				$pages = $query->fetchAll();
		
				// Ensure pages array correlates to page_ids array
				foreach($this->page_ids as $page_id){
					foreach($pages as $page){
						if($page_id == $page['page_id']){
							$this->pages[] = $page;
						}
					}
				}
		
				// Store page count as integer
				$page_count = count($this->pages);
		
				// Attach additional fields
				for($i = 0; $i < $page_count; ++$i){
					if(empty($this->pages[$i]['page_title_url']) or (URL_RW != '/')){
						$this->pages[$i]['page_uri_rel'] = BASE . 'page' . URL_ID . $this->pages[$i]['page_id'] . URL_RW;
					}
					else{
						$this->pages[$i]['page_uri_rel'] = BASE . 'page' . URL_ID . $this->pages[$i]['page_id'] . '-' . $this->pages[$i]['page_title_url'] . URL_RW;
					}

					$this->pages[$i]['page_uri'] = LOCATION . $this->pages[$i]['page_uri_rel'];
				}
			}
			
			$cache->save(serialize($this->pages));
		}
		
		// Store page count as integer
		$this->page_count = count($this->pages);
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	/**
	 * Perform Orbit hook
	 *
	 * @param string $orbit 
	 * @return void
	 */
	public function hook($orbit=null){
		if(!is_object($orbit)){
			$orbit = new Orbit;
		}
		
		$this->pages = $orbit->hook('page', $this->pages, $this->pages);
	}
	
	/**
	 * Update page fields
	 *
	 * @param string $fields Associative array of columns and fields
	 * @param bool $overwrite 
	 * @param bool $version If post_text_raw changed, create a new version
	 * @return PDOStatement
	 */
	public function updateFields($fields, $overwrite=true, $version=true){
		// Error checking
		if(!is_array($fields)){
			return false;
		}
		
		$fields_original = $fields;
		
		for($i=0; $i < $this->page_count; $i++){
			$fields = $fields_original;
			
			$page_title = $fields['page_title'];
			$page_text_raw = $fields['page_text_raw'];
			
			// Verify each key has changed; if not, unset the key
			foreach($fields as $key => $value){
				if($fields[$key] == $this->pages[$i][$key]){
					unset($fields[$key]);
				}
				if(!empty($this->pages[$i][$key]) and ($overwrite === false)){
					unset($fields[$key]);
				}
			}
			
			// If no keys have changed, break
			if(count($fields) == 0){
				continue;
			}
			
			// Create version
			if(!empty($fields['page_text_raw']) and (($fields['page_text_raw'] != $this->pages[$i]['page_text_raw']) or ($fields['page_title'] != $this->pages[$i]['page_title'])) and ($version == true)){
				similar_text($fields['post_text_raw'], $this->posts[$i]['post_text_raw'], $version_similarity);
				$version_fields = array('page_id' => $this->pages[$i]['page_id'],
					'user_id' => $this->user['user_id'],
					'version_title' => $page_title,
					'version_text_raw' => $page_text_raw,
					'version_created' => date('Y-m-d H:i:s'),
					'version_similarity' => round($version_similarity));
				$this->addRow($version_fields, 'versions');
			}
			
			$this->updateRow($fields, 'pages', $this->pages[$i]['page_id']);
		}
		
		return true;
	}
	
	/**
	 * Deletes pages
	 *
	 * @param bool Delete permanently (and therefore cannot be recovered)
	 * @return void
	 */
	public function delete($permanent=false){
		if($permanent === true){
			$this->deleteRow('pages', $this->page_ids);
		}
		else{
			$fields = array('page_deleted' => date('Y-m-d H:i:s'));
			$this->updateFields($fields);
		}
		
		return true;
	}
	
	/**
	 * Recover pages (and comments also deleted at same time)
	 * 
	 * @return bool
	 */
	public function recover(){
		$fields = array('page_deleted' => null);
		$this->updateFields($fields);
		
		return true;
	}
	
	/**
	 * Increase page_views field by 1
	 *
	 * @return void
	 */
	public function updateViews(){
		for($i = 0; $i < $this->page_count; ++$i){
			$this->pages[$i]['page_views']++;
			$this->exec('UPDATE pages SET page_views = ' . $this->pages[$i]['page_views'] . ' WHERE page_id = ' . $this->pages[$i]['page_id'] . ';');
		}
	}
	
	/**
	 * Format time
	 *
	 * @param string $format Same format as date();
	 * @return void
	 */
	public function formatTime($format=null){
		foreach($this->pages as &$page){
			$page['page_created_format'] = parent::formatTime($page['page_created'], $format);
			$page['page_modified_format'] = parent::formatTime($page['page_modified'], $format);
		}
	}
	
	/**
	 * Get word and numerical sequencing of pages
	 *
	 * @param int $start First number on page
	 * @param bool $asc Sequence order (false if DESC)
	 * @return void
	 */
	public function getSeries($start=null, $asc=true){
		if(!ispage($start)){
			$start = 1;
		}
		else{
			$start = intval($start);
		}
		
		if($asc === true){
			$values = range($start, $start+$this->page_count);
		}
		else{
			$values = range($start, $start-$this->page_count);
		}
		
		for($i = 0; $i < $this->page_count; ++$i){
			$this->pages[$i]['page_numeric'] = $values[$i];
			$this->pages[$i]['page_alpha'] = ucwords($this->numberToWords($values[$i]));
		}
	}
	
	/**
	 * Add string notation to particular sequence, good for CSS columns
	 *
	 * @param string $label String notation
	 * @param int $frequency 
	 * @param bool $start_first True if first page should be selected and begin sequence
	 * @return void
	 */
	public function addSequence($label, $frequency, $start_first=false){
		if($start_first === false){
			$i = 1;
		}
		else{
			$i = $frequency;
		}
		
		// Store page comment fields
		foreach($this->pages as &$page){
			if($i == $frequency){
				if(empty($page['page_sequence'])){
					$page['page_sequence'] = $label;
				}
				else{
					$page['page_sequence'] .= ' ' . $label;
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
	 * Attribute actions to user
	 *
	 * @param User $user User object
	 * @return void
	 */
	public function attachUser($user){
		$this->user = $user->user;
	}
	
	/**
	 * Get version data and save to object
	 *
	 * @return array Array of version data
	 */
	public function getVersions(){
		$query = $this->prepare('SELECT versions.* FROM versions, pages' . $this->sql . ' AND versions.page_id = pages.page_id ORDER BY versions.version_created DESC;');
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
		$query = $this->prepare('SELECT citations.* FROM citations, pages' . $this->sql . ' AND citations.page_id = pages.page_id;');
		$query->execute();
		$this->citations = $query->fetchAll();
		
		$citation_count = count($this->citations);
		
		for($i=0; $i < $citation_count; $i++){
			$domain = $this->siftDomain($this->citations[$i]['citation_uri_requested']);
			if(file_exists(PATH . CACHE . 'favicons/' . $this->makeFilenameSafe($domain) . '.png')){
				$this->citations[$i]['citation_favicon_uri'] = LOCATION . BASE . CACHE . 'favicons/' . $this->makeFilenameSafe($domain) . '.png';
			}
		}
		
		return $this->citations;
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
			$citations[$citation['page_id']][] = $citation['citation_uri_requested'];
			$key = array_search($citation['page_id'], $this->page_ids);
			if($key !== false){
				if(strpos($this->pages[$key]['page_text_raw'], $citation['citation_uri_requested']) === false){
					$to_delete[] = $citation['citation_id'];
				}
			}
		}
		
		if(count($to_delete) > 0){
			$this->deleteRow('citations', $to_delete);
		}
		
		foreach($this->pages as $page){
			preg_match_all('#\b((?:[a-z][\w-]+:(?:\/{1,3}|[a-z0-9%])|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'".,<>?«»“”‘’]))#si', $page['page_text_raw'], $matches);
			foreach($matches[1] as $match){
				if(isset($citations[$page['page_id']])){
					if(in_array($matches[1], $citations[$page['page_id']])){ continue; }
				}
				$this->loadCitation($match, 'page_id', $page['page_id']);
			}
			
			$query = $this->prepare('SELECT citations.* FROM citations, pages WHERE pages.page_id = :page_id AND citations.page_id = pages.page_id;');
			$query->execute(array(':page_id' => $page['page_id']));
			$citations = $query->fetchAll();
			
			$page_citations = array();
			$ignore_keys = array('citation_created', 'citation_modified');
			
			foreach($citations as $citation){
				foreach($citation as $key => $value){
					if(is_numeric($key) or is_numeric($value) or empty($value) or in_array($key, $ignore_keys)){ continue; }
					$page_citations[] = $value;
				}
			}
			
			$this->updateRow(array('page_citations' => implode(' ', $page_citations)), 'pages', $page['page_id']);
		}
	}
}

?>