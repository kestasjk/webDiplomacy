<?php
/*
    Copyright (C) 2004-2010 Kestas J. Kuliukas

	This file is part of webDiplomacy.

    webDiplomacy is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    webDiplomacy is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @package Base
 * @subpackage Forms
 */

 require_once('header.php');
require_once(l_r('lib/metrics.php'));

libHTML::starthtml();

print libHTML::pageTitle(l_t('System status'),l_t('View the current status of this site\'s systems and services.'));

$healthCheckResults = array();

$healthCheckResults[] = array(
	'label' => 'Panic Mode',
	'description' => 'Is the site in panic mode, which disables certain features to protect the system.',
	'active' => $Misc->Panic,
	'text' => $Misc->Panic ? 'On' : 'Off',
	'importance' => 3
);
$healthCheckResults[] = array(
	'label' => 'Maintenance Mode',
	'description' => 'Is the site in maintenance mode, which disables certain features to protect the system.',
	'active' => $Misc->Maintenance,
	'text' => $Misc->Maintenance ? 'On' : 'Off',
	'importance' => 3
);

$healthCheckResults[] = array(
	'label' => 'Game Processing',
	'description' => 'Has game processing not occurred within the configured downtime trigger threshold.',
	'active' => ( time() - $Misc->LastProcessTime ) > Config::$downtimeTriggerMinutes*60,
	'text' => libTime::timeLengthText( time() - $Misc->LastProcessTime ) . ' since last process',
	'importance' => 3
);

$Redis->set("REDIS_HEALTHCHECK", time());
$redisOnline = $Redis->get("REDIS_HEALTHCHECK");

$healthCheckResults[] = array(
	'label' => 'Redis Server Online',
	'description' => 'Redis server connectivity check.',
	'active' => !$redisOnline,
	'text' => $redisOnline ? 'Online' : 'Offline',
	'importance' => 3
);

// A site can be set to only process games while this page is being requested, which is how a monitor
// outside the network keeps game processing tied to the site being reachable from outside it, now that
// the gamemaster is called from the web server itself. gamemaster.php reads this; see
// Config::$gamemasterRequiresStatusCheckMinutes.
if( $redisOnline ) $Redis->set("STATUS_LASTREQUEST", time());

$healthCheckResults[] = array(
	'label' => 'Games Crashed',
	'description' => 'The number of games that have crashed.',
	'active' => $Misc->GamesCrashed > 0,
	'text' => $Misc->GamesCrashed . ' games crashed',
	'importance' => 3
);

$healthCheckResults[] = array(
	'label' => 'Game Backups',
	'description' => 'Are game backups being generated.',
	'active' => ( time() - $Misc->LastBackupUpdate ) > 1*60*60,
	'text' => libTime::timeLengthText( time() - $Misc->LastBackupUpdate ) . ' since last backup',
	'importance' => 2
);

$healthCheckResults[] = array(
	'label' => 'Game Backup Archived',
	'description' => 'Are game backups being archived offsite.',
	'active' => ( time() - $Misc->LastBackupArchived ) > 1*60*60,
	'text' => libTime::timeLengthText( time() - $Misc->LastBackupArchived ) . ' since last backup',
	'importance' => 2
);

$healthCheckResults[] = array(
	'label' => 'Bot Game Cleanup',
	'description' => 'Are the bot game cleanups being done.',
	'active' => ( time() - $Misc->LastBotGameCleanup ) > 72*60*60,
	'text' => libTime::timeLengthText( time() - $Misc->LastBotGameCleanup ) . ' since last cleanup',
	'importance' => 2
);

$healthCheckResults[] = array(
	'label' => 'Group/Relation Update',
	'description' => 'Are the group/relation updates being done.',
	'active' => ( time() - $Misc->LastGroupUpdate ) > 12*60*60,
	'text' => libTime::timeLengthText( time() - $Misc->LastGroupUpdate ) . ' since last update',
	'importance' => 1
);

