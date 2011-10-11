<?php

defined('IN_CODE')or die('This script can not be run by itself.');

class ChangeFlag_drawMap extends drawMap
{
	// Here is where I tried to add the modified function!
	public function countryFlag($terrName, $countryID)
	{
		$flagBlackback = $this->color(array(0, 0, 0));

		$flagColor = $this->color($this->countryColors[$countryID]);

		list($x, $y) = $this->territoryPositions[$terrName];

		$coordinates = array(
			'top-left' => array( 
						 'x'=>$x-intval($this->fleet['width']/2+2),
						 'y'=>$y-intval($this->fleet['height']/2+2)
						 ),
			'bottom-right' => array(
						 'x'=>$x+intval($this->fleet['width']/2+2)-2,
						 'y'=>$y+intval($this->fleet['height']/2+2)-2
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

class NeutralScBox_drawMap extends ChangeFlag_drawMap
{
	/**
	* An array containing the XY-positions of the "neutral-SC-box" and 
	* the country-color it should be colored if it's still unoccupied.
	*
	* Format: terrID => array (countryID, smallmapx, smallmapy, mapx, mapy)
	**/
	protected $nsc_info=array(
		 3 => array( 2,  72, 242, 148, 488), // Eastern Spanish Netherlands
		13 => array( 7, 189, 222, 382, 448), // A of Cologne
		32 => array( 7, 404, 131, 812, 266), // P of Anhalt
		34 => array( 6, 395, 180, 794, 364), // A of Cologne
		50 => array( 7, 316, 317, 636, 638), // B of Wurzburg
		57 => array( 5, 564, 193,1132, 390), // D of Silesia
		60 => array( 1, 506, 471,1016, 946), // D of Styria		 
		66 => array( 7, 392, 532, 788,1068), // B of Trient		
		74 => array( 5, 231, 332, 466, 668), // P of Zweibrucken
		76 => array( 5, 276, 301, 556, 606), // Lower Electoral Palatinate
		87 => array( 7, 333, 440, 670, 884)  // B of Augsburg
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
		$this->sc = $this->loadImage('variants/Germany1648/resources/sc_'.($this->smallmap ? 'small' : 'large').'.png');	
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

class Germany1648Variant_drawMap extends NeutralScBox_drawMap {

	protected $countryColors = array(
		 0 =>  array(226, 198, 158), // Neutral
		 1 =>  array(196, 143, 133), // Austrian Habsburg
		 2 =>  array(234, 234, 175), // spanish Habsburg
		 3 =>  array(239, 196, 228), // Wettin
		 4 =>  array(114, 146, 103), // Bavarian Wittelsbach
		 5 =>  array(206, 153, 103), // Palatinate Wittelsbach
		 6 =>  array( 64, 108, 128), // Hohenzollern
		 7 =>  array(121, 175, 198), // Ecclesiastic Lands
		 8 =>  array(255,  20,  20), // Neutral units
	);

	// All images have transparend background already.
	protected function setTransparancy(array $image, array $color=array(255,255,255)) {}

	protected function resources()
	{
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/Germany1648/resources/smallmap.png',
				'army'    =>'variants/Germany1648/resources/smallarmy.png',
				'fleet'   =>'variants/Germany1648/resources/smallfleet.png',
				'names'   =>'variants/Germany1648/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'     =>'variants/Germany1648/resources/map.png',
				'army'    =>'variants/Germany1648/resources/army.png',
				'fleet'   =>'variants/Germany1648/resources/fleet.png',
				'names'   =>'variants/Germany1648/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}
}

?>
