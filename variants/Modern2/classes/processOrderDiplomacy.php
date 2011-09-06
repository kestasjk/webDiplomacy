<?php
/*
	Copyright (C) 2010 Carey Jensen / Oliver Auth

	This file is part of the Modern Diplomacy II variant for webDiplomacy

	The Modern Diplomacy II variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Modern Diplomacy II variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Modern2Variant_processOrderDiplomacy extends processOrderDiplomacy
{
	public function create()
	{
		global $DB, $Game;

		// An order is needed for every unit current in-game unit
		$DB->sql_put("INSERT INTO wD_Orders
				( gameID, countryID, type, unitID )
			SELECT u.gameID, u.countryID, 'Hold', u.id
			FROM wD_Units u 
			INNER JOIN wD_Games g ON (u.gameID=g.id) 
			INNER JOIN wD_Territories t ON (t.mapID=g.variantID) 
			WHERE u.gameID=".$Game->id." AND u.terrID=t.id 
			ORDER BY u.type,t.name");		
	}

}

?>