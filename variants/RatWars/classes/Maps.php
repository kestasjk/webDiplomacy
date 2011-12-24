<?php
/*
	Copyright (C) 2011 kaner406 / Oliver Auth

	This file is part of the Rat Wars variant for webDiplomacy

	The Rat Wars variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Rat Wars variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Fog_Maps extends Maps
{
	// The verify-code for the map display
	protected $verify;
	
	function mapHTML($turn)
	{
		return str_replace("map.php?" ,"variants/RatWars/resources/fogmap.php?verify=".$this->verify."&" , parent::mapHTML($turn));
	}
	
	function __construct()
	{
		global $Game, $User, $DB;
		if ($Game->Members->isJoined()) {
			list($ccode)=$DB->sql_row("SELECT text FROM wD_Notices WHERE toUserID=3 AND timeSent=0 AND fromID=".$Game->id);
			$this->verify=substr($ccode,((int)$Game->Members->ByUserID[$User->id]->countryID)*6,6);
		} elseif ($User->type['Moderator']) {
			list($ccode)=$DB->sql_row("SELECT text FROM wD_Notices WHERE toUserID=3 AND timeSent=0 AND fromID=".$Game->id);
			$this->verify=substr($ccode,0,6);
		} else {
			$this->verify='fog';
		}
	}
}
 
class RatWarsVariant_Maps extends Fog_Maps {}
