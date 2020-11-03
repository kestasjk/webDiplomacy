<?php

/**
 * @package Base
 */

require_once('header.php');

global $DB;

if( !$User->type['User'] )
{
	libHTML::error(l_t("You must be a registered user to sign up for a tournament."));
}

$tournamentID = 0;
$submitted = false;

$apply = false;
$withdraw = false;
$spectate = false;
$update = false;

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
    if ($_POST['tab'] == 'apply')
    {
      $tournamentID = (int)$_POST['tournamentID'];
      $apply = true;
    }
    else if ($_POST['tab'] == 'withdraw')
    {
      $tournamentID = (int)$_POST['tournamentID'];
      $withdraw = true;
    }
		else if ($_POST['tab'] == 'spectate')
		{
			$tournamentID = (int)$_POST['tournamentID'];
			$spectate = true;
		}
		else if ($_POST['tab'] == 'update')
		{
			$tournamentID = (int)$_POST['tournamentID'];
			$update = true;
		}
    else { die('Sorry, but something went wrong.'); }
  }
}

// Print the header and standard php for the site that is required on every page.
libHTML::starthtml();
print libHTML::pageTitle(l_t('Tournament Registration'),l_t('Register for Tournaments'));
?>

<?php

if ($tournamentID > 0)
{
	$sql = "SELECT * FROM wD_Tournaments where id = ". $tournamentID;

	$tabl = $DB->sql_tabl($sql);
	list($id, $name, $description, $status, $minRR, $year, $totalRounds, $forumThreadLink, $externalLink, $directorID, $coDirectorID, $firstPlace, $secondPlace, $thirdPlace) = $DB->tabl_row($tabl);

	$userType = 'Normal';
	if ($User->type['Moderator'])
	{
		$userType = 'Moderator';
	}
	if ($User->id == $directorID || $User->id == $coDirectorID)
	{
		$userType = 'Director';
	}

	if ($submitted)
	{
		$worked = true;
	  if ($update)
	  {
			if ($userType <> 'Normal')
			{
				foreach($_REQUEST as $key => $value)
				{
					if(strpos('x'.$key,'id') <> 0)
					{
						$curID = substr($key,2);
						$curStatus = '';
						$message = '';
						if ($value == 'Accepted')
						{
							$curStatus = 'Accepted';
							$message = 'Congratulations! You have been accepted into '.$name;
						}
						else if ($value == 'Rejected')
						{
							$curStatus = 'Rejected';
							$message = 'Your application to join '.$name.' has been rejected.';
						}
						else if ($value == 'Left')
						{
							$curStatus = 'Left';
							$message = 'You have been removed from '.$name;
						}
						if ($curStatus <> '' && $curID <> $User->id)
						{
							$sql = " UPDATE wD_TournamentParticipants SET status='".$curStatus."' WHERE userID=".$curID;
							try
					    {
					    	$DB->sql_put($sql);
					    }
					    catch(Exception $e)
					    {
					    	print '<div class="contactUs"> Sorry, but there was a problem in updating participants, contact the moderator team at '.Config::$modEMail;
					      print '<p class="contactUs">'.$e->getMessage().'</p>';
					      print '</div>';
					      $worked = false;
					    }
							if ($worked)
							{
								notice::send($curID, 1, 'User', 'No', 'No', $message, 'Gamemaster');
							}
						}
					}
				}
				if ($worked == true)
		    {
		    	print '<p class = "contactUs">Tournament participants have been updated.</br>';
		      print '<a href="tournamentRegistration.php?tournamentID='.$tournamentID.'">Edit Again</a></p>';
		    }
			}
			else
			{
				print 'You are not allowed to update this tournament.</br>';
				print '<a href="tournamentRegistration.php?tournamentID='.$tournamentID.'">Go Back</a></p>';
			}
		}
	  else if ($apply)
	  {
			list($isParticipant) = $DB->sql_row("SELECT COUNT(1) FROM wD_TournamentParticipants WHERE tournamentID=".$tournamentID." AND userID=".$User->id);
			if ($isParticipant < 1)
			{
	  		$sql = " INSERT INTO wD_TournamentParticipants ( tournamentID, userID, status ) ";
	    	$sql .= " Values ('".$tournamentID."','".$User->id."', 'Applied' ) ";

	    	try
	    	{
	    		$DB->sql_put($sql);
	    	}
	    	catch(Exception $e)
	    	{
	    		print '<div class="contactUs"> Sorry, but there was a problem in submitting your application, contact the moderator team at '.Config::$modEMail;
	      	print '<p class="contactUs">'.$e->getMessage().'</p>';
	      	print '</div>';
	      	$worked = false;
	    	}
			}
			else
			{
				$sql = " UPDATE wD_TournamentParticipants SET status='Applied' WHERE userID=".$User->id;
	    	try
	    	{
	    		$DB->sql_put($sql);
	    	}
	    	catch(Exception $e)
	    	{
	    		print '<div class="contactUs"> Sorry, but there was a problem in submitting your application, contact the moderator team at '.Config::$modEMail;
	      	print '<p class="contactUs">'.$e->getMessage().'</p>';
	      	print '</div>';
	      	$worked = false;
	    	}
			}
	  }
		else if ($withdraw)
		{
			$sql = " DELETE FROM wD_TournamentParticipants WHERE tournamentID=".$tournamentID." AND userID=".$User->id;

	    try
	    {
	    	$DB->sql_put($sql);
	    }
	    catch(Exception $e)
	    {
	    	print '<div class="contactUs"> Sorry, but there was a problem in withdrawing your application, contact the moderator team at '.Config::$modEMail;
	      print '<p class="contactUs">'.$e->getMessage().'</p>';
	      print '</div>';
	      $worked = false;
	    }
		}
		else if ($spectate)
		{
			$sql = " INSERT INTO wD_TournamentSpectators ( tournamentID, userID ) ";
	    $sql .= " Values ('".$tournamentID."','".$User->id."' ) ";

	    try
	    {
	     	$DB->sql_put($sql);
	    }
	    catch(Exception $e)
	    {
	     	print '<div class="contactUs"> Sorry, but there was a problem, contact the moderator team at '.Config::$modEMail;
	      print '<p class="contactUs">'.$e->getMessage().'</p>';
	      print '</div>';
	      $worked = false;
	    }
		}
	}

	if (!$update)
	{
		print '<div class = "tournamentShow">';
		print '<h2 class = "tournamentCenter">'.$name.'</h2>';
		if ($status != 'PreStart' && $status != 'Registration')
		{
			print '<a href="tournamentScoring.php?tournamentID='.$id.'">Tournament Scoring</a></br>';
			if($status != 'Registration')
			{
				print '<a href="gamelistings.php?gamelistType=Search&tournamentID='.$id.'">Tournament Games</a></br>';
			}
		}
		if ($userType != 'Normal')
		{
			print '<a href="tournamentManagement.php?tournamentID='.$id.'">Modify Tournament</a></br></br>';
		}
		if ($directorID > 0 )
		{
			list($directorUsername) = $DB->sql_row("Select username from wD_Users where id =".$directorID);
			print '<strong>Director:</strong> <a href="userprofile.php?userID='.$directorID.'">'.$directorUsername.'</a></br>';
		}
		if ($coDirectorID > 0 )
		{
			list($coDirectorUsername) = $DB->sql_row("Select username from wD_Users where id =".$coDirectorID);
			print ' <strong>Co-Director:</strong> <a href="userprofile.php?userID='.$coDirectorID.'">'.$coDirectorUsername.'</a>';
		}
		if ($forumThreadLink != '')
		{
			print '</br> <strong>Forum thread:</strong> <a href="'.$forumThreadLink.'">here</a>';
		}
		if ($externalLink != '')
		{
			print '</br> <strong>External site:</strong> <a href="'.$externalLink.'">here</a></br>';
		}

		print'</br><strong>Description:</strong></br>'.$description.'
		</br></br>
		<strong>Start year:</strong> '.$year.' </br></br>
		<strong>Rounds: </strong> '.$totalRounds.'
		</br> </br>
		<strong>Required Reliability:</strong> '.$minRR.'%';
		print '</br></br></div></br>';
		if ($userType <> 'Director')
		{
			if ($status == 'PreStart')
			{
				print 'Registration has not opened yet for this tournament. Try back later.';
			}
			else
			{
				if ($status == 'Registration')
				{
					$userStatus = '';
					if (!list($userStatus) = $DB->sql_row("SELECT status FROM wD_TournamentParticipants WHERE userID=".$User->id." AND tournamentID=".$tournamentID))
					{
						if ($User->reliabilityRating >= $minRR)
						{
							print '<form onsubmit="return confirm(\''. l_t("Please confirm you have read all of the rules of this tournament. You will be expected to understand and follow them.").'\');" method="post" action="#">
			        <input type="hidden" name="tab" value="apply">
							<input type="hidden" name="tournamentID" value="'.$tournamentID.'">
			        <input type="submit" class="green-Submit" name="submit" value="Apply to this Tournament">
			        </form>';
						}
						else
						{
							print 'You are too unreliable to join this tournament. The tournament has a minimum reliability rating of '.$minRR.', and <a href="userprofile.php?detail=civilDisorders&userID='.$User->id.'">';
							print 'your reliability rating is '.$User->reliabilityRating.'</a>.';
							print ' To make it into future tournaments, increase your reliability rating by not missing turns.';
						}
					}
					elseif ($userStatus == 'Applied')
					{
						print 'You have applied to this tournament. The tournament director will review your application shortly.</br></br>';
						print '<form method="post" action="#">
						<input type="hidden" name="tab" value="withdraw">
						<input type="hidden" name="tournamentID" value="'.$tournamentID.'">
						<input type="submit" class="green-Submit" name="submit" value="Withdraw from this Tournament">
						</form>';
					}
					elseif ($userStatus == 'Accepted')
					{
						print 'Congratulations, you have been accepted into this tournament.';
					}
					elseif ($userStatus == 'Rejected')
					{
						print 'You have been rejected from this tournament. Please contact the tournament director with any questions.';
					}
					else
					{
						print 'You have been removed from this tournament. Please contact the tournament director with any questions.';
					}
				}
				else
				{
					print '</br>The registration period for this tournament has closed.</br></br>';
					list($isSpectating) = $DB->sql_row("SELECT COUNT(1) FROM wD_TournamentSpectators WHERE tournamentID=".$tournamentID." AND userID=".$User->id);
					list($isParticipant) = $DB->sql_row("SELECT COUNT(1) FROM wD_TournamentParticipants WHERE tournamentID=".$tournamentID." AND userID=".$User->id." AND status<>'Rejected' AND status <>'Applied'");
					if ($isSpectating == 0 && $isParticipant == 0)
					{
						print '<form method="post" action="#">
						<input type="hidden" name="tab" value="spectate">
						<input type="hidden" name="tournamentID" value="'.$tournamentID.'">
						<input type="submit" class="green-Submit" name="submit" value="Spectate this Tournament">
						</form>';
					}
				}
			}
		}
		if ($userType == 'Normal')
		{
			list($participantCount) = $DB->sql_row("SELECT COUNT(1) FROM wD_TournamentParticipants
				WHERE tournamentID=".$tournamentID." AND status='Accepted'");
			if ($participantCount > 0 && $status <> 'PreStart')
			{
				print '</br></br>';
				print "<TABLE class='advancedSearch'>";
				print "<tr>";
				print '<th class = "advancedSearch">Approved Participants</th>';
				print "</tr>";
				$participantTabl = $DB->sql_tabl("SELECT t.userID, u.username FROM wD_TournamentParticipants t INNER JOIN wD_Users u ON t.userID = u.id
					WHERE t.tournamentID=".$tournamentID." AND t.status='Accepted' ORDER BY u.username ASC");
				while (list($curUserID, $curUsername) = $DB->tabl_row($participantTabl))
				{
					print '<TD class = "advancedSearch"><a href="userprofile.php?userID='.$curUserID.'">'.$curUsername.'</a></TD></TR>';
				}
				print '</TABLE>';
			}
		}
		else
		{
			print '</br></br>';
			print '<form method="post" action="#">
			<input type="hidden" name="tab" value="update">
			<input type="hidden" name="tournamentID" value="'.$tournamentID.'">';
			print "<TABLE class='advancedSearch'>";
			print "<tr>";
			print '<th class = "advancedSearch">Applicants</th>';
			print '<th class = "advancedSearch">Points</th>';
			print '<th class = "advancedSearch">Time Joined</th>';
			print '<th class = "advancedSearch">Reliability</th>';
			print '<th class = "advancedSearch">Finished Games</th>';
			print '<th class = "advancedSearch">Accept</th>';
			print '<th class = "advancedSearch">Reject</th>';
			print "</tr>";
			$participantTabl = $DB->sql_tabl("SELECT t.userID, u.gameCount FROM wD_TournamentParticipants t INNER JOIN wD_Users u ON t.userID = u.id
				WHERE t.tournamentID=".$tournamentID." AND t.status='Applied' ORDER BY u.username ASC");
			while (list($curUserID, $curGameCount) = $DB->tabl_row($participantTabl))
			{
				$curUser = new User($curUserID);
				print '<TD class = "advancedSearch"><a href="userprofile.php?userID='.$curUserID.'">'.$curUser->username.'</a></TD>';
				print '<TD class = "advancedSearch">'.$curUser->points.'</TD>';
				print '<TD class = "advancedSearch">'.$curUser->timeJoinedtxt().'</TD>';
				print '<TD class = "advancedSearch">'.$curUser->reliabilityRating.'</TD>';
				print '<TD class = "advancedSearch">'.$curGameCount.'</TD>';
				print '<TD class = "advancedSearch">
							<INPUT type="checkbox" name="id'.$curUserID.'" value="Accepted">
							</TD>
							<TD class = "advancedSearch">
							<INPUT type="checkbox" name="id'.$curUserID.'" value="Rejected">
							</TD>';
				print'</TR>';
			}
			print '</TABLE>';

			print '</br></br>';
			print "<TABLE class='advancedSearch'>";
			print "<tr>";
			print '<th class = "advancedSearch">Accepted Participants</th>';
			print '<th class = "advancedSearch">Points</th>';
			print '<th class = "advancedSearch">Time Joined</th>';
			print '<th class = "advancedSearch">Reliability</th>';
			print '<th class = "advancedSearch">Finished Games</th>';
			if ($status == 'Registration' || $status == 'PreStart')
			{
				print '<th class = "advancedSearch">Set to Rejected</th>';
			}
			else
			{
				print '<th class = "advancedSearch">Set to Left</th>';
			}
			print "</tr>";
			$participantTabl = $DB->sql_tabl("SELECT t.userID, u.gameCount FROM wD_TournamentParticipants t INNER JOIN wD_Users u ON t.userID = u.id
				WHERE t.tournamentID=".$tournamentID." AND t.status='Accepted' ORDER BY u.username ASC");
			while (list($curUserID, $curGameCount) = $DB->tabl_row($participantTabl))
			{
				$curUser = new User($curUserID);
				print '<TD class = "advancedSearch"><a href="userprofile.php?userID='.$curUserID.'">'.$curUser->username.'</a></TD>';
				print '<TD class = "advancedSearch">'.$curUser->points.'</TD>';
				print '<TD class = "advancedSearch">'.$curUser->timeJoinedtxt().'</TD>';
				print '<TD class = "advancedSearch">'.$curUser->reliabilityRating.'</TD>';
				print '<TD class = "advancedSearch">'.$curGameCount.'</TD>';
				print '<TD class = "advancedSearch">';
				if ($status == 'Registration' || $status == 'PreStart')
				{
					print '<INPUT type="checkbox" name="id'.$curUserID.'" value="Rejected">';
				}
				else
				{
					print '<INPUT type="checkbox" name="id'.$curUserID.'" value="Left">';
				}
				print '</TD></TR>';
			}
			print '</TABLE>';

			print '</br></br>';
			print "<TABLE class='advancedSearch'>";
			print "<tr>";
			print '<th class = "advancedSearch">Rejected Applicants</th>';
			print '<th class = "advancedSearch">Points</th>';
			print '<th class = "advancedSearch">Time Joined</th>';
			print '<th class = "advancedSearch">Reliability</th>';
			print '<th class = "advancedSearch">Finished Games</th>';
			print '<th class = "advancedSearch">Set to Accepted</th>';
			print "</tr>";
			$participantTabl = $DB->sql_tabl("SELECT t.userID, u.gameCount FROM wD_TournamentParticipants t INNER JOIN wD_Users u ON t.userID = u.id
				WHERE t.tournamentID=".$tournamentID." AND t.status='Rejected' ORDER BY u.username ASC");
			while (list($curUserID, $curGameCount) = $DB->tabl_row($participantTabl))
			{
				$curUser = new User($curUserID);
				print '<TD class = "advancedSearch"><a href="userprofile.php?userID='.$curUserID.'">'.$curUser->username.'</a></TD>';
				print '<TD class = "advancedSearch">'.$curUser->points.'</TD>';
				print '<TD class = "advancedSearch">'.$curUser->timeJoinedtxt().'</TD>';
				print '<TD class = "advancedSearch">'.$curUser->reliabilityRating.'</TD>';
				print '<TD class = "advancedSearch">'.$curGameCount.'</TD>';
				print '<TD class = "advancedSearch">
				<INPUT type="checkbox" name="id'.$curUserID.'" value="Accepted">
				</TD>';
				print'</TR>';
			}
			print '</TABLE>';

			print '</br></br>';
			print "<TABLE class='advancedSearch'>";
			print "<tr>";
			print '<th class = "advancedSearch">Left Participants</th>';
			print '<th class = "advancedSearch">Points</th>';
			print '<th class = "advancedSearch">Time Joined</th>';
			print '<th class = "advancedSearch">Reliability</th>';
			print '<th class = "advancedSearch">Finished Games</th>';
			print '<th class = "advancedSearch">Set to Accepted</th>';
			print "</tr>";
			$participantTabl = $DB->sql_tabl("SELECT t.userID, u.gameCount FROM wD_TournamentParticipants t INNER JOIN wD_Users u ON t.userID = u.id
				WHERE t.tournamentID=".$tournamentID." AND t.status='Left' ORDER BY u.username ASC");
			while (list($curUserID, $curGameCount) = $DB->tabl_row($participantTabl))
			{
				$curUser = new User($curUserID);
				print '<TD class = "advancedSearch"><a href="userprofile.php?userID='.$curUserID.'">'.$curUser->username.'</a></TD>';
				print '<TD class = "advancedSearch">'.$curUser->points.'</TD>';
				print '<TD class = "advancedSearch">'.$curUser->timeJoinedtxt().'</TD>';
				print '<TD class = "advancedSearch">'.$curUser->reliabilityRating.'</TD>';
				print '<TD class = "advancedSearch">'.$curGameCount.'</TD>';
				print '<TD class = "advancedSearch">
				<INPUT type="checkbox" name="id'.$curUserID.'" value="Accepted">
				</TD>';
				print'</TR>';
			}
			print '</TABLE>';
			print'</br></br><input type="submit" class="green-Submit" name="submit" value="Update"></form>';
		}
	}

	print '</div>';
	print '</div>';
}
else
{
	$tourneys = $DB->sql_tabl("SELECT id, name FROM wD_Tournaments WHERE status = 'Registration'");
  print '<div class = "gameCreateShow">
  			<FORM class="gameCreate" method="get" action="tournamentRegistration.php">
    		<p><strong><center>Select A Tournament</center></strong>
    		<br/><select  class = "gameCreate" name="tournamentID">';
      while (list($id, $name) = $DB->tabl_row($tourneys))
      {
        print'<option value="'.$id.'">'.$name.'</option>';
      }
	print '</select><br/><br/><br/>
  <input type="submit" name="submit" class="green-Submit" value="Go" /></p></form></div>';
	print '</div>';
}

?>

<?php
libHTML::footer();
?>
