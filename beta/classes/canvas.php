<?php

class Canvas extends Alkaline{
	public $tables;
	public $template;
	
	public function __construct($template=null){
		parent::__construct();
		
		$this->tables = array('photos', 'comments', 'tags');
		$this->template = (empty($template)) ? '' : $template . "\n";
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function __toString(){
		self::generate();
		
		// Return unevaluated
		return $this->template;
	}
	
	// APPEND TEMPLATE
	public function append($template){
		 $this->template .= $template . "\n";
	}
	
	// LOAD TEMPLATE FROM FILE
	public function load($file){
		 $this->template .= file_get_contents(PATH . THEMES . THEME . '/' . $file . TEMP_EXT) . "\n";
	}
	
	// ASSIGN VARIABLE
	public function assign($var, $value){
		// Set variable, scrub to remove conditionals
		$this->template = str_replace('<!-- ' . $var . ' -->', $value, $this->template);
		$this->template = self::scrub($var, $this->template);
		return true;
	}
	
	// REMOVE CONDITIONALS
	public function scrub($var, $template){
		$template = str_replace('<!-- IF(' . $var . ') -->', '', $template);
		$template = preg_replace('/(?=\<\!-- ELSEIF\(' . $var . '\) --\>|.*)(?=.*?)\<\!-- ENDIF\(' . $var . '\) --\>/s', '', $template);
		return $template;
	}
	
	// SET PHOTO CLASS ARRAY TO LOOP
	public function loop($array){
		$loops = array();
		
		$table_regex = implode('|', $this->tables);
		$table_regex = strtoupper($table_regex);
		
		$matches = array();
		
		preg_match_all('/\<!-- LOOP\((' . $table_regex . ')\) --\>(.*?)\<!-- ENDLOOP\(\1\) --\>/s', $this->template, $matches, PREG_SET_ORDER);
		
		if(count($matches) > 0){
			$loops = array();
			
			foreach($matches as $match){
				$match[1] = strtolower($match[1]);
				$loops[] = array('replace' => $match[0], 'reel' => $match[1], 'template' => $match[2], 'replacement' => '');
			}
		}
		else{
			return false;
		}
		
		for($j = 0; $j < count($loops); ++$j){
			$replacement = '';
			$reel = $array->$loops[$j]['reel'];
			
			for($i = 0; $i < count($reel); ++$i){
				$loop_template = $loops[$j]['template'];
				
				foreach($reel[$i] as $key => $value){
					if(is_array($value)){
						$value = var_export($value, true);
						$loop_template = str_replace('<!-- ' . strtoupper($key) . ' -->', $value, $loop_template);
					}
					else{
						$loop_template = str_replace('<!-- ' . strtoupper($key) . ' -->', $value, $loop_template);
					}
				}
				
				$loop_template = self::loopSub($array, $loop_template, $reel[$i]['photo_id']);
				
				$replacement .= $loop_template;
			}
			
			$loops[$j]['replacement'] = $replacement;
		}
		
		foreach($loops as $loop){
			$this->template = str_replace($loop['replace'], $loop['replacement'], $this->template);
		}
		
		return true;
	}
	
	protected function loopSub($array, $template, $photo_id){
		$loops = array();
		
		$table_regex = implode('|', $this->tables);
		$table_regex = strtoupper($table_regex);
		
		$matches = array();
		
		preg_match_all('/\<!-- LOOP\((' . $table_regex . ')\) --\>(.*?)\<!-- ENDLOOP\(\1\) --\>/s', $template, $matches, PREG_SET_ORDER);
		
		if(count($matches) > 0){
			$loops = array();
			
			foreach($matches as $match){
				$match[1] = strtolower($match[1]);
				$loops[] = array('replace' => $match[0], 'reel' => $match[1], 'template' => $match[2], 'replacement' => '');
			}
		}
		else{
			return $template;
		}
		
		for($j = 0; $j < count($loops); ++$j){
			$replacement = '';
			$reel = $array->$loops[$j]['reel'];
			
			for($i = 0; $i < count($reel); ++$i){
				$loop_template = '';
				
				if($reel[$i]['photo_id'] == $photo_id){
					if(empty($loop_template)){
						$loop_template = $loops[$j]['template'];
					}
					foreach($reel[$i] as $key => $value){
						if(is_array($value)){
							$value = var_export($value, true);
							$loop_template = str_replace('<!-- ' . strtoupper($key) . ' -->', $value, $loop_template);
						}
						else{
							$loop_template = str_replace('<!-- ' . strtoupper($key) . ' -->', $value, $loop_template);
						}
					}
				}
				
				$replacement .= $loop_template;
			}
			
			$loops[$j]['replacement'] = $replacement;
		}
		
		foreach($loops as $loop){
			if(!empty($loop['replacement'])){
				$template = str_replace($loop['replace'], $loop['replacement'], $template);
				$template = self::scrub(strtoupper($loop['reel']), $template);
			}
		}
		
		return $template;
	}
	
	public function generate(){
		// Remove unused conditionals, replace with ELSEIF as available
		$this->template = preg_replace('/\<!-- IF\([A-Z0-9_]*\) --\>(.*?)\<!-- ELSEIF\([A-Z0-9_]*\) --\>(.*?)\<!-- ENDIF\([A-Z0-9_]*\) --\>/s', '$2', $this->template);
		$this->template = preg_replace('/\<!-- IF\([A-Z0-9_]*\) --\>(.*?)\<!-- ENDIF\([A-Z0-9_]*\) --\>/s', '', $this->template);
		
		return true;
	}
	
	public function display(){
		self::generate();
		
		// Echo after evaluating
		echo @eval('?>' . $this->template);
	}
	
}

?>