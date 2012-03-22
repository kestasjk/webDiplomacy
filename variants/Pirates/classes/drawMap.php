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

class ColorUnits_drawMap extends drawMap
{
	protected function loadImages()
	{
		$this->hurricane = $this->loadImage('variants/Pirates/resources/'.($this->smallmap ? 'small' : '').'hurricane.png');
		parent::loadImages();
	}
	
	public function addUnit($terrName, $unitType)
	{
		if ($this->unit_c[$terrName] == 14)
		{
			$swap = $this->fleet;
			$this->fleet = $this->hurricane;
			parent::addUnit($terrName, 'fleet');
			$this->fleet = $swap;
		}
		else
		{
			list($r, $g, $b) = $this->countryColors[$this->unit_c[$terrName]];
			imagecolorset($this->fleet['image'],0, $r, $g, $b);
			imagecolorset($this->army['image'],0, $r, $g, $b);
			parent::addUnit($terrName, $unitType);
		}
	}
	
	public function colorTerritory($terrID, $countryID)	
	{
		$terrName=$this->territoryNames[$terrID];
		if (strpos($terrName,')') === false)
		{
			$this->unit_c[$terrID]=$countryID;
			$this->unit_c[array_search($terrName. " (North Coast)" ,$this->territoryNames)]=$countryID;
			$this->unit_c[array_search($terrName. " (East Coast)"  ,$this->territoryNames)]=$countryID;
			$this->unit_c[array_search($terrName. " (South Coast)" ,$this->territoryNames)]=$countryID;
			$this->unit_c[array_search($terrName. " (West Coast)"  ,$this->territoryNames)]=$countryID;
		}
		parent::colorTerritory($terrID, $countryID);
	}
	
	public function countryFlag($terrName, $countryID)	{
		$this->unit_c[$terrName]=$countryID;
	}
	
}

class SeaMap_drawMap extends ColorUnits_drawMap
{
	protected function loadImages()
	{
		$this->mapTerr = $this->loadImage('variants/Pirates/resources/'.($this->smallmap ? 'small' : '').'mapTerr.png');
		parent::loadImages();
	}

	// Combine the 2 maps.
	public function write($filename)
	{
		$w = $this->map['width'];
		$h = $this->map['height'];
		$im = imagecreate($this->map['width'], $this->map['height']);
		imagecopyresampled($im, $this->mapTerr['image'], 0, 0, 0, 0, $w, $h, $w, $h);
		imagecopyresampled($im, $this->map['image'], 0, 0, 0, 0, $w, $h, $w, $h);
		imagetruecolortopalette($im, true, 256);
		$this->map['image']=$im;
		parent::write($filename);
	}
	
	public function ColorTerritory($terrID, $countryID)
	{
		$swap = $this->map['image'];
		$this->map['image'] = $this->mapTerr['image'];
		parent::ColorTerritory($terrID, $countryID);
		$this->map['image'] = $swap;
	}
}

// Transform command
class Transform_drawMap extends SeaMap_drawMap
{
	private $trafo=array();
	
	public function drawSupportHold($fromTerrID, $toTerrID, $success)
	{
		if ($toTerrID < 1000) return parent::drawSupportHold($fromTerrID, $toTerrID, $success);
		
		$toTerrID = $toTerrID - 1000;
		if ($success)
			$this->trafo[$fromTerrID]=$toTerrID;

		$this->drawTransform($fromTerrID, $toTerrID, $success);
	}
	
	// If a unit did a transform draw the new unit-type on the board instead of the old...
	public function addUnit($terrID, $unitType)
	{
		if (array_key_exists($terrID,$this->trafo))
			return parent::addUnit($this->trafo[$terrID], ($unitType == 'Fleet' ? 'Army' : 'Fleet'));
		parent::addUnit($terrID, $unitType);
	}

	// Draw the transformation circle:
	protected function drawTransform($fromTerrID, $toTerrID, $success)
	{
	
		$terrID = ($success ? $toTerrID : $fromTerrID);
		
		if ( $fromTerrID != $toTerrID )
			$this->drawMove($fromTerrID,$toTerrID, $success);
		
		$darkblue  = $this->color(array(40, 80,130));
		$lightblue = $this->color(array(70,150,230));
		
		list($x, $y) = $this->territoryPositions[$terrID];
		
		$width=($this->fleet['width'])+($this->fleet['width'])/2;
		
		imagefilledellipse ( $this->map['image'], $x, $y, $width, $width, $darkblue);
		imagefilledellipse ( $this->map['image'], $x, $y, $width-2, $width-2, $lightblue);
		
		if ( !$success ) $this->drawFailure(array($x-1, $y),array($x+2, $y));
	}
}

