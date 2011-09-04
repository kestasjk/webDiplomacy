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

class adjudicatorBuilds {
	/**
	 * Adjudicate unit placing orders
	 */
	function adjudicate()
	{
		global $DB;

		/*
		 * - Fail incomplete Destory orders
		 * - Fail extra orders in which more than one order refers to the same territory
		 * - All remaining orders are successful
		 *
		 * If a destruction order fails a different unit will be selected for destruction when the move
		 * results are apply()ed, accoring to the weird rules of Diplomacy
		 */

		/*
		 * Fail incomplete moves. Incomplete build moves will already have been converted to "Wait" moves
		 *
		 * This could use WHERE moveType = 'Destroy', but NOT 'Wait' is used to clarify that only 'Wait' orders
		 * may be missing a toTerrID field
		 */
		$DB->sql_put(
			"UPDATE wD_Moves
			SET success = 'No'
			WHERE NOT moveType = 'Wait' AND toTerrID IS NULL AND gameID = ".$GLOBALS['GAMEID']
		);

		// Fail conflicting moves
		{
			$tabl = $DB->sql_tabl(
				"SELECT id, toTerrID
				FROM wD_Moves
				WHERE toTerrID IS NOT NULL AND gameID = ".$GLOBALS['GAMEID']
			);
			$usedTerrs = array();
			$badMoveIDs = array();
			while(list($id, $toTerrID) = $DB->tabl_row($tabl))
			{
				if ( ! isset($usedTerrs[$toTerrID]) )
				{
					$usedTerrs[$toTerrID] = true;
					// The first ID is allowed through, all following IDs for the same terrID are failed
				}
				else
				{
					$badMoveIDs[] = $id;
				}
			}
			unset($usedTerrs);

			if ( count($badMoveIDs) )
			{
				$DB->sql_put(
					"UPDATE wD_Moves
					SET success = 'No'
					WHERE id IN (".implode(",",$badMoveIDs).") AND gameID = ".$GLOBALS['GAMEID']
				);
			}
		}

		// Failed moves have been eliminated, those remaining are successful
		$DB->sql_put(
			"UPDATE wD_Moves
			SET success = 'Yes'
			WHERE success = 'Undecided' AND gameID = ".$GLOBALS['GAMEID']
		);

		$DB->sql_put("UPDATE wD_Moves SET dislodged = 'No' WHERE gameID = ".$GLOBALS['GAMEID']);
	}
}
?>