$lastSessionTableUpdate = $Redis->get('lastSessionTableUpdate');
$lastSessionTableUpdate = $lastSessionTableUpdate ? $lastSessionTableUpdate : 0;
$healthCheckResults[] = array(
	'label' => 'Session Table Update',
	'description' => 'Are the session table being updated.',
	'active' => ( time() - $lastSessionTableUpdate ) > 15*60,
	'text' => libTime::timeLengthText( time() - $lastSessionTableUpdate ) . ' since last update',
	'importance' => 1
);

$lastOnlineUsersUpdate = $Redis->get('lastOnlineUsersUpdate');
$lastOnlineUsersUpdate = $lastOnlineUsersUpdate ? $lastOnlineUsersUpdate : 0;
$healthCheckResults[] = array(
	'label' => 'Online Users Update',
	'description' => 'Are the online users list being updated.',
	'active' => ( time() - $lastOnlineUsersUpdate ) > 15*60,
	'text' => libTime::timeLengthText( time() - $lastOnlineUsersUpdate ) . ' since last update',
	'importance' => 1
);

$healthCheckResults[] = array(
	'label' => 'Stats Update',
	'description' => 'Are the site statistics being updated.',
	'active' => ( time() - $Misc->LastStatsUpdate ) > 6*60*60,
	'text' => libTime::timeLengthText( time() - $Misc->LastStatsUpdate ) . ' since last update',
	'importance' => 2
);
$healthCheckResults[] = array(
	'label' => 'Reliability Ratings Update',
	'description' => 'Are the reliability ratings being updated.',
	'active' => ( time() - $Misc->LastReliabilityRatingsUpdate ) > 6*60*60,
	'text' => libTime::timeLengthText( time() - $Misc->LastReliabilityRatingsUpdate ) . ' since last update',
	'importance' => 1
);
$healthCheckResults[] = array(
	'label' => 'Reliability Ratings Reset',
	'description' => 'Are the reliability ratings being reset.',
	'active' => ( time() - $Misc->LastReliabilityRatingsRefresh ) > 4*24*60*60,
	'text' => libTime::timeLengthText( time() - $Misc->LastReliabilityRatingsRefresh ) . ' since last update',
	'importance' => 1
);
/*
$healthCheckResults[] = array(
	'label' => 'NMR Warning Send',
	'description' => 'Are the NMR warnings being sent.',
	'active' => ( time() - $Misc->LastNMRWarningUpdate ) > 24*60*60,
	'text' => libTime::timeLengthText( time() - $Misc->LastNMRWarningUpdate ) . ' since last update',
	'importance' => 1
);
*/
$healthCheckResults[] = array(
	'label' => 'User connections update',
	'description' => 'Are the user connections being updated.',
	'active' => ( time() - $Misc->LastUserConnectionsUpdate ) > 24*60*60,
	'text' => libTime::timeLengthText( time() - $Misc->LastUserConnectionsUpdate ) . ' since last update',
	'importance' => 1
);
$healthCheckResults[] = array(
	'label' => 'Watched games cleanup',
	'description' => 'Are watched games being cleaned up.',
	'active' => ( time() - $Misc->LastTidyWatchedGamesUpdate ) > 3*24*60*60,
	'text' => libTime::timeLengthText( time() - $Misc->LastTidyWatchedGamesUpdate ) . ' since last update',
	'importance' => 1
);
$healthCheckResults[] = array(
	'label' => 'Points refill',
	'description' => 'Are player points being refilled.',
	'active' => ( time() - $Misc->LastPointsCheckUpdate ) > 5*24*60*60,
	'text' => libTime::timeLengthText( time() - $Misc->LastPointsCheckUpdate ) . ' since last update',
	'importance' => 2
);

