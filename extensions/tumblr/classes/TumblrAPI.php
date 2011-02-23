<?php

/**
 * Modified version of Evan Walsh's Tumblr class
 * (http://code.evanwalsh.net/Projects/Tumblr).
 *
 * Modified by Alex Dunae (dialect.ca).
 *
 * This version adds methods to cache Tumblr API data.
 * @package   Tumblr
 */
class TumblrAPI{

        /*
                Tumblr API PHP class by Evan Walsh
                http://code.evanwalsh.net/Projects/Tumblr
        */

		var $_cache_duration;
		var $_cache_path = '';


        function init($email,$password,$generator = "Tumblr PHP class"){
                $this->email = $email;
                $this->password = $password;
                $this->generator = $generator;
        }

		/**
		* Set up read cache.
		*
		* @param int     $duration   Number of seconds to cache data
		* @param string  $path       Where to store the cache files (e.g. '_cache/')
		*/
		function init_cache($duration, $path = '') {
			$this->_cache_duration = $duration;
			$this->_cache_path = $path;
		}

        function read($url,$json = false){
				$output = $this->_read_from_cache($url, $json);

				if(!empty($output))
					return $output;

                $url = "$url/api/read";
                if($json){
                        $url .= "/json";
                }

                $url .= '?filter=text';
                if(ini_get("allow_url_fopen")){
                        $output = file_get_contents($url);
                        $this->_save_to_cache($url, $json, $output);
                }
                elseif(function_exists("curl_version")){
                        $c = curl_init($url);
                        curl_setopt($c,CURLOPT_HEADER,1);
                        curl_setopt($c,CURLOPT_RETURNTRANSFER,1);
                        $output = curl_exec($c);
                        $this->_save_to_cache($url, $json, $output);
                }
                else{
                        $output = "error: cannot fetch file";
                }
                return $output;
        }

        function post($data){
                if(function_exists("curl_version")){
                        $data["email"] = $this->email;
                        $data["password"] = $this->password;
                        $data["generator"] = $this->generator;
                        $request = http_build_query($data);
                        $c = curl_init('http://www.tumblr.com/api/write');
                        curl_setopt($c,CURLOPT_POST,true);
                        curl_setopt($c,CURLOPT_POSTFIELDS,$request);
                        curl_setopt($c,CURLOPT_RETURNTRANSFER,true);
                        $return = curl_exec($c);
                        $status = curl_getinfo($c,CURLINFO_HTTP_CODE);
                        curl_close($c);
                        if($status == "201"){
                            return true;
                        }
                        elseif($status == "403"){
                            return false;
                        }
                        else{
                            return "error: $return";
                        }
                }
                else{
                        return "error: cURL not installed";
                }
        }

        function check($action){
                $accepted = array("authenticate","check-vimeo","check-audio");
                if(in_array($action,$accepted)){
                        $data["email"] = $this->email;
                        $data["password"] = $this->password;
                        $data["generator"] = $this->generator;
                        $data["action"] = $action;
                        if(function_exists("curl_version")){
                                $c = curl_init('http://www.tumblr.com/api/write');
                                curl_setopt($c,CURLOPT_POST,true);
                                curl_setopt($c,CURLOPT_POSTFIELDS,$data);
                                curl_setopt($c,CURLOPT_RETURNTRANSFER,true);
                                $result = curl_exec($c);
                                $status = curl_getinfo($c,CURLINFO_HTTP_CODE);
                                curl_close($c);
                                if($status == "200"){
                                        $status = true;
                                }
                                elseif($status == "403" || $status == "400"){
                                        $status = false;
                                }
                                return $status;
                        }
                        else{
                                return "error: cURL not installed";
                        }
                }
        }

		/**
		* Attempt to read the results of a read request from the cache.
		*
		* @returns string Either an empty string or the cached data
		*/
		function _read_from_cache($url, $json) {
			// no caching
			if(!$this->_cache_duration)
				return '';

			$cache_file = $this->_cache_path . 'tumblr-' . md5($url . $json) . '.js';

			$cache_created = (@file_exists($cachefile))? @filemtime($cachefile) : 0;
			clearstatcache();

			// cache has expired
			if (time() - $this->_cache_duration > $cache_created)
				return '';


			$output = @file_get_contents($cache_file, false);

			return ($res === false ? '' : $output);
		}


		/**
		* Save the results of a read request.
		*
		* @returns bool
		*/
		function _save_to_cache($url, $json, $data) {
			// no caching
			if(!$this->_cache_duration) return;

			$cache_file = $this->_cache_path . 'tumblr-' . md5($url . $json) . '.js';

			$res = @file_put_contents($cache_file, $data, FILE_TEXT | LOCK_EX);

			return ($res === false ? false : true);
		}
}
