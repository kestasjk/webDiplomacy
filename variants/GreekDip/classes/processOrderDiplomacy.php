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

class BiddingStart_processOrderDiplomacy extends processOrderDiplomacy
{
	// Change additional move commands to support-move commands for adjucation...
	public function toMoves()
	{
		global $DB, $Game;

		if ($Game->turn == 0)
		{
			$tabl = $DB->sql_tabl("SELECT o.id, o.countryID, o.type, unit.terrID, o.toTerrID
									FROM wD_Orders o
									INNER JOIN wD_Units unit ON ( o.unitID = unit.id )
									WHERE o.gameID = ".$Game->id);
			
			$orders = array();

			while( list( $id, $countryID, $type, $terrID, $toTerrID) = $DB->tabl_row($tabl))
			{
				if (isset ($orders[$countryID][$toTerrID]))
					$DB->sql_put("UPDATE wD_Orders 
									SET type='Support Move', fromTerrID=".$orders[$countryID][$toTerrID]."
									WHERE id = ".$id);
				elseif ($type == 'Move')
					$orders[$countryID][$toTerrID] = $terrID;
			}
		}
		parent::toMoves();				
	}

}

class GreekDipVariant_processOrderDiplomacy extends BiddingStart_processOrderDiplomacy {}