if( $redisOnline ) 
{
	$sseHealthCheck = $Redis->get("SSE_HEALTHCHECK");
	$sseHealthCheck = $sseHealthCheck ? $sseHealthCheck : 0;
	$healthCheckResults[] = array(
		'label' => 'SSE Server Online',
		'description' => 'Last SSE server ping, indicating the notification server is running. (< 1 minute)',
		'active' => ( time() - $sseHealthCheck ) > 60,
		'text' => libTime::timeLengthText( time() - $sseHealthCheck ) . ' since last update',
		'importance' => 3
	);
	
	$sseLastConnect = $Redis->get("SSE_LASTCLIENTCONNECT");
	$sseLastConnect = $sseLastConnect ? $sseLastConnect : 0;
	$healthCheckResults[] = array(
		'label' => 'SSE Server Last Client Connect',
		'description' => 'Last SSE server client connection, indicating the SSE server is accessible to users. (< 60 minutes)',
		'active' => ( time() - $sseLastConnect ) > 60*60,
		'text' => libTime::timeLengthText( time() - $sseLastConnect ) . ' since last update',
		'importance' => 1
	);

	// The SSE server calls gamemaster.php in a loop and records every call the site answered. Game
	// Processing above stops moving both when nothing is calling the gamemaster and when the gamemaster
	// is turning the calls away, so this is what tells those two apart. An install that doesn't run it
	// from there (GAMEMASTER_URL unset) never sets this and gets no row.
	$gamemasterLastRun = $Redis->get("GAMEMASTER_LASTRUN");
	if( $gamemasterLastRun !== false && $gamemasterLastRun !== null )
	{
		$healthCheckResults[] = array(
			'label' => 'Gamemaster Called',
			'description' => 'Last gamemaster.php call by the SSE server, which calls it in a loop. (< 5 minutes)',
			'active' => ( time() - $gamemasterLastRun ) > 5*60,
			'text' => libTime::timeLengthText( time() - $gamemasterLastRun ) . ' since last call',
			'importance' => 3
		);
	}
}

list($botsOfflineCount) = $DB->sql_row("SELECT COUNT(*) FROM wD_ApiKeys a WHERE isChecked = 1 AND (UNIX_TIMESTAMP() - lastHit) > 15*60");
$healthCheckResults[] = array(
	'label' => 'Inactive Bots (Other)',
	'description' => 'The number of bot accounts which have not made an API call in the last 15 minutes.',
	'active' => $botsOfflineCount > 0,
	'text' => $botsOfflineCount . ' bots inactive',
	'importance' => 2
);

list($botsOfflineCount) = $DB->sql_row("SELECT COUNT(*) FROM wD_ApiKeys a WHERE isChecked = 1 AND (UNIX_TIMESTAMP() - lastHit) > 15*60");
$healthCheckResults[] = array(
	'label' => 'Inactive Bots (Gunboat)',
	'description' => 'The number of bot accounts which have not made an API call in the last 15 minutes.',
	'active' => $botsOfflineCount > 0,
	'text' => $botsOfflineCount . ' bots inactive',
	'importance' => 2
);

$healthCheckResults[] = array(
	'label' => 'Gunboat Games (Anon)',
	'description' => 'The number of error log entries accumulated.',
	'active' => $Misc->BotGamesActiveNoPress_Anonymous > 190,
	'text' => $Misc->BotGamesActiveNoPress_Anonymous . ' entries',
	'importance' => 2
);

$healthCheckResults[] = array(
	'label' => 'Gunboat Games (User)',
	'description' => 'The number of error log entries accumulated.',
	'active' => $Misc->BotGamesActiveNoPress > 550,
	'text' => $Misc->BotGamesActiveNoPress . ' entries',
	'importance' => 2
);

$healthCheckResults[] = array(
	'label' => 'Error Logs',
	'description' => 'The number of error log entries accumulated.',
	'active' => $Misc->ErrorLogs > 1000,
	'text' => $Misc->ErrorLogs . ' entries',
	'importance' => 1
);

if(function_exists('disk_free_space') && function_exists('disk_total_space') && function_exists('getcwd') && function_exists('file_exists') )
{
	// Get the database directory:
	list($dataPath) = $DB->sql_row("SELECT @@datadir");
	// Get the PHP current folder:
	$cwd = getcwd();

	foreach( array($dataPath, $cwd, '/var','/tmp','/usr','/') as $path )
	{
		if( file_exists($path) )
		{
			$disk_free_space = disk_free_space($path);
			$disk_total_space = disk_total_space($path);

			if( $disk_free_space === false || $disk_total_space === false || $disk_total_space == 0 ) continue;

			$percentFree = $disk_free_space / $disk_total_space;

			$healthCheckResults[] = array(
				'label' => 'Disk space - ' . $path,
				'description' => 'The space available in this path, if under 15% an alert is raised.',
				'active' => $percentFree < 0.1,
				'text' => intval($percentFree * 100) . '% free',
				'importance' => 2
			);
		}
	}
}

