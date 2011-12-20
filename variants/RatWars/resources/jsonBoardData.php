<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class jsonBoardData
{
	public static function getBoardTurnData($gameID,$noFog)
	{
		return "function loadBoardTurnData() {\n".self::getUnits($gameID,$noFog)."\n\n".self::getTerrStatus($gameID,$noFog)."\n}\n";
	}

	protected static function getUnits($gameID,$noFog)
	{
		global $DB, $Variant;

		$units = array();
		$tabl=$DB->sql_tabl("SELECT id, terrID, countryID, type FROM wD_Units WHERE gameID = ".$gameID);
		while($row=$DB->tabl_hash($tabl))
		{
			if (in_array($Variant->decoast($row['terrID']),$noFog))
				$units[$row['id']] = $row;
		}
		$tabl=$DB->sql_tabl("SELECT id, type FROM wD_Territories WHERE mapID = ".$Variant->mapID);
		while($row=$DB->tabl_hash($tabl))
		{
			if (!(in_array($Variant->decoast($row['id']),$noFog))) {
				if (!(in_array($row['id'],$units))) {
					if ($row['type'] == 'Sea') 
						$unit='Fleet';
					else
						$unit = 'Army';
					$units[$row['id']] = array('id' => $row['id'], 'terrID' => $row['id'], 'countryID'=> "0", 'type' => $unit);
				}
			}
		}
	
		return 'Units = $H('.json_encode($units).');';
	}
	
	protected static function getTerrStatus($gameID,$noFog)
	{
		global $DB,$Variant;

		$terrstatus=array();
		$tabl=$DB->sql_tabl("SELECT terrID as id, standoff, occupiedFromTerrID, occupyingUnitID as unitID, countryID as ownerCountryID
			FROM wD_TerrStatus WHERE gameID = ".$gameID);
		while($row=$DB->tabl_hash($tabl)) {
			if (in_array($Variant->decoast($row['id']),$noFog)) {
				$row['standoff'] = ($row['standoff']=='Yes');
				$terrstatus[] = $row;
			}
		}
		$tabl=$DB->sql_tabl("SELECT id FROM wD_Territories WHERE mapID = ".$Variant->mapID);
		while($row=$DB->tabl_hash($tabl))
		{
			if (!(in_array($Variant->decoast($row['id']),$noFog))) {
				if (!(in_array($row['id'],$terrstatus))) {
					$terrstatus[] = array('id' => $row['id'], 'standoff' => false, 'occupiedFromTerrID'=> "null", 'unitID' => $row['id'], 'countryID'=> "0");
				}
			}
		}

		return 'TerrStatus = '.json_encode($terrstatus).';';
	}
}

?>