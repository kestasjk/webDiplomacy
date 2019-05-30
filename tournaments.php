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

require_once('header.php');

global $User, $Misc, $DB;

$tab = 'Finished';

// Get values from posted form. 
if(isset($_POST['submit'])) 
{
    if( isset($_POST['spectateID']) )
    {
        if($User->type['User'] )
        {
            $tournamentID = (int)$_POST['spectateID'];

            list($alreadySpectating) = $DB->sql_row("SELECT COUNT(1) FROM wD_TournamentSpectators s WHERE s.tournamentID = ".$tournamentID." and s.userID = ".$User->id);
            
            // spectate or unspecate
            if ($alreadySpectating > 0)
            {
                $sql = "delete FROM wD_TournamentSpectators WHERE tournamentID = ".$tournamentID." and userID = ".$User->id;
                $DB->sql_put($sql);
            }
            else
            {
                $sql = "insert into wD_TournamentSpectators (tournamentID, userID) values (".$tournamentID.", ".$User->id.")";
                $DB->sql_put($sql);
            }
            $tab = $_POST['tabs'];
        }
    }
}

libHTML::starthtml();

print '<div class="content">';

$tabs = array();

list($open) = $DB->sql_row("SELECT COUNT(1) FROM wD_Tournaments t WHERE t.status = 'Registration'");
list($ongoing) = $DB->sql_row("SELECT COUNT(1) FROM wD_Tournaments t WHERE t.status = 'Active'");
list($finished) = $DB->sql_row("SELECT COUNT(1) FROM wD_Tournaments t WHERE t.status = 'Finished'");

if ($open > 0) { $tabs['Registration Open']=l_t("Tournaments that are open for signup"); }
if ($ongoing > 0) { $tabs['Ongoing']=l_t("Tournaments that are currently running"); }


