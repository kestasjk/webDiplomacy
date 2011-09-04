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
 * Performs a few game processing order related tasks. Mainly just a wrapper for SQL
 * to process orders
 *
 * @package GameMaster
 * @subpackage Orders
 */
class processOrder
{
	/**
	 * Save the current orders as the archive for the Game's current turn
	 */
	public function archiveMoves()
	{
		global $DB, $Game;

		// Wipe all the orders of phases which come after us or instead of us
		$wipeMoveTypes = '';
		switch($Game->phase)
		{
			case 'Diplomacy':
				$wipeMoveTypes = "'Hold','Move','Support hold','Support move','Convoy',";
			case 'Retreats':
				$wipeMoveTypes .= "'Retreat','Disband',";
			case 'Builds':
				$wipeMoveTypes .= "'Build Army','Build Fleet','Wait','Destroy'";
		}
		$wipeMoveTypes = '('.$wipeMoveTypes.')';

		/*
		 * Delete archived orders which might remain from a failed earlier game process which wasn't committed
		 * in the transactional tables.
		 */
		$DB->sql_put("DELETE FROM wD_MovesArchive
			WHERE gameID = ".$Game->id." AND turn = ".$Game->turn." AND type IN ".$wipeMoveTypes);

		if ( $Game->phase != 'Builds' )
		{
			$DB->sql_put(
				"INSERT INTO wD_MovesArchive (
					gameID, turn, type, terrID, /* Index */
					countryID, toTerrID, fromTerrID, viaConvoy, /* Order */
					unitType, /* Unit */
					success, dislodged /* Move */
				)
				SELECT
					o.gameID, ".$Game->turn.", o.type, u.terrID,
					o.countryID, o.toTerrID, o.fromTerrID, IF(o.viaConvoy IS NULL,'No',o.viaConvoy), /* viaConvoy is null for retreat orders */
					u.type,
					/* If 'Undecided' then the order didn't come into play,
						and it can be considered successful
						For dislodged, however, 'Undecided' means that it
						was not dislodged */
					m.success, m.dislodged
				FROM wD_Orders o
				/* Moves needed to get success/dislodged results data */
				INNER JOIN wD_Moves m ON ( m.orderID = o.id AND m.gameID=".$GLOBALS['GAMEID']." )
				/* Units needed for unit type and territory data */
				INNER JOIN wD_Units u ON ( u.id = o.unitID )
				WHERE o.gameID = ".$Game->id);
		}
		else
		{
			/*
			 * Builds is a litte weird in that the orders are not linked to a specific unit, unlike
			 * Diplomacy and Retreats orders.
			 * If building a unit toTerrID contains the place to build at
			 * If destroying a unit toTerrID contains the place to destroy at
			 * If waiting to build there is no data in the order
			 *
			 * The MovesArchive table is indexed with terrID, so to location to build/destroy at is stored at
			 * terrID, instead of toTerrID.
			 * Wait orders are not put into the moves archive, because you can't draw an order which is not
			 * associated with any unit or territory
			 *
			 * Builds moves are the only ones where the unitType can be null
			 */

			$DB->sql_put(
				"INSERT INTO wD_MovesArchive (
					gameID, turn, type, terrID, /* Index */
					countryID, toTerrID, fromTerrID, viaConvoy, /* Order */
					unitType, /* Unit */
					success, dislodged /* Move */
				)
				SELECT
					o.gameID, ".$Game->turn.", o.type, o.toTerrID,
					o.countryID, NULL, NULL, 'No',
					NULL,
					m.success, 'No'
				FROM wD_Orders o
				/* Moves needed to get success/dislodged results data */
				INNER JOIN wD_Moves m ON ( m.orderID = o.id AND m.gameID=".$GLOBALS['GAMEID']." )
				WHERE o.gameID = ".$Game->id." AND NOT o.type = 'Wait' AND success = 'Yes'");
		}
	}

	/**
	 * Delete all the orders
	 */
	public function wipe()
	{
		global $DB, $Game;

		$DB->sql_put("DELETE FROM wD_Orders WHERE gameID = ".$Game->id);
	}
}

?>