<?php
/*
	Copyright (C) 2012 Kaner406 / Oliver Auth

	This file is part of the KnownWorld_901 variant for webDiplomacy

	The KnownWorld_901 variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The KnownWorld_901 variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class NeutralUnits_processGame extends processGame
{

	// Check if we might need a Build-phase for the "neutral units"
	protected function changePhase()
	{
		global $DB;
		
		$newturn = parent::changePhase();
		// If we switch from a retreat or a diplomacy-phase to a new turn (without a build) check if neutral units need a build.
		if ( $newturn == true)
		{
			$sql = "SELECT COUNT(*) FROM wD_Terrstatus 
						WHERE occupyingUnitID IS NULL
							AND gameID = ".$this->id."
							AND countryID = ".(count($this->Variant->countries) + 1);
							
			list($emptyNeutrals) = $DB->sql_row($sql);
			
			// Revert the already increased turn and set phase to Builds.
			if ($emptyNeutrals > 0)
			{
				$this->turn--;
				$DB->sql_put("UPDATE wD_Games SET turn = turn - 1 WHERE id=".$this->id);
				$this->setPhase('Builds');
				$newturn=false;
			}	
		}
		return $newturn;
	}
	
	function process()
	{
		global $DB;
		parent::process();
		
		// Add Build-commands for the "neutral units"
		if ($this->phase == 'Builds')
		{
			$sql = "SELECT terrID FROM wD_Terrstatus 
						WHERE occupyingUnitID IS NULL
							AND gameID = ".$this->id."
							AND countryID = ".(count($this->Variant->countries) + 1);
			$tabl = $DB->sql_tabl($sql);
			
			while(list($terrID) = $DB->tabl_row($tabl))
				$DB->sql_put("INSERT INTO wD_Orders
								SET gameID = ".$this->id.",
									countryID = ".(count($this->Variant->countries) + 1)." ,
									toTerrID = ".$terrID." ,
									type = 'Build Army'");
		}
		
		// If only the "neutral player has to do retreats or builds process again.
		if ($this->phase == 'Retreats' || $this->phase == 'Builds')
		{	
			list($count) = $DB->sql_row("SELECT COUNT(*)
				FROM wD_Members 
				WHERE orderStatus != 'None' AND gameID = ".$this->id);
			if ($count == 0)
				self::process();
		}	
	}
}

class KnownWorld_901Variant_processGame extends NeutralUnits_processGame {}
