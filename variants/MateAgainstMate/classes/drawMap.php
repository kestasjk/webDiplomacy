<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the MateAgainstMate variant for webDiplomacy

	The MateAgainstMate variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The MateAgainstMate variant for webDiplomacy is distributed in the hope that it
	will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.		
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class NeutralScBox_drawMap extends drawMap
{
	/**
	* An array containing the XY-positions of the "neutral-SC-box" and 
	* the country-color it should be colored if it's still unoccupied.
	*
	* Format: terrID => array (countryID, smallmapx, smallmapy, mapx, mapy)
	**/
	protected $nsc_info=array(
		 36 => array( 8, 487,  31, 948,  62), // Port Moresby
		 39 => array( 8, 441, 141, 861, 282), // Townsville
		 42 => array( 8, 485, 190, 951, 384), // Rockhampton
		 46 => array( 8, 517, 244,1015, 493), // Gold Coast	
		 53 => array( 5, 674, 391,1318, 784), // Hamilton	
		 58 => array( 5, 624, 486,1221, 977), // Dunedin									
		 72 => array( 2,  71, 247, 139, 498), // Geraldton
		 74 => array( 2, 125, 327, 251, 662), // Albany
		 82 => array( 3, 308,  66, 613, 134), // Kakadu
		 84 => array( 3, 284, 227, 550, 454), // Coober Pedy	
		 89 => array( 3, 372, 372, 728, 745), // Mount Gambier
		 96 => array( 7, 502, 306, 980, 618), // Newcastle	
		 99 => array( 7, 488, 330, 958, 668), // Wollongong	
		107 => array( 6, 435, 360, 852, 728), // Shepparton	
		109 => array( 6, 410, 367, 804, 736), // Ballarat		
	);
	
	/**
	* An array containing the neutral support-center icon image resource, and its width and height.
	* $image['image'],['width'],['height']
	* @var array
	**/
	protected $sc=array();
	
	/**
	* An array containing the information if one of the first 9 territories 
	* still has a neutral support-center (So we might not need to draw a flag)
	**/
	protected $nsc=array();

	protected function loadImages()
	{
		parent::loadImages();
		$this->sc = $this->loadImage('variants/MateAgainstMate/resources/'.($this->smallmap ? 'small' : 'large').'_sc.png');	
	}

	/**
	* There are some territories on the map that belong to a country but have a supply-center
	* that is considered "neutral".
	* They are set to owner "Neutral" in the installation-file, so we need to check if they are
	* still "neutal" and paint the territory in the color of the country they "should" belong to.
	* After that draw the "Neutral-SC-overloay" on the map.
	**/
	public function ColorTerritory($terrID, $countryID)
	{
		parent::ColorTerritory($terrID, $countryID);

		if ((isset($this->nsc_info[$terrID][0])) && $countryID==0)
		{
			parent::ColorTerritory($terrID, $this->nsc_info[$terrID][0]);
			$this->nsc[$terrID]=$countryID;
			$sx=($this->smallmap ? $this->nsc_info[$terrID][1] : $this->nsc_info[$terrID][3]);
			$sy=($this->smallmap ? $this->nsc_info[$terrID][2] : $this->nsc_info[$terrID][4]);
			$this->putImage($this->sc, $sx, $sy);
		}
	}
		
	/* No need to draw the country flags for "neural-SC-territories if they get occupied by 
	** the country they should belong to
	*/
	public function countryFlag($terrID, $countryID)
	{
		if (isset($this->nsc[$terrID]) && ($this->nsc[$terrID] == $countryID)) return;
		parent::countryFlag($terrID, $countryID);
	}

}

class MateAgainstMateVariant_drawMap extends NeutralScBox_drawMap
{
	protected $countryColors = array(
		 0 => array(226, 198, 158), // Neutral
		 1 => array(239, 196, 228), // Indonesia
		 2 => array(216, 197,  89), // Western Australia
		 3 => array(196, 143, 133), // South Australia
		 4 => array(150, 200, 130), // Tasmania
		 5 => array(121, 121, 121), // New Zealand
		 6 => array( 64, 108, 128), // Victoria
		 7 => array(127, 183, 248), // New South Wales
		 8 => array(140,  40,  60), // Queensland
		 9 => array(164, 130, 132), // Neutral units
		10 => array(167, 222, 235)  // Neutral sea
	);

	public function __construct($smallmap)
	{
		// Map is too big, so up the memory-limit
		parent::__construct($smallmap);
		if ( !$this->smallmap )
			ini_set('memory_limit',"32M");
	}
	
	protected function resources()
	{
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/MateAgainstMate/resources/smallmap.png',
				'names'   =>'variants/MateAgainstMate/resources/smallmapNames.png',
				'army'    =>'contrib/smallarmy.png',
				'fleet'   =>'contrib/smallfleet.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'     =>'variants/MateAgainstMate/resources/map.png',
				'names'   =>'variants/MateAgainstMate/resources/mapNames.png',
				'army'    =>'contrib/army.png',
				'fleet'   =>'contrib/fleet.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}

	protected function color(array $color, $image=false)
	{
		if ( ! is_array($image) )
			$image = $this->map;
		
		list($r, $g, $b) = $color;
		
		$colorRes = imagecolorexact($image['image'], $r, $g, $b);
		if ($colorRes == -1) {
			$colorRes = imageColorAllocate($image['image'], $r, $g, $b);
			if (!$colorRes)
				$colorRes = imageColorClosest($image['image'], $r, $g, $b);
		}
		return $colorRes; 
	}
}
