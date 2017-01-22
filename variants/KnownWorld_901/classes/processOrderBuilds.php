<?php
/*
	Copyright (C) 2011 Kaner406 / Oliver Auth

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

class BuildAnywhere_processOrderBuilds extends processOrderBuilds {

	public function create()
	{
		global $DB, $Game;

		$newOrders = array();
		foreach($Game->Members->ByID as $Member )
		{
			$difference = 0;
			if ( $Member->unitNo > $Member->supplyCenterNo )
			{
				$difference = $Member->unitNo - $Member->supplyCenterNo;
				$type = 'Destroy';
			}
			elseif ( $Member->unitNo < $Member->supplyCenterNo )
			{
				$difference = $Member->supplyCenterNo - $Member->unitNo;
				$type = 'Build Army';
			}

			for( $i=0; $i < $difference; ++$i )
			{
				$newOrders[] = "(".$Game->id.", ".$Member->countryID.", '".$type."')";
			}
		}

		if ( count($newOrders) )
		{
			$DB->sql_put("INSERT INTO wD_Orders
							(gameID, countryID, type)
							VALUES ".implode(', ', $newOrders));
		}
	}

}

class KnownWorld_901Variant_processOrderBuilds extends BuildAnywhere_processOrderBuilds {}
