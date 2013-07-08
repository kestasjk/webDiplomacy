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

class ImperiumVariant_userOrderDiplomacy extends userOrderDiplomacy
{

	public function supportHoldToTerrCheck()
	{
		if(($GLOBALS['Variants'][VARIANTID]->river_move($this->Unit->terrID,$this->toTerrID)) && ($this->Unit->type == 'Army'))
			return false;
		else
			return parent::supportHoldToTerrCheck();

	}

	public function supportMoveToTerrCheck()
	{
		if($GLOBALS['Variants'][VARIANTID]->river_move($this->Unit->terrID,$this->toTerrID))
			return false;
		else
			return parent::supportMoveToTerrCheck();
	}
}

?>