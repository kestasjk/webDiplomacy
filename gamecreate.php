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
require_once('lib/reliability.php');

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

libHTML::starthtml();

//print '<div class="content">';

if( isset($_REQUEST['newGame']) and is_array($_REQUEST['newGame']) )
{
	try
	{
		$form = $_REQUEST['newGame']; // This makes $form look harmless when it is unsanitized; the parameters must all be sanitized

		$input = array();
		$required = array('variantID', 'name', 'password', 'passwordcheck', 'bet', 'potType', 'phaseMinutes', 'joinPeriod', 'anon', 'pressType'
						,'countryID'
						,'minRating' 
						,'minPhases'
						,'maxTurns'
						,'specialCDturn'
						,'specialCDcount'
						,'targetSCs'
						,'rlPolicy'					
					);

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

		// This is hashed, so doesn't need validation
		if ( $input['password'] != $input['passwordcheck'] )
		{
			throw new Exception("The two passwords entered don't match.");
		}

		$input['bet'] = (int) $input['bet'];
		if ( $input['bet'] < 2 or $input['bet'] > $User->points )
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

		$input['joinPeriod'] = (int)$input['joinPeriod'];
		if ( $input['joinPeriod'] < 5 or $input['joinPeriod'] > 1440*10 )
		{
			throw new Exception("Joining period value out of range.");
		}
		
		$input['anon'] = ( (strtolower($input['anon']) == 'yes') ? 'Yes' : 'No' );
		
		switch($input['pressType']) {
			case 'PublicPressOnly':
				$input['pressType'] = 'PublicPressOnly';
				break;
			case 'NoPress':
				$input['pressType'] = 'NoPress';
				break;
			case 'Regular': // Regular is the default
			default:
				$input['pressType'] = 'Regular';
		}
	
		$input['minRating'] = (int)$input['minRating'];		
		if ( $input['minRating'] > abs($User->getReliability()) )
		{
			throw new Exception("Your reliability-rating is to low (".$User->getReliability().") for your own requirement (".$input['minRating'].").");
		}
		
		$input['minPhases'] = (int)$input['minPhases'];
		if ( $input['minPhases'] > $User->phasesPlayed )
		{
			throw new Exception("You didn't play enough phases (".$User->phasesPlayed.") for your own requirement (".$input['minPhases'].")");
		}
		
		$input['maxTurns'] = (int)$input['maxTurns'];		
		if ( $input['maxTurns'] < 4 )
			$input['maxTurns'] = 0;
		if ( $input['maxTurns'] > 200 )
			$input['maxTurns'] = 200;

		$input['targetSCs'] = (int)$input['targetSCs'];		
		$input['countryID'] = (int)$input['countryID'];
		
		$input['specialCDturn'] = (int)$input['specialCDturn'];
		if ( $input['specialCDturn'] < 0 ) $input['specialCDturn'] = 0;
		$input['specialCDcount'] = (int)$input['specialCDcount'];
		if ( $input['specialCDcount'] < 0 )	$input['specialCDcount'] = 0;
		
		switch($input['rlPolicy']) {
			case 'Friends':
				$input['rlPolicy'] = 'Friends';
				break;
			case 'Strict':
				$input['rlPolicy'] = 'Strict';
				break;
			case 'None': // Regular is the default
			default:
				$input['rlPolicy'] = 'None';
		}

		// Create Game record & object
		require_once('gamemaster/game.php');
		$Game = processGame::create($input['variantID'], $input['name'], $input['password'], $input['bet'], $input['potType'], $input['phaseMinutes'], 
										$input['joinPeriod'], $input['anon'], $input['pressType'],
										$input['maxTurns'],
										$input['targetSCs'],
										$input['minRating'],
										$input['minPhases'],
										$input['specialCDturn'],
										$input['specialCDcount'],
										$input['rlPolicy']
										);

		/**
		 * Check for reliability, bevore a user can create a new game...
		 */
		if( (count($Game->Variant->countries)>2) && ($message = $User->isReliable($Game)) )
		{
			processGame::eraseGame($Game->id);
			libHTML::notice('Reliable rating not high enough', $message);
		}
		// END RELIABILITY-PATCH
		
		// Create first Member record & object
		processMember::create($User->id, $input['bet'],$input['countryID']);
		
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

if ( $User->points >= 3 )
{
	$roundedDefault = round(($User->points/7)/10)*10;
	if ($roundedDefault > 3 )
		$defaultPoints = $roundedDefault;
	else
		$defaultPoints = 3;

}
else
{
	print "You can't create a new game; you have fewer than 3".libHTML::points().", you only have ".$User->points.libHTML::points().".
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
