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

print libHTML::pageTitle(l_t('Hall of fame'),l_t('The webDiplomacy hall of fame, the 100 highest ranking players on the site.'));


print '<button class="SearchCollapsible">All Time Points</button>';
print'<div class="advancedSearchContent">';

if ( $User->type['User'] && $User->points > 100 )
{
	list($position) = $DB->sql_row("SELECT COUNT(id)+1 FROM wD_Users WHERE points > ".$User->points);
	$players = $Misc->RankingPlayers;

	print '<p class = "hof">'.l_t('You are ranked %s out of %s players with over 100%s','<a href="#me" class="light">#'.$position.'</a>',$players,libHTML::points()).
		l_t('. For more stats on your ranking, visit <a class="light" href="userprofile.php?userID='.$User->id.'">your profile</a>.').'</p>';
}

$i=1;
$crashed = $DB->sql_tabl("SELECT id, username, points FROM wD_Users order BY points DESC LIMIT 100 ");

print "<TABLE class='hof'>";
print "<tr>";
print '<th class= "hof">Points/Rank</th>';
print '<th class= "hof">User</th>';
print "</tr>";

$showMe = 1;
while ( list($id, $username, $points) = $DB->tabl_row($crashed) )
{
	print ' <tr class="hof">
			<td class="hof"> '.number_format($points).' '.libHTML::points().' - #'.$i.' </td>';
	if ($User->username == $username)
	{
		print '<td class="hof"><a class="hof-self" href="userprofile.php?userID='.$id.'">'.$username.'</a></td> ';
		$showMe = 0;
	}
	else
	{
		print '<td class="hof"><a href="userprofile.php?userID='.$id.'">'.$username.'</a></td> ';
	}
	print'	</tr>';
	$i++;
}

if ( $User->type['User'] && $User->points > 100 && $showMe == 1 )
{
	print ' <tr class="hof"><td class="hof">...</td><td class="hof">...</td></tr>';
	print ' <tr class="hof">
			<td class="hof"> '.number_format($User->points).' '.libHTML::points().' - <a name="me"></a>#'.$position.' </td>
			<td class="hof hof-self"><strong><em>'.$User->username.'</em></strong></td>
			</tr>';
}

print '</table>';
print '</div></br></br>';

print '<button class="SearchCollapsible">All Time Points (Last 6 Months)</button>';
print'<div class="advancedSearchContent"></br>';

$sixMonths = time() - 15552000;

if ( $User->type['User'] && $User->points > 100 && $User->timeLastSessionEnded > $sixMonths)
{
	list($position) = $DB->sql_row("SELECT COUNT(id)+1 FROM wD_Users WHERE points > ".$User->points." AND timeLastSessionEnded > ".$sixMonths);

	list($playersSixMonths) = $DB->sql_row("SELECT COUNT(1) FROM wD_Users WHERE points > 100  AND timeLastSessionEnded > ".$sixMonths);

	print '<p class = "hof">'.l_t('You are ranked %s out of %s players with over 100%s who have been active in the last six months','<a href="#me" class="light">#'.$position.'</a>',$playersSixMonths,libHTML::points()).
		l_t('. For more stats on your ranking visit <a class="light" href="userprofile.php?userID='.$User->id.'">your profile</a>.').'</p>';
}

$i=1;
$crashed = $DB->sql_tabl("SELECT id, username, points FROM wD_Users WHERE timeLastSessionEnded > ".$sixMonths." order BY points DESC LIMIT 100 ");

print "<TABLE class='hof'>";
print "<tr>";
print '<th class= "hof">Points/Rank</th>';
print '<th class= "hof">User</th>';
print "</tr>";

$showMe = 1;
while ( list($id, $username, $points) = $DB->tabl_row($crashed) )
{
	print ' <tr class="hof">
			<td class="hof"> '.number_format($points).' '.libHTML::points().' - #'.$i.' </td>';
	if ($User->username == $username)
	{
		print '<td class="hof"><a class="hof-self" href="userprofile.php?userID='.$id.'">'.$username.'</a></td> ';
		$showMe = 0;
	}
	else
	{
		print '<td class="hof"><a href="userprofile.php?userID='.$id.'">'.$username.'</a></td> ';
	}
	print'	</tr>';
	$i++;
}

if ( $User->type['User'] && $User->points > 100 &&  $User->timeLastSessionEnded > $sixMonths and $showMe == 1 )
{
	print ' <tr class="hof"><td class="hof">...</td><td class="hof">...</td></tr>';
	print ' <tr class="hof">
			<td class="hof"> '.number_format($User->points).' '.libHTML::points().' - <a name="me"></a>#'.$position.' </td>
			<td class="hof hof-self"><strong><em>'.$User->username.'</em></strong></td>
			</tr>';
}

print '</table>';
print '</div></br></br>';

// Ghost Rating section
print '<button class="SearchCollapsible">Overall Peak Ghost Rating</button>';
print'<div class="advancedSearchContent">';

$currentRating = 0;
list ($currentRating) = $DB->sql_row("SELECT rating FROM wD_GhostRatings WHERE categoryID = 0 and userID = ".$User->id);

