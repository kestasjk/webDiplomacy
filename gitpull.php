<?php

// Receive webhook requests from github to allow quick publish and test

// Set GITWEBHOOKSECRET in the Apache config files with SetEnv

$headers = apache_request_headers();
if( !isset($headers['X-Hub-Signature-256']) )
{
	if( isset($_REQUEST['log']) )
		die(file_get_contents('../gitpull.log'));
	else
		die('Unauthorized');
}

$rawReq = file_get_contents('php://input');

$sig_check = 'sha256=' . hash_hmac('sha256', $rawReq, getenv('GITWEBHOOKSECRET'));

if (!hash_equals($sig_check, $headers['X-Hub-Signature-256']))
{
	die("Access denied, request logged");
}

// This is an authenticated notification from github that we need to pull.

shell_exec('date >> ../gitpull.log 2>&1');
shell_exec('git pull >> ../gitpull.log 2>&1');

