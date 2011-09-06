<?php

defined('IN_CODE') or die('This script can not be run by itself.');

class DuoVariant_drawMap extends drawMap {

	protected $countryColors = array(
		0 => array(226, 198, 158), /* Neutral */
		1 => array(196, 143, 133), /* Red     */
		2 => array(164, 196, 153), /* Green   */
		3 => array(136, 136, 136), /* Black   */		
	);

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/Duo/resources/smallmap.png',
				'army'    =>'contrib/smallarmy.png',
				'fleet'   =>'contrib/smallfleet.png',
				'names'   =>'variants/Duo/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'     =>'variants/Duo/resources/map.png',
				'army'    =>'contrib/army.png',
				'fleet'   =>'contrib/fleet.png',
				'names'   =>'variants/Duo/resources/mapNames.png',
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
		if ($colorRes == -1)
			$colorRes = imageColorAllocate($image['image'], $r, $g, $b);

 		return $colorRes;
 	}
	
	public function drawMove($fromTerrID, $toTerrID, $success)
	{
		if ($fromTerrID == $toTerrID) {
			$this->drawTransform($fromTerrID, $success);
		} else {
			parent::drawMove($fromTerrID, $toTerrID, $success);
		}
	}

	protected function drawTransform($terrID, $success)
	{
		$darkblue  = $this->color(array(40, 80,130));
		$lightblue = $this->color(array(70,150,230));

		list($x, $y) = $this->territoryPositions[$terrID];

		$width=($this->fleet['width'])+($this->fleet['width'])/2;
		
		imagefilledellipse ( $this->map['image'], $x, $y, $width, $width, $darkblue);
		imagefilledellipse ( $this->map['image'], $x, $y, $width-2, $width-2, $lightblue);
		
		if ( !$success ) $this->drawFailure(array($x-1, $y),array($x+2, $y));
	}	
	


}

?>