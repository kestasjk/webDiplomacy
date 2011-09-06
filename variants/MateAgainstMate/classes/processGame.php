<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the MateAgainstMate variant for webDiplomacy

	The MateAgainstMate variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The MateAgainstMate variant for webDiplomacy is distributed in the hope that it
	will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.		
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class SeaSc_processGame extends processGame
{
	protected function updateOwners()
	{
		parent::updateOwners();

		global $DB;

		// Update the status of the "fake" territories only during a "spring" move
		if ( 0 != ($this->turn % 2) )
		{
			$newOwners=array();
			
			/**
			*           Great Barrier Reef = 41 / fakeSC = 114
			*   terrID: Bass Strait        = 65 / fakeSC = 115
			*           North West Shelf   = 69 / fakeSC = 116
			**/
			$tabl = $DB->sql_tabl("SELECT
				terrID,	countryID
				FROM wD_Units
				WHERE gameID=".$this->id." AND terrID IN (41,65,69)");
				
			while(list($terrID, $countryID) = $DB->tabl_row($tabl))
			{
				if (($terrID == 41) && ($countryID != 0)) $newOwners[] = "(".$this->id.",114,".$countryID.")";
				if (($terrID == 65) && ($countryID != 0)) $newOwners[] = "(".$this->id.",115,".$countryID.")";
				if (($terrID == 69) && ($countryID != 0)) $newOwners[] = "(".$this->id.",116,".$countryID.")";
			}		
			
			if ( count($newOwners) )
			{
				$DB->sql_put("INSERT INTO wD_TerrStatus
					(gameID, terrID, countryID)
					VALUES ".implode(', ', $newOwners)." 
					ON DUPLICATE KEY UPDATE countryID=VALUES(countryID)");
			}
		}
	}
}

class NeutralUnits_processGame extends SeaSc_processGame
{
	function process()
	{
		global $DB;
		parent::process();
		
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

class MateAgainstMateVariant_processGame extends NeutralUnits_processGame {}
