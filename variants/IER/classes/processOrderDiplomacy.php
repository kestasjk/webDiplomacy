<?php
/*
	Copyright (C) 2010 Carey Jensen / Oliver Auth ... and Orathaic

	This file is part of the Italy+ Vs England+ Vs Russia variant for webDiplomacy (based on Modern Diplomacy II)

	The Italy+ Vs England+ Vs Russia variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Italy+ Vs England+ Vs Russia variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class IERVariant_processOrderDiplomacy extends processOrderDiplomacy
{
	public function create()
	{
		global $DB, $Game;

		// An order is needed for every unit current in-game unit
		$DB->sql_put("INSERT INTO wD_Orders
				( gameID, countryID, type, unitID )
			SELECT u.gameID, u.countryID, 'Hold', u.id
			FROM wD_Units u 
			INNER JOIN wD_Territories t
			WHERE u.gameID=".$Game->id." AND u.terrID=t.id AND t.mapID=".$Game->Variant->mapID."
			ORDER BY u.type,t.name");		
	}

}

?>