class NeutralScBox_drawMap extends Transform_drawMap
{
	/**
	* An array containing the XY-positions of the "neutral-SC-box" and 
	* the country-color it should be colored if it's still unoccupied.
	*
	* Format: terrID => array (countryID, smallmapx, smallmapy, mapx, mapy)
	**/
	protected $nsc_info=array(
		   1 => array( 2, 662, 284, 1061, 456), // Antigua
		   6 => array( 2, 587,  17, 941,   28), // Bermuda
		   7 => array( 1, 344, 476, 553,  763), // Buried Treasure
		  13 => array( 1, 388, 451, 623,  723), // Cartagena
		  16 => array( 1, 513, 417, 823,  669), // Coro
		  18 => array( 1, 604, 438, 968,  703), // Cunana
		  26 => array( 1, 418, 149, 669,  240), // Eleuthera
		  30 => array( 1, 474, 461, 760,  739), // Gibraltar
		  31 => array( 1, 199, 397, 320,  637), // Gran Granada
		  33 => array( 3, 672, 363, 1077, 582), // Grenada
		  45 => array( 3, 502, 236, 805,  379), // La Vega
		  48 => array( 1, 459, 424, 736,  680), // Maracaibo
		  49 => array( 1, 649, 413, 1040, 662), // Margarita
		  52 => array( 1, 174, 258, 283,  412), // Mayan Gold
		  56 => array( 2, 652, 300, 1045, 481), // Montserrat
		  58 => array( 1, 390, 120, 626,  193), // Nassau
		  61 => array( 1, 331, 461, 531,  739), // Nombre de Dior
		  69 => array( 1, 339, 205, 543,  331), // Old Spanish Armory
		  72 => array( 3, 436, 261, 699,  419), // Petit Goave
		  75 => array( 3, 458, 234, 735,  376), // Port-de-Paix
		  77 => array( 1, 296, 466, 475,  747), // Puerto Bello
		  78 => array( 1, 547, 435, 877,  698), // Puerto Cabello
		  79 => array( 1, 435, 418, 698,  670), // Rio de la Hacha
		  80 => array( 1, 486, 178, 778,  288), // Rum Distillery
		  81 => array( 1, 582, 264, 933,  424), // San Juan
		  82 => array( 1, 417, 420, 669,  674), // Santa Maria
		  83 => array( 1, 414, 228, 664,  366), // Santiago
		  84 => array( 1,  12, 225,  20,  362), // Santo Poco
		  92 => array( 1, 337,  19, 541,   31), // St Augustine
		  96 => array( 3, 669, 347, 1073, 556), // St Lucia
		 102 => array( 3, 482, 286, 773,  460), // Treasure Island
		 103 => array( 1, 662, 439, 1061, 704), // Trinadad
		 104 => array( 1,  75, 267, 121,  429), // Veracruz
		 115 => array( 3, 466, 253, 747,  406), // Yaguano
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
		$this->sc = $this->loadImage('variants/Pirates/resources/'.($this->smallmap ? 'small' : 'large').'_sc.png');	
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

		if ((isset($this->nsc_info[$terrID][0])) && $countryID==0 )
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

class PiratesVariant_drawMap extends NeutralScBox_drawMap
{
	protected $countryColors = array(
		 0  => array(226, 198, 158), /* Neutral   */
		 1  => array(233, 230,  31), /* Spain */
		 2  => array(239, 196, 228), /* England */
		 3  => array(121, 175, 198), /* France */
		 4  => array(230, 180,  60), /* Holland */
		 5  => array(230, 120,  60), /* Dunkirkers */
		 6  => array(185,  50, 185), /* Henry Morgan */
		 7  => array( 80,  80, 250), /* Francois l’Olonnais */
		 8  => array(240, 130, 130), /* Isaac Rochussen */
		 9  => array(195, 240, 130), /* The Infamous El Guapo */
		10  => array(190, 150, 240), /* Daniel "The Exterminator" Montbars */		 
		11  => array(190, 160, 160), /* Roche "The Rock" Braziliano */
		12  => array( 50, 170,  30), /* Bartolomeu "The Portuguese" de la Cueva */
		13  => array(150, 240, 220), /* Daniel "The Terror" Johnson */
		14  => array(226, 198, 158), /* Neutral - Hurricane   */
	);

	// No need to set the transparency for our custom icons and mapnames.
	protected function setTransparancy(array $image, array $color=array(255,255,255)) {}

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/Pirates/resources/smallmap.png',
				'army'    =>'variants/Pirates/resources/smallarmy.png',
				'fleet'   =>'variants/Pirates/resources/smallfleet.png',
				'names'   =>'variants/Pirates/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'     =>'variants/Pirates/resources/map.png',
				'army'    =>'variants/Pirates/resources/army.png',
				'fleet'   =>'variants/Pirates/resources/fleet.png',
				'names'   =>'variants/Pirates/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}

}

?>