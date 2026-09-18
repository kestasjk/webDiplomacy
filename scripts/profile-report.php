<?php
/*
 * Adds up the sampling profiler's files (see lib/profiler.php) to show where the site's PHP time goes.
 *
 * php scripts/profile-report.php [--cpu] [--days=N | --date=YYYY-MM-DD] [--page=NAME] [--top=N] [--dir=PATH]
 *                                [--collapsed]
 *
 *   --cpu        CPU time instead of wall-clock time. Wall-clock time includes waiting for MariaDB and Redis,
 *                counted against the PHP function that was waiting; CPU time is only PHP's own work.
 *   --days=N     The last N days' files, today included (default 1). --date=YYYY-MM-DD for one day instead.
 *   --page=NAME  Only one page, e.g. board.php or api.php:game/overview; api.php alone includes all its routes
 *   --top=N      Rows in each table (default 30)
 *   --dir=PATH   Where the files are (default Config::$profilerDirectory)
 *   --collapsed  Print the added-up stacks instead, for a flame graph: save the output to a file and open it
 *                at https://www.speedscope.app, or run it through flamegraph.pl
 */

if( php_sapi_name() != 'cli' ) die();

ini_set('memory_limit', '-1');
chdir(dirname(__DIR__));
define('IN_CODE', 1);
if( is_file('config.php') )
	require_once('config.php');
require_once('lib/profiler.php');

$options = getopt('', array('cpu', 'days:', 'date:', 'page:', 'top:', 'dir:', 'collapsed', 'help'));
if( isset($options['help']) )
{
	// The usage comment at the top of this file
	preg_match('#/\*(.*?)\*/#s', file_get_contents(__FILE__), $usage);
	print trim(preg_replace('/^\s*\* ?/m', '', $usage[1]))."\n";
	exit;
}

$mode = isset($options['cpu']) ? 'cpu' : 'wall';
$top = isset($options['top']) ? max(1, (int)$options['top']) : 30;
$pageFilter = isset($options['page']) ? $options['page'] : null;

if( isset($options['dir']) )
	$dir = $options['dir'];
elseif( class_exists('Config') && isset(Config::$profilerDirectory) && Config::$profilerDirectory != '' )
	$dir = Config::$profilerDirectory;
else
{
	fwrite(STDERR, "No --dir given and Config::\$profilerDirectory isn't set\n");
	exit(1);
}
$dir = rtrim($dir, '/');

if( isset($options['date']) )
	$dates = array($options['date']);
else
{
	$dates = array();
	for( $i = max(1, isset($options['days']) ? (int)$options['days'] : 1) - 1; $i >= 0; $i-- )
		$dates[] = gmdate('Y-m-d', strtotime('-'.$i.' days'));
}

$files = array();
foreach( $dates as $date )
	if( is_file($dir.'/'.$mode.'-'.$date.'.collapsed') )
		$files[] = $dir.'/'.$mode.'-'.$date.'.collapsed';

if( !count($files) )
{
	fwrite(STDERR, "No $mode profile files for ".implode(', ', $dates)." in $dir. Profiling needs the Excimer extension, ".
		"Config::\$profilerSampleRate above 0, and the directory writable by the web server.\n");
	exit(1);
}

$total = 0;
$pages = $self = $inclusive = $stacks = array();
foreach( $files as $file )
{
	$fh = fopen($file, 'r');
	while( ($line = fgets($fh)) !== false )
	{
		// page;frame;frame;...;frame count
		$line = rtrim($line, "\n");
		$space = strrpos($line, ' ');
		if( $space === false )
			continue;
		$count = (int)substr($line, $space + 1);
		$frames = explode(';', substr($line, 0, $space));
		$page = array_shift($frames);

		if( $pageFilter !== null && $page !== $pageFilter && strpos($page, $pageFilter.':') !== 0 )
			continue;

		$total += $count;
		$pages[$page] = (isset($pages[$page]) ? $pages[$page] : 0) + $count;

		if( count($frames) )
		{
			$leaf = end($frames);
			$self[$leaf] = (isset($self[$leaf]) ? $self[$leaf] : 0) + $count;

			// A recursive function counts once per sample
			foreach( array_unique($frames) as $frame )
				$inclusive[$frame] = (isset($inclusive[$frame]) ? $inclusive[$frame] : 0) + $count;
		}

		if( isset($options['collapsed']) )
		{
			$stack = substr($line, 0, $space);
			$stacks[$stack] = (isset($stacks[$stack]) ? $stacks[$stack] : 0) + $count;
		}
	}
	fclose($fh);
}

if( isset($options['collapsed']) )
{
	foreach( $stacks as $stack => $count )
		print $stack.' '.$count."\n";
	exit;
}

if( $total == 0 )
{
	print "No samples".($pageFilter !== null ? " for page $pageFilter" : '')."\n";
	exit;
}

$seconds = $total * libProfiler::PERIOD;
print ($mode == 'cpu' ? 'CPU' : 'Wall-clock').' time'.($pageFilter !== null ? " in $pageFilter" : '').', '.implode(', ', $dates).': '.
	number_format($total).' samples, '.number_format($seconds, 1)." s of profiled requests\n";
if( class_exists('Config') && isset(Config::$profilerSampleRate) && Config::$profilerSampleRate > 0 && Config::$profilerSampleRate < 1 )
	print 'At the current sample rate ('.Config::$profilerSampleRate.') that is roughly '.
		number_format($seconds / Config::$profilerSampleRate)." s across all requests\n";

function printTable($title, $rows, $total, $top)
{
	arsort($rows);
	print "\n".$title."\n";
	printf("  %-64s %9s %6s %9s\n", '', 'samples', '%', 'seconds');
	foreach( array_slice($rows, 0, $top, true) as $name => $count )
		printf("  %-64s %9s %5.1f%% %9s\n", strlen($name) > 64 ? substr($name, 0, 61).'...' : $name,
			number_format($count), 100 * $count / $total, number_format($count * libProfiler::PERIOD, 1));
}

if( count($pages) > 1 )
	printTable('Pages', $pages, $total, $top);
printTable('Functions by self time (in the function itself, not in functions it calls)', $self, $total, $top);
printTable('Functions by inclusive time (including the functions it calls)', $inclusive, $total, $top);
