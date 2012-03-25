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

class PiratesVariant_adjMove extends adjMove
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
	
	protected function _attackStrength()
	{
		global $Game;
		$attackStrength = parent::_attackStrength();
		// If we're a fregatte 
		if ( in_array($this->id, $Game->Variant->fregatte) ) {
			$attackStrength['min'] = $attackStrength['min']+0.5;
			$attackStrength['max'] = $attackStrength['max']+0.5;
		}
		if ( $this->countryID == 14 ) {
			$attackStrength['min'] = $attackStrength['min']+3;
			$attackStrength['max'] = $attackStrength['max']+3;
		}
		return $attackStrength;
	}
	
	protected function _holdStrength()
	{
		global $Game;
		$holdStrength = parent::_holdStrength();
		// If we're a fregatte 
		if ( in_array($this->id, $Game->Variant->fregatte) ) {
			$holdStrength['min'] = $holdStrength['min']+0.5;
			$holdStrength['max'] = $holdStrength['max']+0.5;
		}
		if ( $this->countryID == 14 ) {
			$holdStrength['min'] = $holdStrength['min']+3;
			$holdStrength['max'] = $holdStrength['max']+3;
		}		
		return $holdStrength;
	}
}
