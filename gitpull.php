<?php

// Receive webhook requests from github to allow quick publish and test

// Set GITWEBHOOKSECRET in the Apache config files with SetEnv

// Can also be run manually from the CLI on the server: php gitpull.php
// Pass FORCEALL to rebuild the board, restart the SSE server and re-copy the
// phpBB files even if the pull brought no changes to them: php gitpull.php FORCEALL

// Run a deploy step, appending the output to the permanent log (outside the
// webroot) and to gitpull.txt (latest run only, web accessible so the deploy
// can be checked remotely after a push)
function runStep($cmd)
{
	$line = "\n$ ".$cmd."\n";
	// The exit status marker makes silent failures diagnosable remotely; e.g. a
	// signal-killed npm (128+signal: 137=SIGKILL/OOM, 139=segfault) prints nothing
	$output = shell_exec('('.$cmd.') 2>&1; echo "[exit status: $?]"');
	file_put_contents('../gitpull.log', $line.$output, FILE_APPEND);
	file_put_contents('./gitpull.txt', $line.$output, FILE_APPEND);
	if( php_sapi_name() == 'cli' ) print $line.$output;
	return $output;
}

/**
 * The port the SSE server listens on, from sse-server/.env, which is not in git; server.js defaults
 * to 3000 when the file doesn't set one.
 */
function ssePort()
{
	$env = @file_get_contents(__DIR__.'/sse-server/.env');
	if( $env !== false && preg_match('/^\s*SSE_PORT\s*=\s*(\d+)/m', $env, $m) )
		return intval($m[1]);

	return 3000;
}

/**
 * The pids of any SSE server running from this checkout. It is started below with the full path to
 * server.js, so this doesn't match another checkout's copy (a staging site on the same machine).
 */
function ssePids()
{
	$out = trim(''.shell_exec('pgrep -f '.escapeshellarg('^node .*'.__DIR__.'/sse-server/server\.js$').' 2>/dev/null'));
	if( $out === '' ) return array();

	return array_map('intval', preg_split('/\s+/', $out));
}

/**
 * Whether something is listening on the SSE port yet, given a few seconds to come up.
 */
function sseListening($seconds = 10)
{
	$port = ssePort();
	for( $i = 0; $i < $seconds * 2; $i++ )
	{
		$socket = @fsockopen('127.0.0.1', $port, $errno, $errstr, 1);
		if( $socket ) { fclose($socket); return true; }
		usleep(500000);
	}

	return false;
}

/**
 * Start the SSE server (sse-server/server.js), or restart it when its code changed.
 *
 * Nothing else supervises it: this script owns the process, writing its pid to ../sse-server.pid and
 * its output to ../sse-server.log, both outside the webroot. A deploy that doesn't touch sse-server/
 * leaves the connected clients alone and only starts it if it isn't running, e.g. after a reboot.
 */
function deploySSE($changedFiles, $forceAll)
{
	$changed = $forceAll || strpos($changedFiles, 'sse-server/') !== false;
	$running = ssePids();

	if( !$changed && $running )
	{
		runStep('echo SSE server already running as pid '.implode(' ', $running).', and sse-server/ has not changed');
		return;
	}

	if( !is_file(__DIR__.'/sse-server/.env') )
	{
		runStep('echo No sse-server/.env, so the SSE server has nothing to sign tokens with; not starting it');
		return;
	}

	// Its dependencies aren't in git either
	if( $changed || !is_dir(__DIR__.'/sse-server/node_modules') )
		runStep('cd sse-server && npm ci --cache ../cache/npm --no-audit --no-fund');

	foreach( $running as $pid )
		runStep('kill '.intval($pid).' && echo Stopped SSE server pid '.intval($pid));

	// Give them a moment to close their listening socket, then insist
	for( $i = 0; $i < 20 && ssePids(); $i++ )
		usleep(250000);
	foreach( ssePids() as $pid )
		runStep('kill -9 '.intval($pid).' && echo SSE server pid '.intval($pid).' did not stop, killed it');

	// Started with the full path so the pgrep above can tell this checkout's server from another's,
	// but from its own directory, which is where it reads .env from
	runStep('cd sse-server && nohup node '.escapeshellarg(__DIR__.'/sse-server/server.js')
		.' >> ../../sse-server.log 2>&1 < /dev/null & echo Started the SSE server');

	// It has either come up or written why it couldn't. Check the process first: if the port alone were
	// checked, another checkout's server holding the port would look like success.
	$pids = array();
	for( $i = 0; $i < 20 && !$pids; $i++ )
	{
		usleep(250000);
		$pids = ssePids();
	}

	if( !$pids )
		runStep('echo The SSE server did not start ; tail -n 20 ../sse-server.log');
	elseif( !sseListening() )
		runStep('echo The SSE server is running as pid '.implode(' ', $pids).' but nothing is listening on port '
			.ssePort().' ; tail -n 20 ../sse-server.log');
	else
	{
		file_put_contents('../sse-server.pid', implode(' ', $pids)."\n");
		runStep('echo SSE server listening on port '.ssePort().' as pid '.implode(' ', $pids));
	}
}

function deploy($forceAll = false)
{
	chdir(__DIR__);

	// The web server can run this with no PATH at all (e.g. PHP-FPM's clear_env). The shell still finds npm on
	// its built-in default path, but npm only puts node_modules/.bin on a PATH that exists, so npm scripts then
	// can't run package binaries. It also has to find node, for the board build and the SSE server. Runs from
	// a shell are fine.
	if( getenv('PATH') === false || getenv('PATH') === '' )
		putenv('PATH=/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin');

	// Keep deploying even after github closes the webhook connection; the
	// board build can take a few minutes
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
	{
		$changedFiles = ''.shell_exec('git diff --name-only '.$oldHead.' '.$newHead.' 2>&1');

		// A [force-deploy] marker in any pulled commit message forces the build and
		// overlay steps below even when the pull didn't touch their paths, allowing a
		// full redeploy to be triggered remotely with an empty commit
		if( !$forceAll && strpos(''.shell_exec('git log --format=%B '.$oldHead.'..'.$newHead), '[force-deploy]') !== false )
		{
			runStep('echo Force deploy requested by commit message');
			$forceAll = true;
		}
	}

	// Rebuild the React game board (game-src/, built to game/) when its source changed.
	// npm ci rather than npm install so package-lock.json is never rewritten,
	// which would dirty the working tree and break future pulls; the cache
	// flag keeps npm out of the web user's (unwritable) home directory.
	// It replaced the beta board, so on a server that has not built it before it takes the beta's
	// .env.production, which is not in git and so is still sitting in beta-src/.
	if( $forceAll || strpos($changedFiles, 'game-src/') !== false )
	{
		// Environment snapshot; a build that dies without output is usually resources
		runStep('free -m; df -h .; node --version; npm --version');
		runStep('cd game-src && ( [ ! -f ../beta-src/.env.production ] || [ -f .env.production ] || cp ../beta-src/.env.production . )'
			.' && npm ci --cache ../cache/npm'
			.' && if [ -f .env.production ]; then npm run build:production; else npm run build; fi');
	}
	else
		runStep('echo Skipping game board build, no game-src changes');

	// Start the SSE server, or restart it if its code changed
	deploySSE($changedFiles, $forceAll);

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

// Define this in the apache site config, with the secret set on the site's GitHub webhook:
// SetEnv GITWEBHOOKSECRET "<the webhook's secret>"

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
