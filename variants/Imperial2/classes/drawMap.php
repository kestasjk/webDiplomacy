<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the Imperial2 variant for webDiplomacy

	The Imperial2 variant for webDiplomacy is free software:
	you can redistribute it and/or modify it under the terms of the GNU Affero
	General Public License as published by the Free Software Foundation, either
	version 3 of the License, or (at your option) any later version.

	The Imperial2 variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
	If you have questions or suggestions send me a mail: Oliver.Auth@rhoen.de

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class MultiLayerMap_drawMap extends drawMap
{
	// A place to store the 2nd part of the map
	protected $map2 = array();

	// Load the 2nd part of the map.
	protected function loadImages()
	{
		ini_set('memory_limit',"50M");
		$this->map2 = $this->loadImage('variants/Imperial2/resources/map_2.png');
		parent::loadImages();
	}
	
	// The territories that get colored on the 2nd map have colorID=0 (transparent) or 1 (water)
	public function colorTerritory($terrID, $countryID)
	{
		list($x, $y) = $this->territoryPositions[$terrID];

		if (imagecolorat($this->map['image'], $x, $y) > 1 )
			return parent::colorTerritory($terrID, $countryID);

		list($this->map['image'], $this->map2['image']) = array($this->map2['image'], $this->map['image'] );
		parent::colorTerritory($terrID, $countryID);
		list($this->map['image'], $this->map2['image']) = array($this->map2['image'], $this->map['image'] );
	}
	
	// Combine the 2 maps.
	public function write($filename)
	{
		$w = $this->map['width'];
		$h = $this->map['height'];
		$im = imagecreate($this->map['width'], $this->map['height']);
		imagecopyresampled($im, $this->map2['image'], 0, 0, 0, 0, $w, $h, $w, $h);
		imagecopyresampled($im, $this->map['image'], 0, 0, 0, 0, $w, $h, $w, $h);
		imagetruecolortopalette($im, true, 256);
		$this->map['image']=$im;

		parent::write($filename);
	}

}

class ColorUnits_drawMap extends MultiLayerMap_drawMap
{

	protected $countryIconColors = array(
		 0 => array(200, 170, 100), // Neutral
		 1 => array(255,  43,   0), // Austria
		 2 => array(  0, 255, 128), // Brazil
		 3 => array(  0, 128, 255), // Britain
		 4 => array(255, 170,   0), // China
		 5 => array(170, 128,   0), // CSA
		 6 => array(255, 128, 213), // France
		 7 => array(  0,  85, 170), // Holland
		 8 => array(213, 213,   0), // Japan
		 9 => array(255, 128,  85), // Mexico
		10 => array(213, 213, 255), // Prussia
		11 => array( 85,   0, 255), // Russia
		12 => array(128, 128, 128), // Turkey
		13 => array(  0,  85,  85)  // USA
	);
	
