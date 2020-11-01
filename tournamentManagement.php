<?php

/**
 * @package Base
 */

require_once('header.php');

global $DB;

if( !$User->type['User'] )
{
	libHTML::error(l_t("This page is only for registered users."));
}

$tournamentID = 0;
$submitted = false;
$update = false;
$makeNew = false;
$addGames = false;
$removeGames = false;
$addPlayers = false;
$removePlayers = false;
$sync = false;

$paramName = '';
$paramDescription = '';
$paramstatus = 'PreStart';
$paramMinRR = -1;
$paramYear = -1;
$paramTotalRounds = -1;
$paramForumLink = '';
$paramExternalLink = '';
$paramDirector = -1;
$paramCODirector = -1;
$paramFirstPlace = -1;
$paramSecondPlace = -1;
$paramThirdPlace = -1;

$paramRound = -1;
$paramAddGameList = '';
$paramRemoveGameList = '';
$paramAddPlayerList = '';
$paramRemovePlayerList = '';

if( isset($_REQUEST['tournamentID']) )
{
	$tournamentID = (int)$_REQUEST['tournamentID'];
}

// Get values from posted form.
if(isset($_POST['submit']))
{
    $submitted = true;
    if( isset($_POST['tab']) )
    {
        if ($_POST['tab'] == 'updateTournament')
        {
            $tournamentID = (int)$_POST['tournamentID'];
            $update = true;
        }
        else if ($_POST['tab'] == 'newTournament')
        {
            $tournamentID = 0;
            $makeNew = true;
        }
				else if ($_POST['tab'] == 'addGames')
				{
						$tournamentID = (int)$_POST['tournamentID'];
						$addGames = true;
				}
				else if ($_POST['tab'] == 'removeGames')
				{
						$tournamentID = (int)$_POST['tournamentID'];
						$removeGames = true;
				}
				else if ($_POST['tab'] == 'addPlayers')
				{
						$tournamentID = (int)$_POST['tournamentID'];
						$addPlayers = true;
				}
				else if ($_POST['tab'] == 'removePlayers')
				{
						$tournamentID = (int)$_POST['tournamentID'];
						$removePlayers = true;
				}
				else if ($_POST['tab'] == 'sync')
				{
						$tournamentID = (int)$_POST['tournamentID'];
						$sync = true;
				}
        else { die('Sorry, but something went wrong.'); }
    }

    if(isset($_POST['name']) && strlen($_POST['name']))
    {
        $paramName = $DB->escape($_POST['name']);
        $paramName = strip_tags(html_entity_decode(trim($paramName)));
    }

    if(isset($_POST['description']) && strlen($_POST['description']))
    {
        $paramDescription = $DB->escape($_POST['description'], $htmlAllowed=true);
    }

    if ( isset($_POST['status']) && $_POST['status'] && strlen($_POST['status']) )
    {
        if ($_POST['status'] == 'PreStart') {$paramstatus = 'PreStart';}
        else if ($_POST['status'] == 'Registration') {$paramstatus = 'Registration';}
        else if ($_POST['status'] == 'Active') {$paramstatus = 'Active';}
        else if ($_POST['status'] == 'Finished') {$paramstatus = 'Finished';}
    }

    if( isset($_POST['minRR']) ) { $paramMinRR = (int)$_POST['minRR']; }
    if( isset($_POST['year']) ) { $paramYear = (int)$_POST['year']; }
    if( isset($_POST['rounds']) ) { $paramTotalRounds = (int)$_POST['rounds']; }

    if(isset($_POST['forumLink']) && strlen($_POST['forumLink']))
    {
        $paramForumLink = $DB->escape($_POST['forumLink']);
        $paramForumLink = strip_tags(html_entity_decode(trim($paramForumLink)));
    }

    if(isset($_POST['externalLink']) && strlen($_POST['externalLink']))
    {
        $paramExternalLink = $DB->escape($_POST['externalLink']);
        $paramExternalLink = strip_tags(html_entity_decode(trim($paramExternalLink)));
    }

    if( isset($_POST['director']) ) { $paramDirector = (int)$_POST['director']; }
    if( isset($_POST['coDirector']) ) { $paramCODirector = (int)$_POST['coDirector']; }
    if( isset($_POST['firstPlace']) ) { $paramFirstPlace = (int)$_POST['firstPlace']; }
    if( isset($_POST['secondPlace']) ) { $paramSecondPlace = (int)$_POST['secondPlace']; }
    if( isset($_POST['thirdPlace']) ) { $paramThirdPlace = (int)$_POST['thirdPlace']; }

		if (isset($_POST['round']) ) { $paramRound = (int)$_POST['round']; }
		if (isset($_POST['addGameList']) ) { $paramAddGameList = explode(",",$_POST['addGameList']); }
		if (isset($_POST['removeGameList']) ) { $paramRemoveGameList = explode(",",$_POST['removeGameList']); }
		if (isset($_POST['addPlayerList']) ) { $paramAddPlayerList = explode(",",$_POST['addPlayerList']); }
		if (isset($_POST['removePlayerList']) ) { $paramRemovePlayerList = explode(",",$_POST['removePlayerList']); }
}

