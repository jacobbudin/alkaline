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
	public $page_ids;
	public $page_count;
	public $pages;
	
	/**
	 * Initiate Page object
	 *
	 * @param int|array|string $page Page search (IDs or page title)
	 */
	public function __construct($page=null){
		parent::__construct();
		
		if(!empty($page)){
			$sql_params = array();
			
			if(is_int($page)){
				$page_id = $page;
				$query = $this->prepare('SELECT * FROM pages WHERE page_id = ' . $page_id . ';');
			}
			elseif(is_string($page)){
				$query = $this->prepare('SELECT * FROM pages WHERE (LOWER(page_title_url) LIKE :page_title_url);');
				$sql_params[':page_title_url'] = '%' . strtolower($page) . '%';
			}
			elseif(is_array($page)){
				$page_ids = $this->convertToIntegerArray($page);
				$query = $this->prepare('SELECT * FROM pages WHERE page_id = ' . implode(' OR page_id = ', $page_ids) . ';');
			}
			
			if(!empty($query)){
				$query->execute($sql_params);
				$this->pages = $query->fetchAll();
				
				$this->page_count = count($this->pages);
			}
		}
		
		// Attach additional fields
		for($i = 0; $i < $this->page_count; ++$i){
			if(empty($this->pages[$i]['page_title_url']) or (URL_RW != '/')){
				$this->pages[$i]['page_uri'] = LOCATION . BASE . 'page' . URL_ID . $this->pages[$i]['page_id'] . URL_RW;
			}
			else{
				$this->pages[$i]['page_uri'] = LOCATION . BASE . 'page' . URL_ID . $this->pages[$i]['page_id'] . '-' . $this->pages[$i]['page_title_url'] . URL_RW;
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
	
	public function create(){
		
	}
	
	/**
	 * Fetch all pages
	 *
	 * @return void
	 */
	public function fetchAll(){
		$query = $this->prepare('SELECT page_id FROM pages;');
		$query->execute();
		$pages = $query->fetchAll();
		
		$page_ids = array();
		
		foreach($pages as $page){
			$page_ids[] = $page['page_id'];
		}
		
		// Reconstruct
		self::__construct($page_ids);
	}
	
	public function search($search=null){
		
	}
	
	/**
	 * Update page fields
	 *
	 * @param string $fields Associative array of columns and fields
	 * @return PDOStatement
	 */
	public function update($fields){
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
			$page['page_created'] = parent::formatTime($page['page_created'], $format);
			$page['page_modified'] = parent::formatTime($page['page_modified'], $format);
		}
	}
}

?>