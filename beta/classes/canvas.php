<?php

class Canvas extends Alkaline{
	public $arrays;
	public $template;
	public $variables;
	
	public function __construct($template=null){
		parent::__construct();
		
		$this->template = '';
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	public function append($template){
		 $this->template .= $template;
	}
	
	public function load($file){
		 $this->template .= file_get_contents(PATH . THEMES . THEME . '/' . $file . TEMP_EXT);
	}
	
	public function setVar($var, $value){
		// Set variable; since used, remove conditionals
		$this->variables->$var = $value;
		$this->template = preg_replace('(\<!-- ' . $var . ' --\>)', $value, $this->template);
		$this->template = preg_replace('(\<!-- IF\(' . $var . '\) --\>)', '', $this->template);
		$this->template = preg_replace('(\<!-- ENDIF\(' . $var . '\) --\>)', '', $this->template);
	}
	
	public function setArray($array, $prefix, $value){
		// Set array; since used, remove conditionals
		$this->arrays->$array = $value;
		$this->template = preg_replace('(\<!-- IF\(' . $array . '\) --\>)', '', $this->template);
		$this->template = preg_replace('(\<!-- ENDIF\(' . $array . '\) --\>)', '', $this->template);
		preg_match('/\<!-- LOOP\(' . $array . '\) --\>(.*)\<!-- ENDLOOP\(' . $array . '\) --\>/s', $this->template, $matches);
		@$loop_template = $matches[1];
		$template = '';
		foreach($this->arrays->$array as $units){
			$loop = $loop_template;
			foreach($units as $key => $value){
				$loop = @str_replace('<!-- ' . strtoupper($key) . ' -->', $value, $loop);
			}
			$template .= $loop;
		}
		$this->template = preg_replace('/\<!-- LOOP\(' . $array . '\) --\>(.*)\<!-- ENDLOOP\(' . $array . '\) --\>/s', $template, $this->template);
	}
	
	public function output(){
		// Remove unused conditionals, replace with ELSEIF as available
		$this->template = preg_replace('/\<!-- IF\([A-Z0-9_]*\) --\>(.*?)\<!-- ELSEIF\([A-Z0-9_]*\) --\>(.*?)\<!-- ENDIF\([A-Z0-9_]*\) --\>/s', '$2', $this->template);
		$this->template = preg_replace('/\<!-- IF\([A-Z0-9_]*\) --\>(.*?)\<!-- ENDIF\([A-Z0-9_]*\) --\>/s', '', $this->template);
		
		// Evaluate and echo
		echo @eval('?>' . $this->template);
	}
}

?>