print '<h3>Health Checks</h3>';
print '<table class="hof"><tr><th>Name / Importance</th><th>Status</th><th>Description</th></tr>';
$maxImportance = 0;
// Order $healthCheckResults by importance descending then name:
usort($healthCheckResults, function($a, $b) {
	if($a['importance'] == $b['importance'])
		return strcmp($a['label'], $b['label']);
	return $b['importance'] - $a['importance'];
});

foreach($healthCheckResults as $check)
{
	if($check['importance'] > $maxImportance && $check['active'])
		$maxImportance = $check['importance'];

	$importanceClass = '';
	if( $check['importance'] == 3 )
		$importanceClass = 'healthcheck-importance-high';
	elseif( $check['importance'] == 2 )
		$importanceClass = 'healthcheck-importance-medium';
	elseif( $check['importance'] == 1 )
		$importanceClass = 'healthcheck-importance-low';
	print '<tr class="'.($check['active'] ? 'healthcheck-active ' : 'healthcheck-inactive ').$importanceClass.'">';
	print '<td style="color:';
	if( $check['importance'] == 3 )
		print '#aa4b4bff !important;';
	elseif( $check['importance'] == 2 )
		print '#a9aa4bff !important;';
	elseif( $check['importance'] == 1 )
		print '#4baa50ff !important;';

	print '">'.$check['label'];
	print ' ('.$check['importance'].')';
	print '</td>';
	print '<td style="'.($check['active'] ? 'background-color: #ffe0e0;' : 'background-color: #e0ffe0;').'">'.($check['active'] ? '⚠️ ' : '✅ '). 
		$check['text'].'</td>';
	print '<td>'.$check['description'].'</td>';
	print '</tr>';
}
print '</table>';		

print '<p class="notice">';
print 'Summary status: ';
// Codes are included to make it easier for automated monitoring systems:
if( $maxImportance == 3 )
	print '<span class="healthcheck-importance-high">High (g4i0o59j45)</span>';
elseif( $maxImportance == 2 )
	print '<span class="healthcheck-importance-medium">Medium (7BYDynhGpx)</span>';
elseif( $maxImportance == 1 )
	print '<span class="healthcheck-importance-low">Low (pSxqXCuhhq / J9RhUjPGOS)</span>';
else
	print 'None (pSxqXCuhhq)';
print '</p>';

print '<div class="hr"></div>';



// Known API endpoints to check
$apiEndpoints = array(
	'PLAYERS_CD',
	'PLAYERS_MISSING_ORDERS',
	'PLAYERS_ACTIVE_GAMES',
	'GAME_STATUS',
	'GAME_OVERVIEW',
	'GAME_DATA',
	'GAME_MEMBERS',
	'GAME_JOIN',
	'GAME_LEAVE',
	'GAME_ORDERS',
	'GAME_TOGGLEVOTE',
	'GAME_SETVOTE',
	'SSE_AUTHENTICATION',
	'GAME_SENDMESSAGE',
	'GAME_GETMESSAGES',
	'GAME_MESSAGESSEEN',
	'GAME_MARKBACKFROMLEFT',
	'SANDBOX_CREATE',
	'SANDBOX_COPY',
	'SANDBOX_MOVETURNBACK',
	'SANDBOX_DELETE'
);
// Known AJAX endpoints to check
$ajaxEndpoints = array(
	'SMS_TOKEN',
	'LIKE_MESSAGE_TOGGLE',
	'ORDER_UPDATES',
	'GROUP_MANAGEMENT',
	'INVALID'
);
// Known PAGE endpoints to check
$pageEndpoints = array(
	'HOME', 'BOARD', 'FORUM', 'USERCP', 'ADMINCP', 'PROFILE', 'GAMES', 'TOURNAMENTS',
	'RULES', 'FAQ', 'CREDITS', 'VARIANTS', 'REGISTER', 'CONTACTUS', 'DEVELOPERS',
	'DONATIONS', 'POINTS', 'SEARCH', 'MESSAGE', 'MODFORUM', 'BOTGAMECREATE',
	'BOTSTATUS', 'USERPROFILE', 'USEROPTIONS', 'USERNOTIFICATIONS',
	'GAMEMASTER', 'GAMELISTINGS'
);
// The parts of each gamemaster.php run, which are also counted in PAGE_GAMEMASTER
$gamemasterParts = libMetrics::gamemasterParts();
// What browsers report about themselves through the client/metrics API route
$clientParts = libMetrics::clientParts();
$metricTypes = array('COUNT', 'TIME_MS', 'DB_GET', 'DB_PUT', 'DB_TIME_MS', 'BOTCOUNT');
					
