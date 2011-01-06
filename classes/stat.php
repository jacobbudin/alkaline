<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalinenapp.com/
*/

/**
 * @author Budin Ltd. <contact@budinltd.com>
 * @copyright Copyright (c) 2010-2011, Budin Ltd.
 * @version 1.0
 */

class Stat extends Alkaline{
	public $durations;
	public $pages;
	public $page_types;
	public $referrers_recent;
	public $referrers_popular;
	public $stats;
	public $stat_begin;
	public $stat_begin_ts;
	public $stat_end;
	public $stat_end_ts;
	public $views;
	public $visitors;
	
	/**
	 * Initiate Stat object
	 *
	 * @param int|string $stat_begin Date begin
	 * @param int|string $stat_end Date end
	 */
	public function __construct($stat_begin=null, $stat_end=null){
		parent::__construct();
		
		if(empty($stat_begin)){
			$this->stat_begin_ts = strtotime('-60 days');
		}
		elseif(is_int($stat_begin)){
			$this->stat_begin_ts = $stat_begin;
		}
		else{
			$this->stat_begin_ts = strtotime($stat_begin);
		}
		
		if(empty($stat_end)){
			$this->stat_end_ts = time();
		}
		elseif(is_int($stat_end)){
			$this->stat_end_ts = $stat_end;
		}
		else{
			$this->stat_end_ts = strtotime($stat_end);
		}
		
		$this->stat_begin = date('Y-m-d H:i:s', $this->stat_begin_ts);
		$this->stat_end = date('Y-m-d H:i:s', $this->stat_end_ts);
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
		
		$this->stats = $orbit->hook('stat', $this->stats, $this->stats);
	}
	
	/**
	 * Get monthly views and visitors
	 *
	 * @return void
	 */
	public function getMonthly(){
		$this->views = 0;
		$this->visitors = 0;
		
		$query = $this->prepare('SELECT MONTH(stat_date) as stat_month, YEAR(stat_date) as stat_year, COUNT(*) as stat_views FROM stats WHERE stat_date >= :stat_date_begin AND stat_date <= :stat_date_end GROUP BY stat_month, stat_year ORDER BY stat_year DESC, stat_month DESC;');
		$query->execute(array(':stat_date_begin' => $this->stat_begin, ':stat_date_end' => $this->stat_end));
		$stats = $query->fetchAll();
		
		foreach($stats as &$stat){
			$stat['stat_ts_js'] = strtotime($stat['stat_month'] . '-' . $stat['stat_month'] . '-15') * 1000;
			$stat['stat_views'] = intval($stat['stat_views']);
		}
		
		$this->stats = array();
		$next = date('Y-m', strtotime('+1 month', $this->stat_end_ts));
		
		$next_month = intval(substr($next, 5, 2));
		$next_year = intval(substr($next, 0, 4));
		
		$current_month = intval(substr($this->stat_begin, 5, 2));
		$current_year = intval(substr($this->stat_begin, 0, 4));
		
		while(!(($next_month <= $current_month) and ($next_year <= $current_year))){
			if($current_month == 13){
				$current_year++;
				$current_month = 1;
			}
			$stat_ts_js = strtotime($current_year . '-' . $current_month) * 1000;
			$this->stats[] = array('stat_month' => $current_month, 'stat_year' => $current_year, 'stat_views' => 0, 'stat_visitors' => 0, 'stat_ts_js' => $stat_ts_js);
			$current_month++;
		}
		
		foreach($this->stats as &$monthly){
			foreach($stats as $stat){
				if(($stat['stat_month'] == $monthly['stat_month']) and ($stat['stat_year'] == $monthly['stat_year'])){
					$monthly['stat_views'] = $stat['stat_views'];
				}
			}
		}
		
		$query = $this->prepare('SELECT MONTH(stat_date) as stat_month, YEAR(stat_date) as stat_year, COUNT(*) as stat_visitors FROM stats WHERE stat_duration = 0 AND stat_date >= :stat_date_begin AND stat_date <= :stat_date_end GROUP BY stat_month, stat_year ORDER BY stat_year DESC, stat_month DESC;');
		$query->execute(array(':stat_date_begin' => $this->stat_begin, ':stat_date_end' => $this->stat_end));
		$stats = $query->fetchAll();
		
		foreach($stats as &$stat){
			$stat['stat_visitors'] = intval($stat['stat_visitors']);
		}
		
		foreach($this->stats as &$monthly){
			foreach($stats as $stat){
				if(($stat['stat_month'] == $monthly['stat_month']) and ($stat['stat_year'] == $monthly['stat_year'])){
					$monthly['stat_visitors'] = $stat['stat_visitors'];
				}
			}
		}
		
		$this->views = $this->getCumulative($this->stats, 'stat_views');
		$this->visitors = $this->getCumulative($this->stats, 'stat_visitors');
	}
	
