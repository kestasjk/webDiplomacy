<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class DuoVariant_processGame extends processGame {

	public function __construct($id)
	{
		parent::__construct($id);
	}

	// 2 players are enough in Pregame to start the game:
	public function needsProcess()
	{
		if ($this->phase=='Pre-game' && count($this->Members->ByID)==2 && $this->phaseMinutes>30 )
			return true;
		return parent::needsProcess();
	}
	
	function process()
	{
		global $DB;

		// Set neutral player to "Playing" bevore processing
		$DB->sql_put("UPDATE wD_Members SET status='Playing' WHERE gameID=".$this->id." AND countryID=3");

		parent::process();
		
		// custom movement code here:

/*	
		$orders=$DB->sql_tabl("SELECT o.unitID, u.terrID, o.type 
						FROM wD_Orders o INNER JOIN wD_Units u ON (u.id = o.unitID)
						WHERE o.countryID=3 AND o.gameID=".$this->id);
		$sql_terr=$DB->sql_tabl("SELECT u.terrID 
						FROM wD_Orders o INNER JOIN wD_Units u ON (u.id = o.unitID)
						WHERE o.countryID=3 AND o.gameID=".$this->id);
		$all_black=array();
		while ( list($id) = $DB->tabl_row($sql_terr)) {	array_push($all_black,$id);	}
		$sql_terr=$DB->sql_tabl("SELECT u.terrID 
						FROM wD_Orders o INNER JOIN wD_Units u ON (u.id = o.unitID)
						WHERE o.gameID=".$this->id);
		$all_id=array();
		while ( list($id) = $DB->tabl_row($sql_terr)) {	array_push($all_id,$id); }
		
		while ( $row = $DB->tabl_hash($orders))	{
			$order="";
			// Norterend Support-Hold auf Westberg
			if (($row['type'] == 'Hold') && ($row['terrID'] == 1) && (in_array('2',$all_black))) { 
				$order="type='Support Hold', toTerrID=2";
			// Westberg Support-Hold auf Norterend
			} else if (($row['type'] == 'Hold') && ($row['terrID'] ==  2) && (in_array('1',$all_black))) { 
				$order="type='Support Hold', toTerrID=1";				
			// Westberg Support-Hold auf Ostberg
			} else if (($row['type'] == 'Hold') && ($row['terrID'] ==  2) && (in_array('3',$all_black))) { 
				$order="type='Support Hold', toTerrID=3";
			// Ostberg Support-Hold auf Sund
			} else if (($row['type'] == 'Hold') && ($row['terrID'] ==  3) && (in_array('4',$all_black))) { 
				$order="type='Support Hold', toTerrID=4";
			// Ostberg Support-Hold auf Westberg
			} else if (($row['type'] == 'Hold') && ($row['terrID'] ==  3) && (in_array('2',$all_black))) { 
				$order="type='Support Hold', toTerrID=2";
			// Sund Support-Hold auf Ostberg
			} else if (($row['type'] == 'Hold') && ($row['terrID'] ==  4) && (in_array('3',$all_black))) { 
				$order="type='Support Hold', toTerrID=3";
			// Norterend Retreat to Westberg if possible
			} else if (($row['type'] == 'Retreat') && ($row['terrID'] == 1) && !(in_array('2',$all_id))) { 
				$order="toTerrID=2";
			} else if (($row['type'] == 'Retreat') && ($row['terrID'] == 2) && !(in_array('2',$all_id))) { 
				$order="toTerrID=3";
			} else if (($row['type'] == 'Retreat') && ($row['terrID'] == 2) && !(in_array('1',$all_id))) { 
				$order="toTerrID=1";
			} else if (($row['type'] == 'Retreat') && ($row['terrID'] == 3) && !(in_array('2',$all_id))) { 
				$order="toTerrID=2";
			} else if (($row['type'] == 'Retreat') && ($row['terrID'] == 3) && !(in_array('4',$all_id))) { 
				$order="toTerrID=4";
			} else if (($row['type'] == 'Retreat') && ($row['terrID'] == 4) && !(in_array('3',$all_id))) { 
				$order="toTerrID=3";
			} else if (($row['type'] == 'Retreat') && ($row['terrID'] ==  5)) { // Helom
				$order="type='Support Hold', toTerrID=22";
			} else if (($row['type'] == 'Hold') && ($row['terrID'] ==  6)) { // Nordostmeer
				$order="type='Support Hold', toTerrID=22";
			} else if (($row['type'] == 'Retreat') && ($row['terrID'] ==  7)) { // Conno
				$order="type='Support Hold', toTerrID=22";
			} else if (($row['type'] == 'Retreat') && ($row['terrID'] ==  8)) { // Abaun
				$order="type='Support Hold', toTerrID=22";
			} else if (($row['type'] == 'Hold') && ($row['terrID'] ==  9)) { // Suedwestmeer
				$order="type='Support Hold', toTerrID=22";
			} else if (($row['type'] == 'Retreat') && ($row['terrID'] == 10)) { // Corws
				$order="type='Support Hold', toTerrID=22";
			}
			
			if ($order != "") {
				$DB->sql_put("UPDATE wD_Orders SET ".$order." WHERE gameID=".$this->id." AND countryID=3 AND unitID=".$row['unitID']);
			}
		}
*/
		// Set "neutral player" as defeated, so we don't need to wait for his orders and votes
		$DB->sql_put("UPDATE wD_Members SET status='Defeated', missedPhases=0 WHERE gameID=".$this->id." AND countryID=3");

		
	}
		
}

?>