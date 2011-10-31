<?php
/*
	Copyright (C) 2011 Kaner406 / Oliver Auth

	This file is part of the KnownWorld_901 variant for webDiplomacy

	The KnownWorld_901 variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The KnownWorld_901 variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

// Transform command
class Transform_drawMap extends drawMap
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

class ZoomMap_drawMap extends Transform_drawMap
{
	// Always only load the largemap (as there is no smallmap)
	public function __construct($smallmap) {
		parent::__construct(false);
	}
	
	// Always use the small orderarrows...
	protected function loadOrderArrows() {
		$this->smallmap=true; parent::loadOrderArrows(); $this->smallmap=false;
	}	
	
	// Always use the small standoff-Icons
	public function drawStandoff($terrName) {
		$this->smallmap=true; parent::drawStandoff($terrName); $this->smallmap=false;
	}

	// Always use the small failure-cross...
	protected function drawFailure(array $from, array $to) {
		$this->smallmap=true; parent::drawFailure($from, $to); $this->smallmap=false;
	}
}

class ResetPaletteVariant_drawMap extends ZoomMap_drawMap
{
	public $countColor=0;
	public $setColors=false;

	protected function loadColors()
	{
		if ($this->setColors==true)
			$this->colors = array(
				'border'=>array(0,0,0),
				'standoff'=>array(200,20,20)
			);
		parent::loadColors();
	}
	
	protected function loadOrderArrows()
	{
		if ($this->setColors==true)
			parent::loadOrderArrows();
	}
	
	// After 220 territories reset the palette, so there is enough color for the units left.
	public function colorTerritory($terrID, $countryID)
	{
		$this->countColor++;
		if ($this->countColor == 220)
		{
			$im = imagecreate($this->map['width'],$this->map['height']);
			imagecopy($im, $this->map['image'], 0, 0, 0, 0, $this->map['width'],$this->map['height']);
			imagedestroy($this->map['image']);
			$this->map['image']=$im;
			$this->setColors=true;
			$this->loadColors();
			$this->loadOrderArrows();
		}
		parent::colorTerritory($terrID, $countryID);
	}
}

class KnownWorld_901Variant_drawMap extends ResetPaletteVariant_drawMap {

	public function __construct($smallmap)
	{
		// Map is too big, so up the memory-limit
		parent::__construct($smallmap);
		ini_set('memory_limit',"35M");
	}

	protected $countryColors = array(
		0  => array(226, 198, 158), /* Neutral   */
		1  => array(239, 196, 228), /* Arabia    */
		2  => array(121, 175, 198), /* Axum      */
		3  => array(164, 196, 153), /* Byzantium */
  		4  => array(160, 138, 117), /* China     */
  		5  => array(196, 143, 133), /* Denmark   */
  		6  => array(234, 234, 175), /* Egypt     */
  		7  => array(168, 126, 159), /* France    */
  		8  => array( 64, 108, 128), /* Germany   */
  		9  => array(206, 153, 103), /* Khazaria  */
  		10 => array(115, 113,  14), /* Russia    */
  		11 => array(114, 146, 103), /* Spain     */
  		12 => array(120, 120, 120), /* Turan     */
  		13 => array(140, 186,  28), /* Srivijaya */
  		14 => array( 10, 122, 124), /* Wagadu    */
  		15 => array(222,  91,  91), /* India     */
  		16 => array(226, 198, 158)  /* Neutral units */
	);

	// Move the countryflag.
	public function countryFlag($terrID, $countryID)
	{
		list($x, $y) = $this->territoryPositions[$terrID];
		$this->territoryPositions[0] = array($x,$y+$this->fleet['width']/2+5);
		$save = $this->fleet;
		$this->fleet = array('width'=>$this->fleet['width']*1.3, 'height'=>$this->fleet['height']*1.3);
		parent::countryFlag(0, $countryID);
		$this->fleet = $save;
	}

	// No need to set the transparency for our custom icons.
	protected function setTransparancies() {}

	protected function resources() {
		return array(
			'map'     =>'variants/KnownWorld_901/resources/map.png',
			'army'    =>'variants/KnownWorld_901/resources/army.png',
			'fleet'   =>'variants/KnownWorld_901/resources/fleet.png',
			'names'   =>'variants/KnownWorld_901/resources/mapNames.png',
			'standoff'=>'images/icons/cross.png'
		);
	}

}

?>