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
 */

require_once('header.php');

require_once(l_r('objects/game.php'));
require_once(l_r('gamepanel/game.php'));

if ( isset($_REQUEST['userID']) && intval($_REQUEST['userID'])>0 )
{
	$userID = (int)$_REQUEST['userID'];
}

else
{
	$userID = false;
}

try
{
	$UserProfile = new User($userID);
}
catch (Exception $e)
{
	libHTML::error(l_t("Invalid user ID given."));
}

if ( ! $UserProfile->type['User'] && !$UserProfile->type['Banned'] )
{
	$message = l_t('Cannot display profile: The specified account #%s is not an active user;',$userID).' ';
	if( $UserProfile->type['Guest'] )
		$message .= l_t('it is a guest account, used by unregistered people to view the server without interacting.');
	elseif( $UserProfile->type['System'] )
		$message .= l_t('it is a system account, without a real human using it.');
	else
		$message .= l_t('in fact I\'m not sure what this account is...');

	foreach($UserProfile->type as $name=>$on)
	{
		if ( $on )
			$message .= l_t($name).', ';
	}
	libHTML::error($message);
}

libHTML::starthtml();

print '<div class="content">';

print '<div>';
print '<h2 class = "profileUsername">'.$UserProfile->username.'</h2>';

// Show moderator information
// Print out relibality rating information here instead of having it a new link.
if ( $User->type['Moderator'] || $User->id == $UserProfile->id )
{	
	print '<div class = "profile-show">';

	print '<div class = "profile_title"> Moderator Info</div>';
	print '<div class = "profile_content_show">';

	if( $User->type['Moderator'] )
	{
		if ( $User->type['Moderator'] && $User->id != $UserProfile->id )
		{
			$modActions=array();

			if ( $User->type['Admin'] )
				$modActions[] = '<a href="index.php?auid='.$UserProfile->id.'">'.l_t('Enter this user\'s account').'</a>';

			$modActions[] = libHTML::admincpType('User',$UserProfile->id);

			if( !$UserProfile->type['Admin'] && ( $User->type['Admin'] || !$UserProfile->type['Moderator'] ) )
				$modActions[] = libHTML::admincp('banUser',array('userID'=>$UserProfile->id), l_t('Ban user'));

			$modActions[] = '<a href="admincp.php?tab=Multi-accounts&aUserID='.$UserProfile->id.'" class="light">'.
				l_t('Enter multi-account finder').'</a>';

			if($modActions)
			{
				print '<p class="notice">'.implode(' - ', $modActions).'</p>';
			}
		}

		print '<strong>UserId:</strong> '.$UserProfile->id.'</br></br>';

		print '<strong>Email:</strong></br>'.$UserProfile->email;
		
		$lastCheckedBy = $UserProfile->modLastCheckedBy();
		$modLastCheckedOn = $UserProfile->modLastCheckedOn();
		list($previousUsernames) = $DB->sql_row(
			"SELECT GROUP_CONCAT(DISTINCT oldUsername SEPARATOR ', ') FROM wD_UsernameHistory WHERE userID = ".$UserProfile->id
		);
		list($previousEmails) = $DB->sql_row(
			"SELECT GROUP_CONCAT(DISTINCT oldEmail SEPARATOR ', ') FROM wD_EmailHistory WHERE userID = ".$UserProfile->id
		);
	
		if($UserProfile->modLastCheckedOn() > 0 && $lastCheckedBy > 0)
		{
			list($modUsername) = $DB->sql_row("SELECT username FROM `wD_Users` WHERE id = ".$lastCheckedBy);
			print '<p class="profileCommentURL">Investigated: '.libTime::text($modLastCheckedOn).', by: <a href="/profile.php?userID='.$lastCheckedBy.'">'.$modUsername.'</a></p>';
		}
		else
		{
			print '<p>Investigated: Never</p>';
		}
	
		if ($UserProfile->userIsTempBanned())
		{
			print '<p>Temp Ban Time: '.libTime::remainingText($UserProfile->tempBan).' Reason: '.$UserProfile->tempBanReason.'</p>';
		}
	
		if (!empty($previousUsernames))
		{
			print '<p class="profileCommentURL">Previous Usernames: '.$previousUsernames.'</p>';
		}
	
		if (!empty($previousEmails))
		{
			print '<p class="profileCommentURL">Previous Emails: '.$previousEmails.'</p>';
		}
	
		if($UserProfile->qualifiesForEmergency() )
		{
			print '<p class="profileCommentURL">User qualifies for emergency pause</p>';
		}
		else if ($UserProfile->emergencyPauseDate == 1)
		{
			print '<p class="profileCommentURL">User is mod banned from emergency pause</p>';
		}
		else
		{
			print '<p class="profileCommentURL">User does not qualify for emergency pause</p>';
		}

		if( !$UserProfile->type['Admin'] && ( $User->type['Admin'] || $User->type['ForumModerator'] ) )
		{
			print '<div class = "profile_title" style="width:90%">';
			print '<strong>Silence Info:</strong> </div>';
			print	'<div class = "profile_content">';
			$silences = $UserProfile->getSilences();

			print '<p><ul class="formlist"><li><strong>'.l_t('Silences:').'</strong></li><li>';

			if( count($silences) == 0 )
				print l_t('No silences against this user.').'</p>';
			else
			{
				print '<ul class="formlist">';
				foreach($silences as $silence) 
				{
					if( !$silence->isEnabled() || $silence->id == $UserProfile->silenceID )
						print '<li>'.$silence->toString().'</li>';
				}
				print '</ul>';
			}

			print '</li><li>';
			print libHTML::admincp('createUserSilence',array('userID'=>$UserProfile->id,'reason'=>''),l_t('Silence user'));
			print '</li></ul></p>';
				
			print '</div>';
		}
	}
	print '</div></div></br>';
}


