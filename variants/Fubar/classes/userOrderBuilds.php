<?php


defined('IN_CODE') or die('This script can not be run by itself.');

class FubarVariant_userOrderBuilds extends userOrderBuilds
{

	protected function toTerrIDCheck()
	{
		global $DB;

		// Don't duplicate destroy validation code
		if( $this->type != 'Build Army' && $this->type != 'Build Fleet' )
			return parent::toTerrIDCheck();

		if( $this->type == 'Build Army' )
		{
			/*
			 * Creating an army at which territory
			 *
			 * Unoccupied supply centers owned by our country, which the specified unit type
			 * can be built in. If a parent coast is found return Child entries.
			 */
			return $this->sqlCheck("SELECT t.id
				FROM wD_TerrStatus ts
				INNER JOIN wD_Territories t
					ON ( t.id = ts.terrID )
				WHERE ts.gameID = ".$this->gameID."
					AND t.mapID=".MAPID."
					AND ts.countryID = ".$this->countryID."
					AND ts.occupyingUnitID IS NULL
					AND t.id=".$this->toTerrID."
					AND t.supply = 'Yes' AND NOT t.type='Sea'
					AND NOT t.coast = 'Child'");
		}
		elseif( $this->type == 'Build Fleet' )
		{
			return $this->sqlCheck("SELECT IF(t.coast='Parent', coast.id, t.id) as terrID
				FROM wD_TerrStatus ts
				INNER JOIN wD_Territories t ON ( t.id = ts.terrID )
				LEFT JOIN wD_Territories coast ON ( coast.mapID=".MAPID." AND coast.coastParentID = t.id AND NOT t.id = coast.id )
				WHERE ts.gameID = ".$this->gameID."
					AND t.mapID=".MAPID."
					AND ts.countryID = ".$this->countryID."
					AND ts.occupyingUnitID IS NULL
					AND t.supply = 'Yes'
					AND t.type = 'Coast'
					AND (
						(t.coast='Parent' AND coast.id=".$this->toTerrID.")
						OR t.id=".$this->toTerrID."
					)
					AND (
						t.coast='No' OR ( t.coast='Parent' AND NOT coast.id IS NULL )
					)");
		}
	}
}

?>
