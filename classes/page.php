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
			$page_ids = $page_ids->ids;
		}
		
		$this->page_ids = parent::convertToIntegerArray($page_ids);
		
		// Error checking
		$this->sql = ' WHERE (pages.page_id IS NULL)';
		
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
			$this->page_count = count($this->pages);
		
			// Attach additional fields
			for($i = 0; $i < $this->page_count; ++$i){
				if(empty($this->pages[$i]['page_title_url']) or (URL_RW != '/')){
					$this->pages[$i]['page_uri_rel'] = BASE . 'page' . URL_ID . $this->pages[$i]['page_id'] . URL_RW;
				}
				else{
					$this->pages[$i]['page_uri_rel'] = BASE . 'page' . URL_ID . $this->pages[$i]['page_id'] . '-' . $this->pages[$i]['page_title_url'] . URL_RW;
				}

				$this->pages[$i]['page_uri'] = LOCATION . $this->pages[$i]['page_uri_rel'];
			}
		}
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
	 * @return PDOStatement
	 */
	public function updateFields($fields){
		$ids = array();
		foreach($this->pages as $page){
			$ids[] = $page['page_id'];
		}
		return parent::updateRow($fields, 'pages', $ids);
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
}

?>