print '<div class = "profile-show-floating">';

// Profile Information
print '<div class = "profile-show-inside-left">';
	print '<strong>Profile Information</strong>';
	print '<p><ul class="formlist">';

	if( $UserProfile->type['Banned'] )
		print '<p><strong>'.l_t('Banned').'</strong></p>';

	if( $UserProfile->type['Bot'] )
		print '<li><p class="profileCommentURL">Bot User</p>';

	if ( $UserProfile->comment )
	{
		print '<li><div class = "comment_title" style="width:90%">';
		print '<strong>User Comment:</strong> </div></li>';

		print	'<div class = "comment_content">';
		print '<p class="profileComment">"'.$UserProfile->comment.'"</p>';
		print '</div></br>';
	}


	if ( $UserProfile->type['Moderator'] ||  $UserProfile->type['ForumModerator'] || $UserProfile->type['Admin'] )
	{
		print '<li><strong>'.l_t('Mod/Admin team').'</strong></li>';
		print '<li>'.l_t('The best way to get moderator assistance is using our built in <a href="contactUsDirect.php">help page</a>. Please do not message
		moderators directly for help.').'</li>';
		print '<li>&nbsp;</li>';
	}

	if ( $UserProfile->online || time() - (24*60*60) < $UserProfile->timeLastSessionEnded)
		print '<li><strong>'.l_t('Visited in last 24 hours').'</strong></li>';
	else
		print '<li><strong>'.l_t('Last visited:').'</strong> '.libTime::text($UserProfile->timeLastSessionEnded).'</li>';

	print '<li><strong>'.l_t('Joined:').'</strong> '.$UserProfile->timeJoinedtxt().'</li></br>';

	if( $UserProfile->type['DonatorPlatinum'] )
		$donatorMarker = libHTML::platinum().' - <strong>'.l_t('Platinum').'</strong>';
	elseif( $UserProfile->type['DonatorGold'] )
		$donatorMarker = libHTML::gold().' - <strong>'.l_t('Gold').'</strong>';
	elseif( $UserProfile->type['DonatorSilver'] )
		$donatorMarker = libHTML::silver().' - '.l_t('Silver');
	elseif( $UserProfile->type['DonatorBronze'] )
		$donatorMarker = libHTML::bronze().' - '.l_t('Bronze');
	else
		$donatorMarker = false;

	if( $donatorMarker )
		print '<li><strong>'.l_t('Donator:').'</strong> '.$donatorMarker.'</li>';
	print '</li></ul></p>';
print '</div></br>';

// Ranking Info
print '<div class = "profile-show-ranking">';
print '<strong>Ranking Info</strong>';

	$rankingDetails = $UserProfile->rankingDetails();
	$rankingDetailsClassic = $UserProfile->rankingDetailsClassic();
	$rankingDetailsClassicPress = $UserProfile->rankingDetailsClassicPress();
	$rankingDetailsClassicGunboat = $UserProfile->rankingDetailsClassicGunboat();
	$rankingDetailsClassicRanked = $UserProfile->rankingDetailsClassicRanked();
	$rankingDetailsVariants = $UserProfile->rankingDetailsVariants();

	$showAnon = ($UserProfile->id == $User->id || $User->type['Moderator']);

	print '<p><ul class="formlist">';
	print '<div class = "profile_title"><strong>Points'.libHTML::points().':</strong></div>';
	print '<div class = "profile_content_show">';
		print '<li><strong>Available:</strong> '.number_format($UserProfile->points).'</li>';
		print '<li><strong>In play:</strong> '.number_format(($rankingDetails['worth']-$UserProfile->points-($showAnon ? 0 : $rankingDetails['anon']['points']))).'</li>';
		print '<li><strong>Total:</strong> '.number_format($rankingDetails['worth']).'</li>';
		print '</div>';
	print '</ul></p>';

print '</div></br>';

// Game Stats
print '<div class = "profile-show-inside">';
print '<strong style = "text-align: center;">Game Stats</strong>';

	print '<ul class="formlist">';
	$total = 0;
	$includeStatus=array('Won','Drawn','Survived','Defeated','Resigned');
	foreach($rankingDetails['stats'] as $name => $status)
	{
		if ( !in_array($name, $includeStatus) ) continue;

		$total += $status;
		if (!$showAnon && isset($rankingDetails['anon'][$name]))
			$total -= $rankingDetails['anon'][$name];
	}

	if( $total )
	{
		print '<div class = "profile_title">';
		print '<li><strong>'.l_t('All Game stats:').'</strong> </div>
		<div class = "profile_content_show">';

		list($posts) = $DB->sql_row("SELECT SUM(gameMessagesSent) FROM wD_Members m WHERE m.userID = ".$UserProfile->id);
		if( is_null($posts) ) $posts=0;
		print '<li><strong>'.l_t('Game messages:').'</strong> '.number_format($posts).'</li></br>';

		// Shows each of the game details
		foreach($includeStatus as $name)
		{
			if ( !array_key_exists($name, $rankingDetails['stats']) ) continue;

			$status = $rankingDetails['stats'][$name];

			if (!$showAnon && isset($rankingDetails['anon'][$name]))
				$status -= $rankingDetails['anon'][$name];

			print '<li>'.l_t($name.': <strong>%s</strong>',$status);
			print ' ( '.round(($status/$total)*100).'% )';
			print '</li>';
		}
		print '<li>'.l_t('Total (finished): <strong>%s</strong>',$total).'</li>';
		print '<br>';

		// This shows the Playing/Civil Disorder and CD takeover stats.
		foreach($rankingDetails['stats'] as $name => $status)
		{
			if ( in_array($name, $includeStatus) ) continue;

			if (!$showAnon && isset($rankingDetails['anon'][$name]))
				$status -= $rankingDetails['anon'][$name];
			print '<li>'.l_t($name.': <strong>%s</strong>',$status).'</li>';
		}
		print '</li>';
		print '</div>';

		// Get a count of the number of classic games that have been played.
		$totalClassic = 0;
		foreach($rankingDetailsClassic['stats'] as $name => $status)
		{
			if ( !in_array($name, $includeStatus) ) continue;

			$totalClassic += $status;
		}

		// Print out Classic stats if any classic games have been finished.
		if( $totalClassic )
		{
			print '<div class = "profile_title">';
			print '<li><strong>'.l_t('Classic:').'</strong></div><div class = "profile_content">';
			foreach($includeStatus as $name)
			{
				if ( !array_key_exists($name, $rankingDetailsClassic['stats']) ) continue;

				$status = $rankingDetailsClassic['stats'][$name];

				print '<li>'.l_t($name.': <strong>%s</strong>',$status);
				print ' ( '.round(($status/$totalClassic)*100).'% )';
				print '</li>';
			}
			print '<li>'.l_t('Total (finished): <strong>%s</strong>',$totalClassic).'</li>';
			print '</li>';
			print '</div>';
		}

		// Get a count of the number of classic press games that have been played.
		$totalClassicPress = 0;
		foreach($rankingDetailsClassicPress['stats'] as $name => $status)
		{
			if ( !in_array($name, $includeStatus) ) continue;

			$totalClassicPress += $status;
		}

		// Print out Classic Press stats if any classic press games have been finished.
		if( $totalClassicPress )
		{
			print '<div class = "profile_title">';
			print '<li><strong>'.l_t('Classic Press:').'</strong> </div><div class = "profile_content">';

			foreach($includeStatus as $name)
			{
				if ( !array_key_exists($name, $rankingDetailsClassicPress['stats']) ) continue;

				$status = $rankingDetailsClassicPress['stats'][$name];

				print '<li>'.l_t($name.': <strong>%s</strong>',$status);
				print ' ( '.round(($status/$totalClassicPress)*100).'% )';
				print '</li>';
			}
			print '<li>'.l_t('Total (finished): <strong>%s</strong>',$totalClassicPress).'</li>';
			print '</li>';
			print '</div>';
		}

		// Get a count of the number of classic gunboat games that have been played.
		$totalClassicGunboat = 0;
		foreach($rankingDetailsClassicGunboat['stats'] as $name => $status)
		{
			if ( !in_array($name, $includeStatus) ) continue;

			$totalClassicGunboat += $status;
		}

		// Print out Classic Gunboat stats if any classic gunboat games have been finished.
		if( $totalClassicGunboat )
		{
			print '<div class = "profile_title">';
			print '<li><strong>'.l_t('Classic Gunboat:').'</strong> </div><div class = "profile_content">';

			foreach($includeStatus as $name)
			{
				if ( !array_key_exists($name, $rankingDetailsClassicGunboat['stats']) ) continue;

				$status = $rankingDetailsClassicGunboat['stats'][$name];

				print '<li>'.l_t($name.': <strong>%s</strong>',$status);
				print ' ( '.round(($status/$totalClassicGunboat)*100).'% )';
				print '</li>';
			}
			print '<li>'.l_t('Total (finished): <strong>%s</strong>',$totalClassicGunboat).'</li>';
			print '</li>';
			print '</div>';
		}

		// Get a count of the number of classic ranked games that have been played.
		$totalClassicRanked = 0;
		foreach($rankingDetailsClassicRanked['stats'] as $name => $status)
		{
			if ( !in_array($name, $includeStatus) ) continue;

			$totalClassicRanked += $status;
		}

		// Print out Classic Ranked stats if any classic ranked games have been finished.
		if( $totalClassicRanked )
		{
			print '<div class = "profile_title">';
			print '<li><strong>'.l_t('Classic Ranked:').'</strong> </div><div class = "profile_content">';

			foreach($includeStatus as $name)
			{
				if ( !array_key_exists($name, $rankingDetailsClassicRanked['stats']) ) continue;

				$status = $rankingDetailsClassicRanked['stats'][$name];

				print '<li>'.l_t($name.': <strong>%s</strong>',$status);
				print ' ( '.round(($status/$totalClassicRanked)*100).'% )';
				print '</li>';
			}
			print '<li>'.l_t('Total (finished): <strong>%s</strong>',$totalClassicRanked).'</li>';
			print '</li>';
			print '</div>';
		}

		// Get a count of the number of classic games that have been played.
		$totalVariants = 0;
		foreach($rankingDetailsVariants['stats'] as $name => $status)
		{
			if ( !in_array($name, $includeStatus) ) continue;

			$totalVariants += $status;
		}

		// Print out Variant stats if any variant games have been finished.
		if( $totalVariants )
		{
			print '<div class = "profile_title">';
			print '<li><strong>'.l_t('Variant stats:').'</strong> </div> <div class = "profile_content">';

			foreach($includeStatus as $name)
			{
				if ( !array_key_exists($name, $rankingDetailsVariants['stats']) ) continue;

				$status = $rankingDetailsVariants['stats'][$name];

				print '<li>'.l_t($name.': <strong>%s</strong>',$status);
				print ' ( '.round(($status/$totalVariants)*100).'% )';
				print '</li>';
			}
			print '<li>'.l_t('Total (finished): <strong>%s</strong>',$totalVariants).'</li>';
			print '</li>';
			print '</div>';
		}

		print '</br>';
		print '</li>';
	}

	print '</ul></div>';
	print '</div></br>';

print '</div>';

// Do all the RR calculations here
$missedTurns = $UserProfile->getMissedTurns();
$liveMissedTurns = $UserProfile->getLiveMissedTurns();
$allMissedTurns = $missedTurns + $liveMissedTurns;

$recentUnExcusedMissedTurns = $UserProfile->getRecentUnExcusedMissedTurns();
$allUnExcusedMissedTurns = $UserProfile->getYearlyUnExcusedMissedTurns();

$recentLiveUnExcusedMissedTurns = $UserProfile->getLiveRecentUnExcusedMissedTurns();
$allLiveUnExcusedMissedTurns = $UserProfile->getLiveUnExcusedMissedTurns();

$basePercentage = (100*(1- ($allMissedTurns/max($UserProfile->yearlyPhaseCount,1))));
$yearlyPenalty = ($allUnExcusedMissedTurns*5);
$recentPenalty = ($recentUnExcusedMissedTurns*6);
$liveLongPenalty = ($allLiveUnExcusedMissedTurns*5);
$liveShortPenalty = ($recentLiveUnExcusedMissedTurns*6);

$totalRR = max(($basePercentage - $recentPenalty - $yearlyPenalty - $liveShortPenalty - $liveLongPenalty),0);

// Print out relibality rating information here instead of having it a new link.
if ( $User->type['Moderator'] || $User->id == $UserProfile->id )
{	
	print '<div class = "profile-show">';
	print '<div class = "rrInfo">';

	print '<div class = "profile_title"> Reliability Rating: '.$totalRR.'%</div>';
	print '<div class = "profile_content">';
	
	print '<h4>'.l_t('Reliability Explained:').'</h4>';

	print '<div class = "profile_title">What is Reliability?</div>';
	print '<div class = "profile_content">';
	print '<p>Reliability is how consistently you avoid interrupting games. Any un-excused missed turns hurt your rating. If you have any un-excused
	missed turns in the last 4 weeks you will receive an 11% penalty to your RR for <strong>each</strong> of those delays. It is very important
	to everyone you are playing with to be reliable but we understand mistakes happen so this extra penalty will drop to 5% after 28 days. All of the un-excused
	missed turns that negatively impact your rating are highlighted in red below. Excused delays will only negatively impact your base score, seen below. Mod excused
	delays do not hurt your score in any way.
	</br>
	</br>
	<strong>Live Game:</strong> If a game had phases 60 minutes long or less any excused missed turns will only impact your rating for 28 days total. The penalty is the same, 
	5% long term and 6% short term, except the long term penalty is for 28 days and the short term is for 7 days.</br>
	<strong>System Excused:</strong> If you had an "excused missed turn" left this will be yes and will not cause additional penalties against your rating.</br>
	<strong>Mod Excused:</strong> If a moderator excused the missed turn this field will be yes and will not cause additional penalties against your rating.</br>
	<strong>Same Period Excused:</strong> If you have multiple un-excused missed turns in a 24 hour period you are only penalized once with the exception of live games, 
	if this field is yes it will not cause additional penalties against your rating.
	</p></div>';
	print '<div class = "profile_title">What happens if my rating is low?</div>';
	print '<div class = "profile_content">';
	print '<p>
	Many games are made with a minimum rating requirement so this may impact the quality of games you can enter. If you have more then 3 non-live un-excused missed turns in a year
	you will begin getting temporarily banned from making new games, joining existing games, or rejoining your own games.</br>
	</br>
	 <li>1-3 un-excused delays: warnings</li>
	 <li>4 un-excused delays: 1-day temp ban</li>
	 <li>5 un-excused delays: 3-day temp ban</li>
	 <li>6 un-excused delays: 7-day temp ban</li>
	 <li>7 un-excused delays: 14-day temp ban</li>
	 <li>8 un-excused delays: 30-day temp ban</li>
	 <li>9 or more un-excused delays: infinite, must contact mods for removal</li>
	 Live game excused turns are penalized independently for temporary bans. 1-2 un-excused missed turns in live games will be a warning, and the 3rd, and any after that will 
	 result in a 24 hour temp ban. The 2 warnings reset every 28 days resulting in significantly more yearly warnings for live game players then the normal system.
	</p></div>';

	print '<h4>Factors Impacting RR:</h4>';

	if ( $User->type['Moderator'] || $User->id == $UserProfile->id )
	{	
		print '<p> <Strong>Yearly Turns:</Strong> '.$UserProfile->yearlyPhaseCount.'</br>';
	}
	
	if ($allLiveUnExcusedMissedTurns > 0 ) 
	{ 
		print '<Strong>Yearly Missed Turns:</Strong> '.$missedTurns.'</br>
		<Strong>Past Month Live Missed Turns:</Strong> '.$liveMissedTurns.'</br>'; 
	}

	if ( $User->type['Moderator'] || $User->id == $UserProfile->id )
	{
		print'<Strong>Total Counted Missed Turns:</Strong> '.$allMissedTurns.'</br>';
	}

	print '</br><strong>Base Percentage:</strong> '.$basePercentage.'%</br>';

	if ($allLiveUnExcusedMissedTurns > 0 ) { print'(100* (1 - (Yearly Missed Turns + Live Missed Turns)/Yearly Turns))'; }
	else { print'(100* (1 - Yearly Missed Turns/Yearly Turns))'; }

	print'<h4>Added Penalties:</h4>
	<Strong>Yearly Unexcused Missed Turns:</Strong> '.$allUnExcusedMissedTurns.' for a penalty of '.$yearlyPenalty.'%</br>
	<Strong>Recent Unexcused Missed Turns:</Strong> '.$recentUnExcusedMissedTurns.' for a penalty of '.$recentPenalty.'%</br>';

	if ($allLiveUnExcusedMissedTurns > 0 )
	{
		print' <h4>Added Live Game Penalties:</h4>
		<Strong>Last Month Live Unexcused Missed Turns:</Strong> '.$allLiveUnExcusedMissedTurns.' for a penalty of '.$liveLongPenalty.'%</br>
		<Strong>Last Week Live Unexcused Missed Turns:</Strong> '.$recentLiveUnExcusedMissedTurns.' for a penalty of '.$liveShortPenalty.'%</br>';
	}

	print'<h4>Total:</h4>
	<Strong>Reliability Rating:</Strong> '.max(($basePercentage - $recentPenalty - $yearlyPenalty - $liveShortPenalty - $liveLongPenalty),0) .'%
	</p>';

	if ( $User->type['Moderator'] || $User->id == $UserProfile->id )
	{
		print '<h4>Missed Turns:</h4>
		<p>Red = Unexcused</p>';
		$tabl = $DB->sql_tabl("SELECT n.gameID, n.countryID, n.turn, 
		( CASE WHEN n.liveGame = 1 THEN 'Yes' ELSE 'No' END ), 
		g.name,
		( CASE WHEN n.systemExcused = 1 THEN 'Yes' ELSE 'No' END ),
		( CASE WHEN n.modExcused = 1 THEN 'Yes' ELSE 'No' END ),
		( CASE WHEN n.samePeriodExcused = 1 THEN 'Yes' ELSE 'No' END ),
		n.id,
		n.turnDateTime
		FROM wD_MissedTurns n
		LEFT JOIN wD_Games g ON n.gameID = g.id
		WHERE n.userID = ".$UserProfile->id. " and n.turnDateTime > ".(time() - 31536000));

		if ($DB->last_affected() != 0)
		{
			print '<TABLE class="rrInfo">';
			print '<tr>';
			print '<th class= "rrInfo">ID:</th>';
			print '<th class= "rrInfo">Game:</th>';
			print '<th class= "rrInfo">Country</th>';
			print '<th class= "rrInfo">Turn:</th>';
			print '<th class= "rrInfo">LiveGame:</th>';
			print '<th class= "rrInfo">System Excused:</th>';
			print '<th class= "rrInfo">Mod Excused:</th>';
			print '<th class= "rrInfo">Same Period Excused:</th>';
			print '<th class= "rrInfo">Turn Date:</th>';
			print '</tr>';

			while(list($gameID, $countryID, $turn, $liveGame, $name, $systemExcused, $modExcused, $samePeriodExcused, $id, $turnDateTime)=$DB->tabl_row($tabl))
			{
				if ($systemExcused == 'No' && $modExcused == 'No' && $samePeriodExcused == 'No') { print '<tr style="background-color:#F08080;">'; }
				else { print '<tr>'; }

				print '<td> <strong>'.$id.'</strong></td>';
				if ($name != '')
				{
					$Variant=libVariant::loadFromGameID($gameID);
					print '<td> <strong><a href="board.php?gameID='.$gameID.'">'.$name.'</a></strong></td>';
					print '<td> <strong>'.$Variant->countries[$countryID-1].'</strong></td>';
					print '<td> <strong>'.$Variant->turnAsDate($turn).'</strong></td>';
					print '<td> <strong>'.$liveGame.'</strong></td>';
					print '<td> <strong>'.$systemExcused.'</strong></td>';
					print '<td> <strong>'.$modExcused.'</strong></td>';
					print '<td> <strong>'.$samePeriodExcused.'</strong></td>';
					print '<td> <strong>'.libTime::detailedText($turnDateTime).'</strong></td>';
				}
				else
				{
					print '<td> <strong>Cancelled Game</strong></td>';
					print '<td> <strong>'.$countryID.'</strong></td>';
					print '<td> <strong>'.$turn.'</strong></td>';
					print '<td> <strong>'.$liveGame.'</strong></td>';
					print '<td> <strong>'.$systemExcused.'</strong></td>';
					print '<td> <strong>'.$modExcused.'</strong></td>';
					print '<td> <strong>'.$samePeriodExcused.'</strong></td>';
					print '<td> <strong>'.libTime::detailedText($turnDateTime).'</strong></td>';
				}
				
				print '</tr>';
			}
			print '</table>';
		
		}
		else
		{
			print l_t('No missed turns found for this profile.');
		}
		print '</div>';
		print '</div>';
		print '</div>';
	}
}

print '<div id="profile-separator"></div>';

// Start interactive area:

if( isset(Config::$customForumURL) )
{
	if ( $User->type['User'] && $User->id != $UserProfile->id)
	{
		list($newForumId) = $DB->sql_row("SELECT user_id FROM `phpbb_users` WHERE webdip_user_id = ".$UserProfile->id);
		if ($newForumId > 0)
		{
			print '
			<div id="profile-forum-link-container">
				<div class="profile-forum-links">
					<a class="profile-link" href="/contrib/phpBB3/memberlist.php?mode=viewprofile&u='.$newForumId.'">
						<button class="form-submit" id="view-forum-profile">
							New Forum Profile
						</button>
					</a>
				</div>';
			print '
				<div class="profile-forum-links">
					<a class="profile-link" href="/contrib/phpBB3/ucp.php?i=pm&mode=compose&u='.$newForumId.'">
						<button class="form-submit" id="send-pm">
							Send a message to this user
						</button>
					</a>
				</div>
			</div>';
		}
		else
		{
			print '<p class="profileCommentURL">This user cannot currently receive messages.</p>';
		}
	}
}
print '</div>';
?>

<script type="text/javascript">
   var coll = document.getElementsByClassName("profile_title");
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

libHTML::pagebreak();

print '<h3>'.l_t('%s\'s games',$UserProfile->username).' '.( $User->type['User'] ? '(<a href="gamelistings.php?userID='.$UserProfile->id.'&gamelistType=Search">'.l_t('Search').'</a>)' : '' ).'</h3>';

$pagenum = 1;
$resultsPerPage = 20;
$maxPage = 0;
$totalResults = 0;
$sortCol = 'id';
$sortType = 'desc';

if ( isset($_REQUEST['sortCol']))
{
	if ($_REQUEST['sortCol'] == 'name') { $sortCol='name'; }
	else if ($_REQUEST['sortCol'] == 'pot') { $sortCol='pot'; }
	else if ($_REQUEST['sortCol'] == 'phaseMinutes') { $sortCol='phaseMinutes'; }
	else if ($_REQUEST['sortCol'] == 'minimumBet') {$sortCol='minimumBet'; }
	else if ($_REQUEST['sortCol'] == 'minimumReliabilityRating') {$sortCol='minimumReliabilityRating'; }
	else if ($_REQUEST['sortCol'] == 'watchedGames') {$sortCol='watchedGames'; }
	else if ($_REQUEST['sortCol'] == 'turn') {$sortCol='turn'; }
	else if ($_REQUEST['sortCol'] == 'processTime') {$sortCol='processTime'; }
}
if ( isset($_REQUEST['sortType'])) { if ($_REQUEST['sortType'] == 'asc') { $sortType='asc'; } }
if ( isset($_REQUEST['pagenum'])) { $pagenum=(int)$_REQUEST['pagenum']; }

$SQL = "SELECT g.*, (SELECT count(1) FROM wD_WatchedGames w WHERE w.gameID = g.id) AS watchedGames FROM wD_Games g INNER JOIN wD_Members m ON m.gameID = g.id WHERE m.userID = ".$UserProfile->id;
$SQLCounter = "SELECT count(1) FROM wD_Games g INNER JOIN wD_Members m ON m.gameID = g.id WHERE m.userID = ".$UserProfile->id;

if($User->id != $UserProfile->id && !$User->type['Moderator'])
{
	$SQL .= " AND (g.anon = 'No' OR g.phase = 'Finished')";
	$SQLCounter .= " AND (g.anon = 'No' OR g.phase = 'Finished')";
}
$SQL = $SQL . " ORDER BY ";

if ($sortCol <> 'watchedGames' && $sortCol <> 'processTime' && $sortCol <> 'minimumBet') {$SQL .= "g.";}
$ordering = $sortCol;

if ($sortCol == 'processTime') {$ordering = "(CASE WHEN g.processStatus = 'Paused' THEN (g.pauseTimeRemaining + ".time().") ELSE g.processTime END)";}
elseif ($sortCol == 'minimumBet') {$ordering = "(SELECT m4.bet FROM wD_Members m4 WHERE m4.gameID = g.id AND m4.bet > 0 LIMIT 1)";}

$SQL = $SQL . $ordering." ".$sortType." ";
$SQL = $SQL . " Limit ". ($resultsPerPage * ($pagenum - 1)) . "," . $resultsPerPage .";";

$tabl = $DB->sql_tabl($SQL);
list($totalResults) = $DB->sql_row($SQLCounter);
$maxPage = ceil($totalResults / $resultsPerPage);
print "<a name='results'></a>";

if($totalResults == 0)
{
	print l_t('No games found for this profile.');
}
else
{
	print '<center><b> Showing results '.number_format(min(((($pagenum - 1) * $resultsPerPage)+1),$totalResults)).' to '.number_format(min(($pagenum * $resultsPerPage),$totalResults)).' of '.number_format($totalResults).' total results. </b></center></br>';
	printPageBar($pagenum, $maxPage, $sortCol, $sortType, $sortBar = True);

	print '<div class="gamesList">';
	while( $row = $DB->tabl_hash($tabl) )
	{
		$Variant = libVariant::loadFromVariantID($row['variantID']);
		$G = $Variant->panelGame($row);
		print $G->summary(false);
	}
	print '</div>';

	print '</br>';
	printPageBar($pagenum, $maxPage, $sortCol, $sortType);
}

print '</div>';

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
			<FORM class="advancedSearch" method="get" action="profile.php#results">
			<b>Sort By:</b>
			<select  class = "advancedSearch" name="sortCol">
				<option'.(($sortCol=='id') ? ' selected="selected"' : '').' value="id">Game ID</option>
				<option'.(($sortCol=='name') ? ' selected="selected"' : '').' value="name">Game Name</option>
				<option'.(($sortCol=='pot') ? ' selected="selected"' : '').' value="pot">Pot Size</option>
				<option'.(($sortCol=='minimumBet') ? ' selected="selected"' : '').' value="minimumBet">Bet</option>
				<option'.(($sortCol=='phaseMinutes') ? ' selected="selected"' : '').' value="phaseMinutes">Phase Length</option>
				<option'.(($sortCol=='minimumReliabilityRating') ? ' selected="selected"' : '').' value="minimumReliabilityRating">Reliability Rating</option>
				<option'.(($sortCol=='watchedGames') ? ' selected="selected"' : '').' value="watchedGames">Spectator Count</option>
				<option'.(($sortCol=='turn') ? ' selected="selected"' : '').' value="turn">Game Turn</option>
				<option'.(($sortCol=='processTime') ? ' selected="selected"' : '').' value="processTime">Time to Next Phase</option>
			</select>
			<select class = "advancedSearch" name="sortType">
				<option'.(($sortType=='asc') ? ' selected="selected"' : '').' value="asc">Ascending</option>
				<option'.(($sortType=='desc') ? ' selected="selected"' : '').' value="desc">Descending</option>
			</select>';

			foreach($_REQUEST as $key => $value)
			{
				if ($key == 'searchUser')
				{
					foreach ($value as $curKey => $curVal)
					{
						print '<input type="hidden" name="searchUser['.$curKey.']" value="'.$curVal.'">';
					}
				}
				elseif(strpos('x'.$key,'wD') == false && strpos('x'.$key,'phpbb3') == false && strpos('x'.$key,'__utm')== false && $key!="pagenum" && $key!="sortCol" && $key!="sortType")
				{
					print '<input type="hidden" name="'.$key.'" value="'.$value.'">';
				}
			}
			print ' ';
			print '<input type="submit" class="form-submit" name="Submit" value="Refresh" /></form>
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
		print '<FORM method="get" action=profile.php#results>';

		foreach($_REQUEST as $key => $value)
		{
			if ($key == 'searchUser')
			{
				foreach ($value as $curKey => $curVal)
				{
					print '<input type="hidden" name="searchUser['.$curKey.']" value="'.$curVal.'">';
				}
			}
			elseif(strpos('x'.$key,'wD') == false && strpos('x'.$key,'phpbb3')== false && strpos('x'.$key,'__utm')== false && $key!="pagenum")
			{
				print '<input type="hidden" name="'.$key.'" value="'.$value.'">';
			}
		}
		print '<input type="submit" name="pagenum" class="form-submit" value='.$pagenum.' /></form></div>';
	}
}

libHTML::footer();
?>
