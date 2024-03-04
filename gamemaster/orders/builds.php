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
	 * Apply the adjudicated moves; retreat/disband units as decided
	 */
	public function apply()
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
			$territoryDistances = array();
			if( count($supplyCenters) == 0 )
			{
				// If there are no supply centers all units are equivalent
				foreach($units as $terrID => $unitID)
					$territoryDistances[$terrID] = 0;
			}
			else
			{
				// Find the distance of each territory from the supply centers, until we find one or more units
				$distances = array();
				foreach($supplyCenters as $sc) $distances[$sc] = 0;
				while(count($territoryDistances) < count($units) )
				{
					foreach($distances as $terrID => $distance)
					{
						foreach($links[$terrID] as $toTerrID)
							if( !key_exists($toTerrID, $distances) || $distances[$toTerrID] > $distance + 1 )
								$distances[$toTerrID] = $distance + 1;
					}
					// After each extra distance check whether we have found any distances to units:
					foreach($units as $terrID => $unitID)
					{
						if( key_exists($terrID, $distances) )
						{
							$territoryDistances[$terrID] = $distances[$terrID];
							if( $maxDistance < $distances[$terrID] ) $maxDistance = $distances[$terrID];
						}
					}
				}
			}

			// Get the furthest territories as the candidate territories that can be deleted:
			$candidateTerritories = array();
			foreach($territoryDistances as $terrID => $distance)
			{
				if( $distance == $maxDistance ) $candidateTerritories[] = $terrID;
			}

			// Get the first territory from the candidate territories ordered by the territory name:
			list($destroyTerrID) = $DB->sql_row("SELECT id FROM wD_Territories ".
				"WHERE mapID=".$Game->Variant->mapID.
					" AND id IN (".implode(',',$candidateTerritories).") ".
				"ORDER BY name LIMIT 1");
			$destroyUnitID = $units[$destroyTerrID];
				
			$DB->sql_put("UPDATE wD_Orders SET toTerrID = '".$destroyTerrID."' WHERE id = ".$orderID);
			$DB->sql_put("UPDATE wD_Moves
				SET success = 'Yes', toTerrID = ".$destroyTerrID." WHERE gameID=".$GLOBALS['GAMEID']." AND orderID = ".$orderID);

			$DB->sql_put("DELETE FROM wD_Units WHERE id = ".$destroyUnitID);
		}

		$DB->sql_put("INSERT INTO wD_Units ( gameID, countryID, type, terrID )
					SELECT o.gameID, o.countryID, IF(o.type = 'Build Army','Army','Fleet') as type, o.toTerrID
					FROM wD_Orders o INNER JOIN wD_Moves m ON ( m.orderID = o.id AND m.gameID=".$GLOBALS['GAMEID']." )
					WHERE o.gameID=".$Game->id." AND o.type LIKE 'Build%' AND m.success = 'Yes'");
		// All players have the correct amount of units
	}
}

?>
