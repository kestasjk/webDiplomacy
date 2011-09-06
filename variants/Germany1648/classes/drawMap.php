<?php

defined('IN_CODE')or die('This script can not be run by itself.');

class Germany1648Variant_drawMap extends drawMap {

	protected $countryColors = array(
		 0 =>  array(226, 198, 158), // Neutral
		 1 =>  array(196, 143, 133), // Austrian Habsburg
		 2 =>  array(234, 234, 175), // spanish Habsburg
		 3 =>  array(239, 196, 228), // Wettin
		 4 =>  array(114, 146, 103), // Bavarian Wittelsbach
		 5 =>  array(206, 153, 103), // Palatinate Wittelsbach
		 6 =>  array( 64, 108, 128), // Hohenzollern
		 7 =>  array(121, 175, 198), // Ecclesiastic Lands
		 8 =>  array(255,  20,  20), // Free Imperial Cities
	);


	/* An array containing the neutral support-center icon image resource, 
	*  and its width and height.
	*  $image['image'],['width'],['height']
	* 
	@var array
	*/
	protected $sc=array();

	/* An array containing the information if one of the territories 
	still has a neutral support-center (So we might not need to draw a
	flag)
	*/
	protected $nsc=array();

	// Load all the images and after that the neutral-box image
	protected function loadImages()
	{
		parent::loadImages();
		if ($this->smallmap )
			$this->sc = $this->loadImage('variants/Germany1648/resources/sc_small.png');
		else
			$this->sc = $this->loadImage('variants/Germany1648/resources/sc_large.png');
	}

	/* Set the transparent backgrounds for all the images and after that the
	background for the neutral-SC image */
	protected function setTransparancies()
	{
		parent::setTransparancies();
		$this->setTransparancy($this->sc);
	}

	/* There are territories on the map that belong to a country but have a supply-center that is considered "neutral"
	** They are set to owner "Neutral" in the installation-file and because of that would get drawn in the neutral-color
	** We need to check if they are still "neutal" and paint the territory in the color of the country they "should" belong to.
	** after that we draw the "neutral-sc-image" on the map.
	**
	** terrID is the ID of the territory, and if countryID is still 0 it's still neutral and we need to set the countryID to the owner
	** it should belong before we call the original paint routine. $sx and $sy are the coordinates the neutral-box is drawn
	** after we colored the territory
	*/
	public function ColorTerritory($terrID, $countryID)
	{
		if     ($terrID ==  3 && $countryID == 0 && $this->smallmap)  { $sx=  72; $sy= 242; $countryID=2; } // Eastern Spanish Netherlands
		elseif ($terrID ==  3 && $countryID == 0 && !$this->smallmap) { $sx= 148; $sy= 488; $countryID=2; } // Eastern Spanish Netherlands
		elseif ($terrID == 32 && $countryID == 0 && $this->smallmap)  { $sx= 404; $sy= 131; $countryID=7; } // A of Magdeburg
		elseif ($terrID == 32 && $countryID == 0 && !$this->smallmap) { $sx= 812; $sy= 266; $countryID=7; } // A of Magdeburg
		elseif ($terrID == 34 && $countryID == 0 && $this->smallmap)  { $sx= 395; $sy= 180; $countryID=6; } // P of Anhalt
		elseif ($terrID == 34 && $countryID == 0 && !$this->smallmap) { $sx= 794; $sy= 364; $countryID=6; } // P of Anhalt
		elseif ($terrID == 13 && $countryID == 0 && $this->smallmap)  { $sx= 189; $sy= 222; $countryID=7; } // A of Cologne
		elseif ($terrID == 13 && $countryID == 0 && !$this->smallmap) { $sx= 382; $sy= 448; $countryID=7; } // A of Cologne
		elseif ($terrID == 50 && $countryID == 0 && $this->smallmap)  { $sx= 316; $sy= 317; $countryID=7; } // B of Wurzburg
		elseif ($terrID == 50 && $countryID == 0 && !$this->smallmap) { $sx= 636; $sy= 638; $countryID=7; } // B of Wurzburg
		elseif ($terrID == 57 && $countryID == 0 && $this->smallmap)  { $sx= 564; $sy= 193; $countryID=1; } // D of Silesia
		elseif ($terrID == 57 && $countryID == 0 && !$this->smallmap) { $sx=1132; $sy= 390; $countryID=1; } // D of Silesia
		elseif ($terrID == 60 && $countryID == 0 && $this->smallmap)  { $sx= 506; $sy= 471; $countryID=1; } // D of Styria
		elseif ($terrID == 60 && $countryID == 0 && !$this->smallmap) { $sx=1016; $sy= 946; $countryID=1; } // D of Styria
		elseif ($terrID == 66 && $countryID == 0 && $this->smallmap)  { $sx= 392; $sy= 532; $countryID=7; } // B of Trient
		elseif ($terrID == 66 && $countryID == 0 && !$this->smallmap) { $sx= 788; $sy=1068; $countryID=7; } // B of Trient
		elseif ($terrID == 74 && $countryID == 0 && $this->smallmap)  { $sx= 231; $sy= 332; $countryID=5; } // P of Zweibrucken
		elseif ($terrID == 74 && $countryID == 0 && !$this->smallmap) { $sx= 466; $sy= 668; $countryID=5; } // P of Zweibrucken
		elseif ($terrID == 76 && $countryID == 0 && $this->smallmap)  { $sx= 276; $sy= 301; $countryID=5; } // Lower Electoral Palatinate
		elseif ($terrID == 76 && $countryID == 0 && !$this->smallmap) { $sx= 556; $sy= 606; $countryID=5; } // Lower Electoral Palatinate
		elseif ($terrID == 87 && $countryID == 0 && $this->smallmap)  { $sx= 333; $sy= 440; $countryID=7; } // B of Augsburg
		elseif ($terrID == 87 && $countryID == 0 && !$this->smallmap) { $sx= 670; $sy= 884; $countryID=7; } // B of Augsburg

		parent::ColorTerritory($terrID, $countryID);
		$this->nsc[$terrID]=0;

		// Did we set a sx? If yes we need to draw the box and set nsc of this terrID to 1
		// if nsc is 1 we know that territory is considered neutral, but is colored
		if (isset($sx))
		{
			$this->putImage($this->sc, $sx, $sy);
			$this->nsc[$terrID]=$countryID;
		}
	}


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

	// Here is where I tried to add the modified function!
	public function countryFlag($terrName, $countryID)
	{
		if ($this->nsc[$terrName] == $countryID) return;

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
