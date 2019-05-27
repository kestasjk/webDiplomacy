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
 * @subpackage Static
 */

require_once('header.php');

libHTML::starthtml();

print libHTML::pageTitle(l_t('Tournament Scoring'),l_t('Scoreboard for each tournament recorded on webDiplomacy.'));

global $DB, $User;

$tournamentID = 0;

if(isset($_REQUEST['tournamentID']))
{
  $tournamentID = $_REQUEST['tournamentID'];
  $sortCol = 'name';
  $sortType = 'asc';
  if (isset($_REQUEST['sortCol']))
  {
    if (strpos('x'.$_REQUEST['sortCol'],'r') == 1)
    {
      $round = (int)substr($_REQUEST['sortCol'],1);
      if ($round <> 0) {$sortCol = 'r'.$round; }
    }
  }
  if (isset($_REQUEST['sortType']))
  {
    if ($_REQUEST['sortType'] == 'desc') {$sortType = 'desc'; }
  }
  list($tournamentName, $tournamentRounds, $director, $codirector) = $DB->sql_row("SELECT name, totalRounds, directorID, coDirectorID FROM wD_Tournaments WHERE id =".$tournamentID);

  $editor = False;
  if($User->type['Moderator'] || $User->id == $director || $User->id == $codirector)
  {
    $editor = True;
  }

  if ($editor)
  {
    foreach($_REQUEST as $key => $value)
  	{
  		if(strpos('x'.$key,'id') == 1)
  		{
        $valArray = explode('r',substr($key,2));
        $updateID = $valArray[0];
        $updateRound = $valArray[1];
        if (strpos('x'.$updateID,'new') == 1 && $value <> '')
        {
          $DB->sql_put("INSERT INTO wD_TournamentScoring (tournamentID, userID, round, score)
          VALUES (".(int)$tournamentID.", ".(int)substr($updateID,3).", ".(int)$updateRound.", ".floatval($value).")");
        }
        elseif ($value <> '')
        {
          $DB->sql_put("UPDATE wD_TournamentScoring SET score=".floatval($value)." WHERE userID=".$updateID." AND tournamentID=".$tournamentID." AND round=".$updateRound);
        }
  		}
  	}
  }

  $nullRound = array();
  list($SQLExpected) = $DB->sql_row("SELECT COUNT(1) FROM wD_TournamentParticipants t
    INNER JOIN wD_Users u ON t.userID = u.id
    WHERE t.tournamentID = ".$tournamentID." AND t.status <> 'Rejected'");
  for ($i = 1; $i <= $tournamentRounds; $i++)
  {
    list($SQLResults) = $DB->sql_row("SELECT COUNT(1) FROM wD_TournamentParticipants t
      INNER JOIN wD_Users u ON t.userID = u.id
      LEFT JOIN wD_TournamentScoring s ON t.userID = s.userID
      WHERE t.tournamentID = ".$tournamentID." AND t.status <> 'Rejected'
      AND s.round = ".$i." AND s.tournamentID = ".$tournamentID);
    if ($SQLResults == 0)
    {
      $nullRound[] = $i;
    }
    elseif ($SQLResults <> $SQLExpected)
    {
      $SQLExpectedUser = $DB->sql_tabl("SELECT t.userID FROM wD_TournamentParticipants t
        INNER JOIN wD_Users u ON t.userID = u.id
        WHERE t.tournamentID = ".$tournamentID." AND t.status <> 'Rejected'
        ORDER BY t.userID ASC;");
      $SQLResultsUser = $DB->sql_tabl("SELECT t.userID FROM wD_TournamentParticipants t
        INNER JOIN wD_Users u ON t.userID = u.id
        INNER JOIN wD_TournamentScoring s ON t.userID = s.userID
        WHERE t.tournamentID = ".$tournamentID." AND t.status <> 'Rejected'
        AND s.round = ".$i." AND s.tournamentID = ".$tournamentID.
        " ORDER BY t.userID ASC;");
      $curIDresults = $curIDexpected = 0;
      list($curIDresults) = $DB->tabl_row($SQLResultsUser);
      while( list($curIDexpected) = $DB->tabl_row($SQLExpectedUser))
      {
        if ($curIDresults == $curIDexpected)
        {
          list($curIDresults) = $DB->tabl_row($SQLResultsUser);
        }
        else
        {
          $DB->sql_put("INSERT INTO wD_TournamentScoring (tournamentID, userID, round, score)
          VALUES ($tournamentID, $curIDexpected, $i, 0);");
        }
      }
    }
  }

  print "<a name='tableLocation'></a>";
  print '<b><center>Scores for '.$tournamentName.'</center></b><br/>';
	print "<TABLE class='advancedSearch'>";
		print "<tr>";
    if ($sortCol <> 'name')
    {
      print '<th class = "advancedSearch">Rank</th>';
    }
		print '<th class= "advancedSearch"';
      if ($sortCol == 'name')
      {
				print 'style="background-color: #006699;"';
			}
		print '>';
		printHeaderLink('Name', $sortCol, $sortType, $tournamentRounds);
		print '</th>';
    for ($i = 1; $i <= $tournamentRounds; $i++)
    {
      if (in_array($i, $nullRound))
      {
        print '<th class= "advancedSearch">Round '.$i.'</th>';
      }
      else
      {
        print '<th class= "advancedSearch"';
    		if ($sortCol == 'r'.$i)
    		{
    			print 'style="background-color: #006699;"';
    		}
    		print '>';
    		printHeaderLink('Round '.$i, $sortCol, $sortType, $tournamentRounds);
    		print '</th>';
      }
    }
		print "</tr>";

    if ($editor)
    {
      print "<FORM Method='Get' Action='tournamentScoring.php?tournamentID=".$tournamentID."#tableLocation'>";
      foreach($_REQUEST as $key => $value)
      {
        if(strpos('x'.$key,'wD') == false && strpos('x'.$key,'phpbb3')== false && strpos('x'.$key,'__utm')== false && strpos('x'.$key,'id') == false && $key <> 'submit')
        {
          print '<input type="hidden" name="'.$key.'" value='.$value.'>';
        }
      }
    }

    $SQL = "";

    if($sortCol == 'name')
    {
      $SQL = "SELECT t.userID, u.username, 1 FROM wD_TournamentParticipants t
        INNER JOIN wD_Users u ON t.userID = u.id
        WHERE t.tournamentID = ".$tournamentID." AND t.status <> 'Rejected' ORDER BY u.username ".$sortType;
    }
    else
    {
      $SQL = "SELECT t.userID, u.username, s.score FROM wD_TournamentParticipants t
        INNER JOIN wD_Users u ON t.userID = u.id
        LEFT JOIN wD_TournamentScoring s ON t.userID = s.userID
        WHERE t.tournamentID = ".$tournamentID." AND t.status <> 'Rejected'
        AND s.round = ".(int)substr($_REQUEST['sortCol'],1)." AND s.tournamentID = ".$tournamentID.
        " ORDER BY s.score ".$sortType;
    }

    $SQLtabl = $DB->sql_tabl($SQL);

    $rank = 0;
    if($sortType == 'asc')
    {
      $rank = $SQLExpected + 1;
    }
    $previousScore = -1;
    $ties = 0;

		while (list($userID, $username, $score)= $DB->tabl_row($SQLtabl))
		{
      if ($score <> $previousScore)
      {
        if ($sortType == 'desc') { $rank += (1 + $ties); }
        else { $rank -= (1 + $ties); }
        $ties = 0;
      }
      else
      {
        $ties++;
      }
      if ($sortCol <> 'name')
      {
        print '<TD class = "advancedSearch">'.$rank.'</TD>';
      }
      print '<TD class= "advancedSearch"><a href="profile.php?userID='.$userID.'">'.$username.'</a></TD>';
      for ($i = 1; $i <= $tournamentRounds; $i++)
      {
        $curScore = '';
        list($curScore) = $DB->sql_row("SELECT score FROM wD_TournamentScoring WHERE tournamentID = ".$tournamentID." AND userID = ".$userID." AND round = ".$i);
        if ($editor)
        {
          print '<TD class= "advancedSearch"><Input type="number" name="'.'id'.($curScore=='' ? 'new' : '').$userID.'r'.$i.'" value="'.$curScore.'" step="any"></TD>';
        }
        else
        {
          print '<TD class= "advancedSearch">'.$curScore.'</TD>';
        }
      }
			print "</TR>";
      $previousScore = $score;
		}
		print "</TABLE>";
    if ($editor)
    {
      print "<br/><INPUT type='submit' value='Update' name='submit' class='green-Submit'></form>";
    }
}
else
{
  $tourneys = $DB->sql_tabl("SELECT id, name, year FROM wD_Tournaments WHERE status <> 'PreStart' AND status <> 'Registration' ORDER BY year DESC");
  print '<div class = "gameCreateShow">
  <FORM class="gameCreate" method="get" action="tournamentScoring.php#tableLocation">
    <p><strong><center>Select A Tournament</center></strong>
    <br/><select  class = "gameCreate" name="tournamentID">';
      while (list($id, $name, $year) = $DB->tabl_row($tourneys))
      {
        print'<option value="'.$id.'">'.$name." (".$year.")".'</option>';
      }
		print '</select><br/><br/><br/>
    <input type="submit" name="submit" class="green-Submit" value="Go" /></p></form></div>';
}

function printHeaderLink($header, $sortCol, $sortType, $rounds)
{
	print '<FORM method="get" action=tournamentScoring.php#tableLocation>';
	foreach($_REQUEST as $key => $value)
	{
		if(strpos('x'.$key,'wD') == false && strpos('x'.$key,'phpbb3')== false && strpos('x'.$key,'__utm')== false && $key!="sortCol" && $key!="sortType" && strpos('x'.$key,'id') == false && $key <> 'submit')
		{
			print '<input type="hidden" name="'.$key.'" value='.$value.'>';
		}
	}
	$convert = array("Player ID"=>"id","Name"=>"name");
  for ($i = 1; $i <= $rounds; $i++)
  {
    $convert["Round ".$i] = "r".$i;
  }
	if ($convert[$header] == $sortCol)
	{
		if ($sortType == 'desc')
		{
			print '<input type="hidden" name="sortType" value=asc>';
			print '<button type="submit" name="sortCol" value='.$convert[$header].' class="advancedSearchHeader"';
			print '>'.$header.' &#9652</button></form>';
		}
		else
    {
			print '<input type="hidden" name="sortType" value=desc>';
			print '<button type="submit" name="sortCol" value='.$convert[$header].' class="advancedSearchHeader"';
			print '>'.$header.' &#9662</button></form>';
		}
	}
	else
  {
		print '<input type="hidden" name="sortType" value=desc>';
		print '<button type="submit" name="sortCol" value='.$convert[$header].' class="advancedSearchHeader"';
		print '>'.$header.'</button></form>';
	}
}

print '</div>';
libHTML::footer();
