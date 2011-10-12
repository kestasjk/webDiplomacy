<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the Duo variant for webDiplomacy

	The Duo variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Duo variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class NeutralUnits_processGame extends processGame
{
	function process()
	{
		global $DB;
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
		// If only the "neutral player has to do retreats process again.
		if ($this->phase == 'Retreats')
		{	
			list($count) = $DB->sql_row("SELECT COUNT(*)
				FROM wD_Members 
				WHERE orderStatus != 'None' AND gameID = ".$this->id);
			if ($count == 0)
				parent::process();
		}	
	}
}

class DuoVariant_processGame extends NeutralUnits_processGame {}

?>