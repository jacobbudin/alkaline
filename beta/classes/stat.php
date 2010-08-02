<?php

class Stat extends Alkaline{
	public $hourly;
	public $daily;
	public $monthly;
	public $stat_begin;
	public $stat_begin_ts;
	public $stat_end;
	public $stat_end_ts;
	
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
	
	// STAT-TO-ARRAY FUNCTIONS
	
	public function getMonthly(){
		$query = $this->db->prepare('SELECT stat_date, MONTH(stat_date) as stat_month, YEAR(stat_date) as stat_year, COUNT(*) as stat_views FROM stats WHERE stat_date >= "' . $this->stat_begin . '" AND stat_date <= "' . $this->stat_end . '" GROUP BY MONTH(stat_date), YEAR(stat_date) ORDER BY YEAR(stat_date) DESC, MONTH(stat_date) DESC;');
		$query->execute();
		$stats = $query->fetchAll();
		
		foreach($stats as &$stat){
			$stat['stat_ts_js'] = strtotime($stat['stat_date']) * 1000;
			$stat['stat_views'] = intval($stat['stat_views']);
		}
		
		$this->monthly = array();
		$next = date('Y-m', strtotime('+1 month', $this->stat_end_ts));
		
		$next_month = substr($next, 5, 2);
		$next_year = substr($next, 0, 4);
		
		$current_month = substr($this->stat_begin, 5, 2);
		$current_year = substr($this->stat_begin, 0, 4);
		
		while(!(($next_month == $current_month) and ($next_year == $current_year))){
			if($current_month < 13){
				$stat_ts_js = strtotime($current_year . '-' . $current_month) * 1000;
				$this->monthly[] = array('stat_month' => $current_month, 'stat_year' => $current_year, 'stat_views' => 0, 'stat_visitors' => 0, 'stat_ts_js' => $stat_ts_js);
				$current_month++;
			}
			else{
				$current_year++;
				$current_month = 1;
				$stat_ts_js = strtotime($current_year . '-' . $current_month) * 1000;
				$this->monthly[] = array('stat_month' => $current_month, 'stat_year' => $current_year, 'stat_views' => 0, 'stat_visitors' => 0, 'stat_ts_js' => $stat_ts_js);
				$current_month++;
			}
		}
		
		foreach($this->monthly as &$monthly){
			foreach($stats as $stat){
				if(($stat['stat_month'] == $monthly['stat_month']) and ($stat['stat_year'] == $monthly['stat_year'])){
					$monthly['stat_views'] = $stat['stat_views'];
				}
			}
		}
		
		$query = $this->db->prepare('SELECT stat_date, MONTH(stat_date) as stat_month, YEAR(stat_date) as stat_year, COUNT(*) as stat_visitors FROM stats WHERE stat_duration = 0 AND stat_date >= "' . $this->stat_begin . '" AND stat_date <= "' . $this->stat_end . '" GROUP BY MONTH(stat_date), YEAR(stat_date) ORDER BY YEAR(stat_date) DESC, MONTH(stat_date) DESC;');
		$query->execute();
		$stats = $query->fetchAll();
		
		foreach($stats as &$stat){
			$stat['stat_visitors'] = intval($stat['stat_visitors']);
		}
		
		foreach($this->monthly as &$monthly){
			foreach($stats as $stat){
				if(($stat['stat_month'] == $monthly['stat_month']) and ($stat['stat_year'] == $monthly['stat_year'])){
					$monthly['stat_visitors'] = $stat['stat_visitors'];
				}
			}
		}
		
		return true;
	}
	
