<?php

/*
// Alkaline
// Copyright (c) 2010-2012 by Budin Ltd. Some rights reserved.
// http://www.alkalineapp.com/
*/

require_once('./../../config.php');
require_once(PATH . CLASSES . 'alkaline.php');

$alkaline = new Alkaline;
$user = new User;

$user->perm(true, 'maintenance');

// Cache
require_once(PATH . CLASSES . 'cache_lite/Lite.php');

// Set a few options
$options = array(
    'cacheDir' => PATH . CACHE,
    'lifeTime' => 999999999999999,
);

// Create a Cache_Lite object
$cache = new Cache_Lite($options);

if(!$report = $cache->get('diagnostic_report')){
	$alkaline->addNote('Your diagnostic report could not be submitted. Please try again.', 'error');
	header('Location: ' . LOCATION . BASE . ADMIN . 'settings' . URL_CAP);
}

$data = http_build_query(
    array(
		'domain' => $_SERVER['HTTP_HOST'],
        'report' => $report
    )
);

$opts = array(
	'http' => array(
		'method' => 'POST',
		'header' => 'Content-type: application/x-www-form-urlencoded; charset=utf-8',
		'content' => $data
	)
);

$context = stream_context_create($opts);
$body = file_get_contents('http://www.alkalineapp.com/boomerang/report/', false, $context);

if($body == 'true'){
	$alkaline->addNote('Your diagnostic report has been submitted. Please <a href="http://www.alkalineapp.com/support/">submit a bug report</a> if you have not already done so.', 'success');
}
else{
	$alkaline->addNote('Your diagnostic report could not be submitted at this time.', 'error');
}

header('Location: ' . LOCATION . BASE . ADMIN);

?>