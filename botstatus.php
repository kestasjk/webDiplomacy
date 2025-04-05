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

libHTML::starthtml();

print libHTML::pageTitle(l_t('Bot status'),l_t('View the current status of the AI bots available on this site.'));
 
print '<div class="content">';

//SELECT userID, u.username, UNIX_TIMESTAMP() - lastHit secondsSinceLastHit, a.hits, FROM_UNIXTIME(lastHit) latestHit FROM wD_ApiKeys a INNER JOIN wD_Users u ON u.id = a.userID WHERE isChecked = 1;
//SELECT COUNT(*) FROM wD_ApiKeys a WHERE isChecked = 1 AND (UNIX_TIMESTAMP() - lastHit) > 3*60;

// Show currently running bot games:
if( isset($User) && $User->id == 10 )
{
	// Full press:
	$tabl = $DB->sql_tabl("SELECT g.turn, g.name, g.id, FROM_UNIXTIME(g.processTime) nextProcess, u.username, FROM_UNIXTIME(m.timeLoggedIn) lastLoggedOn, m.missedPhases, m.supplyCenterNo, FROM_UNIXTIME(u.timeLastSessionEnded) lastSessionEnd, FROM_UNIXTIME(s.firstRequest) firstRequest, FROM_UNIXTIME(s.lastRequest) lastRequest, s.hits FROM wD_ApiKeys a INNER JOIN wD_Members m ON m.userID = a.userID INNER JOIN wD_Games g ON g.id = m.gameID INNER JOIN wD_Members hm ON hm.gameID = g.id INNER JOIN wD_Users u ON u.id = hm.userID LEFT JOIN wD_Sessions s ON s.userID = u.id LEFT JOIN wD_ApiKeys ha ON ha.userID = u.id WHERE a.username = 'dipgpt3' AND g.phase <> 'Finished' AND ha.userID IS NULL");

	// No press, members:
	$tabl = $DB->sql_tabl("SELECT g.turn, g.name, g.id, FROM_UNIXTIME(g.processTime) nextProcess, u.username, FROM_UNIXTIME(m.timeLoggedIn) lastLoggedOn, m.missedPhases, m.supplyCenterNo,FROM_UNIXTIME(u.timeLastSessionEnded) lastSessionEnd, FROM_UNIXTIME(s.firstRequest) firstRequest, FROM_UNIXTIME(s.lastRequest) lastRequest, s.hits FROM wD_Games g INNER JOIN wD_Members m ON m.gameID = g.id INNER JOIN wD_Users u ON u.id = m.userID LEFT JOIN wD_Sessions s ON s.userID = u.id WHERE NOT u.type LIKE '%Bot%' AND g.gameOver = 'No' AND g.playerTypes = 'MemberVsBots' AND NOT u.username LIKE 'diplonow_%' AND NOT g.name LIKE 'SB_%'");

	// No press, anonymous:
	$tabl = $DB->sql_tabl("SELECT g.turn, g.name, g.id, FROM_UNIXTIME(g.processTime) nextProcess, u.username, FROM_UNIXTIME(m.timeLoggedIn) lastLoggedOn, m.missedPhases, m.supplyCenterNo, FROM_UNIXTIME(u.timeLastSessionEnded) lastSessionEnd, FROM_UNIXTIME(s.firstRequest) firstRequest, FROM_UNIXTIME(s.lastRequest) lastRequest, s.hits FROM wD_Games g INNER JOIN wD_Members m ON m.gameID = g.id INNER JOIN wD_Users u ON u.id = m.userID LEFT JOIN wD_Sessions s ON s.userID = u.id WHERE NOT u.type LIKE '%Bot%' AND g.gameOver = 'No' AND g.playerTypes = 'MemberVsBots' AND u.username LIKE 'diplonow_%' AND NOT g.name LIKE 'SB_%'");
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
print '<table><tr><th>Label</th><th>User</th><th>API Calls</th><th>Last API Call</th><th>Multiplex offset*</th><th>Description</th></tr>';

$tabl = $DB->sql_tabl("SELECT u.id, u.username, u.type, u.points, u.identityScore, 
a.hits, a.lastHit, a.multiplexOffset, a.description, a.label,
p.getStateOfAllGames, p.submitOrdersForUserInCD, p.listGamesWithPlayersInCD, p.getRedactedMessages, p.submitOrdersForDelegatedMembers,
p.submitMessages, p.voteDraw, p.playBotsVsHuman, p.playBotVsHuman, p.minimumPhaseLength, p.variantIDs
FROM wD_ApiKeys a 
INNER JOIN wD_Users u ON u.id = a.userID
INNER JOIN wD_ApiPermissions p ON u.id = p.userID
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
