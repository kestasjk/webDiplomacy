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
 * Loads this players options for their Retreats phase orders. Lets users choose orders, and then
 * is used to check the selection for validity.
 *
 * @package Board
 * @subpackage Orders
 */
class userOrderRetreats extends userOrder
{
	public function __construct($orderID, $gameID, $countryID)
	{
		parent::__construct($orderID, $gameID, $countryID);
	}

	protected function updaterequirements()
	{
		if( $this->type == 'Retreat' )
			$this->requirements=array('type','toTerrID');
		else
			$this->requirements=array('type');
	}

	protected function typeCheck()
	{
		switch($this->type) {
			case 'Retreat':
			case 'Disband':
				return true;
			default:
				return false;
		}
	}

	protected function toTerrIDCheck()
	{
		$this->toTerrID=(int)$this->toTerrID;

		return $this->sqlCheck(
			"SELECT
				linkBorder.toTerrID
			FROM wD_Units retreatingUnit
			INNER JOIN wD_TerrStatus retreatingFrom
				ON ( retreatingFrom.retreatingUnitID = retreatingUnit.id )
			INNER JOIN wD_CoastalBorders linkBorder
				ON (
					linkBorder.mapID = ".MAPID." AND
					linkBorder.fromTerrID = retreatingUnit.terrID
					AND (
						retreatingFrom.occupiedFromTerrID IS NULL
						OR NOT ".libVariant::$Variant->deCoastCompare('retreatingFrom.occupiedFromTerrID','linkBorder.toTerrID')."
					)
				)
			LEFT JOIN wD_TerrStatus retreatingTo
				ON (
					".libVariant::$Variant->deCoastCompare('retreatingTo.terrID','linkBorder.toTerrID')."
					AND retreatingFrom.gameID = retreatingTo.gameID
				)
			WHERE retreatingUnit.id = ".$this->Unit->id."
				AND linkBorder.".strtolower($this->Unit->type)."sPass = 'Yes'
				AND retreatingTo.occupyingUnitID IS NULL
				AND ( retreatingTo.standoff IS NULL OR retreatingTo.standoff = 'No' )
				AND linkBorder.toTerrID = ".$this->toTerrID."
			LIMIT 1"
		);
	}
}

?>