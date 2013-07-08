<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the ClassicVS variant for webDiplomacy

	The ClassicVS variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The ClassicVS variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class CustomCountries_drawMap extends drawMap
{
	// If true we realized it's the pregame map. 
	// All territories got recolored. Don't change the territory-colors again and draw no countryFlags
	public $preGameCheck = false;
	
	// New function to adjust the country-colors to the different countries now in play...
	public function recolor($countryID)
	{
		global $Variant;
		if     ((in_array('England',$Variant->countries) == true) && (array_search('England',$Variant->countries) === ($countryID - 1))) $countryID = 1;
		elseif ((in_array('France' ,$Variant->countries) == true) && (array_search('France' ,$Variant->countries) === ($countryID - 1))) $countryID = 2;
		elseif ((in_array('Italy'  ,$Variant->countries) == true) && (array_search('Italy'  ,$Variant->countries) === ($countryID - 1))) $countryID = 3;
		elseif ((in_array('Germany',$Variant->countries) == true) && (array_search('Germany',$Variant->countries) === ($countryID - 1))) $countryID = 4;
		elseif ((in_array('Austria',$Variant->countries) == true) && (array_search('Austria',$Variant->countries) === ($countryID - 1))) $countryID = 5;
		elseif ((in_array('Turkey' ,$Variant->countries) == true) && (array_search('Turkey' ,$Variant->countries) === ($countryID - 1))) $countryID = 6;
		elseif ((in_array('Russia' ,$Variant->countries) == true) && (array_search('Russia' ,$Variant->countries) === ($countryID - 1))) $countryID = 7;
		return $countryID;
	}
	
	public function colorTerritory($terrID, $countryID)
	{
		/* The only possible way we need to color this "fake" territory witch a country-color is because it's called 
		*  with the data from the pre-game-map (there is no way this territory makes it to the terrstatus-table)
		*
		*  If this happens we need to recolor every territory with the right colors according to the involved countries
		*  and ignore any further request for this map to color another territory or draw a unit-flag.
		*/
		if (($this->territoryNames[$terrID] == 'PreGameCheck') && ($countryID != 0))
		{
			global $Variant, $DB;
			$sql = "SELECT id, countryID
					FROM wD_Territories
					WHERE (coast='No' OR coast='Parent')
						AND name != 'PreGameCheck'
						AND type != 'Sea'
						AND mapID=".$Variant->mapID;
			$tabl = $DB->sql_tabl($sql);
		
			// Color countries that do not get played as 'neutral'
			while(list($terrID, $countryID) = $DB->tabl_row($tabl))
			{
				if ((in_array('England',$Variant->countries) == false) && $countryID == 1) $countryID = 0;
				if ((in_array('France' ,$Variant->countries) == false) && $countryID == 2) $countryID = 0;
				if ((in_array('Italy'  ,$Variant->countries) == false) && $countryID == 3) $countryID = 0;
				if ((in_array('Germany',$Variant->countries) == false) && $countryID == 4) $countryID = 0;
				if ((in_array('Austria',$Variant->countries) == false) && $countryID == 5) $countryID = 0;
				if ((in_array('Turkey' ,$Variant->countries) == false) && $countryID == 6) $countryID = 0;
				if ((in_array('Russia' ,$Variant->countries) == false) && $countryID == 7) $countryID = 0;
				parent::colorTerritory($terrID, $countryID);
			}			
			$this->preGameCheck = true;				
		}
		// If this is not the pregame-map change the colors according to the countries in play.
		elseif (($this->preGameCheck == false) && ($this->territoryNames[$terrID] != 'PreGameCheck'))
		{
			parent::colorTerritory($terrID,$this->recolor($countryID));
		}
	}
	
	// If we are drawing the pregame map never draw country-flags. The data is from the generic map and can't be used.
	public function countryFlag($terrID, $countryID)
	{
		if ($this->preGameCheck == false)
			parent::countryFlag($terrID, $this->recolor($countryID));
	}
}

class ClassicVSVariant_drawMap extends CustomCountries_drawMap
{
	protected $countryColors = array(
		0 => array(226, 198, 158),
		1 => array(239, 196, 228),
		2 => array(121, 175, 198),
		3 => array(164, 196, 153),
		4 => array(160, 138, 117),
		5 => array(196, 143, 133),
		6 => array(234, 234, 175),
		7 => array(168, 126, 159)
	);

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'=>'variants/ClassicVS/resources/smallmap.png',
				'army'=>'contrib/smallarmy.png',
				'fleet'=>'contrib/smallfleet.png',
				'names'=>'variants/ClassicVS/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'=>'variants/ClassicVS/resources/map.png',
				'army'=>'contrib/army.png',
				'fleet'=>'contrib/fleet.png',
				'names'=>'variants/ClassicVS/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}
}
