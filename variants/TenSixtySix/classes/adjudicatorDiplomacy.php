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

class Fog_adjudicatorDiplomacy extends adjudicatorDiplomacy
{
	function adjudicate()
	{
		global $DB;

		$fromids=array();
	
		/* Remove invalid support-move orders (Support a move from a territory without a unit*/
		$tabl = $DB->sql_tabl("SELECT terrID FROM wD_Moves 
									WHERE gameID=".$GLOBALS['GAMEID']);
	
		while(list($terrID) = $DB->tabl_row($tabl))
			$fromids[] = $terrID;

		if (!(empty($fromids)))
			$DB->sql_put("UPDATE wD_Moves
							SET moveType = 'Hold'
							WHERE moveType = 'Support move'
								AND gameID=".$GLOBALS['GAMEID']." 
								AND fromTerrID NOT IN (".implode(",", $fromids).")");
					
		return parent::adjudicate();
	}
}

class TenSixtySixVariant_adjudicatorDiplomacy extends Fog_adjudicatorDiplomacy {}
  
?>