	/**
	 * Get daily views and visitors
	 *
	 * @return void
	 */
	public function getDaily(){
		$this->views = 0;
		$this->visitors = 0;
		
		$query = $this->prepare('SELECT DAY(stat_date) as stat_day, MONTH(stat_date) as stat_month, YEAR(stat_date) as stat_year, COUNT(*) as stat_views FROM stats WHERE stat_date >= :stat_date_begin AND stat_date <= :stat_date_end GROUP BY stat_day, stat_month, stat_year ORDER BY stat_year DESC, stat_month DESC, stat_day DESC;');		
		$query->execute(array(':stat_date_begin' => $this->stat_begin, ':stat_date_end' => $this->stat_end));
		$stats = $query->fetchAll();
		
		foreach($stats as &$stat){
			$stat['stat_ts_js'] = strtotime($stat['stat_month'] . '-' . $stat['stat_month'] . '-' . $stat['stat_day']) * 1000;
			$stat['stat_views'] = intval($stat['stat_views']);
		}
		
		$this->stats = array();
		$next = date('Y-m-d', $this->stat_end_ts + 86400);
		
		$next_day = intval(substr($next, 8, 2));
		$next_month = intval(substr($next, 5, 2));
		$next_year = intval(substr($next, 0, 4));
		
		$current_day = intval(substr($this->stat_begin, 8, 2));
		$current_month = intval(substr($this->stat_begin, 5, 2));
		$current_year = intval(substr($this->stat_begin, 0, 4));
		
		while(!(($next_day <= $current_day) and ($next_month <= $current_month) and ($next_year <= $current_year))){
			if(!checkdate($current_month, $current_day, $current_year)){
				$current_month++;
				$current_day = 1;
				if(!checkdate($current_month, $current_day, $current_year)){
					$current_year++;
					$current_month = 1;
				}
			}
			
			$stat_ts_js = strtotime($current_year . '-' . $current_month . '-' . $current_day) * 1000;
			$this->stats[] = array('stat_day' => $current_day, 'stat_month' => $current_month, 'stat_year' => $current_year, 'stat_views' => 0, 'stat_visitors' => 0, 'stat_ts_js' => $stat_ts_js);
			$current_day++;
		}
		
		foreach($this->stats as &$daily){
			foreach($stats as $stat){
				if(($stat['stat_day'] == $daily['stat_day']) and ($stat['stat_month'] == $daily['stat_month']) and ($stat['stat_year'] == $daily['stat_year'])){
					$daily['stat_views'] = $stat['stat_views'];
				}
			}
		}
		
		$query = $this->prepare('SELECT DAY(stat_date) as stat_day, MONTH(stat_date) as stat_month, YEAR(stat_date) as stat_year, COUNT(*) as stat_visitors FROM stats WHERE stat_duration = 0 AND stat_date >= :stat_date_begin AND stat_date <= :stat_date_end GROUP BY stat_day, stat_month, stat_year ORDER BY stat_year DESC, stat_month DESC, stat_day DESC;');
		$query->execute(array(':stat_date_begin' => $this->stat_begin, ':stat_date_end' => $this->stat_end));
		$stats = $query->fetchAll();
		
		foreach($stats as &$stat){
			$stat['stat_visitors'] = intval($stat['stat_visitors']);
		}
		
		foreach($this->stats as &$daily){
			foreach($stats as $stat){
				if(($stat['stat_day'] == $daily['stat_day']) and ($stat['stat_month'] == $daily['stat_month']) and ($stat['stat_year'] == $daily['stat_year'])){
					$daily['stat_visitors'] = $stat['stat_visitors'];
				}
			}
		}
		
		$this->views = $this->getCumulative($this->stats, 'stat_views');
		$this->visitors = $this->getCumulative($this->stats, 'stat_visitors');
	}
	
