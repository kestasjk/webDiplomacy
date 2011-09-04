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
 * Creates an array of convoy chains, starting with the starting unit and ending with the finishing coasts,
 * with multiple routes if available etc. Will also remove broken convoys and set their units to having no path
 *
 * @package GameMaster
 * @subpackage Adjudicator
 */
class adjConvoyChains
{
	var $convoys;

	var $borderNodes = array();
	var $used = false;

	var $id;
	var $dislodged;

	public function __construct($id, $dislodged)
	{
		$this->id = $id;
		$this->dislodged = $dislodged;
	}

	public function addConvoys(&$convoys)
	{
		$this->convoys = &$convoys;
	}

	public function getChain()
	{
		if ( $this->used ) return false;
		else $this->used = true;

		$paths = array();
		foreach($this->borderNodes as $nodeID)
		{
			if ( $nodeID == 'End' )
			{
				$paths[] = array();
				break;
			}

			$chain = $this->convoys[$nodeID]->getChain();

			if ( is_array($chain) )
			{
				$paths[] = $chain;
			}
		}

		if ( count($paths) == 0 ) return false;
		if ( count($paths) == 1 ) $paths = $paths[0];

		if ( $this->dislodged != 'No' )
		{
			$paths[] = $this->id;
		}

		return $paths;
	}

