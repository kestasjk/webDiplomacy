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

class Transform_userOrderDiplomacy extends userOrderDiplomacy
{

	// Allow the transform command
	protected function typeCheck()
	{
		if (strrpos($this->type,'Transform_1') !== false) return true;
		return parent::typeCheck();	
	}	
	
	// Save the transform command as a Support-hold
	public function commit()
	{
		// Clear the toTerrID (if there is any) from the transform command
		if ($this->type=='Hold')
			$this->paramWipe('toTerrID');
	
		if (strrpos($this->type,'Transform_1') !== false)
		{
			$this->toTerrID = substr($this->type, -4);
			$this->wiped = array('fromTerrID','viaConvoy');
			$this->changed = array('type','toTerrID');
			$this->type='Support hold';		
		}
		return parent::commit();
	}

	public function loadFromDB(array $inputs)
	{
		if( isset($inputs['toTerrID']) && $inputs['toTerrID'] >  1000 )
		{
			$inputs['type'] = 'Transform_' . $inputs['toTerrID'];
			unset($inputs['toTerrID']);	
		}
		parent::loadFromDB($inputs);
	}

	public function loadFromInput(array $inputs)
	{
		if( isset($inputs['toTerrID']) && $inputs['toTerrID'] >  1000)
		{
			$inputs['type'] = 'Transform_' . $inputs['toTerrID'];
			unset($inputs['toTerrID']);	
		}
		parent::loadFromInput($inputs);
	}
}

class PiratesVariant_userOrderDiplomacy extends Transform_userOrderDiplomacy {}
