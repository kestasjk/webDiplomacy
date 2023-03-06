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


libHTML::starthtml();

print libHTML::pageTitle(l_t('Bot status'),l_t('View the current status of the AI bots available on this site.'));
 
print '<div class="content">';

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
