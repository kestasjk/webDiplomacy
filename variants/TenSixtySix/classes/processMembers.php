<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the 1066 variant for webDiplomacy

	The 1066 variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The 1066 variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.		
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class NeutralMember
{
	public $supplyCenterNo;
	public $unitNo;
}

class NeutralUnits_processMembers extends processMembers
{
	// bevore the processing add a minimal-member-object for the "neutral  player"
	function countUnitsSCs()
	{
		$this->ByCountryID[count($this->Game->Variant->countries)+1] = new NeutralMember();
		parent::countUnitsSCs();
		unset($this->ByCountryID[count($this->Game->Variant->countries)+1]);
	}
}

class OccupieTargetSCs_processMembers extends NeutralUnits_processMembers
{
	// Winner need to occupie his own capital and one more Winchester/England (id=11), Oslo/Norway (id=45) and Caen/Normandy (id=54)
	function checkForWinner()
	{
		global $DB, $Game;

		$win=parent::checkForWinner();
		if ($win != false)
		{
			$tabl=$DB->sql_tabl("SELECT terrID, countryID FROM wD_TerrStatus WHERE terrID IN (11,45,54) AND GameID=".$Game->id);
			while(list($terrID, $countryID) = $DB->tabl_row($tabl))
				$status[$terrID]=$countryID;
			if ($win->countryID == 1 AND $status[11] == 1 AND ($status[45] == 1 OR $status[54] == 1)) return $win;
			if ($win->countryID == 2 AND $status[54] == 2 AND ($status[11] == 2 OR $status[45] == 2)) return $win;
			if ($win->countryID == 3 AND $status[45] == 3 AND ($status[11] == 3 OR $status[54] == 3)) return $win;			
		}
		return false;
	}	
}

class TenSixtySixVariant_processMembers extends OccupieTargetSCs_processMembers {}

?>
