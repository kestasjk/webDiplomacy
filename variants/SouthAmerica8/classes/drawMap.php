<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the South America 8-Player variant for webDiplomacy

	The South America 8-Player variant for webDiplomacy" is free software:
	you can redistribute it and/or modify it under the terms of the GNU Affero
	General Public License as published by the Free Software Foundation, either 
	version 3 of the License, or (at your option) any later version.

	The South America 8-Player variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class SouthAmerica8Variant_drawMap extends drawMap {

	protected $countryColors = array(
		0 =>  array(226, 198, 158), // Neutral
		1 =>  array(239, 196, 228), // Argentina
		2 =>  array(168, 126, 159), // Bolivia
		3 =>  array(121, 175, 198), // Brazil
		4 =>  array(234, 234, 175), // Chile
		5 =>  array(164, 196, 153), // Colombia
		6 =>  array(206, 153, 103), // Paraguay
		7 =>  array(160, 138, 117), // Peru
		8 =>  array(196, 143, 133)  // Venezuela
	);

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/SouthAmerica8/resources/smallmap.png',
				'army'    =>'contrib/smallarmy.png',
				'fleet'   =>'contrib/smallfleet.png',
				'names'   =>'variants/SouthAmerica8/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'     =>'variants/SouthAmerica8/resources/map.png',
				'army'    =>'contrib/army.png',
				'fleet'   =>'contrib/fleet.png',
				'names'   =>'variants/SouthAmerica8/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}
	
	protected function color(array $color, $image=false)
	{
		if ( ! is_array($image) )
		{
			$image = $this->map;
		}
		
		list($r, $g, $b) = $color;
		
		$colorRes = imagecolorexact($image['image'], $r, $g, $b);
		if ($colorRes == -1)
		{
		$colorRes = imageColorAllocate($image['image'], $r, $g, $b);
			if (!$colorRes)
				$colorRes = imageColorClosest($image['image'], $r, $g, $b);
		}
		
		return $colorRes; 
	}
	
	/**
	 * An array containing the neutral support-center icon image resource, and its width and height.
	 * $image['image'],['width'],['height']
	 * @var array
	 */
	protected $sc=array();
	
	/**
	 * An array containing the information if one of the 15 territories 
	 * still has a neutral support-center (So we might not need to draw a flag)
	 */
	protected $nsc=array();

	protected function loadImages()
	{
		parent::loadImages();

		if( $this->smallmap )
			$this->sc = $this->loadImage('variants/SouthAmerica8/resources/sc_small.png');
		else
			$this->sc = $this->loadImage('variants/SouthAmerica8/resources/sc_large.png');
	}

	protected function setTransparancies()
	{
		parent::setTransparancies();
		$this->setTransparancy($this->sc);
	}	
	

	/* There are 15 territories on the map that belong to a country but have a supply-center that is considered
	** "neutral"
	** They are set to owner "Neutral" in the installation-file, so we need to check if they are still
	** "neutal" and paint the territory in the color of the country they "should" belong to.
	** after that draw the "Neutral-SC-overloay" on the map.
	*/
	public function ColorTerritory($terrID, $countryID)	
	{
		if     ($terrID ==  4 && $countryID == 0 && $this->smallmap)  { $sx=205; $sy=482; $countryID=1; } // Patagonia
		elseif ($terrID ==  4 && $countryID == 0 && !$this->smallmap) { $sx=349; $sy=825; $countryID=1; }
		elseif ($terrID ==  5 && $countryID == 0 && $this->smallmap)  { $sx=190; $sy=342; $countryID=1; } // San Miguel de Tucuman
		elseif ($terrID ==  5 && $countryID == 0 && !$this->smallmap) { $sx=312; $sy=585; $countryID=1; }
		elseif ($terrID ==  6 && $countryID == 0 && $this->smallmap)  { $sx=234; $sy=340; $countryID=1; } // Corrientes
		elseif ($terrID ==  6 && $countryID == 0 && !$this->smallmap) { $sx=394; $sy=578; $countryID=1; }
		elseif ($terrID == 13 && $countryID == 0 && $this->smallmap)  { $sx=220; $sy=260; $countryID=2; } // Santa Cruz
		elseif ($terrID == 13 && $countryID == 0 && !$this->smallmap) { $sx=373; $sy=446; $countryID=2; }
		elseif ($terrID == 14 && $countryID == 0 && $this->smallmap)  { $sx=195; $sy=284; $countryID=2; } // Potosi
		elseif ($terrID == 14 && $countryID == 0 && !$this->smallmap) { $sx=331; $sy=486; $countryID=2; }
		elseif ($terrID == 21 && $countryID == 0 && $this->smallmap)  { $sx=313; $sy=139; $countryID=3; } // Maranhao
		elseif ($terrID == 21 && $countryID == 0 && !$this->smallmap) { $sx=530; $sy=238; $countryID=3; }
		elseif ($terrID == 22 && $countryID == 0 && $this->smallmap)  { $sx=288; $sy=357; $countryID=3; } // Rio Grande do Sul
		elseif ($terrID == 22 && $countryID == 0 && !$this->smallmap) { $sx=470; $sy=630; $countryID=3; }
		elseif ($terrID == 23 && $countryID == 0 && $this->smallmap)  { $sx=280; $sy=317; $countryID=3; } // Parana
		elseif ($terrID == 23 && $countryID == 0 && !$this->smallmap) { $sx=469; $sy=539; $countryID=3; }
		elseif ($terrID == 24 && $countryID == 0 && $this->smallmap)  { $sx=139; $sy=193; $countryID=3; } // Aquiris Ingenous Territory
		elseif ($terrID == 24 && $countryID == 0 && !$this->smallmap) { $sx=240; $sy=335; $countryID=3; }
		elseif ($terrID == 36 && $countryID == 0 && $this->smallmap)  { $sx=200; $sy=555; $countryID=4; } // Punta Arenas
		elseif ($terrID == 36 && $countryID == 0 && !$this->smallmap) { $sx=340; $sy=944; $countryID=4; }
		elseif ($terrID == 42 && $countryID == 0 && $this->smallmap)  { $sx= 66; $sy= 32; $countryID=5; } // Panama
		elseif ($terrID == 42 && $countryID == 0 && !$this->smallmap) { $sx=110; $sy= 55; $countryID=5; }
		elseif ($terrID == 56 && $countryID == 0 && $this->smallmap)  { $sx=110; $sy=137; $countryID=7; } // Iquitos
		elseif ($terrID == 56 && $countryID == 0 && !$this->smallmap) { $sx=188; $sy=236; $countryID=7; }
		elseif ($terrID == 57 && $countryID == 0 && $this->smallmap)  { $sx=139; $sy=250; $countryID=7; } // Arica
		elseif ($terrID == 57 && $countryID == 0 && !$this->smallmap) { $sx=238; $sy=423; $countryID=7; }
		elseif ($terrID == 63 && $countryID == 0 && $this->smallmap)  { $sx=183; $sy= 23; $countryID=8; } // Cumana
		elseif ($terrID == 63 && $countryID == 0 && !$this->smallmap) { $sx=308; $sy= 43; $countryID=8; }
		elseif ($terrID == 64 && $countryID == 0 && $this->smallmap)  { $sx=176; $sy= 56; $countryID=8; } // Angostura
		elseif ($terrID == 64 && $countryID == 0 && !$this->smallmap) { $sx=296; $sy= 95; $countryID=8; }
		
		parent::ColorTerritory($terrID, $countryID);
		$this->nsc[$terrID]=0;

		if (isset($sx))
		{
			$this->putImage($this->sc, $sx, $sy);
			$this->nsc[$terrID]=1;
		}
		
	}
	
	/* No need to draw the country flags for "neural-SC-territories if they get occupied by 
	** the country they should belong to
	*/
	public function countryFlag($terrID, $countryID)
	{
		if (($this->nsc[4]  == 1) && ($terrID ==  4) && ( $countryID == 1)) return;
		if (($this->nsc[5]  == 1) && ($terrID ==  5) && ( $countryID == 1)) return;
		if (($this->nsc[6]  == 1) && ($terrID ==  6) && ( $countryID == 1)) return;
		if (($this->nsc[13] == 1) && ($terrID == 13) && ( $countryID == 2)) return;
		if (($this->nsc[14] == 1) && ($terrID == 14) && ( $countryID == 2)) return;
		if (($this->nsc[21] == 1) && ($terrID == 21) && ( $countryID == 3)) return;
		if (($this->nsc[22] == 1) && ($terrID == 22) && ( $countryID == 3)) return;
		if (($this->nsc[23] == 1) && ($terrID == 23) && ( $countryID == 3)) return;
		if (($this->nsc[24] == 1) && ($terrID == 24) && ( $countryID == 3)) return;
		if (($this->nsc[36] == 1) && ($terrID == 36) && ( $countryID == 4)) return;
		if (($this->nsc[42] == 1) && ($terrID == 42) && ( $countryID == 5)) return;
		if (($this->nsc[56] == 1) && ($terrID == 56) && ( $countryID == 7)) return;
		if (($this->nsc[57] == 1) && ($terrID == 57) && ( $countryID == 7)) return;
		if (($this->nsc[63] == 1) && ($terrID == 63) && ( $countryID == 8)) return;
		if (($this->nsc[64] == 1) && ($terrID == 64) && ( $countryID == 8)) return;	
		parent::countryFlag($terrID, $countryID);
	}	
	
}

?>