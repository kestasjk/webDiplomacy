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

class ImperiumVariant_adjMove extends adjMove
{

	function __construct($id, $countryID)
	{		
		parent::__construct($id, $countryID);
	}	

	// because it's private, the original defenderMoving can not acessed here...
	private function defenderMoving()
	{
		return parent::defenderMoving();
	}
	
	protected function _preventStrength()
	{
		global $Game;
		$prevent = parent::_preventStrength();
		// If we're moving across a river, reduce the strength
		if (in_array($this->id, $Game->Variant->river_moves)) {
			$prevent['min'] = $prevent['min'] - 0.5;
			$prevent['max'] = $prevent['max'] - 0.5;
			if (isset($prevent['paradox'])) $prevent['paradox']--;
		}
		return $prevent;
	}
	
	protected function _attackStrength()
	{
		global $Game;
		$attackStrength = parent::_attackStrength();
		// Check rivers again before returning attackStrength
		if ( in_array($this->id, $Game->Variant->river_moves) ) {
			$attackStrength['min'] = $attackStrength['min']-0.5;
			$attackStrength['max'] = $attackStrength['max']-0.5;
		}
		return $attackStrength;
	}
	
}

?>