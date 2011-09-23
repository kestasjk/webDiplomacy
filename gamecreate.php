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

if ( $Misc->Panic )
{
	libHTML::notice('Game creation disabled',
		"Game creation has been temporarily disabled while we take care of an
		unexpected problem. Please try again later, sorry for the inconvenience.");
}

if( !$User->type['User'] )
{
	libHTML::notice('Not logged on',"Only a logged on user can create games, guests can't.
		Please <a href='logon.php' class='light'>log on</a> to create your own games.");
}

/**
 * Check for reliability, bevore a user can create a new game...
 */
$reliability = $User->getReliability();
$phasesPlayed = $User->phasesPlayed;
$maxGames = ceil($reliability / 10);
$totalGames = $User->getUncompletedGames();
if ($reliability < 100) { // If the rating is 90 or above, there are restrictions other than the possible newbie restriction below.
	if ( $reliability == 0 )
		libHTML::notice('Reliability rating zero', "NOTICE: You are not allowed to join or create any games given your reliability rating of ZERO (meaning you have missed more than 50% of your orders across all of your games)</p><p>You can improve your reliability rating by not missing any orders, even if it's just saving the default 'Hold' for everything.</p><p>If you are not currently in a game and cannot join one, then you may contact an <a href=\"profile.php?userID=5\">admin</a> and explain your extremely low rating.  The admin, at his or her discretion, may set your reliability rating high enough to allow you 1 game at a time.  By consistently putting orders every turn in that game, your reliability rating will improve enough to allow you more simultaneous games</p>");
	elseif ( $reliability < 50 ) 
		libHTML::notice('Reliability too low to create a new game',"<p>NOTICE: You cannot start a new game when you have trouble keeping up with the ones you have.</p><p>You can improve your reliability rating by not missing any orders, even if it's just saving the default 'Hold' for everything.</p><p>If you are not currently in a game, you may still join one, so long as you have not reached the maximum for your reliability rating.</p><p>Please note that if you are marked as 'Left' for a game, your rating will continue to take hits until someone takes over for you.</p>");
	elseif ( $totalGames >= $maxGames ) // Can't have more than reliability rating / 10 games up
		libHTML::notice('Reliability too low to create a new game',"<p>NOTICE: You cannot start a new game, because you seem to be having trouble keeping up with the orders in the ones you already have</p><p>You can improve your reliability rating by not missing any orders, even if it's just saving the default 'Hold' for everything.</p><p>Please note that if you are marked as 'Left' for a game, your rating will continue to take hits until someone takes over for you.</p><p>Your current rating of <strong>".$reliability."</strong> allows you to have no more than <strong>".$maxGames."</strong> concurrent games before you see this message.  Every 10 reliability points will allow you an additional game.</p>");
} elseif ( $totalGames > 1 && $phasesPlayed / $totalGames < 3 ) // This will prevent newbies from joining 10 games and then leaving right away.  Everyone can join 2 without any restrictions, then they can join more after they've played them for 3 phases.  
	libHTML::notice('Slow down there, partner.',"<p>You're taking on too many games at once for a new member.  Please relax and enjoy the game or games that you are currently in before joining a new one.  You need to play <strong>".($totalGames*3-$phasesPlayed)."</strong> more phases (across all your games) before you can take on another game.  This restriction is to prevent new users from joining a bunch of games, and then leaving the site completely the next day without playing in any of them.</p>");
// END RELIABILITY-PATCH

libHTML::starthtml();

//print '<div class="content">';

if( isset($_REQUEST['newGame']) and is_array($_REQUEST['newGame']) )
{
	try
	{
		$form = $_REQUEST['newGame'];

		$input = array();
		$required = array('variantID', 'name', 'password', 'passwordcheck', 'bet', 'potType', 'phaseMinutes', 'joinPeriod', 'anon', 'pressType');

		foreach($required as $requiredName)
		{
			if ( isset($form[$requiredName]) )
			{
				$input[$requiredName] = $form[$requiredName];
			}
			else
			{
				throw new Exception('The variable "'.$requiredName.'" is needed to create a game, but was not entered.');
			}
		}
		unset($required, $form);

		$input['variantID']=(int)$input['variantID'];
		if( !isset(Config::$variants[$input['variantID']]) )
			throw new Exception("Variant ID given (".$input['variantID'].") doesn't represent a real variant.");

		// If the name isn't unique or is too long the database will stop it
		$input['name'] = $DB->escape($input['name']);
		if ( !$input['name'] )
			throw new Exception("No name entered.");


		if ( $input['password'] != $input['passwordcheck'] )
		{
			throw new Exception("The two passwords entered don't match.");
		}

		$input['bet'] = (int) $input['bet'];
		if ( $input['bet'] < 5 or $input['bet'] > $User->points )
		{
			throw new Exception((string)$input['bet']." is an invalid bet size.");
		}

		if ( $input['potType'] != 'Winner-takes-all' and $input['potType'] != 'Points-per-supply-center' )
		{
			throw new Exception('Invalid potType input given.');
		}

		$input['phaseMinutes'] = (int)$input['phaseMinutes'];

		if ( $input['phaseMinutes'] < 5 or $input['phaseMinutes'] > 1440*10 )
		{
			throw new Exception("The phase value is too large or small; it must be between 5 minutes and 10 days.");
		}

		if ( $input['joinPeriod'] < 5 or $input['joinPeriod'] > 1440*10 )
		{
			throw new Exception("Joining period value out of range.");
		}

		// Create Game record & object
		require_once('gamemaster/game.php');
		$Game = processGame::create($input['variantID'], $input['name'], $input['password'], $input['bet'], $input['potType'], $input['phaseMinutes'], $input['joinPeriod'], $input['anon'], $input['pressType']);

		// Create first Member record & object
		processMember::create($User->id, $input['bet']);
		$Game->Members->joinedRedirect();
	}
	catch(Exception $e)
	{
		print '<div class="content">';
		print '<p class="notice">'.$e->getMessage().'</p>';
		print '</div">';

		libHTML::pagebreak();
	}
}

if ( $User->points >= 5 )
{
	$roundedDefault = round(($User->points/7)/10)*10;
	if ($roundedDefault > 5 )
		$defaultPoints = $roundedDefault;
	else
		$defaultPoints = 5;

}
else
{
	print "You can't create a new game; you have fewer than 5".libHTML::points().", you only have ".$User->points.libHTML::points().".
		You will always have at least 100 points, including the points that you have bet into active games, so if you want
		to start a new game just wait until your other games have finished (<a href='points.php#minpoints' class='light'>read more</a>).";

	print '</div>';
	libHTML::footer();
}

if( isset($input) && isset($input['points']) )
	$formPoints = $input['points'];
else
	$formPoints = $defaultPoints;


require_once('locales/'.$User->locale.'/gamecreate.php');

print '</div>';
libHTML::footer();

?>
