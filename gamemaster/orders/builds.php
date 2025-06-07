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
class processOrderBuilds extends processOrder
{
	/**
	 * Wipe all the incomplete orders.
	 */
	public function completeAll()
	{
		global $DB, $Game;

		// Incomplete destroy orders are dealt with in the adjudicator
		$DB->sql_put("UPDATE wD_Orders o INNER JOIN wD_Members m ON ( o.gameID = m.gameID AND o.countryID = m.countryID )
			SET o.type = 'Wait'
			WHERE o.gameID = ".$Game->id." AND o.toTerrID IS NULL AND ( o.type = 'Build Army' OR o.type = 'Build Fleet' )");

		// Make sure users are set to either Wait or Destroy orders correctly depending on how many SCs vs Units they have.
		$DB->sql_put("UPDATE wD_Orders o INNER JOIN wD_Members m ON ( o.gameID = m.gameID AND o.countryID = m.countryID )
			SET o.type = IF( m.supplyCenterNo < m.unitNo, 'Destroy', 'Wait'), o.toTerrID = NULL
			WHERE o.gameID = ".$Game->id." AND (
				( NOT o.type = 'Destroy' AND m.supplyCenterNo < m.unitNo )
				OR ( o.type = 'Destroy' AND m.supplyCenterNo > m.unitNo ) )");
	}

	/**
	 * Convert orders to moves to be adjudicated
	 */
	public function toMoves()
	{
		global $DB, $Game;

		// Insert all the needed info into the moves table, stripping off the coasts data, which the adjudicator doesn't deal with
		$DB->sql_put("INSERT INTO wD_Moves
			( gameID, orderID, unitID, countryID, moveType, toTerrID )
			SELECT gameID, id, 0, countryID, type,  ".$Game->Variant->deCoastSelect('toTerrID')." as toTerrID
			FROM wD_Orders
			WHERE gameID = ".$Game->id);
	}

	/**
	 * Create Unit placing orders for the current game
	 */
	public function create()
	{
		global $DB, $Game;

		$newOrders = array();
		foreach($Game->Members->ByID as $Member )
		{
			$difference = 0;
			if ( $Member->unitNo > $Member->supplyCenterNo )
			{
				$difference = $Member->unitNo - $Member->supplyCenterNo;
				$type = 'Destroy';
			}
			elseif ( $Member->unitNo < $Member->supplyCenterNo )
			{
				$difference = $Member->supplyCenterNo - $Member->unitNo;
				$type = 'Build Army';

				list($max_builds) = $DB->sql_row("SELECT COUNT(*)
					FROM wD_TerrStatus ts
					INNER JOIN wD_Territories t
						ON ( t.id = ts.terrID )
					WHERE ts.gameID = ".$Game->id."
						AND ts.countryID = ".$Member->countryID."
						AND t.countryID = ".$Member->countryID."
						AND ts.occupyingUnitID IS NULL
						AND t.supply = 'Yes'
						AND t.mapID=".$Game->Variant->mapID);

				if ( $difference > $max_builds )
				{
					$difference = $max_builds;
				}
			}

			for( $i=0; $i < $difference; ++$i )
			{
				$newOrders[] = "(".$Game->id.", ".$Member->countryID.", '".$type."')";
			}
		}

		if ( count($newOrders) )
		{
			$DB->sql_put("INSERT INTO wD_Orders
							(gameID, countryID, type)
							VALUES ".implode(', ', $newOrders));
		}
	}
	/**
	 * Apply the adjudicated moves; retreat/disband units as decided. (Updated to account for the 2024 Renegade Games rule changes)
	 */
	public function apply_pre2024Rules()
	{
		global $Game, $DB;

		$DB->sql_put(
				"DELETE FROM u
				USING wD_Units AS u
				INNER JOIN wD_Orders AS o ON ( ".$Game->Variant->deCoastCompare('o.toTerrID','u.terrID')." AND u.gameID = o.gameID )
				INNER JOIN wD_Moves m ON ( m.orderID = o.id AND m.gameID=".$GLOBALS['GAMEID']." )
				WHERE o.gameID = ".$Game->id." AND o.type = 'Destroy'
					AND m.success='Yes'");

		// Remove units as per the destroyindex table for any destory orders that weren't successful
		$tabl = $DB->sql_tabl(
					"SELECT o.id, o.countryID FROM wD_Orders o
					INNER JOIN wD_Moves m ON ( m.orderID = o.id AND m.gameID=".$GLOBALS['GAMEID']." )
					WHERE o.type = 'Destroy' AND m.success = 'No' AND o.gameID = ".$Game->id
				);
		while(list($orderID, $countryID) = $DB->tabl_row($tabl))
		{
			list($unitID, $terrID) = $DB->sql_row(
				"SELECT u.id, u.terrID FROM wD_Units u
					INNER JOIN wD_UnitDestroyIndex i
						ON ( u.countryID = i.countryID AND u.type = i.unitType AND u.terrID = i.terrID )
				WHERE u.gameID = ".$Game->id." AND u.countryID = ".$countryID."
					AND i.mapID=".$Game->Variant->mapID."
				ORDER BY i.destroyIndex ASC LIMIT 1");

			$DB->sql_put("UPDATE wD_Orders SET toTerrID = '".$terrID."' WHERE id = ".$orderID);
			$DB->sql_put("UPDATE wD_Moves
				SET success = 'Yes', toTerrID = ".$Game->Variant->deCoast($terrID)." WHERE gameID=".$GLOBALS['GAMEID']." AND orderID = ".$orderID);

			$DB->sql_put("DELETE FROM wD_Units WHERE id = ".$unitID);
		}

		$DB->sql_put("INSERT INTO wD_Units ( gameID, countryID, type, terrID )
					SELECT o.gameID, o.countryID, IF(o.type = 'Build Army','Army','Fleet') as type, o.toTerrID
					FROM wD_Orders o INNER JOIN wD_Moves m ON ( m.orderID = o.id AND m.gameID=".$GLOBALS['GAMEID']." )
					WHERE o.gameID=".$Game->id." AND o.type LIKE 'Build%' AND m.success = 'Yes'");
		// All players have the correct amount of units
	}
	/**
	 * Apply the adjudicated moves; retreat/disband units as decided (Updated for the 2024 Renegade Games rule changes)
	 */
	public function apply()
	{
		global $Game, $DB;

		// Apply valid destroy orders first, deleting destroyed units:
		$DB->sql_put(
				"DELETE FROM u
				USING wD_Units AS u
				INNER JOIN wD_Orders AS o ON ( ".$Game->Variant->deCoastCompare('o.toTerrID','u.terrID')." AND u.gameID = o.gameID )
				INNER JOIN wD_Moves m ON ( m.orderID = o.id AND m.gameID=".$GLOBALS['GAMEID']." )
				WHERE o.gameID = ".$Game->id." AND o.type = 'Destroy'
					AND m.success='Yes'");

		// Now for destroy orders that weren't successful, we need to find the unit that should be destroyed. The rules
		// changed in 2024.
		$tabl = $DB->sql_tabl(
					"SELECT o.id, o.countryID FROM wD_Orders o
					INNER JOIN wD_Moves m ON ( m.orderID = o.id AND m.gameID=".$GLOBALS['GAMEID']." )
					WHERE o.type = 'Destroy' AND m.success = 'No' AND o.gameID = ".$Game->id
				);
		while(list($orderID, $countryID) = $DB->tabl_row($tabl))
		{
			// For the given failed destroy order / country we need to find the unit which is furthest from an owned supply center,
			// where the distance is defined by number of hops between territories as if armies and fleets can both move anywhere.
			// If two units are equally distant the territory that comes first alphabetically is chosen.

			// Get all the supply center territories for the country:
			$subTabl = $DB->sql_tabl("SELECT t.id FROM wD_Territories t
				INNER JOIN wD_TerrStatus ts ON ( ts.terrID = t.id AND ts.gameID = ".$Game->id." AND ts.countryID = ".$countryID." )
				WHERE t.supply = 'Yes' AND t.mapID=".$Game->Variant->mapID);
			$supplyCenters = array();
			while(list($terrID) = $DB->tabl_row($subTabl)) $supplyCenters[] = $terrID;
			
			// Get all the non-coastal territories for units of the country:
			$subTabl = $DB->sql_tabl("SELECT u.id, t.coastParentID FROM wD_Units u INNER JOIN wD_Territories t ON t.id = u.terrID WHERE u.gameID = ".$Game->id." AND u.countryID = ".$countryID);
			$units = array();
			while(list($unitID, $terrID) = $DB->tabl_row($subTabl)) $units[$terrID] = $unitID;

			// Get the non-coastal territory to territory links:
			$subTabl = $DB->sql_tabl("SELECT fromTerrID, toTerrID FROM wD_Borders WHERE mapID=".$Game->Variant->mapID);
			$links = array();
			while(list($fromTerrID, $toTerrID) = $DB->tabl_row($subTabl))
			{
				if( !key_exists($fromTerrID, $links) ) $links[$fromTerrID] = array();
				$links[$fromTerrID][] = $toTerrID;
			}
			
			$maxDistance = 0;
			$unitDistances = array(); // For each unit that remains this is its territory's distance from the closest supply center
			if( count($supplyCenters) == 0 )
			{
				// If there are no supply centers all units are equivalent
				foreach($units as $terrID => $unitID)
					$unitDistances[$terrID] = 0;
			}
			else
			{
				// A breadth-first search; start with home SCs then go through bordering territories, iterate until all units have distances:

				// Find the distance of each territory from the supply centers, until we find one or more units
				$distances = array(); // The distance in hops from the home supply centers to any territory
				$searched = array(); // Keep track of which territories have been searched so they aren't repeated
				foreach($supplyCenters as $sc) $distances[$sc] = 0; // Initalize with the distances to the home SCs being 0

				while( count($unitDistances) < count($units) ) // Iterate until we have distances for all units
				{
					// For each territory distance look at all bordering territories, either:
					// - We don't have a distance for the bordering territory; add it in as the distance + 1
					// - We already have a distance for the bordering territory, but it is further away than the distance + 1; so reduce it to the new distance + 1 from this territory
					// - The distance is equal or less than the distance + 1, so don't change it
					//
					foreach($distances as $terrID => $distance) // Start with the home supply centers, then branch out
					{
						// If it has been searched already skip it:
						if( !key_exists($terrID, $searched) )
						{
							// If a distance isn't updated from this territory then no adjacent
							// territories can be found to be closer to this terriotry later,
							// so don't check again
							$distanceUpdated = false;
							foreach($links[$terrID] as $toTerrID)
							{
								if( !key_exists($toTerrID, $distances) || $distances[$toTerrID] > $distance + 1 )
								{
									$distances[$toTerrID] = $distance + 1;
									$distanceUpdated = true;
								}
							}
							if( !$distanceUpdated ) $searched[$terrID] = true;
						}
					}
					// After each extra distance check whether we have found any distances to units:
					foreach($units as $terrID => $unitID)
					{
						if( key_exists($terrID, $distances) )
						{
							$unitDistances[$terrID] = $distances[$terrID];
							if( $maxDistance < $distances[$terrID] ) $maxDistance = $distances[$terrID];
						}
					}
				}
			}

			// Get the furthest territories as the candidate territories that can be deleted. This will usually be one territory/unit
			// which is the furthest, but there could be several equally distant units to choose between by alphabetical order of territory name.
			$candidateUnits = array();
			foreach($unitDistances as $terrID => $distance)
			{
				if( $distance == $maxDistance ) $candidateUnits[] = $terrID;
			}

			// Get the first territory from the candidate territories ordered by the territory name:
			list($destroyTerrID) = $DB->sql_row("SELECT id FROM wD_Territories ".
				"WHERE mapID=".$Game->Variant->mapID.
					" AND id IN (".implode(',',$candidateUnits).") ".
				"ORDER BY name LIMIT 1");
			$destroyUnitID = $units[$destroyTerrID];
				
			$DB->sql_put("UPDATE wD_Orders SET toTerrID = '".$destroyTerrID."' WHERE id = ".$orderID);
			$DB->sql_put("UPDATE wD_Moves
				SET success = 'Yes', toTerrID = ".$destroyTerrID." WHERE gameID=".$GLOBALS['GAMEID']." AND orderID = ".$orderID);

			$DB->sql_put("DELETE FROM wD_Units WHERE id = ".$destroyUnitID);

			// Now start again finding the next furthest unit for the next missing order
		}

		$DB->sql_put("INSERT INTO wD_Units ( gameID, countryID, type, terrID )
					SELECT o.gameID, o.countryID, IF(o.type = 'Build Army','Army','Fleet') as type, o.toTerrID
					FROM wD_Orders o INNER JOIN wD_Moves m ON ( m.orderID = o.id AND m.gameID=".$GLOBALS['GAMEID']." )
					WHERE o.gameID=".$Game->id." AND o.type LIKE 'Build%' AND m.success = 'Yes'");
		// All players have the correct amount of units
	}
}

?>