// Handle clearing API metrics if requested
if( $User->type['Admin'] && isset($_GET['clearAPIMetrics']) )
{
	try {
		$clearedCount = 0;
		// Clear API metrics
		foreach ($apiEndpoints as $endpoint) {
			foreach ($metricTypes as $type) {
				$key = 'METRICS_API_' . $endpoint . '_' . $type;
				if ($Redis->delete($key)) {
					$clearedCount++;
				}
			}
		}
		// Clear AJAX metrics
		foreach ($ajaxEndpoints as $endpoint) {
			foreach ($metricTypes as $type) {
				if ($type == 'BOTCOUNT') continue; // AJAX doesn't have bot counts
				$key = 'METRICS_AJAX_' . $endpoint . '_' . $type;
				if ($Redis->delete($key)) {
					$clearedCount++;
				}
			}
		}
		// Clear PAGE metrics
		foreach ($pageEndpoints as $endpoint) {
			foreach ($metricTypes as $type) {
				if ($type == 'BOTCOUNT') continue; // PAGE doesn't have bot counts
				$key = 'METRICS_PAGE_' . $endpoint . '_' . $type;
				if ($Redis->delete($key)) {
					$clearedCount++;
				}
			}
		}
		// Clear client metrics
		foreach ($clientParts as $part) {
			foreach (array('COUNT', 'TIME_MS') as $type) {
				if ($Redis->delete('METRICS_' . $part . '_' . $type)) {
					$clearedCount++;
				}
			}
		}
		// Clear gamemaster part metrics
		foreach ($gamemasterParts as $part) {
			foreach ($metricTypes as $type) {
				if ($type == 'BOTCOUNT') continue;
				if ($Redis->delete('METRICS_' . $part . '_' . $type)) {
					$clearedCount++;
				}
			}
		}
		print '<div class="notice">'.l_t('All metrics cleared successfully. Removed %s metric keys from Redis (API, AJAX, and PAGE metrics).', $clearedCount).'</div>';
	} catch (Exception $e) {
		print '<div class="notice">'.l_t('Error clearing metrics: ').$e->getMessage().'</div>';
	}
}

print '<a id="metrics"></a>';
print '<h3>'.l_t('Performance Metrics:').'</strong> (<a href="?clearAPIMetrics=1#metrics" onclick="return confirm(\'Are you sure you want to clear all API metrics? This action cannot be undone.\')">Clear All Metrics</a>)</h3>';

