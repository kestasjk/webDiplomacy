<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class RinascimentoVariant_drawMap extends drawMap {

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
				'army'   =>'variants/Rinascimento/resources/army.png',
				'fleet'   =>'variants/Rinascimento/resources/fleet.png',
				'names'   =>'variants/Rinascimento/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
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
	**/
	protected $nsc=array();

	/**
	* An array containing the XY-positions of the "neutral-SC-box" and the country-color it should be colored
	* if it's still unoccupied.
	* Format: terrID => countryID, smallmapx, smallmapy, mapx, mapy
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

	protected function loadImages()
	{
		parent::loadImages();

		if( $this->smallmap )
			$this->sc = $this->loadImage('variants/Rinascimento/resources/sc_small.png');
		else
			$this->sc = $this->loadImage('variants/Rinascimento/resources/sc_large.png');
	}

	protected function setTransparancies()
	{
		parent::setTransparancies();
		$this->setTransparancy($this->sc);
	}	
	

	/* There are 15 territories on the map that belong to a country but have a supply-center that is considered
	** "neutral"
	** They are set to owner "Neutral" in the installation-file, so we need to check if they are still
	** "neutral" and paint the territory in the color of the country they "should" belong to.
	** after that draw the "Neutral-SC-overloay" on the map.
	*/
	public function ColorTerritory($terrID, $countryID)	
	{
		if ((isset($this->nsc_info[$terrID][0])) && ($countryID==0)) {
			$countryID=$this->nsc_info[$terrID][0];
			$this->nsc[$terrID]=$countryID;
			if ($this->smallmap) {
				$sx=$this->nsc_info[$terrID][1];
				$sy=$this->nsc_info[$terrID][2];
			} else {
				$sx=$this->nsc_info[$terrID][3];
				$sy=$this->nsc_info[$terrID][4];
			}
		}
		
		parent::ColorTerritory($terrID, $countryID);

		if (isset($sx))
			$this->putImage($this->sc, $sx, $sy);
		
	}
	
	public function countryFlag($terrID, $countryID)
	{

		// This is the code from the colored neutral SC-change:
		if (isset($this->nsc[$terrID]) && ($this->nsc[$terrID] == $countryID)) return;
		
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

}

?>