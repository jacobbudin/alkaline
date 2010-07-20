<?php

class Stat extends Alkaline{
	public $daily;
	public $stat_begin;
	public $stat_begin_ts;
	public $stat_end;
	public $stat_end_ts;
	
	public function __construct($stat_begin=null, $stat_end=null){
		parent::__construct();
		
		if(empty($stat_begin)){
			$this->stat_begin_ts = strtotime('-30 days');
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
	public function getDaily(){
		$query = $this->db->prepare('SELECT DAY(stat_date) as stat_day, MONTH(stat_date) as stat_month, YEAR(stat_date) as stat_year, COUNT(*) as stat_views FROM stats WHERE stat_date >= "' . $this->stat_begin . '" AND stat_date <= "' . $this->stat_end . '" GROUP BY DAY(stat_date), MONTH(stat_date), YEAR(stat_date) ORDER BY YEAR(stat_date) DESC, MONTH(stat_date) DESC, DAY(stat_date) DESC;');
		$query->execute();
		$stats = $query->fetchAll();
		
		$this->daily = array();
		$next = date('Y-m-d', $this->stat_end_ts + 86400);
		
		$next_day = substr($next, 8, 2);
		$next_month = substr($next, 5, 2);
		$next_year = substr($next, 0, 4);
		
		$current_day = substr($this->stat_begin, 8, 2);
		$current_month = substr($this->stat_begin, 5, 2);
		$current_year = substr($this->stat_begin, 0, 4);
		
		while(!(($next_day == $current_day) and ($next_month == $current_month) and ($next_year == $current_year))){
			if(checkdate($current_month, $current_day, $current_year)){
				$this->daily[] = array('stat_day' => $current_day, 'stat_month' => $current_month, 'stat_year' => $current_year, 'stat_views' => 0);
				$current_day++;
			}
			else{
				$current_month++;
				$current_day = 1;
				if(checkdate($current_month, $current_day, $current_year)){
					$this->daily[] = array('stat_day' => $current_day, 'stat_month' => $current_month, 'stat_year' => $current_year, 'stat_views' => 0);
					$current_day++;
				}
				else{
					$current_year++;
					$current_month = 1;
					$current_day = 1;
					if(checkdate($current_month, $current_day, $current_year)){
						$this->daily[] = array('stat_day' => $current_day, 'stat_month' => $current_month, 'stat_year' => $current_year, 'stat_views' => 0);
						$current_day++;
					}
				}
			}
		}
		
		foreach($this->daily as &$daily){
			foreach($stats as $stat){
				if(($stat['stat_day'] == $daily['stat_day']) and ($stat['stat_month'] == $daily['stat_month']) and ($stat['stat_year'] == $daily['stat_year'])){
					$daily = $stat;
				}
			}
		}
	}
}

?>
