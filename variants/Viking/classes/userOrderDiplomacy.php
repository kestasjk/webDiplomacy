<?php
/*
	Copyright (C) 2011 kaner406 / Oliver Auth

	This file is part of the Viking variant for webDiplomacy

	The Viking variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Viking variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Coast_Convoy_userOrderDiplomacy extends userOrderDiplomacy
{
	protected function typeCheck()
	{
		if ($this->Unit->type == 'Fleet' and in_array($this->Unit->Territory->id, $GLOBALS['Variants'][VARIANTID]->convoyCoasts))
			return true;
		return parent::typeCheck();
	}
	
	protected function checkConvoyPath($startCoastTerrID, $endCoastTerrID, $mustContainTerrID=false, $mustNotContainTerrID=false) {

		global $DB;

		if( count($this->convoyPath)<2 ) // First, plus one fleet, then $endCoastTerrID makes the minimum 3
			return false; // Not enough units in the convoyPath to be valid

		if( $this->convoyPath[0]!=$startCoastTerrID )
			return false; // Doesn't start in the right place

		if( $mustContainTerrID && !in_array($mustContainTerrID, $this->convoyPath) )
			return false; // Contains a terrID that it mustn't (a fleet supporting a move, typically)

		if( $mustNotContainTerrID && in_array($mustNotContainTerrID, $this->convoyPath) )
			return false; // Doesn't contain a terrID that it must (a fleet convoying a unit)

		return true;
		
		static $validConvoyPaths;
		if( !isset($validConvoyPaths) )
			$validConvoyPaths=array();
		elseif( in_array($startCoastTerrID.'-'.$endCoastTerrID, $validConvoyPaths) )
			return true;

		/*
		 * The first convoyPath entry is the starting coast with the army.
		 * [ $this->convoyPath[0], $this->convoyPath[1], $this->convoyPath[2], ..., $endFleetTerrID, $endCoastTerrID ]
		 *
		 * The start and end IDs will always be available to be checked e.g. as the terrID/toTerrID/fromTerrID,
		 * all that needs to be checked is that the given convoyPath represents an unbroken chain of fleets at sea from
		 * the start to the end
		 *
		 * With this checked other checks (e.g. whether the path contains a certain fleet or not) can be done independantly.
		 */
		$borderLinks=array();
		for($i=1; $i<count($this->convoyPath); $i++)
		{
			$fromTerrID=$this->convoyPath[$i-1];
			$toTerrID=$this->convoyPath[$i];
			$borderLinks[] = "b.fromTerrID=".$fromTerrID." AND b.toTerrID=".$toTerrID;
		}
		$endFleetTerrID=$toTerrID;

		$borderLinks='('.implode(') OR (',$borderLinks).')';

		/*
		 * - The first select checks that an army is in the starting position.
		 * - The second union select checks all the intermediate fleets in the chain
		 * connecting the start coast to end coast.
		 * - The third union select checks that the final territory is a coast.
		 *
		 * Altogether these check the whole convoyPath, if the right number of rows
		 * are returned the given convoyPath must be a valid convoy-chain linking the
		 * start and end coasts.
		 */
		$tabl=$DB->sql_tabl(
			"SELECT terrID FROM wD_Units
			WHERE gameID=".$this->gameID." AND type='Army' AND terrID=".$startCoastTerrID."

			UNION SELECT b.toTerrID
			FROM wD_Borders b
			INNER JOIN wD_Units fleet
				ON ( fleet.gameID=".$this->gameID." AND fleet.terrID = b.toTerrID AND fleet.type='Fleet' )
			WHERE
				b.mapID=".MAPID." AND ".$borderLinks."
				AND b.fleetsPass='Yes'

			UNION SELECT b.toTerrID
			FROM wD_Borders b INNER JOIN wD_Territories t ON (t.id=b.toTerrID)
			WHERE
				b.mapID=".MAPID." AND t.mapID=".MAPID."
				AND t.type='Coast'
				AND b.fromTerrID=".$endFleetTerrID." AND b.toTerrID=".$endCoastTerrID."
				AND b.fleetsPass='Yes'");

		// Check the number of returned links, if it is the correct length the chain must be valid.
		$i=0;
		while($row=$DB->tabl_row($tabl)) $i++;

		if( $i==(count($this->convoyPath)+1) ) // convoyPath territories plus the end coast, which isn't included
		{
			$validConvoyPaths[]=$startCoastTerrID.'-'.$endCoastTerrID;
			return true; // Every convoyPath element was returned as expected
		}
		else
			return false; // Something is missing
	}

}

class VikingVariant_userOrderDiplomacy extends Coast_Convoy_userOrderDiplomacy {}
