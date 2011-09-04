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

defined('IN_CODE') or die('This script can not be run by itself.');

/**
 * @package GameMaster
 * @subpackage Adjudicator
 */


/*
 * Handles Diplomacy phase orders; the adjudicator script.
 *
 * Many thanks to Lucas Kruijswijk for his algorithm, which this script implements:
 * http://web.inter.nl.net/users/L.B.Kruijswijk/#5
 */

/*
 * Load the supporting cast of adjudication objects
 */

// The move types
require_once('gamemaster/adjudicator/diplomacy/dependencyNode.php');
require_once('gamemaster/adjudicator/diplomacy/hold.php');
require_once('gamemaster/adjudicator/diplomacy/support.php');
require_once('gamemaster/adjudicator/diplomacy/move.php');
require_once('gamemaster/adjudicator/diplomacy/convoyMove.php');

// Load convoy chains ready for processing
require_once('gamemaster/adjudicator/diplomacy/loadConvoyChains.php');

// Paradox detection and resolution exception code
require_once('gamemaster/adjudicator/diplomacy/paradox.php');

class adjudicatorDiplomacy {
	/**
	 * Adjudicate the diplomacy phase. Assumes moves have been loaded into the moves table. requires
	 * nothing outside of the moves table.
	 *
	 * @return array An array of territories in which standoffs occurred. These cannot be saved within
	 * the moves table
	 */
	function adjudicate()
	{
		global $DB, $Game;

		/*
		 * Adjudication:
		 * - Remove invalid orders, give "easy" decisions
		 * - Load convoy chains
		 * - Load units objects
		 * - Recursively process the moves
		 * - Save the successful/dislodged units
		 * - Locate any standoff territories
		 * - Return standoff territories
		 *
		 * See the documentation in diplomacy/dependencyNode.php to find out how the real adjudication occurs. Most of
		 * this function simply prepares the environment for the diplomacy/* objects to perform the adjudication.
		 *
		 * All moves are in the moves table. Removal of invalid orders is done via SQL on the moves table, and easy
		 * decisions are also given using SQL on the moves table, for things which don't require_once any recursive adjudication.
		 * then the recursive adjudcation objects and their relations are loaded from the moves table with SQL.
		 * The diplomacy/* objects, which now contains all the required data and only non-trivial decisions
		 */

		/*
		 * Remove invalid orders; orders which might have been valid, but which aren't because a supporting order
		 * wasn't set correctly.
		 * - Support holding a unit which is moving
		 * - Support moving a unit which isn't moving
		 * - Convoying a unit which isn't moving
		 *
		 * Then easy dislodge decisions are given; i.e. if a unit is not being attacked then it cannot be dislodged,
		 * this allows undisturbed convoys to be allowed without any unnecessary processing.
		 */
		{
			/* Remove invalid support-hold orders */
			$DB->sql_put(
					"UPDATE wD_Moves supportHold
					INNER JOIN wD_Moves supportHeld
						ON ( supportHold.toTerrID = supportHeld.terrID )
					SET supportHold.moveType = 'Hold'
					WHERE supportHold.moveType = 'Support hold' AND supportHeld.moveType = 'Move'
						AND supportHold.gameID = ".$GLOBALS['GAMEID']." AND supportHeld.gameID = ".$GLOBALS['GAMEID']);

			/* Remove invalid support-move orders */
			$DB->sql_put(
					"UPDATE wD_Moves supportMove
					INNER JOIN wD_Moves supportMoved
						ON ( supportMove.fromTerrID = supportMoved.terrID )
					SET supportMove.moveType = 'Hold'
					WHERE supportMove.moveType = 'Support move'
						AND ( ( NOT supportMoved.moveType = 'Move' ) OR ( NOT supportMove.toTerrID = supportMoved.toTerrID ) )
						AND supportMove.gameID = ".$GLOBALS['GAMEID']." AND supportMoved.gameID = ".$GLOBALS['GAMEID']);

			/* Remove invalid convoy orders */
			$DB->sql_put(
					"UPDATE wD_Moves convoy
					INNER JOIN wD_Moves convoyed
						ON ( convoyed.terrID = convoy.fromTerrID
							AND NOT ( convoyed.toTerrID = convoy.toTerrID AND convoyed.moveType = 'Move' ) )
					SET convoy.moveType = 'Hold'
					WHERE convoy.moveType = 'Convoy'
						AND convoy.gameID = ".$GLOBALS['GAMEID']." AND convoyed.gameID = ".$GLOBALS['GAMEID']);

			/* Give easy dislodge = 'No' decisions */
			$DB->sql_put(
				"UPDATE wD_Moves safe
				LEFT JOIN wD_Moves attacker ON (
					attacker.gameID = ".$GLOBALS['GAMEID']." AND safe.terrID = attacker.toTerrID and attacker.moveType = 'Move'
				)
				SET safe.dislodged = 'No'
				WHERE attacker.terrID IS NULL
						AND safe.gameID = ".$GLOBALS['GAMEID']);
		}

		/*
		 * Some units are moving via convoy, but are not set as such. If an army needs to
		 * specify whether or not it wants to move via convoy it will have done so, but if
		 * it only has one choice it will not have specified it.
		 * The default is viaConvoy = 'No', so the only thing we need to check for is an
		 * implied viaConvoy = 'Yes', which is implied by an army moving to a territory to
		 * which it is not adjacent.
		 */
		$DB->sql_put(
			"UPDATE wD_Moves me LEFT JOIN wD_Borders b
			ON ( b.mapID = ".$Game->Variant->mapID." AND b.fromTerrID = me.terrID AND b.toTerrID = me.toTerrID )
			SET /* If we aren't adjacent we must be going via convoy .. */
				me.viaConvoy = IF(b.toTerrID IS NULL, 'Yes', me.viaConvoy)
				/* if we are adjacent we may, or may not, be going via convoy */
			WHERE me.moveType = 'Move' AND me.gameID = ".$GLOBALS['GAMEID']);

		/*
		 * This will load convoy chains, which contain chains of convoying units which are being attacked.
		 *
		 * After this any convoys which don't have a chance will be set to
		 * viaConvoy = 'No'(, moveType='Hold') / success='No'
		 */
		$chains = adjConvoyChains::getConvoys();

		/*
		 * Give easy path decisions, for units which aren't being convoyed.
		 */
		$DB->sql_put("UPDATE wD_Moves SET viaConvoy = 'No' WHERE NOT moveType = 'Move' AND gameID = ".$GLOBALS['GAMEID']);
		$DB->sql_put("UPDATE wD_Moves SET path='Yes' WHERE viaConvoy = 'No' AND NOT path='No' AND gameID = ".$GLOBALS['GAMEID']);

		/*
		 * Now order processing remains; all units which will be relevant to processing must be loaded up.
		 *
		 * These need to be loaded in the correct order; If "Move" objects were loaded before "ConvoyMove" objects
		 * then units which require_once "ConvoyMove" code would simply be loaded as "Move" objects.
		 * In the correct order the unit will be loaded as a "ConvoyMove", and when it matches "Move" the object has
		 * already been loaded as the correct type, and is not re-loaded
		 */
		{
			$units = array();

			// Explicitly holding and convoying units. (Convoying units need no extra special code; they simply have to hold)
			$this->adjLoadUnits($units, 'Hold', 'adjHold');
			$this->adjLoadUnits($units, 'Convoy', 'adjHold');

			/*
			 * If the unit is moving viaConvoy, but already has a successful path, then it does not need to be considered
			 * as a "ConvoyMove", and can be considered as a plain move
			 */
			// Load convoys which are moving into targets
			$this->adjLoadUnits($units, "Move' AND me.viaConvoy='Yes", 'adjConvoyMove', "me.toTerrID = target.terrID");
			// Load convoys which are not moving anywhere
			// TODO: This is unthinkably messy
			$this->adjLoadUnits($units, "Move' AND me.viaConvoy='Yes", 'adjConvoyMove');

			// Load convoy chains into convoying move units, so that they will be able to check their path.
			foreach ( $units as $moveID => $unit )
			{
				if( $unit instanceof adjConvoyMove )
				{
					if ( isset($chains[$moveID]) )
						$units[$moveID]->convoyChain = $chains[$moveID];
					else
						$units[$moveID]->convoyChain = false; // A failed convoy
				}
			}

			// Load up the head to head moves
			$this->adjLoadUnits($units, 'Move', 'adjHeadToHeadMove',"me.toTerrID = target.terrID AND target.moveType='Move'
				AND target.toTerrID = me.terrID AND NOT ( me.viaConvoy = 'Yes' OR target.viaConvoy = 'Yes' )");

			// Load the moves which are moving into targets, which aren't moving back into them
			$this->adjLoadUnits($units, 'Move', 'adjMove', 'me.toTerrID = target.terrID');

			// Load up the remaining moves
			$this->adjLoadUnits($units, 'Move', 'adjMove');

			// Load preventing units into the other units which they are preventing
			//TODO: This is inefficient; a single array of preventers should be copied to all the appropriate units
			$this->adjLoadUnits($units, 'Move', 'adjMove',"me.toTerrID = target.toTerrID AND target.moveType = 'Move' AND NOT me.id = target.id", 'preventers');

			// Load support hold and support moving units
			$this->adjLoadUnits($units, 'Support move', 'adjSupportMove', 'me.toTerrID = target.toTerrID AND me.fromTerrID = target.terrID');
			$this->adjLoadUnits($units, 'Support hold', 'adjSupportHold');

			// Load the units giving support into the units which they are supporting
			$this->adjLoadUnits($units, 'Support move', 'adjSupportMove', 'me.toTerrID = target.toTerrID AND me.fromTerrID = target.terrID', 'supporters');
			$this->adjLoadUnits($units, 'Support hold', 'adjSupportHold', 'me.toTerrID = target.terrID', 'supporters');

			// Load attacking units into the units which they are attacking
			$this->adjLoadUnits($units, 'Move', 'adjMove','me.toTerrID = target.terrID', 'attackers');
		}

		// All unit/dependencyNode objects loaded

		/*
		 * All units have been loaded, but the target variables are simply IDs, not actual objects.
		 * ->setUnits() replaces the IDs in target class fields with the actual unit objects, which can
		 * be used to get data recursively.
		 */
		foreach($units as $unit)
		{
			$unit->setUnits($units);
		}

		// Now that the environment is prepared begin the adjudication
		{
			/*
			 * Create the global decision stack, which is used for detecting paradoxes and telling
			 * how long the paradoxes are:
			 */
			$GLOBALS['decisionStack'] = array();

			/* Collect the successes and the dislodges */
			$successes = array();
			$dislodges = array();
			foreach($units as $unit)
			{
				if ( $unit instanceof adjMove or $unit instanceof adjSupport )
				{
					if ( $unit->success() ) // This function is where all of the real adjudication happens
					{
						$successes[] = $unit->id;
					}
				}

				if ( $unit->dislodged() )
				{
					/*
					 * This result will either be cached, or will be cached itself and be required by an
					 * adjMove/adjSupport object, so there is no re-adjudication in calling these two
					 * overlapping functions.
					 */
					$dislodges[] = $unit->id;
				}
			}

			/* Save the results of the adjudication */
			if ( count($successes) )
				$DB->sql_put("UPDATE wD_Moves SET success = 'Yes' WHERE id IN ( ".implode(',',$successes).") AND gameID = ".$GLOBALS['GAMEID']);

			if ( count($dislodges) )
				$DB->sql_put("UPDATE wD_Moves SET dislodged = 'Yes' WHERE id IN ( ".implode(',',$dislodges).") AND gameID = ".$GLOBALS['GAMEID']);

			$DB->sql_put("UPDATE wD_Moves convoy
				INNER JOIN wD_Moves convoyed
				ON (
					convoyed.moveType = 'Move'
					AND convoyed.success = 'Yes'
					AND convoyed.viaConvoy = 'Yes'
					AND convoyed.toTerrID = convoy.toTerrID
					AND convoyed.terrID = convoy.fromTerrID
				)
				SET convoy.success='Yes'
				WHERE convoy.moveType='Convoy' AND convoy.gameID = ".$GLOBALS['GAMEID']." AND convoyed.gameID = ".$GLOBALS['GAMEID']);

			$DB->sql_put("UPDATE wD_Moves SET success = 'No' WHERE success = 'Undecided' AND gameID = ".$GLOBALS['GAMEID']);
			$DB->sql_put("UPDATE wD_Moves SET dislodged = 'No' WHERE dislodged = 'Undecided' AND gameID = ".$GLOBALS['GAMEID']);
		}

		/*
		 * Collect the standoff territories
		 * - More than one unit attempted to move into the territory
		 * - If there is a holding unit it has a holdStrength of 0
		 * - A unit moving into the territory has a prevent strength greater than 0
		 */
		{
			$standoffs = array();
			$completed = array();
			foreach($units as $unit)
			{
				if ( ! $unit instanceof adjMove )
				{
					// This unit isn't moving
					continue;
				}
				elseif ( isset($completed[$unit->id]) )
				{
					// This unit has already been checked
					continue;
				}
				elseif ( $unit->success() )
				{
					// This unit has successfully occupied this territory
					$completed[$unit->id] = true;
					continue;
				}
				elseif ( isset($unit->defender) and $unit->defender->compare('holdStrength', '>', 0) )
				{
					// There is a defender which still has some hold strength
					$completed[$unit->id] = true;
					continue;
				}
				elseif ( count($unit->preventers) )
				{
					$standoff = false;
					foreach($unit->preventers as $preventer)
					{
						$completed[$preventer->id] = true;

						if ( $preventer->compare('preventStrength', '>', 0) )
						{
							// A unit which was competing for the same territory was successful
							$standoff = true;
							break;
						}
					}

					if ( $standoff )
					{
						// A unit still has prevent strength against this territory
						$standoffs[] = $unit->id;
					}

				}
			}
			unset($completed);

			// Convert standoff move ids into standoff territory names
			if( count($standoffs) )
			{
				$tabl = $DB->sql_tabl("SELECT toTerrID FROM wD_Moves WHERE id IN ( ".implode(',', $standoffs)." ) AND gameID = ".$GLOBALS['GAMEID']);

				$standoffs = array();
				while( list($terrID) = $DB->tabl_row($tabl) )
				{
					$standoffs[] = $terrID;
				}
			}
		}

		return $standoffs;
	}

	/**
	 * Load units into the units array, which is indexed by ID. Intended to make it easy to load each type of unit
	 * into the other units which need to be aware of them (loading units support holding someone into the unit being
	 * held). It is hacked together and not designed, but it does work
	 *
	 * @param array $units The units array, indexed by ID
	 * @param string $moveType The name of the type of move
	 * @param string $objectName The name of the type of object
	 * @param string[optional] $targetQuery A query which links to a target unit
	 * @param string[optional] $multiTarget The name of the aggregate array to load myself into
	 */
	function adjLoadUnits(array &$units, $moveType, $objectName, $targetQuery='', $multiTarget=false)
	{
		global $DB, $Game;

		/*
		 * This function loads units in two ways depending on whether $multiTarget is enabled
		 *
		 * If multiTarget is off then the "me" move is the unit being loaded, and the target's
		 * ID will be saved into the "me" unit being loaded.
		 *
		 * If multiTarget is on then the "me" move is actually the *target*, and the "target" unit
		 * is the unit which the "me" move unit is loaded into.
		 *
		 * multiTarget on: me(1) -> target(1)
		 *
		 * multiTarget off: me(1) <- target(1)
		 *
		 * The "me" unit is always the unit that is loaded. If multiTarget is on it is assumed that
		 * the unit which is being loaded into is already present and loaded.
		 */

		$query = "SELECT me.id, me.countryID, ".($targetQuery?'target.id':'0')."
				FROM wD_Moves me ";

		if ( $targetQuery )
		{
			/*
			 * We are linking to one or more targets
			 */
			$query .= "INNER JOIN wD_Moves target ON ( target.gameID=".$GLOBALS['GAMEID']." AND ( ".$targetQuery." ) ) ";
		}

		if ( $moveType )
		{
			/*
			 * A certain move has been specified
			 */
			$query .= "WHERE me.gameID=".$GLOBALS['GAMEID']." AND ( me.moveType = '".$moveType."' )";
		}
		else
		{
			$query .= "WHERE me.gameID=".$GLOBALS['GAMEID'];
		}


		$tabl = $DB->sql_tabl($query);
		while( list($id, $countryID, $target) = $DB->tabl_row($tabl) )
		{
			if ( isset($units[$id]) and ! $multiTarget )
			{
				/*
				 * The unit has already been initialized
				 */
				continue;
			}

			if ( isset($units[$id]) )
			{
				/*
				 * The unit is already present, so we must be loading it into a target
				 */
				$unit = $units[$id];
			}
			else
			{
				/*
				 * A new unit; initialize it
				 */
				$unit = $Game->Variant->{$objectName}($id, $countryID);
			}

			if ( $multiTarget )
			{
				/*
				 * Load the new unit ID into the target unit
				 */
				$units[$target]->{$multiTarget}[] = $id;
			}
			elseif( $targetQuery )
			{
				/*
				 * Load the target unit's ID into the unit
				 */
				if($objectName == 'adjMove' or $objectName == 'adjHeadToHeadMove' or $objectName == 'adjConvoyMove')
				{
					$unit->defender = $target;
				}
				elseif($objectName == 'adjSupportMove')
				{
					$unit->supporting = $target;
				}
			}

			$units[$id] = $unit;
		}

	}
}

?>