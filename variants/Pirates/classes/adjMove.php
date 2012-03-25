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
	
	protected function supportStrength($checkCountryID=false)
	{
		global $Game;
		$min = 1;
		$max = 1;
		
		if ( in_array($this->id, $Game->Variant->fregatte) ) {
			$min += 0.5;
			$max += 0.5;
		} elseif ( $this->countryID == 14 ) {
			$min += 3;
			$max += 3;
		}
		
		foreach($this->supporters as $supporter)
		{
			/*
			 * If specified then countries are checked to ensure no-one can
			 * give attack support against their own countryID
			 */
			if ( $checkCountryID and $this->defender->countryID == $supporter->countryID )
				continue;
			
			try
			{
				if( $supporter->success() )
				{
					$min++;
					$max++;
				}
			}
			catch(adjParadoxException $pe)
			{
				$max++; // It is a possible supporter
				if ( isset($p) ) $p->downSizeTo($pe);
				else $p = $pe;
			}
		}
		
		$support = array('min'=>$min,'max'=>$max);
		if ( isset($p) )
			$support['paradox'] = $p;
		
		return $support;
	}
	
	protected function _holdStrength()
	{
		global $Game;
		try
		{
			if ( $this->success() )
			{
				$min = 0;
				$max = 0;
			}
			else
			{
				$min = 1;
				$max = 1;
			}
		}
		catch(adjParadoxException $p)
		{
			$min = 0;
			$max = 1;
		}
		
		if ( in_array($this->id, $Game->Variant->fregatte) ) {
			$min += 0.5;
			$max += 0.5;
		} elseif ( $this->countryID == 14 ) {
			$min += 3;
			$max += 3;
		}
		
		$holdStrength = array('min'=>$min,'max'=>$max);
		if ( isset($p) )
			$holdStrength['paradox'] = $p;
		
		return $holdStrength;
	}
	
	
}
