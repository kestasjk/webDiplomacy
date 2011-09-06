<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the WhoControlsAmericaV variant for webDiplomacy

	The WhoControlsAmericaV variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The WhoControlsAmerica variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/
defined('IN_CODE') or die('This script can not be run by itself.');

class WhoControlsAmericaVariant_processMembers extends processMembers {

	// Winner need to occupie The White House (ID=49) and Congress (ID=50)
	function checkForWinner()
	{
		global $DB, $Game;

		$win=parent::checkForWinner();
		if ($win != false) {
			list($wh_stat)=$DB->sql_row("SELECT countryID FROM wD_TerrStatus WHERE terrID=49 AND GameID=".$Game->id);
			if ($wh_stat == $win->countryID) {
				list($con_stat)=$DB->sql_row("SELECT countryID FROM wD_TerrStatus WHERE terrID=50 AND GameID=".$Game->id);
				if ($con_stat == $win->countryID) {
					return $win;
				}
			}
		}
		return false;
	}
	
}
?>
