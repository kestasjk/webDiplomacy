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
 * Hall of fame; the top 100 webDip scorers
 *
 * @package Base
 * @subpackage Game
 */

require_once('header.php');

libHTML::starthtml();

print libHTML::pageTitle('Hall of fame','The webDiplomacy hall of fame; the 100 highest ranking players on this server.');

print '<p align="center"><img src="images/points/stack.png" alt=" "
			title="webDiplomacy ranking points; who are the most skilled at gathering them from their foes?" /></p>';

print '<p></p>';

if ( $User->type['User'] && $User->points > 100 )
{
	list($position) = $DB->sql_row(
		"SELECT COUNT(id)+1 FROM wD_Users WHERE points > ".$User->points);

	$players = $Misc->RankingPlayers;

	print '<p>You are ranked <a href="#me" class="light">#'.$position.'</a>
		out of '.$players.' ranking players (players with >100'.libHTML::points().').
		For more stats on your ranking visit
		<a class="light" href="profile.php?userID='.$User->id.'">your profile</a>,</p>';
}

print '<table class="credits">';

$alternate = false;

$i=1;
$crashed = $DB->sql_tabl("SELECT id, username, points FROM wD_Users
						order BY points DESC LIMIT 100 ");
while ( list($id, $username, $points) = $DB->tabl_row($crashed) )
{
	$alternate = !$alternate;
	print '
	<tr class="replyalternate'.($alternate ? '1' : '2' ).'">
		<td class="left time">
			'.$points.' '.libHTML::points().' - #'.$i.'
		</td>

		<td class="right message"><a href="profile.php?userID='.$id.'">'.$username.'</a></td>
	</tr>';
	$i++;
}

if ( $User->type['User'] && $User->points > 100 )
{
	print '<tr class="replyalternate'.($alternate ? '1' : '2' ).'">
		<td class="left time">
			'.$User->points.' '.libHTML::points().' - <a name="me"></a>#'.$position.'
		</td>

		<td class="right message"><strong><em>'.$User->username.'</em></strong></td>
	</tr>';
}

print '</table>';

print '</div>';
libHTML::footer();

?>