	public function addUnit($terrName, $unitType)
	{
	
		list($r, $g, $b) = $this->countryIconColors[$this->unit_c[$terrName]];
		imagecolorset($this->{strtolower($unitType)}['image'],0, $r, $g, $b);
		parent::addUnit($terrName, $unitType);
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
	
	public function countryFlag($terrName, $countryID)	
	{
		$this->unit_c[$terrName]=$countryID;
	}

}

// All order arrows needs adjustment for the warparound
class WarpAround_drawMap extends ColorUnits_drawMap
{
	private function WrapArrowsX($fromTerr, $toTerr, $terr=0) 
	{
		list($startX, $startY) = $this->territoryPositions[$fromTerr];
		list($endX  , $endY  ) = $this->territoryPositions[$toTerr];
		
		if (abs($startX-$endX) > $this->map['width'] * 1/2)
		{
			$leftX = ($startX<$endX?$startX:$endX);
			$leftY = ($startX<$endX?$startY:$endY);
			$rightX = ($startX>$endX?$startX:$endX);
			$rightY = ($startX>$endX?$startY:$endY);
			$drawToLeftX = 0;
			$drawToRightX = $this->map['width'];
			// Ratio of diff(left side and left x) and diff (right side and right x)
			$ratioLeft = $leftX / ($leftX + $drawToRightX - $rightX);
			$ratioRight = 1.0 - $ratioLeft;
			if ($leftY > $rightY) { // Downward slope
				$drawToLeftY = $leftY - (abs($leftY-$rightY) * $ratioLeft);
				$drawToRightY = $rightY + (abs($leftY-$rightY) * $ratioRight);
			} else { // Upward Slope
				$drawToLeftY = $leftY + (abs($leftY-$rightY) * $ratioLeft);
				$drawToRightY = $rightY - (abs($leftY-$rightY) * $ratioRight);
			}
			if ($startX == $leftX) {
				$this->territoryPositions['WarpFrom1']= array ($leftX       ,$leftY       );
				$this->territoryPositions['WarpTo1']  =	array ($drawToLeftX ,$drawToLeftY );
				$this->territoryPositions['WarpFrom2']=	array ($drawToRightX,$drawToRightY);
				$this->territoryPositions['WarpTo2']  =	array ($rightX      ,$rightY      );
			} else  {
				$this->territoryPositions['WarpFrom1']=	array ($drawToLeftX ,$drawToLeftY );
				$this->territoryPositions['WarpTo1']  =	array ($leftX       ,$leftY       );
				$this->territoryPositions['WarpFrom2']= array ($rightX      ,$rightY      );
				$this->territoryPositions['WarpTo2']  =	array ($drawToRightX,$drawToRightY);
			}
		} else {
			$this->territoryPositions['WarpFrom1'] = $this->territoryPositions[$fromTerr];
			$this->territoryPositions['WarpTo1']   = $this->territoryPositions[$toTerr];
			$this->territoryPositions['WarpFrom2'] = $this->territoryPositions[$fromTerr];
			$this->territoryPositions['WarpTo2']   = $this->territoryPositions[$toTerr];
		}
		// If I have a support-move or convoy
		if ($terr != 0)
		{
			// If I have two arrows check which one to point to:
			if ($this->territoryPositions['WarpFrom1'] != $this->territoryPositions['WarpFrom2'])
			{		
				list($unitX, $unitY) = $this->territoryPositions[$terr];
				$dist1 = abs($unitX - $leftX)  + abs($unitY - $leftY)  + abs($unitX - $drawToLeftX)  + abs($unitY - $drawToLeftY);
				$dist2 = abs($unitX - $rightX) + abs($unitY - $rightY) + abs($unitX - $drawToRightX) + abs($unitY - $drawToRightY);

				if ($dist1 < $dist2) {
					$this->territoryPositions['WarpFrom2'] = $this->territoryPositions['WarpFrom1'];
					$this->territoryPositions['WarpTo2']   = $this->territoryPositions['WarpTo1'];
				} else {
					$this->territoryPositions['WarpFrom1'] = $this->territoryPositions['WarpFrom2'];
					$this->territoryPositions['WarpTo1']   = $this->territoryPositions['WarpTo2'];
				}
				$this->territoryPositions['WarpTerr1'] = $this->territoryPositions[$terr];
				$this->territoryPositions['WarpTerr2'] = $this->territoryPositions[$terr];
			}
			// Maybe the Support/Convoy arrow needs to be split too...
			else
			{
				$this->territoryPositions['SupTo'][0] = $endX - ( $endX - $startX ) / 3;
				$this->territoryPositions['SupTo'][1] = $endY - ( $endY - $startY ) / 3;
				$this->WrapArrowsX($terr, 'SupTo');
				$this->territoryPositions['WarpTerr1'] = $this->territoryPositions['WarpFrom1'];
				$this->territoryPositions['WarpFrom1'] = $this->territoryPositions['WarpTo1'];
				$this->territoryPositions['WarpTo1']   = $this->territoryPositions['WarpTo1'];
				$this->territoryPositions['WarpTerr2'] = $this->territoryPositions['WarpFrom2'];
				$this->territoryPositions['WarpFrom2'] = $this->territoryPositions['WarpTo2'];
				$this->territoryPositions['WarpTo2']   = $this->territoryPositions['WarpTo2'];	
			}
		}
	}

