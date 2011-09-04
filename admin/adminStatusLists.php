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

if( $User->type['Admin'] )
{
	//There may be sensitive info that would allow privilege escalation in these error logs

	print '<p><strong>Error logs:</strong> '.libError::stats().' ('.libHTML::admincp('clearErrorLogs',null,'Clear').')</p>';

	$dir =  libError::directory();
	$errorlogs = libError::errorTimes();

	$alternate = false;
	print '<TABLE class="credits">';

	foreach ( $errorlogs as $errorlog )
	{
		$alternate = ! $alternate;

		print '<tr class="replyalternate'.($alternate ? '1' : '2' ).'">';
		print '<td class="left time">'.libTime::text($errorlog).'</td>';
		print '<td class="right message"><a class="light" href="admincp.php?viewErrorLog='.$errorlog.'">Open</a></td>';
		print '</tr>';
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

	print '<p><strong>'.$name.'</strong></p>';
	if( is_array($query) )
		$result = $query;
	else
	{
		$result=array();
		$tabl = $DB->sql_tabl($query);
		while( list($col1,$col2) = $DB->tabl_row($tabl) )
			$result[]=array($col1,$col2);
	}

	$alternate = false;
	print '<TABLE class="credits">';

	foreach($result as $row)
	{
		list($col1,$col2)=$row;

		$alternate = ! $alternate;

		print '<tr class="replyalternate'.($alternate ? '1' : '2' ).'">';
		print '<td class="left time">'.$col1.'</td>';
		print '<td class="right message">'.$col2.'</td>';
		print '</tr>';
	}

	print '</TABLE>';
}

function adminStatusList($name, $query)
{
	global $DB;

	print '<p><strong>'.$name.':</strong> ';

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
			print '<p class="notice">Order logging not enabled; check config.php</p>';
		}
		else
		{
			require_once('objects/game.php');

			$logfile = libCache::dirID($orderlogDirectory, $viewOrderLogGameID, true).'/'.$viewOrderLogCountryID.'.txt';

			if( ! file_exists($logfile) ) {
				print '<p class="notice">No log file found for this gameID/countryID.</p>';
			} else {
				print '<pre>'.file_get_contents($logfile).'</pre>';
			}

			unset($logfile);
		}
	} else {
		$viewOrderLogGameID='';
		$viewOrderLogCountryID='';
	}

	print '<p><strong>Order logs:</strong><form action="admincp.php" method="get">
		Game ID: <input type="text" name="viewOrderLogGameID" value="'.$viewOrderLogGameID.'" />
		CountryID: <input type="text" name="viewOrderLogCountryID" value="'.$viewOrderLogCountryID.'" />
		<input type="submit" name="Submit" /></form></p>';
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

adminStatusList('Crashed games',"SELECT CONCAT('<a href=\"board.php?gameID=',id,'\" class=\"light\">',name,'</a>')
	FROM wD_Games WHERE processStatus = 'Crashed'");
adminStatusList('Processing games',"SELECT CONCAT('<a href=\"board.php?gameID=',id,'\" class=\"light\">',name,'</a>')
	FROM wD_Games WHERE processStatus = 'Processing'");
adminStatusList('Paused games',"SELECT CONCAT('<a href=\"board.php?gameID=',id,'\" class=\"light\">',name,'</a>')
	FROM wD_Games WHERE processStatus = 'Paused'");

//require_once('gamemaster/game.php');
//adminStatusTable('Backed up games',processGame::backedUpGames());

adminStatusList('Banned users',"SELECT CONCAT('<a href=\"profile.php?userID=',id,'\" class=\"light\">',username,'</a>')
	FROM wD_Users WHERE type LIKE '%Banned%'");
adminStatusList('Mods',"SELECT CONCAT('<a href=\"profile.php?userID=',id,'\" class=\"light\">',username,'</a>')
	FROM wD_Users WHERE type LIKE '%Moderator%'");
adminStatusList('Admins',"SELECT CONCAT('<a href=\"profile.php?userID=',id,'\" class=\"light\">',username,'</a>')
	FROM wD_Users WHERE type LIKE '%Admin%'");
adminStatusList('Donors',"SELECT CONCAT('<a href=\"profile.php?userID=',id,'\" class=\"light\">',username,'</a>')
	FROM wD_Users WHERE type LIKE '%Donator%'");

?>