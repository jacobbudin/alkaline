<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalineapp.com/
*/

$request = file_get_contents('php://input');

require_once('./../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline();
$xmlrpc = new XMLRPC();

if(!function_exists('xmlrpc_server_create')){ $alkaline->addError('Module not found.', 'You do not have the XML-RPC PHP module installed.', null, null, 500); }

$server = xmlrpc_server_create();

xmlrpc_server_register_method($server, 'metaWeblog.newPost', array(&$xmlrpc, 'newPost'));
xmlrpc_server_register_method($server, 'metaWeblog.editPost', array(&$xmlrpc, 'editPost'));
xmlrpc_server_register_method($server, 'metaWeblog.getPost', array(&$xmlrpc, 'getPost'));
xmlrpc_server_register_method($server, 'metaWeblog.getRecentPosts', array(&$xmlrpc, 'getRecentPosts'));
xmlrpc_server_register_method($server, 'metaWeblog.newMediaObject', array(&$xmlrpc, 'newMediaObject'));
xmlrpc_server_register_method($server, 'metaWeblog.deletePost', array(&$xmlrpc, 'deletePost'));
xmlrpc_server_register_method($server, 'metaWeblog.getCategories', array(&$xmlrpc, 'getCategories'));
xmlrpc_server_register_method($server, 'metaWeblog.deletePost', array(&$xmlrpc, 'deletePost'));
xmlrpc_server_register_method($server, 'metaWeblog.getUserInfo', array(&$xmlrpc, 'getUserInfo'));
xmlrpc_server_register_method($server, 'metaWeblog.getUsersBlogs', array(&$xmlrpc, 'getUsersBlogs'));
xmlrpc_server_register_method($server, 'blogger.deletePost', array(&$xmlrpc, 'deletePost'));
xmlrpc_server_register_method($server, 'blogger.getUserInfo', array(&$xmlrpc, 'getUserInfo'));
xmlrpc_server_register_method($server, 'blogger.getUsersBlogs', array(&$xmlrpc, 'getUsersBlogs'));

if($response = xmlrpc_server_call_method($server, $request, null, array('encoding' => 'UTF-8'))){
	header('Content-Type: text/xml');
	echo $response;
}

?>