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

class ImperiumVariant_adjSupportHold extends adjSupportHold
{

	protected function attacked()
	{
		global $Game;
		
		foreach($this->attackers as $attacker)
		{
			// If we're attacked from across a river, it doesn't break support
			if ( in_array($attacker->id, $Game->Variant->river_moves) ) {
				continue;
			}
			try
			{
				if ( $attacker->compare('attackStrength','>',0) )
					return true;
			}
			catch(adjParadoxException $pe)
			{
				if ( isset($p) ) $p->downSizeTo($pe);
				else $p = $pe;
			}
		}

		if ( isset($p) ) throw $p;
		else return false;
	}
}

?>