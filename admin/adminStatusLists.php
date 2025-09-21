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

defined('IN_CODE') or die('This script can not be run by itself.');

/**
 * Display the error logs and other lists useful for admins such as performance metrics
 *
 * @package Admin
 */

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
	'WEBSOCKETS_AUTHENTICATION',
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
$metricTypes = array('COUNT', 'TIME_MS', 'DB_GET', 'DB_PUT', 'DB_TIME_MS', 'BOTCOUNT');
					
// Handle clearing API metrics if requested
if( $User->type['Admin'] && isset($_GET['clearAPIMetrics']) )
{
	try {
		if (class_exists('Redis')) {
			require_once(l_r('objects/redis.php'));
			$Redis = new RedisInterface(Config::$redisHost, Config::$redisPort);


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

			print '<div class="notice">'.l_t('All metrics cleared successfully. Removed %s metric keys from Redis (API, AJAX, and PAGE metrics).', $clearedCount).'</div>';
		} else {
			print '<div class="notice">'.l_t('Redis extension not available. Cannot clear metrics.').'</div>';
		}
	} catch (Exception $e) {
		print '<div class="notice">'.l_t('Error clearing metrics: ').$e->getMessage().'</div>';
	}
}
if( !isset($_REQUEST['full']) )
	print '<p class = "modTools"> <a class="modTools" href="admincp.php?tab=Status Info&full=on">'.l_t('View all logs').'</a>
	</br> Error logs, banned users, and donator lists are limited to 50 items, use this link to see full result set.</p>';

if( $User->type['Admin'] )
{
	//There may be sensitive info that would allow privilege escalation in these error logs

	print '<p class="modTools"><strong>'.l_t('Error logs:').'</strong> '.libError::stats().' ('.libHTML::admincp('clearErrorLogs',null,'Clear').')</p>';

	print '<p><strong>Script to extract the count of errors by type:</strong> cat *.txt | perl -ne \'m/^(Error)/ and s/\n// and $e=$_; m/^(Raised)/ and s/\n// and $r=$_; m/^(Line)/ and print $e.$r.$_;\' | sort | uniq -c | less </p>';

	$dir =  libError::directory();
	$errorlogs = libError::errorTimes();

	print '<TABLE class="modTools">';
	print "<tr>";
    print '<th class= "modTools">Time</th>';
	print '<th class= "modTools">Details</th>';
	print "</tr>";

	$loopCounter = 0;
	foreach ( $errorlogs as $errorlog )
	{
		if((!isset($_REQUEST['full'])) and ($loopCounter > 49 ))
			break;

		print '<tr><td class="modTools">'.libTime::text($errorlog).'</td>';
		print '<td class="modTools"><a class="modTools" href="admincp.php?viewErrorLog='.$errorlog.'">Open</a></td></tr>';
		$loopCounter = $loopCounter + 1;
	}

	print '</TABLE>';
}

/**
 * Fill a named table from a single column query
 *
 * @param string $name The table's name
 * @param string $query The single columned query which will return the data to display
 */
function adminStatusTable($name, $query)
{
	global $DB;

	print '<p class="modTools"><strong>'.$name.'</strong></p>';
	if( is_array($query) )
		$result = $query;
	else
	{
		$result=array();
		$tabl = $DB->sql_tabl($query);
		while( list($col1,$col2) = $DB->tabl_row($tabl) )
			$result[]=array($col1,$col2);
	}

	print '<TABLE class="modTools">';
	print "<tr>";
    print '<th class= "modTools">Orders</th>';
	print '<th class= "modTools">Info</th>';
	print "</tr>";

	foreach($result as $row)
	{
		list($col1,$col2)=$row;

		print '<tr><td class= "modTools">'.$col1.'</td>';
		print '<td class= "modTools">'.$col2.'</td></tr>';
	}

	print '</TABLE>';
}

