<?php
/*
	Copyright (C) 2012 Kaner406 / Oliver Auth

	This file is part of the Mars variant for webDiplomacy

	The Mars variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Mars variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Transform_processOrderDiplomacy extends processOrderDiplomacy
{
	public function apply($standoffTerrs)
	{
		global $Game, $DB;

		// Transform all sucessfull "Transformations":
		$DB->sql_put("UPDATE wD_Units u 
						INNER JOIN wD_Orders o ON (o.unitID = u.id)
						INNER JOIN wD_Moves  m ON (m.gameID=o.gameID AND m.orderID = o.id)
				SET u.type = IF(u.type='Fleet','Army','Fleet'), u.terrID = (o.toTerrID - 1000)
				WHERE o.type='Support hold' AND m.success='Yes' AND o.toTerrID>1000
				AND u.id = o.unitID AND o.gameID = ".$Game->id);
		parent::apply($standoffTerrs);
	}	
}

class MarsVariant_processOrderDiplomacy extends Transform_processOrderDiplomacy {}