	public function drawSupportMove($terr, $fromTerr, $toTerr, $success)
	{		
		$this->WrapArrowsX($fromTerr, $toTerr, $terr);
		parent::drawSupportMove('WarpTerr1', 'WarpFrom1', 'WarpTo1', $success);
		parent::drawSupportMove('WarpTerr2', 'WarpFrom2', 'WarpTo2', $success);
	}
	
	public function drawConvoy($terr, $fromTerr, $toTerr, $success)
	{		
		$this->WrapArrowsX($fromTerr, $toTerr, $terr);
		parent::drawConvoy('WarpTerr1', 'WarpFrom1', 'WarpTo1', $success);
		parent::drawConvoy('WarpTerr2', 'WarpFrom2', 'WarpTo2', $success);
	}

	public function drawMove($fromTerr, $toTerr, $success)
	{
		$this->WrapArrowsX($fromTerr, $toTerr);
		parent::drawMove('WarpFrom1','WarpTo1', $success);
		parent::drawMove('WarpFrom2','WarpTo2', $success);
	}
	
	public function drawRetreat($fromTerr, $toTerr, $success)
	{
		$this->WrapArrowsX($fromTerr, $toTerr);
		parent::drawRetreat('WarpFrom1','WarpTo1', $success);
		parent::drawRetreat('WarpFrom2','WarpTo2', $success);
	}

	public function drawSupportHold($fromTerr, $toTerr, $success)
	{
		$this->WrapArrowsX($fromTerr, $toTerr);			
		parent::drawSupportHold('WarpFrom1','WarpTo1', $success);
		parent::drawSupportHold('WarpFrom2','WarpTo2', $success);	
	}
}

class Imperial2Variant_drawMap extends WarpAround_drawMap {

	protected $countryColors = array(
		 0 => array(255, 213, 128), // Neutral
		 1 => array(170,  43,   0), // Austria
		 2 => array( 85, 170,   0), // Brazil
		 3 => array(  0,  43, 213), // Britain
		 4 => array(255, 255,   0), // China
		 5 => array(170, 170,  85), // CSA
		 6 => array(255,  85,  85), // France
		 7 => array(  0, 170, 255), // Holland
		 8 => array(128, 128,   0), // Japan
		 9 => array(213, 128,   0), // Mexico
		10 => array(  0,  85, 128), // Prussia
		11 => array(128, 128, 255), // Russia
		12 => array(170, 170, 170), // Turkey
		13 => array(  0, 128,  85)  // USA
	);
	
	public function __construct($smallmap)
	{
		// Map is too big, so up the memory-limit
		parent::__construct(false);
		ini_set('memory_limit',"500M");
	}

	protected function resources() {
		return array(
			'map'=>'variants/Imperial2/resources/map.png',
			'army'=>'variants/Imperial2/resources/Army.png',
			'fleet'=>'variants/Imperial2/resources/Fleet.png',
			'names'=>'variants/Imperial2/resources/mapNames.png',
			'standoff'=>'images/icons/cross.png'
		);
	}
	
	// Always use the small orderarrows...
	protected function loadOrderArrows()
	{
		$this->smallmap=true;
		parent::loadOrderArrows();
		$this->smallmap=false;
	}	
	
	// Always use the small standoff-Icons
	public function drawStandoff($terrName)
	{
		$this->smallmap=true;
		parent::drawStandoff($terrName);
		$this->smallmap=false;
	}

	// Always use the small failure-cross...
	protected function drawFailure(array $from, array $to)
	{
		$this->smallmap=true;
		parent::drawFailure($from, $to);
		$this->smallmap=false;
	}
	
}

?>