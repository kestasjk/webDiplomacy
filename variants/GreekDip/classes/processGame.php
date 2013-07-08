<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the GreekDip variant for webDiplomacy

	The GreekDip variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The GreekDip variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

class BiddingStart_processGame extends processGame
{
	function process()
	{
		parent::process();		
		if ( $this->phase == 'Diplomacy' && $this->turn==1 )
		{
			parent::process();		
			parent::process();		
		}
	}
	
	protected function updateOwners()
	{
		global $DB;
		
		if ($this->turn != 0) return parent::updateOwners();

		// set new owner
		$DB->sql_put(
			"UPDATE wD_TerrStatus ts
			INNER JOIN wD_Moves m ON ( m.gameID=ts.gameID )
			SET ts.countryID = m.countryID
			WHERE ts.gameID = ".$this->id."
				AND m.moveType='Move' AND m.success='Yes' AND m.toTerrID=ts.terrID"
		);
				
		// delete all units
		$DB->sql_put( "DELETE FROM wD_Units WHERE gameID = ".$this->id );	
	}
	
}

class GreekDipVariant_processGame extends BiddingStart_processGame {}
