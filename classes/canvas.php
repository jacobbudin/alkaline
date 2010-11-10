<?php

class Canvas extends Alkaline{
	public $form_wrap;
	public $slideshow;
	public $tables;
	public $template;
	protected $value;
	
	public function __construct($template=null){
		parent::__construct();
		
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
	
	// APPEND
	// Append a string to the template
	public function append($template){
		 $this->template .= $template . "\n";
	}
	
	// APPEND LOAD
	// Append a file to the template
	public function load($file){
		$theme_folder = $this->returnConf('theme_folder');
		
		if(empty($theme_folder)){
			$this->error('No default theme selected.');
		}
		
		$this->template .= file_get_contents(parent::correctWinPath(PATH . THEMES . $theme_folder . '/' . $file . TEMP_EXT)) . "\n";
	}
	
	// VARIABLES
	public function assign($var, $value){
		// Error checking
		if(empty($value)){
			return false;
		}
		
		// Set variable, scrub to remove conditionals
		$this->template = str_ireplace('{' . $var . '}', $value, $this->template);
		$this->template = self::scrub($var, $this->template);
		return true;
	}
	
	public function assignArray($array){
		// Error checking
		if(empty($array)){
			return false;
		}
		
		if(is_array($array)){
			foreach($array as $key => $value){
				// Set variable, scrub to remove conditionals
				$this->template = str_ireplace('{' . $key . '}', $value, $this->template);
				$this->template = self::scrub($key, $this->template);
			}
		}
		else{
			return false;
		}
		
		return true;
	}
	
	// LOOPS
	// Set photo array to loop
	public function loop($array){
		$loops = array();
		
		$table_regex = implode('|', array_keys($this->tables));
		$table_regex = strtoupper($table_regex);
		
		if($this->slideshow === true){
			$this->template = '<ul id="slideshow">' . $this->template . '</ul>';
		}
		
		$matches = array();
		
		preg_match_all('#{block:(' . $table_regex . ')}(.*?){/block:\1}#si', $this->template, $matches, PREG_SET_ORDER);
		
		if(count($matches) > 0){
			$loops = array();
			
			foreach($matches as $match){
				$match[1] = strtolower($match[1]);
				
				// Wrap in <form> for commenting
				if(($match[1] == 'photos') and ($this->form_wrap === true)){
					$match[2] = '<form action="" id="photo_{PHOTO_ID}" class="photo" method="post">' . $match[2] . '</form>';
				}
				elseif(($match[1] == 'photos') and ($this->slideshow === true)){
					$match[2] = '<li><!-- ' . $match[2] . ' --></li>';
				}
				$loops[] = array('replace' => $match[0], 'reel' => $match[1], 'template' => $match[2], 'replacement' => '');
			}
		}
		else{
			return false;
		}
		
		$loop_count = count($loops);
		
		for($j = 0; $j < $loop_count; ++$j){
			if(!isset($array->$loops[$j]['reel'])){ continue; }
			
			$replacement = '';
			$reel = $array->$loops[$j]['reel'];
			
			$reel_count = count($reel);
			
			$field = $this->tables[$loops[$j]['reel']];
			
			// Determine if block has items
			if($reel_count > 0){
				$done_once = array();
				for($i = 0; $i < $reel_count; ++$i){
					if(!empty($reel[$i][$field]) and !in_array($reel[$i][$field], $done_once)){
						$loop_template = $loops[$j]['template'];
			
						foreach($reel[$i] as $key => $value){
							if(is_array($value)){
								$value = var_export($value, true);
							}
							
							$this->value = $value;
							
							$loop_template = str_ireplace('{' . $key . '}', $this->value, $loop_template);
							$loop_template = preg_replace('#\{' . $key . '\|([a-z0-9_]+)\}#esi', "Canvas::\\1()", $loop_template);
							
							if(!empty($this->value)){
								$loop_template = self::scrub($key, $loop_template);
							}
						}
						
						// If tied to photo array (either sub or super), execute inner blocks
						if(!empty($reel[$i]['photo_id'])){
							$loop_template = self::loopSub($array, $loop_template, $reel[$i]['photo_id']);
						}
						$done_once[] = $reel[$i][$field];
					}
					else{
						$loop_template = '';
					}
					$replacement .= $loop_template;
				}
				
				$this->template = str_replace($loops[$j]['replace'], $replacement, $this->template, $int);
				$this->template = self::scrub($loops[$j]['reel'], $this->template);
			}
			else{
				$this->template = str_replace($loops[$j]['replace'], '', $this->template);
			}
		}
		
		return true;
	}
	
	public function slideshow($bool=true){
		if(!is_bool($bool)){ return false; }
		
		$this->slideshow = $bool;
		return true;
	}
	
	public function wrapForm($bool=true){
		if(!is_bool($bool)){ return false; }
		
		$this->form_wrap = $bool;
		return true;
	}
	
	// Set subarrays to loop
	protected function loopSub($array, $template, $photo_id){
		$loops = array();
		
		$table_regex = implode('|', array_keys($this->tables));
		$table_regex = strtoupper($table_regex);
		
		$matches = array();
		
		preg_match_all('#{block:(' . $table_regex . ')}(.*?){/block:\1}#si', $template, $matches, PREG_SET_ORDER);
		
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
		
		$loop_count = count($loops);
		
		for($j = 0; $j < $loop_count; ++$j){
			$replacement = '';
			$reel = $array->$loops[$j]['reel'];
			
			$reel_count = count($reel);
			
			if($reel_count > 0){
				for($i = 0; $i < $reel_count; ++$i){
					$loop_template = '';
					
					if(!empty($reel[$i]['photo_id'])){
						if($reel[$i]['photo_id'] == $photo_id){
							if(empty($loop_template)){
								$loop_template = $loops[$j]['template'];
							}
							foreach($reel[$i] as $key => $value){
								if(is_array($value)){
									$value = var_export($value, true);
								}
								
								$this->value = $value;
								
								$loop_template = str_ireplace('{' . $key . '}', $this->value, $loop_template);
								$loop_template = preg_replace('#\{' . $key . '\|([a-z0-9_]+)\}#esi', "Canvas::\\1()", $loop_template);
								
								if(!empty($this->value)){
									$loop_template = self::scrub($key, $loop_template);
								}
							}
						}
					}
					else{
						$loop_template = '';
					}
					$replacement .= $loop_template;
				}
			}
			
			$loops[$j]['replacement'] = $replacement;
		}
		
		foreach($loops as $loop){
			$template = str_replace($loop['replace'], $loop['replacement'], $template);
			$template = self::scrub($loop['reel'], $template);
		}
		
		return $template;
	}
	
	public function makeURL(){
		return Alkaline::makeURL($this->value);
	}
	
	// PREPROCESS
	// Remove conditionals after successful variable, loop placement
	public function scrub($var, $template){
		$template = str_ireplace('{if:' . $var . '}', '', $template);
		if(stripos($template, '{else:' . $var . '}')){
			$template = preg_replace('#{else:' . $var . '}(.*?){/if:' . $var . '}#is', '', $template);
		}
		$template = str_ireplace('{/if:' . $var . '}', '', $template);
		return $template;
	}
	
	// Remove unmatched conditionals before displaying
	public function scrubEmpty($template){
		preg_match_all('#{if:([A-Z0-9_]*)}(.*?){/if:\1}#si', $template, $matches, PREG_SET_ORDER);
		
		if(count($matches) > 0){
			$loops = array();
			
			foreach($matches as $match){
				$loops[] = array('replace' => $match[0], 'var' => $match[1], 'template' => $match[2], 'replacement' => '');
			}
		}
		
		$loop_count = count($loops);
		
		for($j = 0; $j < $loop_count; ++$j){
			if(stripos($loops[$j]['template'], '{else:' . $loops[$j]['var'] . '}')){
				$loops[$j]['replacement'] = $loops[$j]['template'];
				$loops[$j]['replacement'] = preg_replace('#(?:.*){else:' . $loops[$j]['var'] . '}(.*)#is', '$1', $loops[$j]['replacement']);
			}
		}
		
		foreach($loops as $loop){
			$template = str_replace($loop['replace'], $loop['replacement'], $template);
		}
		
		return $template;
	}
	
	// ORBIT
	// Find Orbit hooks and process them
	protected function initOrbit(){
		$orbit = new Orbit();
		
		$matches = array();
		preg_match_all('#{orbit:([A-Z0-9_]*)}#is', $this->template, $matches, PREG_SET_ORDER);
		
		if(count($matches) > 0){
			$hooks = array();
			
			foreach($matches as $match){
				$hook = strtolower($match[1]);
				$hooks[] = array('replace' => $match[0], 'hook' => $hook);
			}
		}
		else{
			return false;
		}
		
		foreach($hooks as $hook){
			ob_start();
			
			// Execute Orbit hook
			$orbit->hook($hook['hook']);
			$content = ob_get_contents();
			
			// Replace contents
			$this->template = str_ireplace($hook['replace'], $content, $this->template);
			ob_end_clean();
		}
	}
	
	// BLOCKS
	// Find Canvas blocks and process them
	protected function initBlocks(){
		$matches = array();
		preg_match_all('#{canvas:([A-Z0-9_]*)}#is', $this->template, $matches, PREG_SET_ORDER);
		
		if(count($matches) > 0){
			$blocks = array();
			
			foreach($matches as $match){
				$block = strtolower($match[1]);
				$blocks[] = array('replace' => $match[0], 'block' => $block);
			}
		}
		else{
			return false;
		}
		
		foreach($blocks as $block){
			$path = PATH . BLOCKS . $block['block'] . '.php';
			
			if(is_file($path)){
				ob_start();

				// Include block
				include($path);
				$content = ob_get_contents();
				
				// Replace contents
				$this->template = str_ireplace($block['replace'], $content, $this->template);
				ob_end_clean();
			}
		}
	}
	
	// PROCESS
	public function generate(){
		// Add copyright information
		$this->assign('Copyright', parent::copyright);
		
		// Process Blocks, Orbit
		$this->initBlocks();
		$this->initOrbit();
		
		// Remove unused conditionals, replace with ELSEIF as available
		$this->template = $this->scrubEmpty($this->template);
		
		return true;
	}
	
	// DISPLAY
	public function display(){
		self::generate();
		
		// Echo after evaluating
		echo @eval('?>' . $this->template);
	}
}

?>