try
{
	// Fetch all API metric keys
	$metrics = array();
	$allKeys = array();
	
	// Collect metrics for API endpoints
	foreach ($apiEndpoints as $endpoint) {
		$count = $Redis->get('METRICS_API_' . $endpoint . '_COUNT');
		if ($count && $count > 0) {
			$metrics['API_' . $endpoint] = array(
				'count' => $count,
				'time_ms' => $Redis->get('METRICS_API_' . $endpoint . '_TIME_MS') ?: 0,
				'db_get' => $Redis->get('METRICS_API_' . $endpoint . '_DB_GET') ?: 0,
				'db_put' => $Redis->get('METRICS_API_' . $endpoint . '_DB_PUT') ?: 0,
				'db_time_ms' => $Redis->get('METRICS_API_' . $endpoint . '_DB_TIME_MS') ?: 0,
				'bot_count' => $Redis->get('METRICS_API_' . $endpoint . '_BOTCOUNT') ?: 0,
				'type' => 'API'
			);
		}
	}
	// Collect metrics for AJAX endpoints
	foreach ($ajaxEndpoints as $endpoint) {
		$count = $Redis->get('METRICS_AJAX_' . $endpoint . '_COUNT');
		if ($count && $count > 0) {
			$metrics['AJAX_' . $endpoint] = array(
				'count' => $count,
				'time_ms' => $Redis->get('METRICS_AJAX_' . $endpoint . '_TIME_MS') ?: 0,
				'db_get' => $Redis->get('METRICS_AJAX_' . $endpoint . '_DB_GET') ?: 0,
				'db_put' => $Redis->get('METRICS_AJAX_' . $endpoint . '_DB_PUT') ?: 0,
				'db_time_ms' => $Redis->get('METRICS_AJAX_' . $endpoint . '_DB_TIME_MS') ?: 0,
				'bot_count' => null, // AJAX doesn't use API key authentication
				'type' => 'AJAX'
			);
		}
	}
	// Collect metrics for PAGE endpoints
	foreach ($pageEndpoints as $endpoint) {
		$count = intval($Redis->get('METRICS_PAGE_' . $endpoint . '_COUNT'));
		if ($count && $count > 0) {
			$metrics['PAGE_' . $endpoint] = array(
				'count' => $count,
				'time_ms' => (float)$Redis->get('METRICS_PAGE_' . $endpoint . '_TIME_MS') ?: 0,
				'db_get' => (float)$Redis->get('METRICS_PAGE_' . $endpoint . '_DB_GET') ?: 0,
				'db_put' => (float)$Redis->get('METRICS_PAGE_' . $endpoint . '_DB_PUT') ?: 0,
				'db_time_ms' => (float)$Redis->get('METRICS_PAGE_' . $endpoint . '_DB_TIME_MS') ?: 0,
				'bot_count' => null, // PAGE doesn't use API key authentication
				'type' => 'PAGE'
			);
		}
	}
	if (empty($metrics)) {
		print '<p class="notice">'.l_t('No metrics have been collected yet. Metrics will appear here once API calls, AJAX requests, or page views are made.').'</p>';
	} else {
		// Sort by hit count (descending)
		uasort($metrics, function($a, $b) {
			return intval($b['count']) - intval($a['count']);
		});
		// Display the metrics table
		print '<TABLE class="modTools">';
		print '<tr>';
		print '<th class="modTools">Type</th>';
		print '<th class="modTools">Route</th>';
		print '<th class="modTools">Hits</th>';
		print '<th class="modTools">Bot Hits</th>';
		print '<th class="modTools">Avg Time (ms)</th>';
		print '<th class="modTools">Avg DB GET/hit</th>';
		print '<th class="modTools">Avg DB PUT/hit</th>';
		print '<th class="modTools">Avg DB Time (ms)</th>';
		print '</tr>';
		foreach ($metrics as $endpoint => $data) {
			// Extract type and clean endpoint name
			$type = $data['type'];
			$cleanEndpoint = str_replace($type . '_', '', $endpoint);
			// Convert endpoint name back to readable format
			if ($type == 'API') {
				$routeName = strtolower(str_replace('_', '/', $cleanEndpoint));
			} elseif ($type == 'AJAX') {
				// For AJAX, keep underscores but make lowercase
				$routeName = strtolower($cleanEndpoint);
			} else {
				// For PAGE, make lowercase with .php extension
				$routeName = strtolower($cleanEndpoint) . '.php';
			}
			// Calculate averages
			$avgTime = round($data['time_ms'] / $data['count'], 2);
			$avgDbGet = round($data['db_get'] / $data['count'], 2);
			$avgDbPut = round($data['db_put'] / $data['count'], 2);
			$avgDbTime = round($data['db_time_ms'] / $data['count'], 2);
			// Format bot hits display
			$botHits = ($data['bot_count'] === null) ? 'N/A' : $data['bot_count'];
			print '<tr>';
			print '<td class="modTools">'.$type.'</td>';
			print '<td class="modTools">'.$routeName.'</td>';
			print '<td class="modTools" style="text-align:right">'.$data['count'].'</td>';
			print '<td class="modTools" style="text-align:right">'.$botHits.'</td>';
			print '<td class="modTools" style="text-align:right">'.$avgTime.'</td>';
			print '<td class="modTools" style="text-align:right">'.$avgDbGet.'</td>';
			print '<td class="modTools" style="text-align:right">'.$avgDbPut.'</td>';
			print '<td class="modTools" style="text-align:right">'.$avgDbTime.'</td>';
			print '</tr>';
		}
		print '</TABLE>';
		// Display totals
		$totalCalls = array_sum(array_column($metrics, 'count'));
		$totalTime = array_sum(array_column($metrics, 'time_ms'));
		$totalDbGet = array_sum(array_column($metrics, 'db_get'));
		$totalDbPut = array_sum(array_column($metrics, 'db_put'));
		print '<p class="modTools"><strong>'.l_t('Totals:').'</strong> ';
		print $totalCalls . ' calls, ';
		print round($totalTime / 1000, 2) . ' seconds total time, ';
		print $totalDbGet . ' DB fetches, ';
		print $totalDbPut . ' DB writes</p>';
	}

	// The gamemaster.php runs counted above, split into their parts. Sorted by total time, as that's where
	// optimizing would pay off.
	$gamemasterMetrics = array();
	foreach ($gamemasterParts as $part) {
		$count = intval($Redis->get('METRICS_' . $part . '_COUNT'));
		if ($count > 0) {
			$gamemasterMetrics[$part] = array(
				'count' => $count,
				'time_ms' => (float)$Redis->get('METRICS_' . $part . '_TIME_MS'),
				'db_get' => (float)$Redis->get('METRICS_' . $part . '_DB_GET'),
				'db_put' => (float)$Redis->get('METRICS_' . $part . '_DB_PUT'),
				'db_time_ms' => (float)$Redis->get('METRICS_' . $part . '_DB_TIME_MS'),
			);
		}
	}
	if (!empty($gamemasterMetrics)) {
		uasort($gamemasterMetrics, function($a, $b) {
			return $b['time_ms'] <=> $a['time_ms'];
		});
		print '<h4>'.l_t('gamemaster.php by part:').'</h4>';
		print '<TABLE class="modTools">';
		print '<tr>';
		print '<th class="modTools">Part</th>';
		print '<th class="modTools">Hits</th>';
		print '<th class="modTools">Total Time (s)</th>';
		print '<th class="modTools">Avg Time (ms)</th>';
		print '<th class="modTools">Avg DB GET/hit</th>';
		print '<th class="modTools">Avg DB PUT/hit</th>';
		print '<th class="modTools">Avg DB Time (ms)</th>';
		print '</tr>';
		foreach ($gamemasterMetrics as $part => $data) {
			print '<tr>';
			print '<td class="modTools">'.strtolower(substr($part, strlen('GAMEMASTER_'))).'</td>';
			print '<td class="modTools" style="text-align:right">'.$data['count'].'</td>';
			print '<td class="modTools" style="text-align:right">'.round($data['time_ms'] / 1000, 1).'</td>';
			print '<td class="modTools" style="text-align:right">'.round($data['time_ms'] / $data['count'], 2).'</td>';
			print '<td class="modTools" style="text-align:right">'.round($data['db_get'] / $data['count'], 2).'</td>';
			print '<td class="modTools" style="text-align:right">'.round($data['db_put'] / $data['count'], 2).'</td>';
			print '<td class="modTools" style="text-align:right">'.round($data['db_time_ms'] / $data['count'], 2).'</td>';
			print '</tr>';
		}
		print '</TABLE>';
	}

	// What browsers reported about themselves (client/metrics and client/error). These are times measured in
	// the browser, so they include the network and the user's machine, which the server-side times above don't.
	$clientMetrics = array();
	foreach ($clientParts as $part) {
		$count = intval($Redis->get('METRICS_' . $part . '_COUNT'));
		if ($count > 0) {
			$clientMetrics[$part] = array(
				'count' => $count,
				'time_ms' => (float)$Redis->get('METRICS_' . $part . '_TIME_MS'),
			);
		}
	}
	if (!empty($clientMetrics)) {
		uasort($clientMetrics, function($a, $b) {
			return $b['count'] <=> $a['count'];
		});
		print '<h4>'.l_t('Reported by browsers:').'</h4>';
		print '<TABLE class="modTools">';
		print '<tr>';
		print '<th class="modTools">Part</th>';
		print '<th class="modTools">Reports</th>';
		print '<th class="modTools">Avg Time (ms)</th>';
		print '</tr>';
		foreach ($clientMetrics as $part => $data) {
			print '<tr>';
			print '<td class="modTools">'.strtolower(substr($part, strlen('CLIENT_'))).'</td>';
			print '<td class="modTools" style="text-align:right">'.$data['count'].'</td>';
			print '<td class="modTools" style="text-align:right">'.( $data['time_ms'] > 0 ? round($data['time_ms'] / $data['count'], 1) : '-' ).'</td>';
			print '</tr>';
		}
		print '</TABLE>';
	}
} catch (Exception $e) {
	print '<p class="notice">'.l_t('Could not connect to Redis: ').$e->getMessage().'</p>';
}

