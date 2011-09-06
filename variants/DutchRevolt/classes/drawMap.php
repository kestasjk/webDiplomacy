<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class DutchRevoltVariant_drawMap extends drawMap {

	protected $countryColors = array(
		0 => array(226, 198, 158), /* Neutral */
		1 => array(239,  196,228), /* England */
		2 => array(121, 175, 198), /* France  */
		3 => array(253, 238,   0), /* Spain   */
		4 => array(160, 138, 117), /* Munster */
		5 => array(255, 126,   0)  /* Holland */
	);

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/DutchRevolt/resources/smallmap.png',
				'names'   =>'variants/DutchRevolt/resources/smallmapNames.png',
				'army'    =>'contrib/smallarmy.png',
				'fleet'   =>'contrib/smallfleet.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'     =>'variants/DutchRevolt/resources/map.png',
				'names'   =>'variants/DutchRevolt/resources/mapNames.png',
				'army'    =>'contrib/army.png',
				'fleet'   =>'contrib/fleet.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}
	
	public function __construct($smallmap)
	{
		global $Game;

		$this->mapID = MAPID;

		$this->smallmap = (bool) $smallmap;

		if ( !$this->smallmap )
			ini_set('memory_limit',"32M");

		$this->loadTerritories();
		$this->loadImages();
		$this->loadColors();
		$this->setTransparancies();
		$this->loadFont();
		$this->loadOrderArrows();
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

	public function ColorTerritory($terrID, $countryID)	
	{
		if ($terrID == 9) return;
		parent::ColorTerritory($terrID, $countryID);
	}
	
}
?>