	public function getDaily(){
		$query = $this->db->prepare('SELECT stat_date, DAY(stat_date) as stat_day, MONTH(stat_date) as stat_month, YEAR(stat_date) as stat_year, COUNT(*) as stat_views FROM stats WHERE stat_date >= "' . $this->stat_begin . '" AND stat_date <= "' . $this->stat_end . '" GROUP BY DAY(stat_date), MONTH(stat_date), YEAR(stat_date) ORDER BY YEAR(stat_date) DESC, MONTH(stat_date) DESC, DAY(stat_date) DESC;');
		$query->execute();
		$stats = $query->fetchAll();
		
		foreach($stats as &$stat){
			$stat['stat_ts_js'] = strtotime($stat['stat_date']) * 1000;
			$stat['stat_views'] = intval($stat['stat_views']);
		}
		
		$this->daily = array();
		$next = date('Y-m-d', $this->stat_end_ts + 86400);
		
		$next_day = intval(substr($next, 8, 2));
		$next_month = intval(substr($next, 5, 2));
		$next_year = intval(substr($next, 0, 4));
		
		$current_day = intval(substr($this->stat_begin, 8, 2));
		$current_month = intval(substr($this->stat_begin, 5, 2));
		$current_year = intval(substr($this->stat_begin, 0, 4));
		
		while(!(($next_day <= $current_day) and ($next_month <= $current_month) and ($next_year <= $current_year))){
			if(checkdate($current_month, $current_day, $current_year)){
				$stat_ts_js = strtotime($current_year . '-' . $current_month . '-' . $current_day) * 1000;
				$this->daily[] = array('stat_day' => $current_day, 'stat_month' => $current_month, 'stat_year' => $current_year, 'stat_views' => 0, 'stat_visitors' => 0, 'stat_ts_js' => $stat_ts_js);
				$current_day++;
			}
			else{
				$current_month++;
				$current_day = 1;
				if(checkdate($current_month, $current_day, $current_year)){
					$stat_ts_js = strtotime($current_year . '-' . $current_month . '-' . $current_day) * 1000;
					$this->daily[] = array('stat_day' => $current_day, 'stat_month' => $current_month, 'stat_year' => $current_year, 'stat_views' => 0, 'stat_visitors' => 0, 'stat_ts_js' => $stat_ts_js);
					$current_day++;
				}
				else{
					$current_year++;
					$current_month = 1;
					$current_day = 1;
					if(checkdate($current_month, $current_day, $current_year)){
						$stat_ts_js = strtotime($current_year . '-' . $current_month . '-' . $current_day) * 1000;
						$this->daily[] = array('stat_day' => $current_day, 'stat_month' => $current_month, 'stat_year' => $current_year, 'stat_views' => 0, 'stat_visitors' => 0, 'stat_ts_js' => $stat_ts_js);
						$current_day++;
					}
				}
			}
		}
		
		foreach($this->daily as &$daily){
			foreach($stats as $stat){
				if(($stat['stat_day'] == $daily['stat_day']) and ($stat['stat_month'] == $daily['stat_month']) and ($stat['stat_year'] == $daily['stat_year'])){
					$daily['stat_views'] = $stat['stat_views'];
				}
			}
		}
		
		$query = $this->db->prepare('SELECT stat_date, DAY(stat_date) as stat_day, MONTH(stat_date) as stat_month, YEAR(stat_date) as stat_year, COUNT(*) as stat_visitors FROM stats WHERE stat_duration = 0 AND stat_date >= "' . $this->stat_begin . '" AND stat_date <= "' . $this->stat_end . '" GROUP BY DAY(stat_date), MONTH(stat_date), YEAR(stat_date) ORDER BY YEAR(stat_date) DESC, MONTH(stat_date) DESC, DAY(stat_date) DESC;');
		$query->execute();
		$stats = $query->fetchAll();
		
		foreach($stats as &$stat){
			$stat['stat_visitors'] = intval($stat['stat_visitors']);
		}
		
		foreach($this->daily as &$daily){
			foreach($stats as $stat){
				if(($stat['stat_day'] == $daily['stat_day']) and ($stat['stat_month'] == $daily['stat_month']) and ($stat['stat_year'] == $daily['stat_year'])){
					$daily['stat_visitors'] = $stat['stat_visitors'];
				}
			}
		}
		
		return true;
	}
	