print '<div class="hr"></div>';

//SELECT userID, u.username, UNIX_TIMESTAMP() - lastHit secondsSinceLastHit, a.hits, FROM_UNIXTIME(lastHit) latestHit FROM wD_ApiKeys a INNER JOIN wD_Users u ON u.id = a.userID WHERE isChecked = 1;
//SELECT COUNT(*) FROM wD_ApiKeys a WHERE isChecked = 1 AND (UNIX_TIMESTAMP() - lastHit) > 3*60;

// Show currently running bot games:
if( isset($User) && $User->id == 10 )
{
	
}

/*
Find users who have played most with full press bots:
SELECT * FROM (SELECT u.username, u.email, u.points, COUNT(*) c, SUM(IF(g.gameOver='No',1,0)) active, MAX(g.turn) maxTurn FROM wD_Members b INNER JOIN wD_Games g ON g.id = b.gameID INNER JOIN wD_Members m ON m.gameID = g.id INNER JOIN wD_Users u ON u.id = m.userID LEFT JOIN wD_ApiKeys a ON a.userID = u.id WHERE b.userID = 181048 AND a.userID IS NULL GROUP BY u.username, u.email, u.points) a ORDER BY c;




Find backup games played with bots:

SELECT * FROM (SELECT u.username, u.email, u.points, COUNT(*) c, SUM(IF(g.gameOver='No',1,0
)) active, MAX(g.turn) maxTurn FROM wD_Backup_Members b INNER JOIN wD_Backup_Games g ON g.id = b.gameID INNER JOIN
wD_Backup_Members m ON m.gameID = g.id INNER JOIN wD_Users u ON u.id = m.userID LEFT JOIN wD_ApiKeys a ON a.userID
= u.id WHERE b.userID = 181048 AND a.userID IS NULL GROUP BY u.username, u.email, u.points) a ORDER BY c;
*/
print '<h3>Bot status</h3>';