function adminStatusList($name, $query)
{
	global $DB;

	print '<p class="modTools"><strong>'.$name.':</strong> ';

	$tabl = $DB->sql_tabl($query);
	while( list($row) = $DB->tabl_row($tabl) )
	{
		print $row.', ';
	}
}

if( $User->type['Admin'] ) 
{
	// Get the order logs used to validate whether stories about orders being swapped around
	if( isset($_REQUEST['viewOrderLogGameID']) && isset($_REQUEST['viewOrderLogCountryID']) ) 
	{
		$viewOrderLogGameID=(int)$_REQUEST['viewOrderLogGameID'];
		$viewOrderLogCountryID=(int)$_REQUEST['viewOrderLogCountryID'];

		$orderlogDirectory = Config::orderlogDirectory();

		if ( false === $orderlogDirectory ) 
		{
			print '<p class="notice">'.l_t('Order logging not enabled; check config.php').'</p>';
		}
		else
		{
			require_once(l_r('objects/game.php'));

			$logfile = libCache::dirID($orderlogDirectory, $viewOrderLogGameID, true).'/'.$viewOrderLogCountryID.'.txt';

			if( ! file_exists($logfile) ) 
			{
				print '<p class="notice">'.l_t('No log file found for this gameID/countryID.').'</p>';
			} 
			else 
			{
				print '<pre>'.file_get_contents($logfile).'</pre>';
			}

			unset($logfile);
		}
	} 
	else 
	{
		$viewOrderLogGameID='';
		$viewOrderLogCountryID='';
	}

	print '<p class="modTools"><strong>'.l_t('Order logs:').'</strong><form class="modTools" action="admincp.php" method="get">
		'.l_t('Game ID').': <input class="modTools" type="text" name="viewOrderLogGameID" value="'.$viewOrderLogGameID.'" />
		'.l_t('CountryID').': <input class="modTools" type="text" name="viewOrderLogCountryID" value="'.$viewOrderLogCountryID.'" />
		<input class="form-submit" type="submit" name="'.l_t('Submit').'" /></form></p>';
}

/*
 * This fills so slowly it's not worth keeping track of, when it happened the first time it must have been due to
 * some error which set an id value much too high
 * adminStatusTable('ID overflow data - The **** hits the fan when these fill up, run
	<a href="admincp.php?systemTask=defragTables">defragTables</a> when they get close to full',

	"SELECT 'Orders', CONCAT(MAX(id),' / ',POW(2,31)-1,' = ',MAX(id)/(POW(2,31)-1)*100,'%') FROM wD_Orders
	UNION SELECT 'TerrStatus', CONCAT(MAX(id),' / ',POW(2,31)-1,' = ',MAX(id)/(POW(2,31)-1)*100,'%') FROM wD_TerrStatus
	UNION SELECT 'Units',CONCAT(MAX(id),' / ',POW(2,31)-1,' = ',MAX(id)/(POW(2,31)-1)*100,'%') FROM wD_Units ");
	*/

