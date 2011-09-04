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
 * Generates the JSON data used to generate orders for a certain game, used by OrderInterface.
 *
 */
class jsonBoardData
{
	public static function getBoardTurnData($gameID)
	{
		return "function loadBoardTurnData() {\n".self::getUnits($gameID)."\n\n".self::getTerrStatus($gameID)."\n}\n";
	}

	protected static function getUnits($gameID)
	{
		global $DB;

		$units = array();
		$tabl=$DB->sql_tabl("SELECT id, terrID, countryID, type FROM wD_Units WHERE gameID = ".$gameID);
		while($row=$DB->tabl_hash($tabl))
		{
			$units[$row['id']] = $row;
		}

		return 'Units = $H('.json_encode($units).');';
	}
	protected static function getTerrStatus($gameID)
	{
		global $DB;

		$terrstatus=array();
		$tabl=$DB->sql_tabl("SELECT terrID as id, standoff, occupiedFromTerrID, occupyingUnitID as unitID, countryID as ownerCountryID
			FROM wD_TerrStatus WHERE gameID = ".$gameID);
		while($row=$DB->tabl_hash($tabl)) {
			$row['standoff'] = ($row['standoff']=='Yes');
			$terrstatus[] = $row;
		}

		return 'TerrStatus = '.json_encode($terrstatus).';';
	}
}

?>