print '<table class="hof"><tr><th>Label</th><th>User</th><th>API Calls</th><th>Last API Call</th><th>Multiplex offset*</th><th>Description</th></tr>';

$tabl = $DB->sql_tabl("SELECT u.id, u.username, u.type, u.points, u.identityScore, 
	a.hits, a.lastHit, a.multiplexOffset, a.description, a.label
FROM wD_ApiKeys a 
INNER JOIN wD_Users u ON u.id = a.userID
ORDER BY a.label");
while($row = $DB->tabl_hash($tabl))
{
	$profileLink = User::profile_link_static(
		$row['id'], $row['username'], $row['type'], $row['points'], $row['identityScore']
	);
	
	print '<tr>';
	print '<td>'.($row['label'] ?? $row['username']).'</td>';
	print '<td>'.$profileLink.'</td>';
	print '<td>'.$row['hits'].'</td>';
	print '<td>'.libTime::text($row['lastHit']).'</td>';
	print '<td>'.($row['multiplexOffset'] ?? 'N/A').'</td>';
	print '<td>'.($row['description'] ?? 'No description').'</td>';
	print '</tr>';
}
print '</table>'; 

print '<p>* The multiplex offset indicates that this bot account is one of several being run by a single AI engine.</p>';
print '</div>';

libHTML::footer();