	public function getHourly(){
		$query = $this->db->prepare('SELECT stat_date, HOUR(stat_date) AS stat_hour, DAY(stat_date) AS stat_day, MONTH(stat_date) AS stat_month, YEAR(stat_date) as stat_year, COUNT(*) AS stat_views FROM stats WHERE stat_date >= "' . $this->stat_begin . '" AND stat_date <= "' . $this->stat_end . '" GROUP BY HOUR(stat_date), DAY(stat_date), MONTH(stat_date), YEAR(stat_date) ORDER BY YEAR(stat_date) DESC, MONTH(stat_date) DESC, DAY(stat_date) DESC, HOUR(stat_date) DESC;');
		$query->execute();
		$stats = $query->fetchAll();
		
		foreach($stats as &$stat){
			$stat['stat_ts_js'] = strtotime($stat['stat_date']) * 1000;
			$stat['stat_views'] = intval($stat['stat_views']);
		}
		
		$this->hourly = array();
		$next = date('Y-m-d H', $this->stat_end_ts + 3600);
		
		$next_hour = substr($next, 11, 2);
		$next_day = substr($next, 8, 2);
		$next_month = substr($next, 5, 2);
		$next_year = substr($next, 0, 4);
		
		$current_hour = substr($this->stat_begin, 11, 2);
		$current_day = substr($this->stat_begin, 8, 2);
		$current_month = substr($this->stat_begin, 5, 2);
		$current_year = substr($this->stat_begin, 0, 4);
		
		while(!(($next_hour == $current_hour) and ($next_day == $current_day) and ($next_month == $current_month) and ($next_year == $current_year))){
			if(checkdate($current_month, $current_day, $current_year) and ($current_hour < 24)){
				$stat_ts_js = (strtotime($current_year . '-' . $current_month . '-' . $current_day . ' ' . $current_hour . ':00:00') - 18000) * 1000;
				$this->hourly[] = array('stat_hour' => $current_hour, 'stat_day' => $current_day, 'stat_month' => $current_month, 'stat_year' => $current_year, 'stat_views' => 0, 'stat_visitors' => 0, 'stat_ts_js' => $stat_ts_js);
				$current_hour++;
			}
			else{
				$current_day++;
				$current_hour = 0;
				if(checkdate($current_month, $current_day, $current_year)){
					$stat_ts_js = (strtotime($current_year . '-' . $current_month . '-' . $current_day . ' ' . $current_hour . ':00:00') - 18000) * 1000;
					$this->hourly[] = array('stat_hour' => $current_hour, 'stat_day' => $current_day, 'stat_month' => $current_month, 'stat_year' => $current_year, 'stat_views' => 0, 'stat_visitors' => 0, 'stat_ts_js' => $stat_ts_js);
					$current_hour++;
				}
				else{
					$current_month++;
					$current_day = 1;
					$current_hour = 0;
					if(checkdate($current_month, $current_day, $current_year)){
						$stat_ts_js = (strtotime($current_year . '-' . $current_month . '-' . $current_day . ' ' . $current_hour . ':00:00') - 18000) * 1000;
						$this->hourly[] = array('stat_hour' => $current_hour, 'stat_day' => $current_day, 'stat_month' => $current_month, 'stat_year' => $current_year, 'stat_views' => 0, 'stat_visitors' => 0, 'stat_ts_js' => $stat_ts_js);
						$current_hour++;
					}
				}
			}
		}
		
		foreach($this->hourly as &$hourly){
			foreach($stats as $stat){
				if(($stat['stat_hour'] == $hourly['stat_hour']) and ($stat['stat_day'] == $hourly['stat_day']) and ($stat['stat_month'] == $hourly['stat_month']) and ($stat['stat_year'] == $hourly['stat_year'])){
					$hourly['stat_views'] = $stat['stat_views'];
				}
			}
		}
		
		$query = $this->db->prepare('SELECT stat_date, HOUR(stat_date) AS stat_hour, DAY(stat_date) as stat_day, MONTH(stat_date) as stat_month, YEAR(stat_date) as stat_year, COUNT(*) as stat_visitors FROM stats WHERE stat_duration = 0 AND stat_date >= "' . $this->stat_begin . '" AND stat_date <= "' . $this->stat_end . '" GROUP BY HOUR(stat_date), DAY(stat_date), MONTH(stat_date), YEAR(stat_date) ORDER BY YEAR(stat_date) DESC, MONTH(stat_date) DESC, DAY(stat_date) DESC, HOUR(stat_date) DESC;');
		$query->execute();
		$stats = $query->fetchAll();
		
		foreach($stats as &$stat){
			$stat['stat_visitors'] = intval($stat['stat_visitors']);
		}
		
		foreach($this->hourly as &$hourly){
			foreach($stats as $stat){
				if(($stat['stat_hour'] == $hourly['stat_hour']) and ($stat['stat_day'] == $hourly['stat_day']) and ($stat['stat_month'] == $hourly['stat_month']) and ($stat['stat_year'] == $hourly['stat_year'])){
					$hourly['stat_visitors'] = $stat['stat_visitors'];
				}
			}
		}
		
		return true;
	}
	
	public function getDurations(){
		$query = $this->db->prepare('SELECT MAX(stat_duration) AS stat_duration FROM stats WHERE stat_date >= "' . $this->stat_begin . '" AND stat_date <= "' . $this->stat_end . '" GROUP BY stat_session');
		$query->execute();
		$durations = $query->fetchAll();
		
		return $durations;
	}
	
	public function getPages(){
		$query = $this->db->prepare('SELECT COUNT(stat_page) as stat_count, stat_page FROM stats WHERE stat_date >= "' . $this->stat_begin . '" AND stat_date <= "' . $this->stat_end . '" GROUP BY stat_page ORDER BY stat_count DESC LIMIT 0, 10');
		$query->execute();
		$pages = $query->fetchAll();
		
		return $pages;
	}
	
	public function getPageTypes(){
		$query = $this->db->prepare('SELECT COUNT(stat_page) as stat_count, stat_page_type FROM stats WHERE stat_date >= "' . $this->stat_begin . '" AND stat_date <= "' . $this->stat_end . '" GROUP BY stat_page_type ORDER BY stat_count DESC LIMIT 0, 10');
		$query->execute();
		$page_types = $query->fetchAll();
		
		return $page_types;
	}
	
	public function getRecentReferrers($limit=20){
		$limit = intval($limit);
		$query = $this->db->prepare('SELECT stat_referrer, stat_date FROM stats WHERE stat_referrer != "" AND stat_date >= "' . $this->stat_begin . '" AND stat_date <= "' . $this->stat_end . '" ORDER BY stat_date DESC LIMIT 0, ' . $limit . ';');
		$query->execute();
		$referrers = $query->fetchAll();

		return $referrers;
	}
	
	public function getPopularReferrers($limit=20){
		$limit = intval($limit);
		$query = $this->db->prepare('SELECT stat_referrer, COUNT(stat_referrer) as stat_referrer_count FROM stats WHERE stat_referrer != "" AND stat_date >= "' . $this->stat_begin . '" AND stat_date <= "' . $this->stat_end . '" GROUP BY stat_referrer ORDER BY stat_referrer_count DESC LIMIT 0, ' . $limit . ';');
		$query->execute();
		$referrers = $query->fetchAll();

		return $referrers;
	}
}

?>