// Print the header and standard php for the site that is required on every page.
libHTML::starthtml();
print libHTML::pageTitle(l_t('Manage Tournaments'),l_t('Update or create tournaments.'));
?>

<?php

if ($submitted == false)
{
    // Update a tournament form.
    if ($tournamentID > 0 )
    {
        list($allowedTD) = $DB->sql_row("SELECT count(1) FROM wD_Tournaments t WHERE t.id = ".$tournamentID." and (t.directorID = ".$User->id." or t.coDirectorID = ".$User->id." )");

        if ( !( $allowedTD > 0) && (!$User->type['Moderator'] ))
        {
            die(l_t('You do not have access to edit this tournament, your access attempt has been logged'));
        }

        $sql = "SELECT * FROM wD_Tournaments t where t.id = ". $tournamentID;

        $tabl = $DB->sql_tabl($sql);
        list($id, $name, $description, $status, $minRR, $year, $totalRounds, $forumThreadLink, $externalLink, $directorID, $coDirectorID, $firstPlace, $secondPlace, $thirdPlace) = $DB->tabl_row($tabl);

        print '<div class = "contactUs"><p>Fill out the following form to update the tournament information for tournamentID: '.$id.'</p></div>';
        print '<div class = "contactUsShow">';

        print '<form action="#" method="post">
        <INPUT type="hidden" name="tab" value="updateTournament" />
        <INPUT type="hidden" name="tournamentID" value="'.$id.'" /></br>';

        if ($User->type['Moderator'] )
        {
            print '
            <strong>Name:</strong>
            <INPUT class="settings" type="text" name="name"  value="'.$name.'" /></br></br>

            <strong>Description:</strong>
            <textarea name="description" class = "settings"  rows="5">'.$description.'</textarea></br></br>';
        }
        else
        {
            print '
            <strong>Name: (only mods can change the name)</strong>
            <INPUT class="settings" disabled type="text" name="name"  value="'.$name.'" /></br></br>

            <strong>Description (only mods can change the description)</strong>
            <textarea name="description" disabled class = "settings" rows="5">'.$description.'</textarea></br></br>';
        }

        print '<strong>Status:</strong></br>
        <select class = "gameCreate" name="status">
            <option name="status" value="PreStart" '.(($status=='PreStart') ? ' selected="selected"' : '').' >PreStart</option>
            <option name="status" value="Registration" '.(($status=='Registration') ? ' selected="selected"' : '').'>Registration</option>
            <option name="status" value="Active" '.(($status=='Active') ? ' selected="selected"' : '').'>Active</option>
            <option name="status" value="Finished" '.(($status=='Finished') ? ' selected="selected"' : '').'>Finished</option>
        </select></br></br>

        <strong>Required reliability:</strong></br>
        <input id="minRating" class = "gameCreate" type="text" name="minRR" size="2" value="'.$minRR.'"
            onkeypress="if (event.keyCode==13) this.blur(); return event.keyCode!=13"
            onChange="
                this.value = parseInt(this.value);
                if (this.value == \'NaN\' ) this.value = 0;
                if (this.value < 0 ) this.value = 0;
                if (this.value > 100 ) this.value = 100;"/></br></br>

        <strong>Year:</strong></br>
        <input class = "gameCreate" type="text" name="year" size="4" value="'.$year.'"></br></br>

        <strong>Total Rounds:</strong>
        <INPUT class="settings" type="text" name="rounds"  value="'.$totalRounds.'" /></br></br>';

        if ($User->type['Moderator'] )
        {
            print '
            <strong>Forum Link: (ex: /contrib/phpBB3/viewtopic.php?f=5&t=1551)</strong>
            <INPUT class="settings" type="text" name="forumLink"  value="'.$forumThreadLink.'" /></br></br>

            <strong>External Link:</strong>
            <INPUT class="settings" type="text" name="externalLink" value="'.$externalLink.'" /></br></br>';

            print '<strong>director user ID:</strong>
            <INPUT class="settings" type="text" name="director" value="'.$directorID.'" /></br></br>

            <strong>co-director user ID:</strong>
            <INPUT class="settings" type="text" name="coDirector" value="'.$coDirectorID.'" /></br></br>';
        }
        else
        {
            print '
            <strong>Forum Link: (only mods can change forum link)</strong>
            <INPUT class="settings" disabled type="text" name="forumLink"  value="'.$forumThreadLink.'" /></br></br>

            <strong>External Link: (only mods can change external link)</strong>
            <INPUT class="settings" disabled type="text" name="externalLink" value="'.$externalLink.'" /></br></br>';

            print '<strong>director user ID: (only mods can change director)</strong>
            <INPUT class="settings" disabled type="text" name="director" value="'.$directorID.'" /></br></br>

            <strong>co-director user ID: (only mods can change co-director)</strong>
            <INPUT class="settings" disabled type="text" name="coDirector" value="'.$coDirectorID.'" /></br></br>';
        }

        print '<strong>First Place userID:</strong>
        <INPUT class="settings" type="text" name="firstPlace" value="'.$firstPlace.'" /></br></br>

        <strong>Seconed Place userID:</strong>
        <INPUT class="settings" type="text" name="secondPlace" value="'.$secondPlace.'" /></br></br>

        <strong>Third Place userID:</strong>
        <INPUT class="settings" type="text" name="thirdPlace" value="'.$thirdPlace.'" /></br></br>
        ';

        print '<p><input type="submit" class = "green-Submit" name="submit"/></p>';
        print '</form>';

				if ($User->type['Moderator'] )
        {
						print '</div></br><strong>List of Games Currently in Tournament: </strong>';
						$gamesTabl = $DB->sql_tabl("SELECT t.gameID, g.name FROM wD_TournamentGames t INNER JOIN wD_Games g ON t.gameID = g.id WHERE t.tournamentID=".$tournamentID);
						$gameString = '';
						while (list($curGameID, $curGamename) = $DB->tabl_row($gamesTabl) )
						{
								$gameString .= '<a href=/board.php?gameID='.$curGameID.'>'.$curGamename.' ('.$curGameID.')</a> ';
						}
						if ($gameString == '') { $gameString = 'None'; }
						print $gameString;

						print '</br></br>
						<div class = "contactUsShow">
						<form action="#" method="post">
		        <INPUT type="hidden" name="tab" value="addGames" />
		        <INPUT type="hidden" name="tournamentID" value="'.$id.'" />
						</br>
						<strong>Add Games through a comma separated list of gameIDs (Mod Only).</br></br>
						Round:</strong></br>
		        <select class = "gameCreate" name="round">';
						for ($i = 1; $i <= $totalRounds; $i++)
						{
								print'<option name="round" value="'.$i.'">'.$i.'</option>';
						}
		        print'</select></br></br>
						<strong>List of Games to Add:</strong>
						<textarea name="addGameList" class = "settings"  rows="3"></textarea></br></br>
						<p><input type="submit" class = "green-Submit" name="submit"/></p></form>';

						print '</div></br></br>
						<div class = "contactUsShow">
						<form action="#" method="post">
		        <INPUT type="hidden" name="tab" value="removeGames" />
		        <INPUT type="hidden" name="tournamentID" value="'.$id.'" />
						</br>
						<strong>Remove Games through a comma separated list of gameIDs (Mod Only).</strong></br></br>
						<strong>List of Games to Remove:</strong>
						<textarea name="removeGameList" class = "settings"  rows="3"></textarea></br></br>
						<p><input type="submit" class = "green-Submit" name="submit"/></p></form>';

						print '</div></br><strong>List of Players Currently in Tournament: </strong>';
						$playersTabl = $DB->sql_tabl("SELECT t.userID, p.username FROM wD_TournamentParticipants t INNER JOIN wD_Users p ON t.userID = p.id WHERE t.tournamentID=".$tournamentID);
						$playerString = '';
						while (list($curPlayerID, $curPlayername) = $DB->tabl_row($playersTabl) )
						{
								$playerString .= '<a href=/userprofile.php?userID='.$curPlayerID.'>'.$curPlayername.' ('.$curPlayerID.')</a> ';
						}
						if ($playerString == '') { $playerString = 'None'; }
						print $playerString;

						print '</br></br>
						<div class = "contactUsShow">
						<form action="#" method="post">
		        <INPUT type="hidden" name="tab" value="addPlayers" />
		        <INPUT type="hidden" name="tournamentID" value="'.$id.'" />
						</br>
						<strong>Add Players through a comma separated list of userIDs (Mod Only).</strong></br></br>
						<strong>List of Players to Add:</strong>
						<textarea name="addPlayerList" class = "settings"  rows="3"></textarea></br></br>
						<p><input type="submit" class = "green-Submit" name="submit"/></p></form>';

						print '</div></br></br>
						<div class = "contactUsShow">
						<form action="#" method="post">
		        <INPUT type="hidden" name="tab" value="removePlayers" />
		        <INPUT type="hidden" name="tournamentID" value="'.$id.'" />
						</br>
						<strong>Remove Players through a comma separated list of userIDs (Mod Only).</strong></br></br>
						<strong>List of Players to Remove:</strong>
						<textarea name="removePlayerList" class = "settings"  rows="3"></textarea></br></br>
						<p><input type="submit" class = "green-Submit" name="submit"/></p></form>';

						print '</div></br></br>
						<div class = "contactUsShow">
						<form action="#" method="post">
						</br><strong>Sync Participants with Games</strong></br>
						</br>This will delete all players from the tournament, then add in any user in any of the games in the tournament.
						<INPUT type="hidden" name="tab" value="sync" />
						<INPUT type="hidden" name="tournamentID" value="'.$id.'" />
						<p><input type="submit" class = "green-Submit" name="submit" value="Sync"/></p></form>';
				}

    }

    // Make a new tournament form.
    else
    {
        if (!$User->type['Moderator'] )
        {
            die(l_t('Only mods can make new tournaments, your access attempt has been logged'));
        }

        print '<div class = "contactUs"><p>Fill out the following form to create a new tournament</p></div>';
        print '<div class = "contactUsShow">';

        print '<form action="#" method="post">
        <INPUT type="hidden" name="tab" value="newTournament" /></br>
        <strong>Name:</strong>
        <INPUT class="settings" type="text" name="name"  value="" /></br></br>

        <strong>Description </strong>
        <textarea name="description" class = "settings"  rows="5"></textarea></br></br>

        <strong>Status:</strong></br>
        <select class = "gameCreate" name="status">
            <option name="status" value="PreStart" checked>PreStart</option>
            <option name="status" value="Registration">Registration</option>
            <option name="status" value="Active" checked>Active</option>
            <option name="status" value="Finished" checked>Finished</option>
        </select></br></br>

        <strong>Required reliability:</strong></br>
        <input id="minRating" class = "gameCreate" type="text" name="minRR" size="2" value="80"
            onkeypress="if (event.keyCode==13) this.blur(); return event.keyCode!=13"
            onChange="
                this.value = parseInt(this.value);
                if (this.value == \'NaN\' ) this.value = 0;
                if (this.value < 0 ) this.value = 0;
                if (this.value > 100 ) this.value = 100;"/></br></br>

        <strong>Year:</strong></br>
        <input class = "gameCreate" type="text" name="year" size="4" value=""></br></br>

        <strong>Total Rounds:</strong>
        <INPUT class="settings" type="text" name="rounds"  value="" /></br></br>

        <strong>Forum Link: (ex: /contrib/phpBB3/viewtopic.php?f=5&t=1551)</strong>
        <INPUT class="settings" type="text" name="forumLink"  value="" /></br></br>

        <strong>External Link:</strong>
        <INPUT class="settings" type="text" name="externalLink"  value="" /></br></br>

        <strong>director user ID:</strong>
        <INPUT class="settings" type="text" name="director"  value="" /></br></br>

        <strong>co-director user ID:</strong>
        <INPUT class="settings" type="text" name="coDirector"  value="" /></br></br>

        <strong>First Place userID:</strong>
        <INPUT class="settings" type="text" name="firstPlace"  value="" /></br></br>

        <strong>Seconed Place userID:</strong>
        <INPUT class="settings" type="text" name="secondPlace"  value="" /></br></br>

        <strong>Third Place userID:</strong>
        <INPUT class="settings" type="text" name="thirdPlace"  value="" /></br></br>
        ';

        print '<p><input type="submit" class = "green-Submit" name="submit"/></p>';
        print '</form>';
    }
}

else
{
    $worked = true;

    if ($update)
    {
        $sql = " UPDATE wD_Tournaments SET status = '".$paramstatus."', ";

        if ($User->type['Moderator'] )
        {
            $sql .= " name = '".$paramName."', description = '".$paramDescription."', forumThreadLink = '".$paramForumLink."', externalLink = '".$paramExternalLink."',";

            if ($paramDirector > -1) { $sql .= " directorID = ".$paramDirector.", "; }
            if ($paramCODirector > -1) { $sql .= " coDirectorID = ".$paramCODirector.", "; }
        }

        if ($paramMinRR > -1) { $sql .= " minRR = ".$paramMinRR.", "; }
        if ($paramYear > -1) { $sql .= " year = ".$paramYear.", "; }
        if ($paramFirstPlace > -1) { $sql .= " firstPlace = ".$paramFirstPlace.", "; }
        if ($paramSecondPlace > -1) { $sql .= " secondPlace = ".$paramSecondPlace.", "; }
        if ($paramThirdPlace > -1) { $sql .= " thirdPlace = ".$paramThirdPlace.", "; }

        $sql .= "totalRounds = ".$paramTotalRounds. " WHERE id = ".$tournamentID;
        try
        {
            $DB->sql_put($sql);
        }
        catch(Exception $e)
        {
            print '<div class="contactUs"> Sorry, but there was a problem making this tournament, contact the moderator team at '.Config::$modEMail;
            print '<p class="contactUs">'.$e->getMessage().'</p>';
            print '</div>';
            $worked = false;
        }

        if ($worked == true)
        {
            print '<p class = "contactUs">Tournament data has been updated.</br>';
            print '<a href="tournamentManagement.php?tournamentID='.$tournamentID.'">Edit Again</a></p>';
        }
    }
    else if ($makeNew)
    {
        $sql = " INSERT INTO wD_Tournaments ( name, description, status, minRR, year, totalRounds, forumThreadLink, externalLink, directorID, coDirectorID, firstPlace, secondPlace, thirdPlace ) ";
        $sql .= " Values ('".$paramName."','".$paramDescription."','".$paramstatus."',".$paramMinRR." ,".$paramYear." ,".$paramTotalRounds." ,'".$paramForumLink."' ,'".$paramExternalLink."'
         ,".$paramDirector." ,".$paramCODirector." ,".$paramFirstPlace." ,".$paramSecondPlace." ,".$paramThirdPlace." ) ";

        try
        {
            $DB->sql_put($sql);
        }
        catch(Exception $e)
        {
            print '<div class="contactUs"> Sorry, but there was a problem making this tournament, contact the moderator team at '.Config::$modEMail;
            print '<p class="contactUs">'.$e->getMessage().'</p>';
            print '</div>';
            $worked = false;
        }

        if ($worked == true)
        {
            print '<p class = "contactUs">Tournament has been made.</br>';
            print '<a href="tournamentManagement.php?tournamentID='.$tournamentID.'">Edit Again</a></p>';
        }
    }
		else if ($addGames)
		{
				list($modInTournament) = $DB->sql_row("SELECT COUNT(1) FROM wD_TournamentParticipants WHERE tournamentID=".$tournamentID." AND userID=".$User->id);
				if ($modInTournament == 0 || $User->type['Admin'])
				{
						$successAdd = '';
						$failedAdd = '';
						foreach ($paramAddGameList as $gameValue){
								$gameID = (int)$gameValue;
								list($gamecheck) = $DB->sql_row("SELECT COUNT(1) FROM wD_Games WHERE id=".$gameID);
								list($gamecheck2) = $DB->sql_row("SELECT COUNT(1) FROM wD_TournamentGames WHERE gameID=".$gameID);
								if ($gamecheck == 1 && $gamecheck2 < 1)
								{
										$sql = "INSERT INTO wD_TournamentGames ( tournamentID, gameID, round ) ";
										$sql .= " VALUES ( ".$tournamentID.", ".$gameID.", ".$paramRound." ) ";
										$DB->sql_put($sql);
										list($curGamename) = $DB->sql_row("SELECT name FROM wD_Games WHERE id=".$gameID);
										if ($successAdd == '')
										{
												$successAdd = 'Succesfully Added: <a href=/board.php?gameID='.$gameID.'>'.$curGamename.' ('.$gameID.')</a>';
										}
										else
										{
												$successAdd .= ', <a href=/board.php?gameID='.$gameID.'>'.$curGamename.' ('.$gameID.')</a>';
										}
								}
								else
								{
										if ($gamecheck <> 1)
										{
												if ($failedAdd == '')
												{
														$failedAdd = 'Failed To Add: </br>'.$gameValue.' - Invalid GameID';
												}
												else
												{
														$failedAdd .= '</br>'.$gameValue.' - Invalid GameID';
												}
										}
										else
										{
												list($curGamename) = $DB->sql_row("SELECT name FROM wD_Games WHERE id=".$gameID);
												if ($failedAdd == '')
												{
														$failedAdd = 'Failed To Add: </br><a href=/board.php?gameID='.$gameID.'>'.$curGamename.' ('.$gameID.')</a> - Already in a Tournament';
												}
												else
												{
														$failedAdd .= '</br><a href=/board.php?gameID='.$gameID.'>'.$curGamename.' ('.$gameID.')</a> - Already in a Tournament';
												}
										}
								}
						}
						print '<p class = "contactUs">'.$successAdd.'</br></br>';
						print $failedAdd.'</br></br>';
						print '<a href="tournamentManagement.php?tournamentID='.$tournamentID.'">Edit Again</a></p>';
						$DB->sql_put("INSERT INTO wD_AdminLog ( name, userID, time, details, params )
									VALUES ( 'Add Games to Tournament', ".$User->id.", ".time().", 'Tournament ".$tournamentID." Round ".$paramRound." ', '".$DB->escape(serialize($paramAddGameList))."' )");
				}
				else
				{
						print '<p class = "contactUs">You cannot edit a tournament you are in.</p>';
				}
		}
		else if ($removeGames)
		{
				list($modInTournament) = $DB->sql_row("SELECT COUNT(1) FROM wD_TournamentParticipants WHERE tournamentID=".$tournamentID." AND userID=".$User->id);
				if ($modInTournament == 0 || $User->type['Admin'])
				{
						$successRemove = '';
						$failedRemove = '';
						foreach ($paramRemoveGameList as $gameValue){
								$gameID = (int)$gameValue;
								list($gamecheck) = $DB->sql_row("SELECT COUNT(1) FROM wD_Games WHERE id=".$gameID);
								list($gamecheck2) = $DB->sql_row("SELECT COUNT(1) FROM wD_TournamentGames WHERE tournamentID=".$tournamentID." AND gameID=".$gameID);
								if ($gamecheck2 > 0)
								{
										$sql = "DELETE FROM wD_TournamentGames WHERE tournamentID=".$tournamentID." AND gameID=".$gameID;
										$DB->sql_put($sql);
										list($curGamename) = $DB->sql_row("SELECT name FROM wD_Games WHERE id=".$gameID);
										if ($successRemove == '')
										{
												$successRemove = 'Succesfully Removed: <a href=/board.php?gameID='.$gameID.'>'.$curGamename.' ('.$gameID.')</a>';
										}
										else
										{
												$successRemove .= ', <a href=/board.php?gameID='.$gameID.'>'.$curGamename.' ('.$gameID.')</a>';
										}
								}
								else
								{
										if ($gamecheck <> 1)
										{
												if ($failedRemove == '')
												{
														$failedRemove = 'Failed To Remove: </br>'.$gameValue.' - Invalid GameID';
												}
												else
												{
														$failedRemove .= '</br>'.$gameValue.' - Invalid GameID';
												}
										}
										else
										{
												list($curGamename) = $DB->sql_row("SELECT name FROM wD_Games WHERE id=".$gameID);
												if ($failedRemove == '')
												{
														$failedRemove = 'Failed To Remove: </br><a href=/board.php?gameID='.$gameID.'>'.$curGamename.' ('.$gameID.')</a> - Not in Tournament';
												}
												else
												{
														$failedRemove .= '</br><a href=/board.php?gameID='.$gameID.'>'.$curGamename.' ('.$gameID.')</a> - Not in Tournament';
												}
										}
								}
						}
						print '<p class = "contactUs">'.$successRemove.'</br></br>';
						print $failedRemove.'</br></br>';
						print '<a href="tournamentManagement.php?tournamentID='.$tournamentID.'">Edit Again</a></p>';
						$DB->sql_put("INSERT INTO wD_AdminLog ( name, userID, time, details, params )
									VALUES ( 'Remove Games From Tournament', ".$User->id.", ".time().", 'Tournament ".$tournamentID." ', '".$DB->escape(serialize($paramRemoveGameList))."' )");
				}
				else
				{
						print '<p class = "contactUs">You cannot edit a tournament you are in.</p>';
				}
		}
		else if ($addPlayers)
		{
				list($modInTournament) = $DB->sql_row("SELECT COUNT(1) FROM wD_TournamentParticipants WHERE tournamentID=".$tournamentID." AND userID=".$User->id);
				if ($modInTournament == 0 || $User->type['Admin'])
				{
						$successAdd = '';
						$failedAdd = '';
						foreach ($paramAddPlayerList as $playerValue){
								$playerID = (int)$playerValue;
								list($playercheck) = $DB->sql_row("SELECT COUNT(1) FROM wD_Users WHERE id=".$playerID);
								list($playercheck2) = $DB->sql_row("SELECT COUNT(1) FROM wD_TournamentParticipants WHERE tournamentID=".$tournamentID." AND userID=".$playerID);
								list($year, $directorID, $coDirectorID) = $DB->sql_row("SELECT year, directorID, coDirectorID FROM wD_Tournaments WHERE id=".$tournamentID);
								$playercheck3 = ( ( ($playerID <> $directorID) && ($playerID <> $coDirectorID) ) || ($year < 2019) ); //hard-coded 2019 in since TD's could be in their own tournaments previously
								if ($playercheck == 1 && $playercheck2 < 1 && $playercheck3)
								{
										$sql = "INSERT INTO wD_TournamentParticipants ( tournamentID, userID, status ) ";
										$sql .= " VALUES ( ".$tournamentID.", ".$playerID.", 'Accepted' ) ";
										$DB->sql_put($sql);
										list($curPlayername) = $DB->sql_row("SELECT username FROM wD_Users WHERE id=".$playerID);
										if ($successAdd == '')
										{
												$successAdd = 'Succesfully Added: <a href=/userprofile.php?userID='.$playerID.'>'.$curPlayername.' ('.$playerID.')</a>';
										}
										else
										{
												$successAdd .= ', <a href=/userprofile.php?userID='.$playerID.'>'.$curPlayername.' ('.$playerID.')</a>';
										}
								}
								else
								{
										if ($playercheck <> 1)
										{
												if ($failedAdd == '')
												{
														$failedAdd = 'Failed To Add: </br>'.$playerValue.' - Invalid UserID';
												}
												else
												{
														$failedAdd .= '</br>'.$playerValue.' - Invalid UserID';
												}
										}
										else if ($playercheck2 > 0)
										{
												list($curPlayername) = $DB->sql_row("SELECT username FROM wD_Users WHERE id=".$playerID);
												if ($failedAdd == '')
												{
														$failedAdd = 'Failed To Add: </br><a href=/userprofile.php?userID='.$playerID.'>'.$curPlayername.' ('.$playerID.')</a> - Already in Tournament';
												}
												else
												{
														$failedAdd .= '</br><a href=/userprofile.php?userID='.$playerID.'>'.$curPlayername.' ('.$playerID.')</a> - Already in Tournament';
												}
										}
										else
										{
												list($curPlayername) = $DB->sql_row("SELECT username FROM wD_Users WHERE id=".$playerID);
												if ($failedAdd == '')
												{
														$failedAdd = 'Failed To Add: </br><a href=/userprofile.php?userID='.$playerID.'>'.$curPlayername.' ('.$playerID.')</a> - Cannot Add Director/Codirector';
												}
												else
												{
														$failedAdd .= '</br><a href=/userprofile.php?userID='.$playerID.'>'.$curPlayername.' ('.$playerID.')</a> - Cannot Add Director/Codirector';
												}
										}
								}
						}
						print '<p class = "contactUs">'.$successAdd.'</br></br>';
						print $failedAdd.'</br></br>';
						print '<a href="tournamentManagement.php?tournamentID='.$tournamentID.'">Edit Again</a></p>';
						$DB->sql_put("INSERT INTO wD_AdminLog ( name, userID, time, details, params )
									VALUES ( 'Add Players to Tournament', ".$User->id.", ".time().", 'Tournament ".$tournamentID." ', '".$DB->escape(serialize($paramAddPlayerList))."' )");
				}
				else
				{
						print '<p class = "contactUs">You cannot edit a tournament you are in.</p>';
				}
		}
		else if ($removePlayers)
		{
				list($modInTournament) = $DB->sql_row("SELECT COUNT(1) FROM wD_TournamentParticipants WHERE tournamentID=".$tournamentID." AND userID=".$User->id);
				if ($modInTournament == 0 || $User->type['Admin'])
				{
						$successRemove = '';
						$failedRemove = '';
						foreach ($paramRemovePlayerList as $playerValue){
								$playerID = (int)$playerValue;
								list($playercheck) = $DB->sql_row("SELECT COUNT(1) FROM wD_Users WHERE id=".$playerID);
								list($playercheck2) = $DB->sql_row("SELECT COUNT(1) FROM wD_TournamentParticipants WHERE tournamentID=".$tournamentID." AND userID=".$playerID);
								if ($playercheck2 > 0)
								{
										$sql = "DELETE FROM wD_TournamentParticipants WHERE tournamentID=".$tournamentID." AND userID=".$playerID;
										$DB->sql_put($sql);
										$sql2 = "DELETE FROM wD_TournamentScoring WHERE tournamentID=".$tournamentID." AND userID=".$playerID. " AND score=0";
										$DB->sql_put($sql);
										list($curPlayername) = $DB->sql_row("SELECT username FROM wD_Users WHERE id=".$playerID);
										if ($successRemove == '')
										{
												$successRemove = 'Succesfully Removed: <a href=/userprofile.php?userID='.$playerID.'>'.$curPlayername.' ('.$playerID.')</a>';
										}
										else
										{
												$successRemove .= ', <a href=/userprofile.php?userID='.$playerID.'>'.$curPlayername.' ('.$playerID.')</a>';
										}
								}
								else
								{
										if ($playercheck <> 1)
										{
												if ($failedRemove == '')
												{
														$failedRemove = 'Failed To Remove: </br>'.$playerValue.' - Invalid UserID';
												}
												else
												{
														$failedRemove .= '</br>'.$playerValue.' - Invalid UserID';
												}
										}
										else
										{
												list($curPlayername) = $DB->sql_row("SELECT username FROM wD_Users WHERE id=".$playerID);
												if ($failedRemove == '')
												{
														$failedRemove = 'Failed To Remove: </br><a href=/userprofile.php?userID='.$playerID.'>'.$curPlayername.' ('.$playerID.')</a> - Not in Tournament';
												}
												else
												{
														$failedRemove .= '</br><a href=/userprofile.php?userID='.$playerID.'>'.$curPlayername.' ('.$playerID.')</a> - Not in Tournament';
												}
										}
								}
						}
						print '<p class = "contactUs">'.$successRemove.'</br></br>';
						print $failedRemove.'</br></br>';
						print '<a href="tournamentManagement.php?tournamentID='.$tournamentID.'">Edit Again</a></p>';
						$DB->sql_put("INSERT INTO wD_AdminLog ( name, userID, time, details, params )
									VALUES ( 'Remove Players From Tournament', ".$User->id.", ".time().", 'Tournament ".$tournamentID." ', '".$DB->escape(serialize($paramRemovePlayerList))."' )");
				}
				else
				{
						print '<p class = "contactUs">You cannot edit a tournament you are in.</p>';
				}
		}
		elseif ($sync)
		{
				list($directorID, $coDirectorID) = $DB->sql_row("SELECT directorID, coDirectorID FROM wD_Tournaments WHERE id=".$tournamentID);
				$sql = "DELETE FROM wD_TournamentParticipants WHERE tournamentID=".$tournamentID.";";
				$sql2 = "INSERT INTO wD_TournamentParticipants ( tournamentID, userID, status)
						SELECT DISTINCT ".$tournamentID.", m.userID, 'Accepted' FROM wD_TournamentGames g INNER JOIN wD_Members m ON g.gameID = m.gameID
						WHERE g.tournamentID=".$tournamentID." AND m.userID <> ".$directorID." AND m.userID <> ".$coDirectorID.";";

				try
				{
						$DB->sql_put($sql);
						$DB->sql_put($sql2);
				}
				catch(Exception $e)
				{
						print '<div class="contactUs"> Sorry, but there was a problem syncing this tournament';
						print '<p class="contactUs">'.$e->getMessage().'</p>';
						print '</div>';
						$worked = false;
				}

				if ($worked == true)
				{
						print '<p class = "contactUs">Tournament participants have been synced.</br>';
						print '<a href="tournamentManagement.php?tournamentID='.$tournamentID.'">Edit Again</a></p>';
				}
		}
}

print '</div>';
print '</div>';

?>

<?php
libHTML::footer();
?>
