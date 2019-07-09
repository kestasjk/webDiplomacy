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
$sortCol = 'year';
$sortType = 'desc';
if ( isset($_REQUEST['sortCol'])) { if ($_REQUEST['sortCol'] == 'name') { $sortCol='name'; } }
if ( isset($_REQUEST['sortType'])) { if ($_REQUEST['sortType'] == 'asc') { $sortType='asc'; } }

// Get values from posted form, used to let people spectate tournaments. 
if(isset($_POST['submit'])) 
{
    if( isset($_POST['spectateID']) )
    {
        if($User->type['User'] )
        {
            $tournamentID = (int)$_POST['spectateID'];

            list($alreadySpectating) = $DB->sql_row("SELECT COUNT(1) FROM wD_TournamentSpectators s WHERE s.tournamentID = ".$tournamentID." and s.userID = ".$User->id);
            
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
$tabNames = array_keys($tabs);

if( isset($_REQUEST['tab']) && in_array($_REQUEST['tab'], $tabNames) ) { $tab = $_SESSION['tab'] = $_REQUEST['tab']; }
if ($tab <> 'Search') { print "<a name='results'></a>"; } print '<div class="gamelistings-tabsNew">';

foreach($tabs as $tabChoice=>$tabTitle)
{
	print '<a title="'.$tabTitle.'" href="tournaments.php?tab='.$tabChoice;

	if ( $tab == $tabChoice ) {	print '" class="gamelistings-tabsNewActive"'; } 
	else {print '"'; }

	print '>'.l_t($tabChoice).'</a> ';
}

print '</div>';
print '<br/><div style="text-align:center">
    For detailed information on how tournaments work on webDiplomacy, click <a href="tournamentInfo.php">here</a>.</div>';

libHTML::pagebreak();

$pagenum = 1;
$resultsPerPage = 5;
$maxPage = 0;
$totalResults = 0;
if ( isset($_REQUEST['pagenum'])) { $pagenum=(int)$_REQUEST['pagenum']; }

if ($tab == 'Finished')
{
    $sql = "select * from wD_Tournaments t where t.status = 'Finished' ORDER BY ";
    $sql .= "t.".$sortCol;
    $sql.= " ".$sortType . " Limit ". ($resultsPerPage * ($pagenum - 1)) . "," . $resultsPerPage .";";
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
$totalResults = $results;
$maxPage = ceil($totalResults / $resultsPerPage);

/*
 * Loop through all tournaments that match the tab.
 */
if ($results > 0)
{
  if ($tab == 'Finished')
  {
    print '<center><b> Showing results '.number_format(min(((($pagenum - 1) * $resultsPerPage)+1),$totalResults)).' to '.number_format(min(($pagenum * $resultsPerPage),$totalResults)).' of '.number_format($totalResults).' total results. </b></center></br>';
  	printPageBar($pagenum, $maxPage, $sortCol, $sortType, $sortBar = True);
  }
  else
  {
    print 'Showing '.$results.' results';
  }
}
else { print 'No tournaments meet the criteria right now.'; }

while (list($id, $name, $description, $status, $minRR, $year, $totalRounds, $forumThreadLink, $externalLink, $directorID, $coDirectorID, $firstPlace, $secondPlace, $thirdPlace) = $DB->tabl_row($tablChecked))
{
    print '<div class = "tournamentShow">';
    print '<h2 class = "tournamentCenter">'.$name.'</h2>';

    list($watchers) = $DB->sql_row("select count(1) from  wD_TournamentSpectators s where s.tournamentID = ".$id);
    
    if ($tab == 'Finished')
    {
        if ($firstPlace > 0)
        {
            list($firstUsername) = $DB->sql_row("Select u.username from wD_Users u where u.id =".$firstPlace);
            print '<div class = "tournamentCenter">'.libHTML::goldStar().'First Place: <a href="profile.php?userID='.$firstPlace.'">'.$firstUsername.'</a>'.libHTML::goldStar().'</div>';
        }
        if ($secondPlace > 0)
        {
            list($secondUsername) = $DB->sql_row("Select u.username from wD_Users u where u.id =".$secondPlace);
            print '<div class = "tournamentCenter">'.libHTML::silverStar().'Second Place: <a href="profile.php?userID='.$secondPlace.'">'.$secondUsername.'</a>'.libHTML::silverStar().'</div>';
        }
        if ($thirdPlace > 0)
        {
            list($thirdUsername) = $DB->sql_row("Select u.username from wD_Users u where u.id =".$thirdPlace);
            print '<div class = "tournamentCenter">'.libHTML::bronzeStar().'Third Place: <a href="profile.php?userID='.$thirdPlace.'">'.$thirdUsername.'</a>'.libHTML::bronzeStar().'</div>';
        }  
        print '<br>';
    }
    else if ($watchers > 0)
    {
        print '<div class = "tournamentCenter">Spectator Count: '.$watchers.'</div>';
        print '<br>';
    }

    // Don't let people sign up if the tournament isn't ready. 
    if ($status != 'PreStart')
    {
        if($status == 'Registration')
        {
            print '<a href="tournamentRegistration.php?tournamentID='.$id.'">Registration</a></br>';
        }
        else
        {
            print '<a href="tournamentScoring.php?tournamentID='.$id.'">Scoring and Participants</a></br>';
            print '<a href="gamelistings.php?gamelistType=Search&tournamentID='.$id.'">Tournament Games</a></br>';
        }
    }
    if (($tab == 'Moderating' || $tab == 'Finished') && ($User->type['User']))
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

    if ($forumThreadLink != '') { print '</br> <strong>Forum thread:</strong> <a href="'.$forumThreadLink.'">here</a>'; }
    if ($externalLink != '') { print '</br> <strong>External site:</strong> <a href="'.$externalLink.'">here</a></br></br>'; }

    print'<strong>Description:</strong></br>'.$description.'
    </br></br>
    <strong>Start year:</strong> '.$year.' </br></br>
    <strong>Rounds: </strong> '.$totalRounds.'
    </br></br>
    <strong>Required Reliability:</strong> '.$minRR.'% </div>';

    $tablRounds = $DB->sql_tabl("select distinct round from wD_TournamentGames where tournamentID = ".$id." order by round");
    $wereRounds = false;

    // Loop through all the rounds in this tournament.
    while (list($round) = $DB->tabl_row($tablRounds))
    {
        // See if there are ongoing and finished games in this round.
        list($ongoingGameCount) = $DB->sql_row("Select count(1) from wD_TournamentGames t inner join wD_Games g on g.id = t.gameID where tournamentID = ".$id." and g.gameOver = 'No' and t.round = ".$round);
        list($finishedGameCount) = $DB->sql_row("Select count(1) from wD_TournamentGames t inner join wD_Games g on g.id = t.gameID where tournamentID = ".$id." and g.gameOver != 'No' and t.round = ".$round);
        
        $wereRounds = true;
        
        print '<div class = "tournament_round"> Round '.$round.'</div>';
        print '<div class = "tournament_games">';
        print '<a href="gamelistings.php?gamelistType=Search&tournamentID='.$id.'&round='.$round.'&Submit=Search#results">Search Round '.$round.' games</a></br></br>';

        if ($ongoingGameCount > 0)
        {
            print '<strong>Ongoing Games</strong>
            <TABLE class="tournament">';
            print '<tr>';
            print '<th class= "tournament">Game</th>';
            print '<th class= "tournament">Turn</th>';
            print '<th class= "tournament">phase</th>';
            print '<th class= "tournament">Status</th>';
            print '<th class= "tournament">Process Time</th>';
            print '</tr>';
            
            $tablRoundsOngoingGames = $DB->sql_tabl("select g.id, g.name, g.turn, g.phase, g.gameOver, g.processStatus, g.processTime from wD_TournamentGames t inner join 
            wD_Games g on g.id = t.gameID where tournamentID = ".$id." and g.gameOver = 'No' and t.round = ".$round);

            // Loop through every game in the rounds. 
            while (list($gameID, $gameName, $turn, $phase, $gameOver, $processStatus, $processTime) = $DB->tabl_row($tablRoundsOngoingGames))
            {
                // Load variant data so we can check game criteria to determine if it is WFO. 
                $Variant=libVariant::loadFromGameID($gameID);
                $Game = $Variant->Game($gameID);
                
                print '<TR><td><a href="board.php?gameID='.$gameID.'">'.$gameName.'</a></TD>';
                print '<td>'.$Variant->turnAsDate($turn).'</td>';
                print '<td>'.$phase.'</td>';

                // If the game is over show gameOver (won/draw), otherwise show if it is stuck in WFO, Paused, Crashed, or Running. 
                if ($Game->missingPlayerPolicy=='Wait' && !$Game->Members->isCompleted() && time()>=$Game->processTime)
                {
                    print '<td style="background-color:#F08080;"> <strong>Waiting for Orders</strong></td>';
                }
                else if ($processStatus == 'Crashed') { print '<td style="background-color:#F08080;"> <strong>Crashed</strong></td>'; }
                else if ($processStatus == 'Paused') { print '<td> <strong>Paused</strong></td>'; }
                else { print '<td>Running</td>'; }
                
                print '<td>'.libTime::detailedText($processTime).'</td>';
            }
            print '</table>';
            if ($finishedGameCount > 0) print '<br>';
        }

        if ($finishedGameCount > 0)
        {
            print '<strong>Finished Games</strong>
            <TABLE class="tournament">';
            print '<tr>';
            print '<th class= "tournament">Game</th>';
            print '<th class= "tournament">Status</th>';
            print '<th class= "tournament">Winners</th>';
            print '<th class= "tournament">Finished Date</th>';
            print '</tr>';
            
            $tablRoundsFinishedGames = $DB->sql_tabl("select g.id, g.name, g.gameOver, g.processTime from wD_TournamentGames t inner join 
            wD_Games g on g.id = t.gameID where tournamentID = ".$id." and g.gameOver != 'No' and t.round = ".$round);

            // Loop through every game in the rounds. 
            while (list($gameID, $gameName, $gameOver, $processTime) = $DB->tabl_row($tablRoundsFinishedGames))
            {
                print '<TR><td><a href="board.php?gameID='.$gameID.'">'.$gameName.'</a></TD>';
                print '<td>'.$gameOver.'</td>';

                // If the game was drawn we want to show a link to each of the winners, so group concat does a pivot on the multiple rows in wD_Members into a single column to display that. 
                if ($gameOver == 'Drawn')
                {
                    list($drawingMembers) = $DB->sql_row( "select GROUP_CONCAT(CONCAT('<a href=\"profile.php?userID=',m.userID ,'\">',u.username ,'</a>') SEPARATOR ' ') from wD_Members m 
                    inner join wD_Users u on u.id = m.userID where m.gameID = ".$gameID." and m.status = 'Drawn'");
                    print '<td>'.$drawingMembers.'</td>';
                }
                else if ($gameOver == 'Won')
                {
                    list($winningMembers) = $DB->sql_row( "select CONCAT('<a href=\"profile.php?userID=',m.userID ,'\">',u.username ,'</a>') from wD_Members m 
                    inner join wD_Users u on u.id = m.userID where m.gameID = ".$gameID." and m.status = 'Won'");
                    print '<td>'.$winningMembers.'</td>';
                }
                
                print '<td>'.gmstrftime(" %d %b %y", $processTime).'</td>';
            }
            print '</table>';
        }
        print'</div>';
    }
    
    if ($wereRounds == true) { print'</br>'; }

    list($userSpectating) = $DB->sql_row("Select count(1) from wD_TournamentSpectators s where s.tournamentID = ".$id." and s.userID = ".$User->id);

    if ($userSpectating == 1 and $status != 'Finished')
    {
        print '</br> </br>
        <form method="post" action="#">
            <input type="hidden" name="spectateID" value="'.$id.'">
            <input type="hidden" name="tabs" value="'.$tab.'">
            <input type="submit" class="green-Submit" name="submit" value="Stop Spectating Tournament">
        </form>';
    }
    else if ($status != 'Finished')
    {
        print '</br> </br>
        <form method="post" action="#">
            <input type="hidden" name="spectateID" value="'.$id.'">
            <input type="hidden" name="tabs" value="'.$tab.'">
            <input type="submit" class="green-Submit" name="submit" value="Spectate Tournament">
        </form>';
    }

     print '</div></br>';
}

printPageBar($pagenum, $maxPage, $sortCol, $sortType);
print '</div></div>';

function printPageBar($pagenum, $maxPage, $sortCol, $sortType, $sortBar = False)
{
	if ($pagenum > 3)
	{
		printPageButton(1,False);
	}
	if ($pagenum > 4)
	{
		print "...";
	}
	if ($pagenum > 2)
	{
		printPageButton($pagenum-2, False);
	}
	if ($pagenum > 1)
	{
		printPageButton($pagenum-1, False);
	}
	if ($maxPage > 1)
	{
		printPageButton($pagenum, True);
	}
	if ($pagenum < $maxPage)
	{
		printPageButton($pagenum+1, False);
	}
	if ($pagenum < $maxPage-1)
	{
		printPageButton($pagenum+2, False);
	}
	if ($pagenum < $maxPage-3)
	{
		print "...";
	}
	if ($pagenum < $maxPage-2)
	{
		printPageButton($maxPage, False);
	}
	if ($maxPage > 1 && $sortBar)
	{
		print '<span style="float:right;">
			<FORM class="advancedSearch" method="get" action="tournaments.php#results">
			<b>Sort By:</b>
			<select  class = "advancedSearch" name="sortCol">
				<option'.(($sortCol=='year') ? ' selected="selected"' : '').' value="year">Year</option>
				<option'.(($sortCol=='name') ? ' selected="selected"' : '').' value="name">Tournament Name</option>
			</select>
			<select class = "advancedSearch" name="sortType">
				<option'.(($sortType=='asc') ? ' selected="selected"' : '').' value="asc">Ascending</option>
				<option'.(($sortType=='desc') ? ' selected="selected"' : '').' value="desc">Descending</option>
			</select>';
			foreach($_REQUEST as $key => $value)
			{
				if(strpos('x'.$key,'wD') == false && strpos('x'.$key,'phpbb3') == false && strpos('x'.$key,'__utm')== false && $key!="pagenum" && $key!="sortCol" && $key!="sortType")
				{
					print '<input type="hidden" name="'.$key.'" value="'.$value.'">';
				}
			}
			print ' ';
			print '<input type="submit" class="form-submit" name="Refresh" value="Refresh" /></form>
			</span>';
		}
}

function printPageButton($pagenum, $currPage)
{
	if ($currPage)
	{
		print '<div class="curr-page">'.$pagenum.'</div>';
	}
	else
	{
		print '<div style="display:inline-block; margin:3px;">';
		print '<FORM method="get" action=tournaments.php#results>';
		foreach($_REQUEST as $key => $value)
		{
			if(strpos('x'.$key,'wD') == false && strpos('x'.$key,'phpbb3')== false && strpos('x'.$key,'__utm')== false && $key!="pagenum")
			{
				print '<input type="hidden" name="'.$key.'" value="'.$value.'">';
			}
		}
		print '<input type="submit" name="pagenum" class="form-submit" value='.$pagenum.' /></form></div>';
	}
}
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
