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

class Canvas extends Alkaline{
	public $form_wrap;
	public $slideshow;
	public $tables;
	public $template;
	protected $value;
	protected $objects;
	
	/**
	 * Initiates Canvas class
	 *
	 * @param string $template Template
	 */
	public function __construct($template=null){
		parent::__construct();
		
		$this->objects = array();
		$this->template = (empty($template)) ? '' : $template . "\n";
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	/**
	 * Return template unevaluated
	 *
	 * @return string
	 */
	public function __toString(){
		self::generate();
		
		// Return unevaluated
		return $this->template;
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
		
		$this->template = $orbit->hook('canvas', $this->template, $this->template);
	}
	
	/**
	 * Append a string to the template
	 *
	 * @param string $template String to append
	 * @return void
	 */
	public function append($template){
		 $this->template .= $template . "\n";
	}
	
	/**
	 * Append a file's contents to the template
	 *
	 * @param string $filename Filename (pulled from the installation's theme folder)
	 * @return void
	 */
	public function load($filename){
		if(!empty($_REQUEST['theme'])){
			$theme_folder = strip_tags($_REQUEST['theme']);
		}
		else{
			$theme_folder = $this->returnConf('theme_folder');
		}
		
		if(empty($theme_folder)){
			$this->addError(E_USER_ERROR, 'No default theme selected');
		}
		
		$this->template .= file_get_contents(parent::correctWinPath(PATH . THEMES . $theme_folder . '/' . $filename . TEMP_EXT)) . "\n";
	}
	
	/**
	 * Assign a template variable
	 *
	 * @param string $var
	 * @param string $value
	 * @return bool True if successful
	 */
	public function assign($var, $value){
		// Error checking
		if(empty($value)){
			return false;
		}
		
		// Set variable, scrub to remove conditionals
		$this->template = str_ireplace('{' . $var . '}', $value, $this->template);
		$this->template = self::scrub($var, $this->template);
		
		preg_match_all('#\{' . $var . '\|([^\}]+)\}#si', $this->template, $vars_full, PREG_SET_ORDER);
		
		foreach($vars_full as $var_full){
			$this->value = $value;
			$this->value = $this->filter($var_full[1]);
			$this->template = str_ireplace($var_full[0], $this->value, $this->template);
		}
		
		if(!empty($this->value)){
			$this->template = self::scrub($var, $this->template);
		}
		
		unset($this->value);
		
		return true;
	}
	
	/**
	 * Assign template variables via an associative array
	 *
	 * @param array $array
	 * @return bool True if successful
	 */
	public function assignArray($array){
		// Error checking
		if(empty($array)){
			return false;
		}
		
		if(is_array($array)){
			foreach($array as $key => $value){
				if(isset($value)){
					// Set variable, scrub to remove conditionals
					$this->template = str_ireplace('{' . $key . '}', $value, $this->template);
					$this->template = self::scrub($key, $this->template);
					
					preg_match_all('#\{' . $key . '\|([^\}]+)\}#si', $this->template, $keys_full, PREG_SET_ORDER);
					
					foreach($keys_full as $key_full){
						$this->value = $value;
						$this->value = $this->filter($key_full[1]);
						$this->template = str_ireplace($key_full[0], $this->value, $this->template);
					}
					
					if(!empty($this->value)){
						$this->template = self::scrub($key, $this->template);
					}
					
					unset($this->value);
				}
			}
		}
		else{
			return false;
		}
		
		return true;
	}
	
	/**
	 * Set the <title> variable (specially formatted)
	 *
	 * @param string $title
	 * @return bool True if successful
	 */
	public function setTitle($title){
		$source = $this->returnConf('web_title');
		
		if(empty($title)){
			$title = $source;
		}
		else{
			$format = $this->returnConf('web_title_format');
			if($format == 'emdash'){
				$title .= ' &#8212; ' . $source;
			}
			else{
				$title = $source . ': ' . $title;
			}
		}
		
		// Set variable
		return $this->assign('TITLE', $title);
	}
	
	/**
	 * Set a image object to process blocks and nested blocks
	 *
	 * @param Image $object Image object
	 * @return bool True if successful
	 */
	public function loop($object){
		$class = strtolower(get_class($object));
		$this->objects[$class] = $object;
		
		$table_regex = implode('|', array_keys($this->tables));
		$table_regex = strtoupper($table_regex);
		
		if($this->slideshow === true){
			$this->template = '<ul id="slideshow">' . $this->template . '</ul>';
		}
		
		$matches = array();
		preg_match_all('#{block:(' . $table_regex . ')}(.*?){/block:\1}#si', $this->template, $matches, PREG_SET_ORDER);
		
		$loops = array();
		
		if(count($matches) > 0){
			foreach($matches as $match){
				$match[1] = strtolower($match[1]);
				
				// Wrap in <form> for commenting
				if(($match[1] == 'images') and ($this->form_wrap === true)){
					$match[2] = '<form action="" method="post">' . $match[2] . '</form>';
				}
				elseif(($match[1] == 'images') and ($this->slideshow === true)){
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
			if(!isset($object->$loops[$j]['reel'])){ continue; }
			
			$replacement = '';
			$reel = $object->$loops[$j]['reel'];
			
			// var_dump($loops[$j]['reel']);
			
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
							// $loop_template = preg_replace('#\{' . $key . '\|([^\}]+)\}#esi', "Canvas::filter('\\1')", $loop_template);
							
							preg_match_all('#\{' . $key . '\|([^\}]+)\}#si', $loop_template, $keys_full, PREG_SET_ORDER);
							
							foreach($keys_full as $key_full){
								$this->value = $value;
								$this->value = $this->filter($key_full[1]);
								$loop_template = str_ireplace($key_full[0], $this->value, $loop_template);
							}
							
							if(!empty($this->value)){
								$loop_template = self::scrub($key, $loop_template);
							}
							
							unset($this->value);
						}
						
						// If tied to image array (either sub or super), execute inner blocks
						if(!empty($reel[$i]['image_id'])){
							$loop_template = self::loopSub($object, $loop_template, 'image_id', $reel[$i]['image_id']);
						}
						if(!empty($reel[$i]['post_id'])){
							$loop_template = self::loopSub($object, $loop_template, 'post_id', $reel[$i]['post_id']);
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
	
	/**
	 * Process nested blocks
	 *
	 * @param string $array 
	 * @param string $template 
	 * @param string $field
	 * @param string $id 
	 * @return void
	 */
	protected function loopSub($array, $template, $field, $id){
		$loops = array();
		
		$table_regex = implode('|', array_keys($this->tables));
		
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
					
					if(!empty($reel[$i][$field])){
						if($reel[$i][$field] == $id){
							if(empty($loop_template)){
								$loop_template = $loops[$j]['template'];
							}
							foreach($reel[$i] as $key => $value){
								if(is_array($value)){
									$value = var_export($value, true);
								}
								
								$this->value = $value;
								
								$loop_template = str_ireplace('{' . $key . '}', $this->value, $loop_template);
								// $loop_template = preg_replace('#\{' . $key . '\|([^\}]+)\}#esi', "Canvas::filter('\\1')", $loop_template);
								
								preg_match_all('#\{' . $key . '\|([^\}]+)\}#si', $loop_template, $keys_full, PREG_SET_ORDER);
								
								foreach($keys_full as $key_full){
									$this->value = $value;
									$this->value = $this->filter($key_full[1]);
									$loop_template = str_ireplace($key_full[0], $this->value, $loop_template);
								}
													
								if(!empty($this->value)){
									$loop_template = self::scrub($key, $loop_template);
								}
								
								unset($this->value);
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
		
		$reels = array();
		
		foreach($loops as $loop){
			if(!empty($loop['replacement'])){
				$template = str_replace($loop['replace'], $loop['replacement'], $template);
				$template = self::scrub($loop['reel'], $template);
			}
		}
		
		$table_regex = implode('|', array_keys($this->tables));
		preg_match_all('#{if:(' . $table_regex . ')}(.*?){/if:\1}#si', $template, $matches, PREG_SET_ORDER);
		
		$loops = array();
		
		if(count($matches) > 0){
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
	
	/**
	 * Set template as a slideshow
	 *
	 * @param bool $bool True if slideshow
	 * @return bool True if successful
	 */
	public function slideshow($bool=true){
		if(!is_bool($bool)){ return false; }
		
		$this->slideshow = $bool;
		return true;
	}
	
	/**
	 * Set a template to accept comments
	 *
	 * @param bool $bool True if wrap in <form> for comments
	 * @return bool True if successful
	 */
	public function wrapForm($bool=true){
		if(!is_bool($bool)){ return false; }
		
		$this->form_wrap = $bool;
		return true;
	}
	
	/**
	 * Process PHP definitions
	 *
	 * @return void
	 */
	public function initDefines(){
		$loops = array();
		
		$matches = array();
		preg_match_all('#{define:([a-z0-9\_\-]+)}#si', $this->template, $matches, PREG_SET_ORDER);
		
		if(count($matches) > 0){
			foreach($matches as $match){
				$loops[] = $match[1];
			}
		}
		else{
			return;
		}
		
		$loops = array_unique($loops);
		
		foreach($loops as $define){
			if(defined($define)){
				$definition = constant($define);
			}
			elseif(defined(strtoupper($define))){
				$definition = constant(strtoupper($define));
			}
			elseif(defined(strtolower($define))){
				$definition = constant(strtolower($define));
			}
			
			if(isset($definition)){
				$this->template = str_ireplace('{define:' . $define . '}', $definition, $this->template);
			}
		}
	}
	
	/**
	 * Process block counts
	 *
	 * @return void
	 */
	public function initCounts(){
		$loops = array();
		
		$table_regex = implode('|', array_keys($this->tables));
		$table_regex = strtoupper($table_regex);
		
		$matches = array();
		preg_match_all('#{count:(' . $table_regex . ')}#si', $this->template, $matches, PREG_SET_ORDER);
		
		if(count($matches) > 0){
			foreach($matches as $match){
				$loops[] = strtolower($match[1]);
			}
		}
		else{
			return;
		}
		
		$loops = array_unique($loops);
		
		foreach($loops as $reel){
			$count = 0;
			foreach($this->objects as $object){
				if(property_exists($object, $reel)){
					$field = $this->tables[$reel];
					$ids = array();
					foreach($object->$reel as $item){
						$ids[] = $item[$field];
					}
					$replacement = count(array_unique($ids));
				}
				else{
					$replacement = 0;
				}
				if($replacement > $count){
					$count = $replacement;
				}
			}
			$this->template = str_ireplace('{count:' . $reel . '}', $count, $this->template);
		}
	}
	
	
	// FILTERS
	
	/**
	 * Execute Canvas filters
	 *
	 * @param string $filters Filters 
	 * @return void
	 */
	public function filter($filters){
		$filters = explode('|', $filters);
		foreach($filters as $filter){
			if(method_exists('Canvas', $filter)){
				$this->value = Canvas::$filter();
			}
		}
		return $this->value;
	}
	
	/**
	 * Perform urlencode() filter
	 *
	 * @return string
	 */
	public function urlencode(){
		return urlencode($this->value);
	}
	
	/**
	 * Fit string by 50 characters, cut at word
	 *
	 * @return void
	 */
	public function fitStringByWord50(){
		return Alkaline::fitStringByWord($this->value, 50);
	}
	
	/**
	 * Make a relative time
	 *
	 * @return void
	 */
	public function reltime(){
		return Alkaline::formatRelTime($this->value, null, '(Unknown)');
	}
	
	/**
	 * Fit string by 100 characters, cut at word
	 *
	 * @return void
	 */
	public function fitStringByWord100(){
		return Alkaline::fitStringByWord($this->value, 100);
	}
	
	/**
	 * Fit string by 250 characters, cut at word
	 *
	 * @return void
	 */
	public function fitStringByWord250(){
		return Alkaline::fitStringByWord($this->value, 250);
	}
	
	/**
	 * Fit string by 500 characters, cut at word
	 *
	 * @return void
	 */
	public function fitStringByWord500(){
		return Alkaline::fitStringByWord($this->value, 500);
	}
	
	/**
	 * Fit string by 1000 characters, cut at word
	 *
	 * @return void
	 */
	public function fitStringByWord1000(){
		return Alkaline::fitStringByWord($this->value, 1000);
	}
	
	/**
	 * Perform Alkaline::makeURL filter
	 *
	 * @return string
	 */
	public function urlize(){
		return Alkaline::makeURL($this->value);
	}
	
	/**
	 * Convert number to words
	 *
	 * @return string
	 */
	public function alpha(){
		return Alkaline::numberToWords($this->value);
	}
	
	/**
	 * Convert number to words (except zero)
	 *
	 * @return string
	 */
	public function alpha0(){
		if($this->value != 0){
			$this->value = Alkaline::numberToWords($this->value);
		}
		return $this->value;
	}
	
	/**
	 * Add S to make plural
	 *
	 * @return void
	 */
	public function pluralize(){
		if($this->value != 1){
			$this->value = 's';
		}
		else{
			$this->value = '';
		}
		return $this->value;
	}
	
	/**
	 * Make words uppercase
	 *
	 * @return string
	 */
	public function ucwords(){
		return ucwords($this->value);
	}
	
	/**
	 * Make first word uppercase
	 *
	 * @return string
	 */
	public function ucfirst(){
		return ucfirst($this->value);
	}
	
	
	// PREPROCESSING
	
	/**
	 * Remove conditionals after successful variable, loop placement
	 *
	 * @param string $var Variable
	 * @param string $template Template
	 * @return string Template
	 */
	public function scrub($var, $template){
		$template = str_ireplace('{if:' . $var . '}', '', $template);
		if(stripos($template, '{else:' . $var . '}')){
			$template = preg_replace('#{else:' . $var . '}(.*?){/if:' . $var . '}#is', '', $template);
		}
		$template = str_ireplace('{/if:' . $var . '}', '', $template);
		return $template;
	}
	
	/**
	 * Remove unmatched conditionals before displaying
	 *
	 * @param string $template Template
	 * @return string Template
	 */
	public function scrubEmpty($template){
		preg_match_all('#{if:([a-z0-9\_\-]*)}(.*?){/if:\1}#si', $template, $matches, PREG_SET_ORDER);
		
		$loops = array();
		
		if(count($matches) > 0){
			foreach($matches as $match){
				$loops[] = array('replace' => $match[0], 'var' => $match[1], 'template' => $match[2], 'replacement' => '');
			}
		}
		
		$loop_count = count($loops);
		
		for($j = 0; $j < $loop_count; ++$j){
			if(stripos($loops[$j]['template'], '{else:' . $loops[$j]['var'] . '}') !== false){
				$loops[$j]['replacement'] = $loops[$j]['template'];
				$loops[$j]['replacement'] = preg_replace('#(?:.*){else:' . $loops[$j]['var'] . '}(.*)#is', '$1', $loops[$j]['replacement'], -1, $count);
			}
		}
		
		foreach($loops as $loop){
			$template = str_replace($loop['replace'], $loop['replacement'], $template);
		}
		
		if($this->returnConf('canvas_remove_unused')){
			$template = preg_replace('#\{[a-z0-9_\-]*}#si', '', $template);
		}
		
		return $template;
	}
	
	/**
	 * Process Orbit hooks as insertions {hook:Hookname}
	 *
	 * @return void
	 */
	protected function initOrbit(){
		$orbit = new Orbit();
		
		$matches = array();
		preg_match_all('#{hook:([a-z0-9_\-]*)}#is', $this->template, $matches, PREG_SET_ORDER);
		
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
	
	/**
	 * Process configuration insertions {config:Configname}
	 *
	 * @return void
	 */
	protected function initConfig(){
		$matches = array();
		preg_match_all('#{config:([a-z0-9_\-]*)}#is', $this->template, $matches, PREG_SET_ORDER);
		
		if(count($matches) > 0){
			$configs = array();
			
			foreach($matches as $match){
				$config = strtolower($match[1]);
				$configs[] = array('replace' => $match[0], 'config' => $config);
			}
		}
		else{
			return false;
		}
		
		foreach($configs as $config){
			// Return configuration
			$content = $this->returnConf($config['config']);
			
			// Replace contents
			$this->template = str_ireplace($config['replace'], $content, $this->template);
		}
	}
	
	/**
	 * Process Canvas includes as insertions {include:Filename}
	 *
	 * @return void
	 */
	protected function initIncludes(){
		$matches = array();
		preg_match_all('#{include:([a-z0-9_\-]*)}#is', $this->template, $matches, PREG_SET_ORDER);
		
		if(count($matches) > 0){
			$includes = array();
			
			foreach($matches as $match){
				$include = strtolower($match[1]);
				$includes[] = array('replace' => $match[0], 'include' => $include);
			}
		}
		else{
			return false;
		}
		
		foreach($includes as $include){
			$path = PATH . INCLUDES . $include['include'] . '.php';
			
			if(is_file($path)){
				ob_start();

				// Include include
				include($path);
				$content = ob_get_contents();
				
				// Replace contents
				$this->template = str_ireplace($include['replace'], $content, $this->template);
				ob_end_clean();
			}
		}
	}
	
	// PROCESSING
	
	/**
	 * Generate template
	 *
	 * @return void
	 */
	public function generate(){
		// Add copyright information
		$this->assign('Copyright', parent::copyright);
		$this->assign('Search_Uri', LOCATION . BASE . 'search' . URL_CAP);
		$this->assign('Results_Uri', LOCATION . BASE . 'results' . URL_CAP);
		$this->assign('Atom_Uri', LOCATION . BASE . 'atom' . URL_CAP);
		$this->assign('Blog_Uri', LOCATION . BASE . 'blog' . URL_CAP);
		
		// Process Counts, Blocks, Orbit, Config
		$this->initDefines();
		$this->initCounts();
		$this->initIncludes();
		$this->initOrbit();
		$this->initConfig();
		
		// Remove unused conditionals and insertions
		$this->template = $this->scrubEmpty($this->template);
	}
	
	/**
	 * Generate template, execute PHP, and echo results
	 *
	 * @return string
	 */
	public function display(){
		self::generate();
		
		// Echo after evaluating
		echo @eval('?>' . $this->template);
	}
}

?>