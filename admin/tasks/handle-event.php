<?php

/*
// Alkaline
// Copyright (c) 2010-2011 by Budin Ltd. All rights reserved.
// Do not redistribute this code without written permission from Budin Ltd.
// http://www.alkalineapp.com/
*/

header('HTTP/1.0 500 Internal Server Error', true);
header('Status: 500 Internal Server Error', true);

if(session_id() == ''){ session_start(); }

function ioncube_event_handler($err_code, $params){
	switch($err_code){
		case 1:
			$error = 'An encoded file has been corrupted';
			break;
		case 2:
			$error = 'An encoded file has reached its expiry time';
			break;
		case 3:
			$error = 'An encoded file has a server restriction and is used on a non-authorized system';
			break;
		case 4:
			$error = 'An encoded file is used on a system where the clock is set more than 24 hours before the file was encoded';
			break;
		case 5:
			$error = 'An encoded file was encoded with the --disallow-untrusted-extensions option, and is used on a system with an unrecognized extension installed';
			break;
		case 6:
			$error = 'The license file required by an encoded script could not be found';
			break;
		case 7:
			$error = 'The license file has been altered or the passphrase used to decrypt the license was incorrect';
			break;
		case 8:
			$error = 'The license file has reached its expiry time';
			break;
		case 9:
			$error = 'A property marked as &#8216;enforced&#8217; in the license file was not matched by a property contained in the encoded file';
			break;
		case 10:
			$error = 'The header block of the license file has been altered';
			break;
		case 11:
			$error = 'The license has a server restriction and is used on a non-authorized system';
			break;
		case 12:
			$error = 'The encoded file has been included by a file which is either non-encoded or has incorrect properties';
			break;
		case 13:
			$error = 'The encoded file has included a file which is either non- encoded or has incorrect properties';
			break;
		case 14:
			$error = 'The php.ini has either the --auto-append-file or --auto-prepend-file setting enabled';
			break;
		default:
			$error = 'An unknown encoding error occurred';
			break;
	}
}

$_SESSION['alkaline']['errors'][] = array('constant' => E_USER_ERROR, 'severity' => 'error', 'message' => $error);

require(PATH . BASE . ADMIN . 'error.php');
exit();

?>