	/**
	 * Get hourly views and visitors
	 *
	 * @return void
	 */
	public function getHourly(){
		$this->views = 0;
		$this->visitors = 0;
		
		$query = $this->prepare('SELECT HOUR(stat_date) AS stat_hour, DAY(stat_date) AS stat_day, MONTH(stat_date) AS stat_month, YEAR(stat_date) as stat_year, COUNT(*) AS stat_views FROM stats WHERE stat_date >= :stat_date_begin AND stat_date <= :stat_date_end GROUP BY stat_hour, stat_day, stat_month, stat_year ORDER BY stat_year DESC, stat_month DESC, stat_day DESC, stat_hour DESC;');
		$query->execute(array(':stat_date_begin' => $this->stat_begin, ':stat_date_end' => $this->stat_end));
		$stats = $query->fetchAll();
		
		foreach($stats as &$stat){
			$stat['stat_ts_js'] = strtotime($stat['stat_month'] . '-' . $stat['stat_month'] . '-' . $stat['stat_day'] . ' ' . $stat['stat_hour'] . ':30:00') * 1000;
			$stat['stat_views'] = intval($stat['stat_views']);
		}
		
		$this->stats = array();
		$next = date('Y-m-d H', $this->stat_end_ts + 3600);
		
		$next_hour = intval(substr($next, 11, 2));
		$next_day = intval(substr($next, 8, 2));
		$next_month = intval(substr($next, 5, 2));
		$next_year = intval(substr($next, 0, 4));
		
		$current_hour = intval(substr($this->stat_begin, 11, 2));
		$current_day = intval(substr($this->stat_begin, 8, 2));
		$current_month = intval(substr($this->stat_begin, 5, 2));
		$current_year = intval(substr($this->stat_begin, 0, 4));
		
		while(!(($next_hour == $current_hour) and ($next_day == $current_day) and ($next_month == $current_month) and ($next_year == $current_year))){
			if($current_hour == 24){
				$current_hour = 0;
				$current_day++;
				if(!checkdate($current_month, $current_day, $current_year)){
					$current_month++;
					$current_day = 1;
					if(!checkdate($current_month, $current_day, $current_year)){
						$current_year++;
						$current_month = 1;
					}
				}
			}
			$stat_ts_js = (strtotime($current_year . '-' . $current_month . '-' . $current_day . ' ' . $current_hour . ':00:00') - 18000) * 1000;
			$this->stats[] = array('stat_hour' => $current_hour, 'stat_day' => $current_day, 'stat_month' => $current_month, 'stat_year' => $current_year, 'stat_views' => 0, 'stat_visitors' => 0, 'stat_ts_js' => $stat_ts_js);
			$current_hour++;
		}
		
		foreach($this->stats as &$hourly){
			foreach($stats as $stat){
				if(($stat['stat_hour'] == $hourly['stat_hour']) and ($stat['stat_day'] == $hourly['stat_day']) and ($stat['stat_month'] == $hourly['stat_month']) and ($stat['stat_year'] == $hourly['stat_year'])){
					$hourly['stat_views'] = $stat['stat_views'];
				}
			}
		}
		
		$query = $this->prepare('SELECT HOUR(stat_date) AS stat_hour, DAY(stat_date) as stat_day, MONTH(stat_date) as stat_month, YEAR(stat_date) as stat_year, COUNT(*) as stat_visitors FROM stats WHERE stat_duration = 0 AND stat_date >= :stat_date_begin AND stat_date <= :stat_date_end GROUP BY stat_hour, stat_day, stat_month, stat_year ORDER BY stat_year DESC, stat_month DESC, stat_day DESC, stat_hour DESC;');
		$query->execute(array(':stat_date_begin' => $this->stat_begin, ':stat_date_end' => $this->stat_end));
		$stats = $query->fetchAll();
		
		foreach($stats as &$stat){
			$stat['stat_visitors'] = intval($stat['stat_visitors']);
		}
		
		foreach($this->stats as &$hourly){
			foreach($stats as $stat){
				if(($stat['stat_hour'] == $hourly['stat_hour']) and ($stat['stat_day'] == $hourly['stat_day']) and ($stat['stat_month'] == $hourly['stat_month']) and ($stat['stat_year'] == $hourly['stat_year'])){
					$hourly['stat_visitors'] = $stat['stat_visitors'];
				}
			}
		}
		
		$this->views = $this->getCumulative($this->stats, 'stat_views');
		$this->visitors = $this->getCumulative($this->stats, 'stat_visitors');
	}
	
