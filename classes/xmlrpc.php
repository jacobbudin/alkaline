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

class XMLRPC extends Alkaline{
	public $user;
	
	public function __construct(){
		parent::__construct();
	}
	
	public function __destruct(){
		parent::__destruct();
	}
	
	protected function auth($username, $password){
		$user = new User;
		if(!$user->auth($username, $password)){
			$this->addError('Must be authenticated.', 'Check your username and password.', null, null, 401);
			exit();
		}
		
		$this->user = $user;
	}
	
	public function newPost($method, $args){
		list($blogid, $username, $password, $content, $publish) = $args;
		
		$this->auth($username, $password);
		
		$post_title = trim($content['title']);
		
		$post_title_url = $this->makeURL($post_title);
		
		$post_text_raw = $content['description'];
		$post_text = $post_text_raw;
		
		// Configuration: post_markup
		if($this->returnConf('post_markup')){
			$orbit = new Orbit;
			$post_markup_ext = $this->returnConf('post_markup_ext');
			$post_text = $orbit->hook('markup_' . $post_markup_ext, $post_text_raw, $post_text);
		}
		else{
			$post_markup_ext = '';
			$post_text = $this->nl2br($post_text_raw);
		}
		
		// Publish?
		$post_publish = '';
		if(($publish == true) or ($publish == 'true')){
			$post_publish == date('Y-m-d H:i:s');
		}
		
		$post_images = implode(', ', $this->findIDRef($post_text));
		
		$post_words = $this->countWords($_POST['post_text_raw'], 0);
		
		$fields = array('post_title' => $this->makeUnicode($post_title),
			'post_title_url' => $post_title_url,
			'post_text_raw' => $this->makeUnicode($post_text_raw),
			'post_markup' => $post_markup_ext,
			'post_images' => $post_images,
			'post_text' => $this->makeUnicode($post_text),
			'post_published' => $post_publish,
			'post_words' => $post_words);
		
		$post_id = $this->addRow($fields, 'posts');
		
		return $post_id;
	}
	
	public function newMediaObject($method, $args){
		list($blogid, $username, $password, $content) = $args;
		
		$this->auth($username, $password);
		
		switch($content['type']){
			case 'image/jpeg':
				$ext = 'jpg';
				break;
			case 'image/gif':
				$ext = 'gif';
				break;
			case 'image/png':
				$ext = 'png';
				break;
			default:
				$this->addError('Unknown filetype.', 'Check the format of your file.', null, null, 415);
				exit();
				break;
		}
		
		$name = $content['name'];
		$name = preg_replace('#(?:/)?([a-z0-9_\-]*).*#si', '\\1', $name);
		if(empty($name)){
			$name = substr(md5($this->randInt()), 0, 8);
		}
		
		if(empty($content['bits'])){ $this->addError('No data.', 'The file is empty.', null, null, 415); exit(); }
		
		$handle = fopen($this->correctWinPath(PATH . SHOEBOX . $name . '.' . $ext), 'xb');
		fwrite($handle, $content['bits']->scalar);
		fclose($handle);
		
		// $im = imagecreatefromstring($content['bits']->scalar);
		// 
		// switch($content['type']){
		// 	case 'image/jpeg':
		// 		imagejpeg($im, $this->correctWinPath(PATH . SHOEBOX . $name . '.' . $ext));
		// 		break;
		// 	case 'image/gif':
		// 		imagegif($im, $this->correctWinPath(PATH . SHOEBOX . $name . '.' . $ext));
		// 		break;
		// 	case 'image/png':
		// 		imagepng($im, $this->correctWinPath(PATH . SHOEBOX . $name . '.' . $ext));
		// 		break;
		// 	default:
		// 		$this->addError('Unknown filetype.', 'Check the format of your file.', null, null, 415);
		// 		exit();
		// 		break;
		// }
		// 
		// imagedestroy($im);
		
		$images = new Image();
		$images->attachUser($this->user);
		$images->import(PATH . SHOEBOX . $name . '.' . $ext);
		
		return array('url' => LOCATION . $images->images[0]['image_src']);
	}
	
	public function editPost($method, $args){
		list($postid, $username, $password, $content, $publish) = $args;
		
		$this->auth($username, $password);
		
		$posts = new Post($postid);
		
		$post_title = trim($content['title']);
		
		$post_text_raw = $content['description'];
		$post_text = $post_text_raw;
		
		// Configuration: post_markup
		if(!empty($_POST['post_markup'])){
			$orbit = new Orbit;
			$post_markup_ext = $_POST['post_markup'];
			$post_text = $orbit->hook('markup_' . $post_markup_ext, $post_text_raw, $post_text);
		}
		else{
			$post_markup_ext = '';
			$post_text = $this->nl2br($post_text_raw);
		}
		
		// Publish?
		$post_publish = '';
		if(($publish == true) or ($publish == 'true')){
			$post_publish == date('Y-m-d H:i:s');
		}
		
		$post_images = implode(', ', $this->findIDRef($post_text));
		
		$post_words = $this->countWords($_POST['post_text_raw'], 0);
		
		$fields = array('post_title' => $this->makeUnicode($post_title),
			'post_text_raw' => $this->makeUnicode($post_text_raw),
			'post_markup' => $post_markup_ext,
			'post_images' => $post_images,
			'post_text' => $this->makeUnicode($post_text),
			'post_published' => $post_publish,
			'post_words' => $post_words);
		
		$posts->updateFields($fields);
		
		return true;
	}
	
	public function getPost($method, $args){
		list($postid, $username, $password) = $args;
		
		$this->auth($username, $password);
		
		$posts = new Post($postid);
		
		$return = array();
		$now = time();
		
		foreach($posts->posts as $post){
			if(strtotime($post['post_published']) <= $now){ $is_draft = false; }
			else{ $is_draft = true; }
			
			$return = array('postid' => $post['post_id'], 'title' => $post['post_title'], 'description' => $post['post_text_raw'], 'dateCreated' => date('Ymd\TH:i:s', strtotime($post['post_created'])), 'isdraft' => $is_draft);
		}
		
		return $return;
	}
	
	public function deletePost($method, $args){
		list($appkey, $postid, $username, $password, $publish) = $args;
		
		$this->auth($username, $password);
		
		$posts = new Post($postid);
		$posts->delete();
		
		return 'true';
	}
	
	public function getRecentPosts($method, $args){
		list($blogid, $username, $password, $numberOfPosts) = $args;
		
		$this->auth($username, $password);
		
		$post_ids = new Find('posts');
		$post_ids->sort('post_modified', 'DESC');
		$post_ids->page(1, $numberOfPosts);
		$post_ids->find();
		
		$posts = new Post($post_ids);
		
		$return = array();
		$now = time();
		
		foreach($posts->posts as $post){
			if(strtotime($post['post_published']) <= $now){ $is_draft = false; }
			else{ $is_draft = true; }
			
			$return[] = array('postid' => $post['post_id'], 'title' => $post['post_title'], 'description' => $post['post_text_raw'], 'dateCreated' => date('Ymd\TH:i:s', strtotime($post['post_created'])), 'isdraft' => $is_draft);
		}
		
		return $return;
	}
}

?>