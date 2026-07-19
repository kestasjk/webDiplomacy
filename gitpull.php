<?php

// Receive webhook requests from github to allow quick publish and test

// Set GITWEBHOOKSECRET in the Apache config files with SetEnv

// Can also be run manually from the CLI on the server: php gitpull.php
// Pass FORCEALL to rebuild the beta app and re-copy the phpBB files even if
// the pull brought no changes to them: php gitpull.php FORCEALL

// Run a deploy step, appending the output to the permanent log (outside the
// webroot) and to gitpull.txt (latest run only, web accessible so the deploy
// can be checked remotely after a push)
function runStep($cmd)
{
	$line = "\n$ ".$cmd."\n";
	$output = shell_exec($cmd.' 2>&1');
	file_put_contents('../gitpull.log', $line.$output, FILE_APPEND);
	file_put_contents('./gitpull.txt', $line.$output, FILE_APPEND);
	if( php_sapi_name() == 'cli' ) print $line.$output;
	return $output;
}

function deploy($forceAll = false)
{
	chdir(__DIR__);

	// Keep deploying even after github closes the webhook connection; the
	// beta build can take a few minutes
	ignore_user_abort(true);
	set_time_limit(0);

	// Serialize deploys; a second push during a deploy waits here, then
	// pulls whatever is newest and skips anything already built
	$lock = fopen('../gitpull.lock', 'c');
	if( $lock ) flock($lock, LOCK_EX);

	file_put_contents('./gitpull.txt', ''); // Start a fresh latest-run log

	runStep('date');

	$oldHead = trim(''.shell_exec('git rev-parse HEAD'));
	runStep('git pull');
	$newHead = trim(''.shell_exec('git rev-parse HEAD'));

	$changedFiles = '';
	if( $oldHead != '' && $newHead != '' && $oldHead != $newHead )
		$changedFiles = ''.shell_exec('git diff --name-only '.$oldHead.' '.$newHead.' 2>&1');

	// Rebuild the beta React app when its source changed.
	// npm ci rather than npm install so package-lock.json is never rewritten,
	// which would dirty the working tree and break future pulls; the cache
	// flag keeps npm out of the web user's (unwritable) home directory.
	if( $forceAll || strpos($changedFiles, 'beta-src/') !== false )
	{
		runStep('cd beta-src && npm ci --cache ../cache/npm'
			.' && if [ -f .env.production ]; then npm run build:production; else npm run build; fi');
	}
	else
		runStep('echo Skipping beta build, no beta-src changes');

	// Overlay the phpBB integration files onto the phpBB install (per
	// contrib/phpBB3-files/README.txt) and wipe the compiled caches, which
	// phpBB rebuilds on the next request. Only the cache folder contents are
	// removed so the folders and their .htaccess stay in place.
	if( is_dir('contrib/phpBB3') && ( $forceAll || strpos($changedFiles, 'contrib/phpBB3-files/') !== false ) )
	{
		foreach( array('config', 'ext', 'phpbb', 'styles') as $dir )
			runStep('cp -a contrib/phpBB3-files/'.$dir.'/. contrib/phpBB3/'.$dir.'/');

		runStep('rm -rf contrib/phpBB3/cache/*/*');
	}
	else
		runStep('echo Skipping phpBB copy, no phpBB3 folder or no phpBB3-files changes');

	runStep('echo Deploy finished ; date');

	if( $lock ) flock($lock, LOCK_UN);
}

if( php_sapi_name() == 'cli' )
{
	deploy(isset($argv) && in_array('FORCEALL', $argv));
	exit;
}

$headers = apache_request_headers();
if( !isset($headers['X-Hub-Signature-256']) )
{
	#if( isset($_REQUEST['log']) )
	#	die(file_get_contents('../gitpull.log'));
	#else
	die('Unauthorized');
}

$rawReq = file_get_contents('php://input');

// Define this in the apache site config:
// SetEnv GITWEBHOOKSECRET "jpoiegwe9823-09rjk209873hf3497hqawodji1032r084hj32"

$envGITHUBSECRET = getenv('GITWEBHOOKSECRET');
if( is_null($envGITHUBSECRET) || $envGITHUBSECRET == '' )
{
	die("GITWEBHOOKSECRET not set in apache config");
}

$sig_check = 'sha256=' . hash_hmac('sha256', $rawReq, $envGITHUBSECRET);

if (!hash_equals($sig_check, $headers['X-Hub-Signature-256']))
{
	die("Access denied, request logged");
}

// This is an authenticated notification from github that we need to pull.

deploy();
