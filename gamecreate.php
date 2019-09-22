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
	libHTML::notice(l_t('Game creation disabled'), 
	l_t("Game creation has been temporarily disabled while we take care of an unexpected problem. Please try again later, sorry for the inconvenience."));
}

if( !$User->type['User'] )
{
	libHTML::notice(l_t('Not logged on'),l_t("Only a logged on user can create games. Please <a href='logon.php' class='light'>log on</a> to create your own games."));
}

libHTML::starthtml();

if( isset($_REQUEST['newGame']) and is_array($_REQUEST['newGame']) )
{
	try
	{
		$form = $_REQUEST['newGame']; // This makes $form look harmless when it is unsanitized; the parameters must all be sanitized

		$input = array();
		$required = array('variantID', 'name', 'password', 'passwordcheck', 'bet', 'potType', 'phaseMinutes', 'joinPeriod', 'anon', 'pressType', 'missingPlayerPolicy','drawType','minimumReliabilityRating','excusedMissedTurns', 'allowBotCDOrdering');

		$playerTypes = 'Members';

		if ( !isset($form['missingPlayerPolicy']) ) {$form['missingPlayerPolicy'] = 'Normal'; }
		
		$input['botFill'] = 'No';
		if ( isset($form['botFill']) )
		{
			if ($form['botFill'] == 'Yes')
			{
				$input['botFill'] = 'Yes';
			}
		}
		
		foreach($required as $requiredName)
		{
			if ( isset($form[$requiredName]) ) { $input[$requiredName] = $form[$requiredName]; }
			else{ throw new Exception(l_t('The variable "%s" is needed to create a game, but was not entered.',$requiredName)); }
		}
		unset($required, $form);

		$input['variantID']=(int)$input['variantID'];
		if( !isset(Config::$variants[$input['variantID']]) ) { throw new Exception(l_t("Variant ID given (%s) doesn't represent a real variant.",$input['variantID'])); }

		// If the name isn't unique or is too long the database will stop it
		$input['name'] = $DB->escape($input['name']);
		if ( !$input['name'] ) { throw new Exception(l_t("No name entered.")); }

		// This is hashed, so doesn't need validation
		if ( $input['password'] != $input['passwordcheck'] )
		{
			throw new Exception(l_t("The two invite codes entered don't match."));
		}

		$input['bet'] = (int) $input['bet'];
		if ( $input['bet'] < 5 or $input['bet'] > $User->points )
		{
			throw new Exception(l_t("%s is an invalid bet size.",(string)$input['bet']));
		}

		if ( $input['potType'] != 'Winner-takes-all' and $input['potType'] != 'Points-per-supply-center' and $input['potType'] != 'Unranked' and $input['potType'] != 'Sum-of-squares')
		{
			throw new Exception(l_t('Invalid potType input given.'));
		}

		$input['phaseMinutes'] = (int)$input['phaseMinutes'];
		if ( $input['phaseMinutes'] < 5 or $input['phaseMinutes'] > 1440*10 )
		{
			throw new Exception(l_t("The phase value is too large or small; it must be between 5 minutes and 10 days."));
		}

		$input['joinPeriod'] = (int)$input['joinPeriod'];
		if ( $input['joinPeriod'] < 5 or $input['joinPeriod'] > 1440*14 )
		{
			throw new Exception(l_t("Joining period value out of range."));
		}
		
		$input['anon'] = ( (strtolower($input['anon']) == 'yes') ? 'Yes' : 'No' );
		$input['allowBotCDOrdering'] = ( (strtolower($input['allowBotCDOrdering']) == 'yes') ? 'Yes' : 'No' );
		
		// Force 1 vs 1 variants to be unranked to prevent point farming. 
		if ( $input['variantID'] == 15 )
		{
			$input['bet'] = 5; 
			$input['potType'] = 'Unranked';
		}
		
		if ( $input['variantID'] == 23 )
		{
			$input['bet'] = 5; 
			$input['potType'] = 'Unranked';
		}

		// Only classic, no press can support fill with bots. 
		if ( ($input['variantID'] != 1) || ($input['pressType'] != 'NoPress') )
		{
			$input['botFill'] = 'No';
		}
		
		// If no press is selected, force the game to anon to prevent cheating via out of game messaging. 
		switch($input['pressType']) 
		{
			case 'PublicPressOnly':
				$input['pressType'] = 'PublicPressOnly';
				break;
			case 'NoPress':
				$input['pressType'] = 'NoPress';
				$input['anon'] = 'Yes';
				break;
			case 'RulebookPress':
				$input['pressType'] = 'RulebookPress';
				break;
			case 'Regular': // Regular is the default
			default:
				$input['pressType'] = 'Regular';
		}
		
		if($input['botFill'] == 'Yes')
		{
			$input['pressType'] = 'NoPress';
			$input['potType'] = 'Unranked';
			$input['bet'] = 5; 
			$playerTypes = 'Mixed';
		}
		
		switch($input['missingPlayerPolicy']) 
		{
			case 'Wait':
				$input['missingPlayerPolicy'] = 'Wait';
				break;
			default:
				$input['missingPlayerPolicy'] = 'Normal';
		}
		switch($input['drawType']) 
		{
			case 'draw-votes-hidden':
				$input['drawType'] = 'draw-votes-hidden'; 
				break;
			default:
				$input['drawType'] = 'draw-votes-public';
				break;
		}
		$input['minimumReliabilityRating'] = (int)$input['minimumReliabilityRating'];
		if ( $input['minimumReliabilityRating'] < 0 or $input['minimumReliabilityRating'] > 100 )
		{
            throw new Exception(l_t("The reliability rating threshold must range from 0-100"));
		}
		if ( $input['minimumReliabilityRating'] > $User->reliabilityRating )
		{
            throw new Exception(l_t("Your reliability rating is %s%%, so you can't create a game which requires players to have a RR of %s%% or greater.",($User->reliabilityRating),$input['minimumReliabilityRating']));
		}
		$input['excusedMissedTurns'] = (int) $input['excusedMissedTurns'];
		if ( $input['excusedMissedTurns'] < 0 || $input['excusedMissedTurns'] > 4 )
		{
			throw new Exception(l_t("The excused missed turn number is too large or small; it must be between 0 and 4."));
		}
		// Create Game record & object
		require_once(l_r('gamemaster/game.php'));
		$Game = processGame::create(
			$input['variantID'], 
			$input['name'], 
			$input['password'], 
			$input['bet'], 
			$input['potType'], 
			$input['phaseMinutes'], 
			$input['joinPeriod'], 
			$input['anon'], 
			$input['pressType'], 
			$input['missingPlayerPolicy'],
			$input['drawType'],
			$input['minimumReliabilityRating'],
			$input['excusedMissedTurns'],
			$playerTypes,
            $input['allowBotCDOrdering']);

		// Prevent temp banned players from making new games.
		if ($User->userIsTempBanned())
		{
			processGame::eraseGame($Game->id);
			libHTML::notice('You are blocked from creating new games.', 'You are blocked from creating new games.');
		}

		// Create first Member record & object
		processMember::create($User->id, $input['bet']);
		$Game->Members->joinedRedirect();
	}
	catch(Exception $e)
	{
		print '<div class="content">';
		print '<p class="notice">'.$e->getMessage().'</p>';
		print '</div>';
	}
}

if ($User->reliabilityRating < 100)
{
	$maxRR = max(0, (floor($User->reliabilityRating - 1)));
	$defaultRR = min(80,$maxRR);
}
else
{
	$maxRR = 100;
	$defaultRR = 80;
}

if ( $User->points >= 5 ) { $defaultPoints = 5; }
else
{
	print l_t("You cannot create a new game because you have less than 5%s, you only have %s%s. ".
		"You will always have at least 100 points, including the points that you have bet into active games, so if you want ".
		"to start a new game just wait until your other games have finished (<a href='points.php#minpoints' class='light'>read more</a>).",libHTML::points(),$User->points,libHTML::points());

	print '</div>';
	libHTML::footer();
}

if( isset($input) && isset($input['points']) ) { $formPoints = $input['points']; }
else { $formPoints = $defaultPoints; }

require_once(l_r('locales/English/gamecreate.php'));

print '</div>';
libHTML::footer();
?>