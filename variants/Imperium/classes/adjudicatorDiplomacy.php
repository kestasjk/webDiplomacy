<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Imperium variant for webDiplomacy

	The Imperium variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Imperium variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ImperiumVariant_adjudicatorDiplomacy extends adjudicatorDiplomacy {

	function adjudicate()
	{
		global $DB, $Game;
		// Catch all the "river-moves" bevore the adjucation.
		$tabl = $DB->sql_tabl( "SELECT m.id FROM wD_Moves m
								JOIN wD_Units u ON (u.id = m.unitID)	
								WHERE u.type='Army' 
									AND m.moveType = 'Move' 
									AND ((m.terrID IN (".implode(',',$Game->Variant->river_left).") AND m.toTerrID IN (".implode(',',$Game->Variant->river_right)."))
											OR (m.terrID IN (".implode(',',$Game->Variant->river_right).") AND m.toTerrID IN (".implode(',',$Game->Variant->river_left).")))
									AND m.gameID=".$GLOBALS['GAMEID']
								);

		while( list($id) = $DB->tabl_row($tabl) )
			$Game->Variant->river_moves[]=$id;
			
		return parent::adjudicate();
	}

}

?>