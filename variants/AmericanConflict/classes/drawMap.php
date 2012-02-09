<?php
/*
	Copyright (C) 2012 Gavin Atkinson

	This file is part of the American Conflict variant for webDiplomacy

	The American Conflict variant for webDiplomacy is free software:
	you can redistribute it and/or modify it under the terms of the GNU Affero
	General Public License as published by the Free Software Foundation, either
	version 3 of the License, or (at your option) any later version.

	The American Conflict variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
	If you have questions or suggestions send me a mail: Oliver.Auth@rhoen.de

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
		 3 => array( 4,  226, 338,  421,  628), // Calgary
		 8 => array( 4,  485, 333,  892,  625), // Quebec
		13 => array( 3,  148, 481,  267,  899), // Los Angeles
		14 => array( 2,  321, 519,  598,  970), // Dallas
		15 => array( 2,  341, 547,  639, 1025), // Houston
		17 => array( 3,  339, 389,  638,  720), // Minneapolis
		21 => array( 3,  385, 401,  722,  740), // Milwaukee
		26 => array( 3,  491, 390,  931,  736), // New York City
		27 => array( 3,  496, 410,  926,  750), // New Jersey
		28 => array( 3,  486, 405,  908,  757), // Philadelphia		 
		29 => array( 2,  492, 546,  926, 1026), // Florida
		30 => array( 6,  473, 581,  888, 1082), // Havana
		39 => array( 5,  304, 632,  568, 1182), // Mexico
		44 => array( 4,  318, 353,  593,  654), // Manitoba
		47 => array( 4,  447, 375,  832,  703), // Ontario
		49 => array( 4,  538, 336, 1003,  636), // Nova Scotia
		52 => array( 3,  137, 384,  253,  714), // Oregon
		56 => array( 3,  269, 446,  503,  834), // Colorado Territory
		58 => array( 3,  334, 467,  615,  874), // Kansas
		61 => array( 3,  375, 455,  696,  855), // Missouri
		66 => array( 3,  425, 409,  790,  763), // Michigan
		71 => array( 3,  432, 435,  807,  814), // Ohio
		78 => array( 3,  417, 451,  778,  846), // Kentucky		 		 		 		 
		80 => array( 2,  417, 490,  781,  920), // Deep South
		81 => array( 2,  440, 487,  830,  908), // Georgia	
		82 => array( 2,  479, 484,  892,  903), // South Carolina
		83 => array( 2,  457, 460,  858,  856), // North Carolina
		85 => array( 6,  604, 561, 1128, 1044), // Dominica
		87 => array( 5,  255, 563,  463, 1046), // Chihuahua 		 		 		 		 		 		 
		89 => array( 5,  311, 577,  578, 1074), // Nuevo Leon		 		 		 		 		 		 		
		90 => array( 5,  266, 580,  499, 1077), // Durango
		93 => array( 5,  425, 608,  798, 1134), // Yuchatan
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
		$this->sc = $this->loadImage('variants/Empire4_TGAC/resources/'.($this->smallmap ? 'small' : 'large').'_sc.png');	
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

class AmericanConflictVariant_drawMap extends NeutralScBox_drawMap {

	protected $countryColors = array(
		0 => array(226, 198, 158), // Neutral
		1 => array(168, 126, 159), // Russia
		2 => array(188, 188, 188), // Confederate States
		3 => array( 64, 108, 128), // United States
		4 => array(239, 196, 228), // England
		5 => array(121, 175, 198), // France
		6 => array(234, 234, 175)  // Spain
	);

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/AmericanConflict/resources/smallmap.png',
				'army'    =>'contrib/smallarmy.png',
				'fleet'   =>'contrib/smallfleet.png',
				'names'   =>'variants/AmericanConflict/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'     =>'variants/AmericanConflict/resources/map.png',
				'army'    =>'contrib/army.png',
				'fleet'   =>'contrib/fleet.png',
				'names'   =>'variants/AmericanConflict/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}

	public function __construct($smallmap)
	{
		// Map is too big, so up the memory-limit
		parent::__construct($smallmap);
		if ( !$this->smallmap )
			ini_set('memory_limit',"32M");
	}
		
	// Draw the flags behind the units for a better readability
	public function countryFlag($terrName, $countryID)
	{
		$flagBlackback = $this->color(array(0, 0, 0));

		$flagColor = $this->color($this->countryColors[$countryID]);

		list($x, $y) = $this->territoryPositions[$terrName];

		$coordinates = array(
			'top-left' => array( 
							'x'=>$x-intval($this->fleet['width']/2+2)+1,
							'y'=>$y-intval($this->fleet['height']/2+2)+1
							),
			'bottom-right' => array(
							'x'=>$x+intval($this->fleet['width']/2+2)-1,
							'y'=>$y+intval($this->fleet['height']/2+2)-1
							)
			);

		imagefilledrectangle($this->map['image'],
			$coordinates['top-left']['x'], $coordinates['top-left']['y'],
			$coordinates['bottom-right']['x'], $coordinates['bottom-right']['y'],
			$flagBlackback);
		imagefilledrectangle($this->map['image'],
			$coordinates['top-left']['x']+1, $coordinates['top-left']['y']+1,
			$coordinates['bottom-right']['x']-1, $coordinates['bottom-right']['y']-1,
			$flagColor);
	}

}