	/**
	 * Calculate a cumulative count of an array key's values
	 *
	 * @param string $array 
	 * @param string $key 
	 * @return int Cumulative count
	 */
	protected function getCumulative($array, $key){
		$count = 0;
		foreach($array as $value){
			$count += $value[$key];
		}
		return $count;
	}
	
	/**
	 * Calculate visitor durations
	 *
	 * @return void
	 */
	public function getDurations(){
		$query = $this->prepare('SELECT MAX(stat_duration) AS stat_duration FROM stats WHERE stat_date >= :stat_date_begin AND stat_date <= :stat_date_end GROUP BY stat_session');
		$query->execute(array(':stat_date_begin' => $this->stat_begin, ':stat_date_end' => $this->stat_end));
		$this->durations = $query->fetchAll();
	}
	
	/**
	 * Get most popular pages
	 *
	 * @param int $limit Number of results
	 * @return void
	 */
	public function getPages($limit=10){
		$limit = intval($limit);
		$query = $this->prepare('SELECT COUNT(stat_page) as stat_count, stat_page FROM stats WHERE stat_date >= :stat_date_begin AND stat_date <= :stat_date_end GROUP BY stat_page ORDER BY stat_count DESC LIMIT 0, ' . $limit . ';');
		$query->execute(array(':stat_date_begin' => $this->stat_begin, ':stat_date_end' => $this->stat_end));
		$this->pages = $query->fetchAll();
	}
	
	/**
	 * Get most popular page types
	 *
	 * @param int $limit Number of results
	 * @return void
	 */
	public function getPageTypes($limit=10){
		$limit = intval($limit);
		$query = $this->prepare('SELECT COUNT(stat_page) as stat_count, stat_page_type FROM stats WHERE stat_date >= :stat_date_begin AND stat_date <= :stat_date_end GROUP BY stat_page_type ORDER BY stat_count DESC LIMIT 0, ' . $limit . ';');
		$query->execute(array(':stat_date_begin' => $this->stat_begin, ':stat_date_end' => $this->stat_end));
		$this->page_types = $query->fetchAll();
	}
	
	/**
	 * Get recent referrers
	 *
	 * @param string $limit Number of results
	 * @param string $include_local Include local refferers
	 * @return void
	 */
	public function getRecentReferrers($limit=20, $include_local=true){
		$limit = intval($limit);
		if($include_local === false){
			$where_local = 'AND stat_local = 0';
		}
		$query = $this->prepare('SELECT stat_referrer, stat_date FROM stats WHERE stat_referrer != :stat_referrer AND stat_date >= :stat_date_begin AND stat_date <= :stat_date_end ' . @$where_local . ' ORDER BY stat_date DESC LIMIT 0, ' . $limit . ';');
		$query->execute(array(':stat_referrer' => '', ':stat_date_begin' => $this->stat_begin, ':stat_date_end' => $this->stat_end));
		$this->referrers_recent = $query->fetchAll();
	}
	
	/**
	 * Get most popular referrers
	 *
	 * @param string $limit Number of results
	 * @param string $include_local Include local refferers
	 * @return void
	 */
	public function getPopularReferrers($limit=20, $include_local=true){
		$limit = intval($limit);
		if($include_local === false){
			$where_local = 'AND stat_local = 0';
		}
		$query = $this->prepare('SELECT stat_referrer, COUNT(stat_referrer) as stat_referrer_count FROM stats WHERE stat_referrer != :stat_referrer AND stat_date >= :stat_date_begin AND stat_date <= :stat_date_end ' . @$where_local . ' GROUP BY stat_referrer ORDER BY stat_referrer_count DESC LIMIT 0, ' . $limit . ';');
		$query->execute(array(':stat_referrer' => '', ':stat_date_begin' => $this->stat_begin, ':stat_date_end' => $this->stat_end));
		$this->referrers_popular = $query->fetchAll();
	}
}

?>