if ( $User->type['User'] && $currentRating > 0 )
{
	list($position) = $DB->sql_row("SELECT COUNT(userID)+1 FROM wD_GhostRatings WHERE categoryID = 0 and rating > ".$currentRating);
	list($players) = $DB->sql_row("SELECT COUNT(1) FROM wD_GhostRatings WHERE categoryID = 0 and rating  > 100");

	print '<p class = "hof">'.l_t('You are ranked <a href="#me" class="light">#'.$position.'</a> out of '.$players.' players with an overall GR of over 100').
		l_t('. For more stats on your ranking, visit <a class="light" href="userprofile.php?userID='.$User->id.'">your profile</a>.').'</p>';
}

$i=1;
$crashed = $DB->sql_tabl("SELECT u.id, u.username, g.rating as 'points' FROM wD_GhostRatings g 
							inner join wD_Users u on g.userID = u.id 
							where g.categoryID = 0 order BY g.rating DESC LIMIT 100 ");

print "<TABLE class='hof'>";
print "<tr>";
print '<th class= "hof">Ghost Rating/Rank</th>';
print '<th class= "hof">User</th>';
print "</tr>";

$showMe = 1;
while ( list($id, $username, $points) = $DB->tabl_row($crashed) )
{
	print ' <tr class="hof">
			<td class="hof"> '.number_format($points).' - #'.$i.' </td>';
	if ($User->username == $username)
	{
		print '<td class="hof"><a class="hof-self" href="userprofile.php?userID='.$id.'">'.$username.'</a></td> ';
		$showMe = 0;
	}
	else
	{
		print '<td class="hof"><a href="userprofile.php?userID='.$id.'">'.$username.'</a></td> ';
	}
	print'	</tr>';
	$i++;
}

if ( $User->type['User'] && $currentRating > 100 && $showMe == 1 )
{
	print ' <tr class="hof"> <td class="hof">...</td> <td class="hof">...</td> </tr>';
	print ' <tr class="hof">
			<td class="hof"> '.number_format($currentRating).' - <a name="me"></a>#'.$position.' </td>
			<td class="hof hof-self"><strong><em>'.$User->username.'</em></strong></td>
			</tr>';
}

print '</table>';
print '</div></br></br>';

// Active GR section
print '<button class="SearchCollapsible">Overall Peak Ghost Rating (Last 6 Months)</button>';
print'<div class="advancedSearchContent"></br>';
$sixMonths = time() - 15552000;

if ( $User->type['User'] && $currentRating > 100 && $User->timeLastSessionEnded > $sixMonths)
{
	list($position) = $DB->sql_row("SELECT COUNT(userID)+1 FROM wD_GhostRatings g inner join wD_Users u on u.id = g.userID 
									WHERE categoryID = 0 and timeLastSessionEnded > ".$sixMonths." and rating > ".$currentRating);

	list($players) = $DB->sql_row("SELECT COUNT(1) FROM wD_GhostRatings g inner join wD_Users u on u.id = g.userID 
									WHERE categoryID = 0 and timeLastSessionEnded > ".$sixMonths." and rating  > 100");

	print '<p class = "hof">'.l_t('You are ranked <a href="#me" class="light">#'.$position.'</a> out of '.$players.' players with an overall GR of over 100 who have been active in the last six months').
		l_t('. For more stats on your ranking, visit <a class="light" href="userprofile.php?userID='.$User->id.'">your profile</a>.').'</p>';
}

$i=1;

$crashed = $DB->sql_tabl("SELECT u.id, u.username, g.peakRating as 'points' FROM wD_GhostRatings g 
							inner join wD_Users u on g.userID = u.id 
							where g.categoryID = 0 and timeLastSessionEnded > ".$sixMonths." order BY g.rating DESC LIMIT 100 ");

print "<TABLE class='hof'>";
print "<tr>";
print '<th class= "hof">Ghost Rating/Rank</th>';
print '<th class= "hof">User</th>';
print "</tr>";

$showMe = 1;
while ( list($id, $username, $points) = $DB->tabl_row($crashed) )
{
	print ' <tr class="hof">
			<td class="hof"> '.number_format($points).' - #'.$i.' </td>';
	if ($User->username == $username)
	{
		print '<td class="hof"><a class="hof-self" href="userprofile.php?userID='.$id.'">'.$username.'</a></td> ';
		$showMe = 0;
	}
	else
	{
		print '<td class="hof"><a href="userprofile.php?userID='.$id.'">'.$username.'</a></td> ';
	}
	print'	</tr>';
	$i++;
}

if ( $User->type['User'] && $currentRating > 100 &&  $User->timeLastSessionEnded > $sixMonths and $showMe == 1 )
{
	print ' <tr class="hof"><td class="hof">...</td><td class="hof">...</td></tr>';
	print ' <tr class="hof">
			<td class="hof"> '.number_format($currentRating).' - <a name="me"></a>#'.$position.' </td>
			<td class="hof hof-self"><strong><em>'.$User->username.'</em></strong></td>
			</tr>';
}

print '</table>';
print '</div>';

print '</div>';
?>

<script>
var coll = document.getElementsByClassName("SearchCollapsible");
var searchCounter;

for (searchCounter = 0; searchCounter < coll.length; searchCounter++) {
  coll[searchCounter].addEventListener("click", function() {
    this.classList.toggle("active");
    var content = this.nextElementSibling;
		if (content.style.display === "block") { content.style.display = "none"; }
		else { content.style.display = "block"; }
  });
}
</script>

<?php
libHTML::footer();
?>