	static public function getConvoys()
	{
		global $DB, $Game;

		/*
		 * There are 3 steps to creating the convoy chains:
		 * - Load all the armies which want to be convoyed
		 *   - If the army doesn't have any convoys set it to hold or move via land if it can
		 *   - $startPositions = array(
		 * 				array('id'=>$convoyedID,
		 * 					'terrID'=>$convoyedTerrID,
		 * 					'toTerrID'=>$convoyedToTerrID,
		 * 					'convoyCount'=>$convoyingUnitNo)
		 * 			);
		 *
		 * - For each army that wants to be convoyed load all of its convoys
		 *   - An array, indexed by move ID, linking to the all adjacent convoys
		 *   - If the convoy is at the start or end of the chain then linkNode is 'Start' or 'End,
		 * 		instead of being the next $moveID
		 *   - If the convoy is the start of the convoy it is placed in the $startConvoys array, which
		 *     is indexed by the move ID of the army which is moving out
		 *   - The convoy's dislodged status is saved, so that non-dislodged convoys do not need to
		 *     be processed
		 *
		 * - For each convoy start the convoy links are recursively traversed to find the end node.
		 *   - An array is made with a structure which contains convoy IDs, and arrays. Each array
		 *     is a sub-convoy-chain, and each convoy ID represents a convoy which may be dislodged,
		 *     but which must not be dislodged for the current chain to be traversed. So to go down
		 *     a sub-convoy-chain you must first make sure all the convoy IDs in that sub-convoy-chain
		 *     are not dislodged
		 *   - When a chain ends it returns an empty array, so a convoy chain may look like this:
		 * 		array(123, 234, 345, array(123, array()), array(1029, 1293, array())
		 *   - If a convoy chain ends without finding the end-node it returns false, in which case the
		 *     current chain is discarded, and alternate paths may be found.
		 *   - This gives two scenarios: The one above, where there is a convoy chain with some units
		 *     which may not be dislodged. Or a failed chain which has no complete path to the end.
		 *     Or a successful chain which is complete and contains no dislodged units, and can be
		 *     used immidiately.
		 *   - If the chain is unsuccessful it will not be in the final $chains array, the army will
		 *     be set to hold or move via land, and all the convoys will fail.
		 *   - If the chain is possible it will be in the $chains array, and it will be given later to
		 *     an adjConvoyMove object to process
		 *   - If the chain has succeeded an empty array will be in the $chains array, which will also
		 *     be given to an adjConvoyMove later, and the adjConvoyMove will recognize that it is
		 *     successful without processing it.
		 *   - While traversing the convoy chains looking for paths all convoys which are used have a
		 *     ->used flag set. If they fail, or are not used, they are set to hold and un-needed.
		 *
		 * - Once the valid convoy chains are collected everything else is set to not move or not move
		 *   via convoy.
		 *
		 * - The final product is $convoyChains, which contains all the valid chains of convoys, and is
		 *   indexed by the move ID of the starting army. All
		 *   successful chains are just empty arrays, unsuccessful chains aren't included.
		 *
		 * - So valid starting armies -> valid starting convoys -> valid convoy chains
		 *   -> filter out non-valid convoys
		 *
		 * Any convoying units which are not convoying for any reason are set to Hold. Any moving
		 * units which were going viaConvoy, but could not, are set to viaConvoy='No', and if they are
		 * not adjacent to the territory they were moving to they are set to path='No'
		 *
		 */
		$tabl = $DB->sql_tabl("SELECT me.id, me.terrID, me.toTerrID, COUNT(convoy.id) as convoyCount
				FROM wD_Moves me LEFT JOIN wD_Moves convoy ON
					( me.gameID = convoy.gameID AND me.terrID = convoy.fromTerrID
					AND me.toTerrID = convoy.toTerrID AND convoy.moveType = 'Convoy' )
				WHERE me.moveType = 'Move' AND me.viaConvoy = 'Yes' AND me.gameID = ".$GLOBALS['GAMEID']."
				GROUP BY me.id");
		$startPositions = array();
		while( $hash = $DB->tabl_hash($tabl) )
		{
			if ( $hash['convoyCount']!=0 )
			{
				$startPositions[] = $hash;
			}
		}


		$convoys = array();
		$convoyStarts = array();

		foreach( $startPositions as $start )
		{
			$tabl = $DB->sql_tabl(
				"SELECT c.id, c.dislodged, IF(link.id IS NULL,'End',
						IF(link.moveType='Move','Start',link.id))
				FROM wD_Moves c
				INNER JOIN wD_Borders b ON ( c.terrID = b.fromTerrID AND b.mapID=".$Game->Variant->mapID." )
				LEFT JOIN wD_Moves link ON
					( c.gameID = link.gameID AND link.terrID = b.toTerrID AND
					(
						( link.moveType = 'Convoy' AND link.fromTerrID = c.fromTerrID )
						OR
						( link.moveType = 'Move' AND link.terrID = c.fromTerrID )
					)
					)
				WHERE ( link.id IS NOT NULL OR b.toTerrID = c.toTerrID )
					AND c.moveType = 'Convoy'
					AND c.fromTerrID = ".$start['terrID']."
					AND c.toTerrID = ".$start['toTerrID']."
					AND c.gameID = ".$GLOBALS['GAMEID']."
				ORDER BY c.fromTerrID");


			while ( list($nodeID, $dislodged, $linkNode) = $DB->tabl_row($tabl) )
			{
				if( ! isset($convoys[$nodeID]) )
				{
					$node = $Game->Variant->adjConvoyChains($nodeID, $dislodged);
					$node->addConvoys($convoys);
				}
				else
					$node = $convoys[$nodeID];

				if ( $linkNode == 'Start' )
				{
					if ( ! isset($convoyStarts[$start['id']]) )
						$convoyStarts[$start['id']] = array();

					// There may be multiple ways for the convoy to start off to
					$convoyStarts[$start['id']][] = $node;
				}
				else
				{
					$node->borderNodes[] = $linkNode;
				}

				$convoys[$nodeID] = $node;
			}
		}

		// $convoyStarts contains nodes which link to $convoys, so $convoys doesn't need to be passed
		$convoyChains = self::getConvoyChains($convoyStarts);

		$unused = array();
		foreach($convoys as $convoy)
		{
			if ( !$convoy->used ) $unused[] = $convoy->id;
		}

		if ( count($unused) )
		{
			$DB->sql_put("UPDATE wD_Moves SET success = 'No' WHERE id IN (".implode(',',$unused).")
					AND gameID = ".$GLOBALS['GAMEID']);
		}

		$validArmies = array_keys($convoyChains);
		$DB->sql_put(
			"UPDATE wD_Moves me LEFT JOIN wD_Borders b
			ON ( b.mapID = ".$Game->Variant->mapID." AND b.fromTerrID = me.terrID AND b.armysPass = 'Yes' AND b.toTerrID = me.toTerrID )
			SET /* If we aren't adjacent there is no path */
				me.path = IF(b.toTerrID IS NULL, 'No', 'Yes'),
				/* If we aren't adjacent we won't be successful */
				me.success = IF(b.toTerrID IS NULL, 'No', me.success),
				/* If we *are* adjacent then we can consider this unit as a non-convoyed unit */
				me.viaConvoy = IF(b.toTerrID IS NOT NULL, 'No', me.viaConvoy)
			WHERE
				me.viaConvoy = 'Yes' AND me.moveType = 'Move' AND me.gameID = ".$GLOBALS['GAMEID']."
				".( count($validArmies) ? "AND NOT me.id IN (".implode(',', $validArmies).")" : '' ) );

		$DB->sql_put(
			"UPDATE wD_Moves c
			INNER JOIN wD_Moves m ON ( m.gameID = c.gameID AND c.fromTerrID = m.terrID )
			SET c.success='No'
			WHERE c.moveType='Convoy'
				AND c.gameID = ".$GLOBALS['GAMEID']."
				".( count($validArmies) ? "AND NOT m.id IN (".implode(',', $validArmies).")" : '' ) );

		return $convoyChains;
	}

	static private function getConvoyChains($convoyStarts)
	{
		$convoyChains = array();
		foreach($convoyStarts as $moveID=>$convoyStart)
		{
			$convoyChains[$moveID] = array();
			foreach($convoyStart as $startNode)
			{
				$chain = $startNode->getChain();

				if ( $chain === false )
				{
					continue;
				}
				elseif ( count($chain) == 0 )
				{
					$convoyChains[$moveID] = array(array()); // A successful chain
					break;
				}
				else
				{
					$convoyChains[$moveID][] = $chain;
				}
			}

			if ( count($convoyChains[$moveID]) == 0 )
			{
				// This convoy chain hasn't gone anywhere
				unset($convoyChains[$moveID]);
			}
		}

		return $convoyChains;
	}
}

?>