if($User->type['User'] )
{
	list($participating) = $DB->sql_row("SELECT COUNT(1) FROM wD_TournamentParticipants p INNER JOIN wD_Tournaments t ON t.id = p.tournamentID 
        WHERE t.status <> 'Finished' and p.userID = ".$User->id);

	list($spectating) = $DB->sql_row("SELECT COUNT(1) FROM wD_TournamentSpectators s INNER JOIN wD_Tournaments t ON t.id = s.tournamentID 
    WHERE t.status <> 'Finished' and s.userID = ".$User->id);

    if ($spectating > 0) 
    { 
        $tabs['Spectating']=l_t("Tournaments you are watching"); 
        $tab = 'Spectating';
    }
    if ($participating > 0) 
    { 
        $tabs['Participating']=l_t("Tournaments you are playing in."); 
        $tab = 'Participating';
    }

    list($allowedTD) = $DB->sql_row("SELECT count(1) FROM wD_Tournaments t WHERE t.status <> 'Finished' and t.directorID = ".$User->id." or t.coDirectorID = ".$User->id);

    if ( ( $allowedTD > 0) || ($User->type['Moderator'] ))
    {
        $tabs['Moderating']=l_t("Tournaments you have access to moderate."); 
        $tab = 'Moderating';
    }
}

$tabs['Finished']=l_t("Tournaments that have ended");

// add a update time to the scoring so notifications can be sent out in the future around scoring?

$tabNames = array_keys($tabs);

if( isset($_REQUEST['tab']) && in_array($_REQUEST['tab'], $tabNames) )
{
	$tab = $_SESSION['tab'] = $_REQUEST['tab'];
}

if ($tab <> 'Search')
{
	print "<a name='results'></a>";
}
print '<div class="gamelistings-tabsNew">';

foreach($tabs as $tabChoice=>$tabTitle)
{
	print '<a title="'.$tabTitle.'" href="tournaments.php?tab='.$tabChoice;

	if ( $tab == $tabChoice ) {	print '" class="gamelistings-tabsNewActive"'; } 
	else {print '"'; }

	print '>'.l_t($tabChoice).'</a> ';
}

print '</div>';

libHTML::pagebreak();

if ($tab == 'Finished')
{
    $sql = "select * from wD_Tournaments t where t.status = 'Finished' ";
    $sqlCounter = "select count(1) from wD_Tournaments t where t.status = 'Finished' ";
}

else if ($tab == 'Ongoing')
{
    $sql = "select * from wD_Tournaments t where t.status = 'Active' ";
    $sqlCounter = "select count(1) from wD_Tournaments t where t.status = 'Active' ";
}

else if ($tab == 'Spectating')
{
    $sql = "select t.* from wD_Tournaments t inner join wD_TournamentSpectators s on s.tournamentID = t.id where t.status <> 'Finished' and s.userID =".$User->id;
    $sqlCounter = "select count(1) from wD_Tournaments t inner join wD_TournamentSpectators s on s.tournamentID = t.id where t.status <> 'Finished' and s.userID =".$User->id;
}

else if ($tab == 'Participating')
{
    $sql = "select t.* from wD_Tournaments t inner join wD_TournamentParticipants s on s.tournamentID = t.id where t.status <> 'Finished' and s.userID =".$User->id;
    $sqlCounter = "select count(1) from wD_Tournaments t inner join wD_TournamentParticipants s on s.tournamentID = t.id where t.status <> 'Finished' and s.userID =".$User->id;
}
else if ($tab == 'Registration Open')
{
    $sql = "select * from wD_Tournaments t where t.status = 'Registration' ";
    $sqlCounter = "select count(1) from wD_Tournaments t where t.status = 'Registration' ";
}
else if ($tab == 'Moderating')
{
    if ($User->type['Moderator'] )
    {
        $sql = "select * from wD_Tournaments t where t.status <> 'Finished' ";
        $sqlCounter = "select count(1) from wD_Tournaments t where t.status <> 'Finished' ";
    }
    else
    {
        $sql = "select * from wD_Tournaments t where t.status <> 'Finished' and (t.directorID =".$User->id." or t.coDirectorID = ".$User->id.")";
        $sqlCounter = "select count(1) from wD_Tournaments t where t.status <> 'Finished' and (t.directorID =".$User->id." or t.coDirectorID = ".$User->id.")";
    }
}

$tablChecked = $DB->sql_tabl($sql);
list($results) = $DB->sql_row($sqlCounter);

/*
* Loop through all tournaments that are aren't finished
*/
if ($results > 0) { print 'Showing '.$results.' results'; }
else { print 'No tournaments meet the criteria right now.'; }

while (list($id, $name, $description, $status, $minRR, $year, $totalRounds, $forumThreadLink, $externalLink, $directorID, $coDirectorID, $firstPlace, $secondPlace, $thirdPlace) = $DB->tabl_row($tablChecked))
{
    print '<div class = "tournamentShow">';
    print '<h2 class = "tournamentCenter">'.$name.'</h2>';
    
    if ($tab == 'Finished')
    {
        if ($firstPlace > 0)
        {
            list($firstUsername) = $DB->sql_row("Select u.username from wD_Users u where u.id =".$firstPlace);
            print '<div class = "tournamentCenter">First Place: <a href="profile.php?userID='.$firstPlace.'">'.$firstUsername.'</a></div>';
        }
        if ($secondPlace > 0)
        {
            list($secondUsername) = $DB->sql_row("Select u.username from wD_Users u where u.id =".$secondPlace);
            print '<div class = "tournamentCenter">Second Place: <a href="profile.php?userID='.$secondPlace.'">'.$secondUsername.'</a></div>';
        }
        if ($thirdPlace > 0)
        {
            list($thirdUsername) = $DB->sql_row("Select u.username from wD_Users u where u.id =".$thirdPlace);
            print '<div class = "tournamentCenter">Third Place: <a href="profile.php?userID='.$thirdPlace.'">'.$thirdUsername.'</a></div>';
        }  
    }
    if ($status != 'PreStart')
    {
        print '<a href="tournamentScoring.php?tournamentID='.$id.'">Scoring and Participants</a></br>';
        if($status != 'Registration')
        {
            print '<a href="gamelistings.php?gamelistType=Search&tournamentID='.$id.'">Tournament Games</a></br>';
        }
    }
    if ($tab == 'Moderating' || $tab == 'Finished')
    {
        if ( ( $allowedTD > 0) || ($User->type['Moderator'] ))
        {
            print '<a href="tournamentManagement.php?tournamentID='.$id.'">Modify Tournament</a></br></br>';
        }
    }
    print '<div class = "tournament_round">Details</div>';
    print '<div class = "tournament_info">';
    if ($directorID > 0 )
    {
        list($directorUsername) = $DB->sql_row("Select username from wD_Users where id =".$directorID);
        print '<strong>Director:</strong> <a href="profile.php?userID='.$directorID.'">'.$directorUsername.'</a>';
    }
    if ($coDirectorID > 0 )
    {
        list($coDirectorUsername) = $DB->sql_row("Select username from wD_Users where id =".$coDirectorID);
        print '</br> <strong>Co-Director:</strong> <a href="profile.php?userID='.$coDirectorID.'">'.$coDirectorUsername.'</a></br>';
    }
    if ($forumThreadLink != '')
    {
        print '</br> <strong>Forum thread:</strong> <a href="'.$forumThreadLink.'">here</a>';
    }
    if ($externalLink != '')
    {
        print '</br> <strong>External site:</strong> <a href="'.$externalLink.'">here</a></br></br>';
    }

    print'<strong>Description:</strong></br>'.$description.'
    </br></br>
    <strong>Start year:</strong> '.$year.' </br></br>
    <strong>Rounds: </strong> '.$totalRounds.'
    </br> </br>
    <strong>Required Reliability:</strong> '.$minRR.'%';
    print '</div>';

    // list through all the games in this tournament with a status table in a collapsable element.

    $tablRounds = $DB->sql_tabl("select distinct round from wD_TournamentGames where tournamentID = ".$id." order by round");

    while (list($round) = $DB->tabl_row($tablRounds))
    {
        print '<div class = "tournament_round"> Round '.$round.'</div>';
        print '<div class = "tournament_games">';
        print '<a href="gamelistings.php?gamelistType=Search&tournamentID='.$id.'&round='.$round.'&Submit=Search#results">Search Round '.$round.' games</a></br>';
        print '<TABLE class="tournament">';
        print '<tr>';
        print '<th class= "tournament">Game</th>';
        print '<th class= "tournament">Turn</th>';
        print '<th class= "tournament">phase</th>';
        print '<th class= "tournament">Status</th>';
        print '<th class= "tournament">Process Time</th>';
        print '</tr>';
        
        $tablRoundsGames = $DB->sql_tabl("select g.id, g.name, g.turn, g.phase, g.gameOver, g.processStatus, g.processTime from wD_TournamentGames t inner join 
        wD_Games g on g.id = t.gameID where tournamentID = ".$id." and t.round = ".$round);

        while (list($gameID, $gameName, $turn, $phase, $gameOver, $processStatus, $processTime) = $DB->tabl_row($tablRoundsGames))
        {
            $Variant=libVariant::loadFromGameID($gameID);
            $Game = $Variant->Game($gameID);

            print '<TR><td><a href="board.php?gameID='.$gameID.'">'.$gameName.'</a></TD>';
            print '<td>'.$Variant->turnAsDate($turn).'</td>';
            print '<td>'.$phase.'</td>';

            $finalStatus = $gameOver;
            if ($finalStatus == 'No')
            {
                if ($Game->missingPlayerPolicy=='Wait'&&!$Game->Members->isCompleted() && time()>=$Game->processTime)
                {
                    print '<td style="background-color:#F08080;"> <strong>Waiting for Orders</strong></td>';
                }
                else if ($processStatus == 'Crashed')
                {
                    print '<td style="background-color:#F08080;"> <strong>Crashed</strong></td>';
                }
                else if ($processStatus == 'Paused')
                {
                    print '<td> <strong>Paused</strong></td>';
                }
                else
                {
                    print '<td>Running</td>';
                }
            }
            else
            {
                print '<td>'.$finalStatus.'</td>';
            }
            
            print '<td>'.libTime::detailedText($processTime).'</td>';
        }
        print '</table>';

        print'</div>';

    }

    list($userSpectating) = $DB->sql_row("Select count(1) from wD_TournamentSpectators s where s.tournamentID = ".$id." and s.userID = ".$User->id);

    if ($userSpectating == 1 and $status != 'Finished')
    {
        print '</br> </br>
        <form method="post" action="#">
            <input type="hidden" name="spectateID" value="'.$id.'">
            <input type="hidden" name="tabs" value="'.$tab.'">
            <input type="submit" class="green-Submit" name="submit" value="Stop Spectating Tournament">
        </form>
        ';
    }
    else if ($status != 'Finished')
    {
        print '</br> </br>
        <form method="post" action="#">
            <input type="hidden" name="spectateID" value="'.$id.'">
            <input type="hidden" name="tabs" value="'.$tab.'">
            <input type="submit" class="green-Submit" name="submit" value="Spectate Tournament">
        </form>
        ';
    }

     print '</div></br>';
}



// If the tournament is in progress or in the spectating or participating area then have a table in a clickable for each of the tournament results that shows 
// an overview of each of the games with information on them such as wfo, last/next process date, is paused. 

// Do a php loop on the request items to see how many to check through, have that auto filled in a hidden field on the form and then process through the list in request. 

print '</div></div>';

?>

<script type="text/javascript">
var coll = document.getElementsByClassName("tournament_round");
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
