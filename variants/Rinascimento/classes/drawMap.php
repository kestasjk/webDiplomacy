<?php
/*
	Copyright (C) 2011 Emmanuele Ravaioli / Oliver Auth

	This file is part of the Rinascimento variant for webDiplomacy

	The Rinascimento variant for webDiplomacy" is free software:
	you can redistribute it and/or modify it under the terms of the GNU Affero
	General Public License as published by the Free Software Foundation, either 
	version 3 of the License, or (at your option) any later version.

	The Rinascimento variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class MoveFlags_drawMap extends drawMap
{
	public function countryFlag($terrID, $countryID)
	{
		
		$flagBlackback = $this->color(array(0, 0, 0));
		$flagColor = $this->color($this->countryColors[$countryID]);

		list($x, $y) = $this->territoryPositions[$terrID];

		$coordinates = array(
			'top-left' => array( 
						 'x'=>$x-intval($this->fleet['width']/2+1),
						 'y'=>$y-intval($this->fleet['height']/2+1)
						 ),
			'bottom-right' => array(
						 'x'=>$x+intval($this->fleet['width']/2+1),
						 'y'=>$y+intval($this->fleet['height']/2-1)
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


class NeutralScBox_drawMap extends MoveFlags_drawMap
{
	/**
	* An array containing the XY-positions of the "neutral-SC-box" and 
	* the country-color it should be colored if it's still unoccupied.
	*
	* Format: terrID => array (countryID, smallmapx, smallmapy, mapx, mapy)
	**/
	protected $nsc_info=array(
		  5 => array( 8,  66, 101, 136, 206), // Aosta
		  9 => array( 8,  32, 257,  68, 518), // Nizza
		 10 => array( 4,  78, 226, 160, 456), // Ventimiglia
		 18 => array( 5, 145,  93, 294, 190), // Como 
		 20 => array( 5, 150, 158, 304, 320), // Pavia
		 23 => array( 4, 173, 201, 350, 406), // La Spezia
		 28 => array(12, 185,  94, 374, 192), // Bergamo
		 36 => array(12, 376,  50, 756, 104), // Udine
		 39 => array(12, 417, 152, 838, 308), // Pola
		 41 => array(12, 492, 205, 988, 414), // Zara
		 46 => array(12, 281, 132, 566, 268), // Padova
		 48 => array( 1, 306, 172, 616, 348), // Comaccio
		 54 => array(10, 323, 185, 652, 416), // Ravenna
		 58 => array( 2, 218, 210, 440, 424), // Lucca
		 63 => array(10, 324, 227, 652, 458), // Urbini
		 68 => array( 2, 255, 290, 512, 584), // Piombino
		 69 => array( 9, 263, 306, 528, 616), // Grosseto
		 73 => array(10, 378, 330, 760, 664), // L'Aquila
		 74 => array(10, 390, 268, 784, 540), // Ancona
		 78 => array(10, 323, 387, 650, 778), // Ostia
		 87 => array( 6, 558, 420,1120, 844), // Bari
		 89 => array( 6, 591, 463,1186, 930), // Taranto
		 93 => array( 6, 510, 637,1024,1278), // Reggio Calabria
		 95 => array( 6, 473, 658, 950,1320), // Catania
		 96 => array( 6, 474, 712, 952,1428), // Siracusa
		102 => array( 6, 120, 554, 244,1112), // Cagliari
		106 => array( 4,  95, 451, 194, 906), // Sassari
		111 => array( 4, 137, 314, 278, 632), // Bastia
		112 => array( 7, 198, 308, 400, 620)  // Elba
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
		$this->sc = $this->loadImage('variants/Rinascimento/resources/sc_'.($this->smallmap ? 'small' : 'large').'.png');	
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

class RinascimentoVariant_drawMap extends NeutralScBox_drawMap {

	protected $countryColors = array(
		 0 =>  array(226, 198, 158), // Neutral
		 1 =>  array(164, 130, 132), // Ferrara
		 2 =>  array(239, 196, 228), // Firenze
		 3 =>  array(198, 121, 166), // French
		 4 =>  array( 64, 108, 128), // Genova
		 5 =>  array(196, 143, 133), // Milano
		 6 =>  array(164, 196, 153), // Napoli
		 7 =>  array(206, 153, 103), // Pisa
		 8 =>  array(168, 126, 159), // Savoia
		 9 =>  array(114, 146, 103), // Siena
		10 =>  array(234, 234, 175), // Stato della Chiesa
		11 =>  array(160, 138, 117), // Turkish
		12 =>  array(121, 175, 198), // Venezia
		13 =>  array(136, 136, 136)  // Neutral units
	);
	
	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/Rinascimento/resources/smallmap.png',
				'army'    =>'variants/Rinascimento/resources/smallarmy.png',
				'fleet'   =>'variants/Rinascimento/resources/smallfleet.png',
				'names'   =>'variants/Rinascimento/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'     =>'variants/Rinascimento/resources/map.png',
				'army'    =>'variants/Rinascimento/resources/army.png',
				'fleet'   =>'variants/Rinascimento/resources/fleet.png',
				'names'   =>'variants/Rinascimento/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}
	
	// No need to set transparency. Icans have transparent background
	protected function setTransparancies() {}	
}
