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
 * This script displays the 20 most recent admin/moderator actions made with the adminActionsForms class.
 *
 * @package Admin
 */


if( !isset($_REQUEST['full']) )
	print '<a class="modTools" href="admincp.php?tab=Logs&full=on">'.l_t('View all logs').'</a>';

print '<table class= "modTools">';
print '<th class= "modTools">Time</th>';
print '<th class= "modTools">Action</th>';

$alternate = 1;
$tabl = $DB->sql_tabl(
	"SELECT a.name, u.username, a.time, a.details, a.params
		FROM wD_AdminLog a INNER JOIN wD_Users u ON ( a.userID = u.id )
		ORDER BY a.time DESC ".(isset($_REQUEST['full'])?'':"LIMIT 20"));

while ( $row = $DB->tabl_hash($tabl) )
{
	$row['time'] = libTime::text($row['time']);

	$params = $row['params'];

	print '<tr><td class= "modTools">'.$row['time'].'</td>';
	print '<td class= "modTools">'.$row['username'].': <strong>'.$row['name'].'</strong>: '.$row['details'].$params.'</td></tr>';
}

print '</table>';

?>