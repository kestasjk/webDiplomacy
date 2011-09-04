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
 * Converts & sanitizes orders to moves for processing, then performs the actions based on
 * the results of the processed moves, and also creates new moves
 *
 * @package GameMaster
 * @subpackage Orders
 */
class processOrderDiplomacy extends processOrder
{
	/**
	 * Wipe all the incomplete orders.
	 */
	public function completeAll()
	{
		global $DB, $Game;

		$DB->sql_put("UPDATE wD_Orders
			SET type='Hold', toTerrID = NULL, fromTerrID = NULL
			WHERE gameID = ".$Game->id." AND NOT (
				( type='Hold' AND toTerrID IS NULL AND fromTerrID IS NULL )
				OR
				( ( type='Move' OR type='Support hold' ) AND toTerrID IS NOT NULL AND fromTerrID IS NULL )
				OR
				( ( type='Support move' OR type='Convoy' ) AND toTerrID IS NOT NULL AND fromTerrID IS NOT NULL )
			)");

		$DB->sql_put("UPDATE wD_Orders SET viaConvoy = 'No'
				WHERE gameID = ".$Game->id." AND viaConvoy IS NULL");
	}

	/**
	 * Convert orders to moves to be adjudicated
	 */
	public function toMoves()
	{
		global $DB, $Game;

		// TODO: Why does wD_Moves need its own ID?
		// Insert all the needed info into the moves table, stripping off the coasts data, which the adjudicator doesn't deal with
		$DB->sql_put("INSERT INTO wD_Moves
				( 	gameID, orderID, unitID, countryID, 	moveType,
					terrID,
					toTerrID,
					fromTerrID,
					viaConvoy )
			SELECT 	o.gameID, o.id, 	o.unitID, o.countryID, o.type,
					".$Game->Variant->deCoastSelect('unit.terrID')." as terrID,
					".$Game->Variant->deCoastSelect('o.toTerrID')." as toTerrID,
					".$Game->Variant->deCoastSelect('o.fromTerrID')." as fromTerrID,
					o.viaConvoy
			FROM wD_Orders o
			INNER JOIN wD_Units unit ON ( o.unitID = unit.id )
			WHERE o.gameID = ".$Game->id);
	}

	/**
	 * Apply the adjudicated moves; move the actual units around and change the ownerships of territories etc
	 *
	 * @param array $standOffTerrs An array of territories which currently have a standoff
	 */
	public function apply($standoffTerrs)
	{
		global $Game, $DB;

		/*
		 * - Occupy all units, Set dislodged units to retreat
		 * - Update unit positions
		 * - Empty all territories now without units
		 * - Insert standoffs
		 */

		/*
		 * - Occupy all units, Set dislodged units to retreat
		 */
		// FIXME: This query seems to take ~1/2 the time of the entire game-processing period
		$DB->sql_put(
			"INSERT INTO wD_TerrStatus
				( gameID, countryID, occupyingUnitID, terrID, occupiedFromTerrID, retreatingUnitID )
			SELECT
				".$Game->id." as gameID,
				0 as countryID, /* This will be set later */
				occupy.unitID as occupyingUnitID,
				occupy.toTerrID as terrID,
				/* If we are moving from a convoy we don't register
				the territory we came from. See DATC 4.A.5-b */
				IF( occupy.viaConvoy='No', occupy.terrID, NULL) as occupiedFromTerrID,
				retreat.unitID as retreatingUnitID
			FROM wD_Moves occupy
			LEFT JOIN wD_Moves retreat
				ON ( retreat.terrID = occupy.toTerrID AND retreat.dislodged = 'Yes' AND retreat.gameID=".$GLOBALS['GAMEID']."  )
			WHERE occupy.success = 'Yes' AND occupy.moveType = 'Move' AND occupy.gameID=".$GLOBALS['GAMEID']."
			ON DUPLICATE KEY
				UPDATE occupyingUnitID = VALUES(occupyingUnitID),
						occupiedFromTerrID = VALUES(occupiedFromTerrID),
						retreatingUnitID = VALUES(retreatingUnitID)"
		);

		// - Update unit positions
		$DB->sql_put(
			"UPDATE wD_Units u INNER JOIN wD_Orders o INNER JOIN wD_Moves m ON ( m.gameID=o.gameID AND m.orderID = o.id )
			SET u.terrID = o.toTerrID
			WHERE o.type='Move' AND m.success='Yes'
				AND u.id = o.unitID AND o.gameID = ".$Game->id);
		/*
		 * ***
		 * An important change has happened: Before that query the units were in their
		 * pre-process positions, now all units in the units table are in their updated positions
		 * ***
		 */

		// - Empty all territories now without units
		/* This is now done in processGame::updateTerrStatus
		$DB->sql_put(
			"UPDATE wD_TerrStatus t
			/* If this territory is occupied this join will find a match .. /
			LEFT JOIN wD_Units u ON ( ".Database::deCoastCompare('t.terrID','u.terrID')." AND u.gameID = t.gameID )
			SET occupyingUnitID = NULL
			WHERE t.gameID = ".$Game->id."
				/* .. so all territories which aren't matched are updated /
				AND u.id IS NULL");
		*/

		// - Insert standoffs
		if ( count($standoffTerrs))
		{
			$DB->sql_put(
			"INSERT INTO wD_TerrStatus
				( gameID, countryID, terrID, standoff )
			VALUES ".Database::packArray('('.$Game->id.", 0, '", $standoffTerrs, "', 'Yes')",',')."
			ON DUPLICATE KEY UPDATE standoff = 'Yes'");
		}

		// All Units and TerrStatus changes done, except countryID and occupiedByID
	}

	/**
	 * Create Diplomacy orders for the current game
	 */
	public function create()
	{
		global $DB, $Game;

		// An order is needed for every unit current in-game unit
		$DB->sql_put("INSERT INTO wD_Orders
				( gameID, countryID, type, unitID )
			SELECT gameID, countryID, 'Hold', id
			FROM wD_Units
			WHERE gameID = ".$Game->id);
	}
}

?>