adminStatusList(l_t('Crashed games'),"SELECT CONCAT('<a href=\"board.php?gameID=',id,'\" class=\"light\">',name,'</a>')
	FROM wD_Games WHERE processStatus = 'Crashed'");
adminStatusList(l_t('Processing games'),"SELECT CONCAT('<a href=\"board.php?gameID=',id,'\" class=\"light\">',name,'</a>')
	FROM wD_Games WHERE processStatus = 'Processing'");
adminStatusList(l_t('Paused games'),"SELECT CONCAT('<a href=\"board.php?gameID=',id,'\" class=\"light\">',name,'</a>')
	FROM wD_Games WHERE processStatus = 'Paused'");

//require_once('gamemaster/game.php');
//adminStatusTable('Backed up games',processGame::backedUpGames());

adminStatusList(l_t('Mods'),"SELECT CONCAT('<a href=\"userprofile.php?userID=',id,'\" class=\"light\">',username,'</a>') FROM wD_Users WHERE type LIKE '%Moderator%'");
adminStatusList(l_t('Senior Mods'),"SELECT CONCAT('<a href=\"userprofile.php?userID=',id,'\" class=\"light\">',username,'</a>') FROM wD_Users WHERE type LIKE '%SeniorMod%'");
adminStatusList(l_t('Admins'),"SELECT CONCAT('<a href=\"userprofile.php?userID=',id,'\" class=\"light\">',username,'</a>') FROM wD_Users WHERE type LIKE '%Admin%'");
adminStatusList(l_t('Temp Banned - By Mod'),"SELECT CONCAT('<a href=\"userprofile.php?userID=',id,'\" class=\"light\">',username,'</a>')
FROM wD_Users WHERE tempBanReason is not null and tempBanReason <> 'System' and tempBan > ".time());

adminStatusList(l_t('Temp Banned - By System'),"SELECT CONCAT('<a href=\"userprofile.php?userID=',id,'\" class=\"light\">',username,'</a>')
FROM wD_Users WHERE (tempBanReason is null or tempBanReason = 'System') and tempBan > ".time());

adminStatusList(l_t('Donors'),"SELECT CONCAT('<a href=\"userprofile.php?userID=',id,'\" class=\"light\">',username,'</a>')
	FROM wD_Users WHERE type LIKE '%Donator%'".(isset($_REQUEST['full'])?'':"LIMIT 50"));

adminStatusList(l_t('Banned users'),"SELECT CONCAT('<a href=\"userprofile.php?userID=',id,'\" class=\"light\">',username,'</a>')
FROM wD_Users WHERE type LIKE '%Banned%'".(isset($_REQUEST['full'])?'':"LIMIT 50"));

list($notice) = $DB->sql_row("SELECT message FROM wD_Config WHERE name = 'Notice'");
list($panic) = $DB->sql_row("SELECT message FROM wD_Config WHERE name = 'Panic'");
list($maintenance) = $DB->sql_row("SELECT message FROM wD_Config WHERE name = 'Maintenance'");

print '<br/><br/>';
print '<b>Site-Wide Notice: '.$notice.'</b><br/><br/>';
print '<b>Panic Message: '.$panic.'</b><br/><br/>';
print '<b>Maintenance Message: '.$maintenance.'</b><br/><br/>';

// Display API Metrics from Redis if available (Admin only)
if( $User->type['Admin'] )
{
	print '<br/><hr/><br/>';
	print '<p class="modTools"><strong>'.l_t('API Performance Metrics:').'</strong> (<a href="admincp.php?tab=Status%20Info&clearAPIMetrics=1" onclick="return confirm(\'Are you sure you want to clear all API metrics? This action cannot be undone.\')" class="modTools">Clear All Metrics</a>)</p>';

	// Check if Redis extension is available
	if (!class_exists('Redis')) {
		print '<p class="notice">'.l_t('Redis PHP extension is not installed. API metrics collection requires Redis.').'</p>';
	} else {
		try {
			// Try to connect to Redis
			require_once(l_r('objects/redis.php'));
			$Redis = new RedisInterface(Config::$redisHost, Config::$redisPort);

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
				$count = $Redis->get('METRICS_PAGE_' . $endpoint . '_COUNT');

				if ($count && $count > 0) {
					$metrics['PAGE_' . $endpoint] = array(
						'count' => $count,
						'time_ms' => $Redis->get('METRICS_PAGE_' . $endpoint . '_TIME_MS') ?: 0,
						'db_get' => $Redis->get('METRICS_PAGE_' . $endpoint . '_DB_GET') ?: 0,
						'db_put' => $Redis->get('METRICS_PAGE_' . $endpoint . '_DB_PUT') ?: 0,
						'db_time_ms' => $Redis->get('METRICS_PAGE_' . $endpoint . '_DB_TIME_MS') ?: 0,
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
					return $b['count'] - $a['count'];
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

		} catch (Exception $e) {
			print '<p class="notice">'.l_t('Could not connect to Redis: ').$e->getMessage().'</p>';
		}
	}
}
?>
