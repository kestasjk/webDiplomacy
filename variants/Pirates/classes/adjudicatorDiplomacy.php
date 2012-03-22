<?php
/*
	Copyright (C) 2012 Gavin Atkinson / Oliver Auth

	This file is part of the Pirates variant for webDiplomacy

	The Pirates variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Pirates variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class PiratesVariant_adjudicatorDiplomacy extends adjudicatorDiplomacy
{
	function adjudicate()
	{
		global $DB, $Game;
		// Catch all the fleets bevore the adjucation
		$tabl = $DB->sql_tabl( "SELECT m.id FROM wD_Moves m
								JOIN wD_Units u ON (u.id = m.unitID)	
								WHERE u.type='Army' AND m.gameID=".$GLOBALS['GAMEID']);

		while( list($id) = $DB->tabl_row($tabl) )
			$Game->Variant->fregatte[]=$id;
			
		list($hurricane) = $DB->sql_row( "SELECT id FROM wD_Moves WHERE countryID = '".(count($Game->Variant->countries) + 1)."' AND gameID=".$GLOBALS['GAMEID']);
		$Game->Variant->hurricane=$hurricane;

		// Give the Privateer units to the same country.
		$DB->sql_put("UPDATE wD_Moves SET countryID=1 WHERE countryID=5 AND gameID=".$Game->id);
		$DB->sql_put("UPDATE wD_Moves SET countryID=2 WHERE countryID=6 AND gameID=".$Game->id);
		$DB->sql_put("UPDATE wD_Moves SET countryID=3 WHERE countryID=7 AND gameID=".$Game->id);
		$DB->sql_put("UPDATE wD_Moves SET countryID=4 WHERE countryID=8 AND gameID=".$Game->id);
			
		return parent::adjudicate();
	}
}
