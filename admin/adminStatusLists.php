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
 * Display the error logs and other lists useful for admins
 *
 * @package Admin
 */
if( !isset($_REQUEST['full']) )
	print '<p class = "modTools"> <a class="modTools" href="admincp.php?tab=Status Info&full=on">'.l_t('View all logs').'</a>
	</br> Error logs, banned users, and donator lists are limited to 50 items, use this link to see full result set.</p>';

if( $User->type['Admin'] )
{
	//There may be sensitive info that would allow privilege escalation in these error logs

	print '<p class="modTools"><strong>'.l_t('Error logs:').'</strong> '.libError::stats().' ('.libHTML::admincp('clearErrorLogs',null,'Clear').')</p>';

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

if( $User->type['Admin'] ) {
	// Get the order logs used to validate whether stories about orders being swapped around
	if( isset($_REQUEST['viewOrderLogGameID']) && isset($_REQUEST['viewOrderLogCountryID']) ) {
		$viewOrderLogGameID=(int)$_REQUEST['viewOrderLogGameID'];
		$viewOrderLogCountryID=(int)$_REQUEST['viewOrderLogCountryID'];

		$orderlogDirectory = Config::orderlogDirectory();
		if ( false === $orderlogDirectory ) {
			print '<p class="notice">'.l_t('Order logging not enabled; check config.php').'</p>';
		}
		else
		{
			require_once(l_r('objects/game.php'));

			$logfile = libCache::dirID($orderlogDirectory, $viewOrderLogGameID, true).'/'.$viewOrderLogCountryID.'.txt';

			if( ! file_exists($logfile) ) {
				print '<p class="notice">'.l_t('No log file found for this gameID/countryID.').'</p>';
			} else {
				print '<pre>'.file_get_contents($logfile).'</pre>';
			}

			unset($logfile);
		}
	} else {
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

adminStatusList(l_t('Mods'),"SELECT CONCAT('<a href=\"profile.php?userID=',id,'\" class=\"light\">',username,'</a>')
	FROM wD_Users WHERE type LIKE '%Moderator%'");
adminStatusList(l_t('Admins'),"SELECT CONCAT('<a href=\"profile.php?userID=',id,'\" class=\"light\">',username,'</a>')
	FROM wD_Users WHERE type LIKE '%Admin%'");
adminStatusList(l_t('Donors'),"SELECT CONCAT('<a href=\"profile.php?userID=',id,'\" class=\"light\">',username,'</a>')
	FROM wD_Users WHERE type LIKE '%Donator%'".(isset($_REQUEST['full'])?'':"LIMIT 50"));
adminStatusList(l_t('Banned users'),"SELECT CONCAT('<a href=\"profile.php?userID=',id,'\" class=\"light\">',username,'</a>')
FROM wD_Users WHERE type LIKE '%Banned%'".(isset($_REQUEST['full'])?'':"LIMIT 50"));

list($notice) = $DB->sql_row("SELECT message FROM wD_Config WHERE name = 'Notice'");
list($panic) = $DB->sql_row("SELECT message FROM wD_Config WHERE name = 'Panic'");
list($maintenance) = $DB->sql_row("SELECT message FROM wD_Config WHERE name = 'Maintenance'");
print '<br/><br/>';
print '<b>Site-Wide Notice: '.$notice.'</b><br/><br/>';
print '<b>Panic Message: '.$panic.'</b><br/><br/>';
print '<b>Maintenance Message: '.$maintenance.'</b><br/><br/>';

?>
