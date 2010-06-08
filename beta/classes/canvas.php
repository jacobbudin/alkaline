<?php

class Canvas extends Alkaline{
	public $template;
	
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
	
	// APPEND TEMPLATE
	public function append($template){
		 $this->template .= $template . "\n";
	}
	
	// LOAD TEMPLATE FROM FILE
	public function load($file){
		 $this->template .= file_get_contents(PATH . THEMES . THEME . '/' . $file . TEMP_EXT) . "\n";
	}
	
	public function setVar($var, $value){
		// Set variable
		$this->template = str_replace('<!-- ' . $var . ' -->', $value, $this->template);
		
		// Remove conditional
		$this->template = str_replace('<!-- IF(' . $var . ') -->', '', $this->template);
		$this->template = str_replace('<!-- ENDIF(' . $var . ') -->)', '', $this->template);
		return true;
	}
	
	public function setArray($reel, $prefix, $array){
		
		// Set array; since used, remove conditionals
		$this->template = str_replace('<!-- IF(' . $reel . ') -->', '', $this->template);
		$this->template = str_replace('<!-- ENDIF(' . $reel . ') -->', '', $this->template);
		preg_match('/\<!-- LOOP\(' . $reel . '\) --\>(.*)\<!-- ENDLOOP\(' . $reel . '\) --\>/s', $this->template, $matches);
		@$loop_template = $matches[1];
		
		// Set array; since used, remove conditionals
		$this->template = str_replace('<!-- IF(COMMENTS) -->', '', $this->template);
		$this->template = str_replace('<!-- ENDIF(COMMENTS) -->', '', $this->template);
		preg_match('/\<!-- LOOP\(COMMENTS\) --\>(.*)\<!-- ENDLOOP\(COMMENTS\) --\>/s', $this->template, $matches);
		@$loop_commments_template = $matches[1];
		
		$template = '';
		
		// Loop through each set, append to empty string
		for($i = 0; $i < count($array->photos); ++$i){
			$loop = $loop_template;
			
			foreach($array->photos[$i] as $key => $value){
				if(is_array($value)){
					$value = var_export($value, true);
					$loop = str_replace('<!-- ' . strtoupper($key) . ' -->', $value, $loop);
				}
				else{
					$loop = str_replace('<!-- ' . strtoupper($key) . ' -->', $value, $loop);
				}
			}
			
			
			if(!empty($loop_commments_template)){
				$comments_template = '';
			
				for($j = 0; $j < count($array->comments); ++$j){
					$loop_commments = $loop_commments_template;
					
					foreach($array->comments[$j] as $key => $value){
						if($array->comments[$j]['photo_id'] == $array->photos[$i]['photo_id']){
							if(is_array($value)){
								$value = var_export($value, true);
								$loop_commments = str_replace('<!-- ' . strtoupper($key) . ' -->', $value, $loop_commments);
							}
							else{
								$loop_commments = str_replace('<!-- ' . strtoupper($key) . ' -->', $value, $loop_commments);
							}
						}
					}
					$comments_template .= $loop_commments;
				}
			
				// Replace comments loop template with string
				$loop = preg_replace('/\<!-- LOOP\(COMMENTS\) --\>(.*)\<!-- ENDLOOP\(COMMENTS\) --\>/s', $comments_template, $loop);
			}
			
			$template .= $loop;
		}
		
		// Replace loop template with string
		$this->template = preg_replace('/\<!-- LOOP\(' . $reel . '\) --\>(.*)\<!-- ENDLOOP\(' . $reel . '\) --\>/s', $template, $this->template);
		
		return true;
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