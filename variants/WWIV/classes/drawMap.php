<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the WWIV variant for webDiplomacy

	The WWIV variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The WWIV variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class MoveFlags_drawMap extends drawMap
{
	public function countryFlag($terrID, $countryID)
	{
		list($x, $y) = $this->territoryPositions[$terrID];
		$this->territoryPositions[0] = array($x,$y+$this->fleet['width']/2-1);
		$save = $this->fleet;
		$this->fleet = array('width'=>$this->fleet['width']*1.3, 'height'=>$this->fleet['height']*1.3);
		parent::countryFlag(0, $countryID);
		$this->fleet = $save;
	}
}

class CustomCountryIcons_drawMap extends MoveFlags_drawMap
{
	// Arrays for the custom icons:
	protected $unit_c =array(); // An array to store the owner of each territory
	protected $army_c =array(); // Custom army icons
	protected $fleet_c=array(); // Custom fleet icons

	// Load custom icons (fleet and army) for each country
	protected function loadImages()
	{
		for ($i=1; $i<=count($GLOBALS['Variants'][VARIANTID]->countries); $i++) {
			$this->army_c[$i]  = $this->loadImage('variants/WWIV/resources/army' .$GLOBALS['Variants'][VARIANTID]->countries[$i-1].'.png');
			$this->fleet_c[$i] = $this->loadImage('variants/WWIV/resources/fleet'.$GLOBALS['Variants'][VARIANTID]->countries[$i-1].'.png');
		}
		parent::loadImages();
	}
	
	// Save the countryID for every colored Territory (and their coasts)
	public function colorTerritory($terrID, $countryID)
	{
		$terrName=$this->territoryNames[$terrID];
		$this->unit_c[$terrID]=$countryID;
		$this->unit_c[array_search($terrName. " (North Coast)" ,$this->territoryNames)]=$countryID;
		$this->unit_c[array_search($terrName. " (East Coast)"  ,$this->territoryNames)]=$countryID;
		$this->unit_c[array_search($terrName. " (South Coast)" ,$this->territoryNames)]=$countryID;
		$this->unit_c[array_search($terrName. " (West Coast)"  ,$this->territoryNames)]=$countryID;
		parent::colorTerritory($terrID, $countryID);
	}
	
	// Store the country if a unit needs to draw a flag for a custom icon.
	public function countryFlag($terrName, $countryID)
	{
		$this->unit_c[$terrName]=$countryID;
		parent::countryFlag($terrName, $countryID);
	}
	
	// Draw the custom icons:
	public function addUnit($terrID, $unitType)
	{
		$this->army  = $this->army_c[$this->unit_c[$terrID]];
		$this->fleet = $this->fleet_c[$this->unit_c[$terrID]];
		parent::addUnit($terrID, $unitType);
	}
	
}

class MultiLayerMap_drawMap extends CustomCountryIcons_drawMap
{

	// A place to store the 2nd part of the map
	protected $map2 = array();

	// Load the 2nd part of the map.
	protected function loadImages()
	{
		ini_set('memory_limit',"50M");
		$this->map2 = $this->loadImage('variants/WWIV/resources/map_2.png');
		parent::loadImages();
	}
	
	// The territories that get colored on the 2nd map have colorID=1 and are set to transparent
	public function colorTerritory($terrID, $countryID)
	{
		list($x, $y) = $this->territoryPositions[$terrID];

		if (imagecolorat($this->map['image'], $x, $y) != 0)
			return parent::colorTerritory($terrID, $countryID);

		$mapsave=$this->map['image'];
		$this->map['image']=$this->map2['image'];
		parent::colorTerritory($terrID, $countryID);
		$this->map2['image']=$this->map['image'];
		$this->map['image']=$mapsave;
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

class ZoomMap_drawMap extends MultiLayerMap_drawMap
{
	// Always only load the largemap (as there is no smallmap)
	public function __construct($smallmap)
	{
		parent::__construct(false);
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

// All order arrows needs adjustment for the warparound
class WarpAround_drawMap extends ZoomMap_drawMap
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

class WWIVVariant_drawMap extends WarpAround_drawMap {

	protected $countryColors = array(
  		 0=> array(226, 198, 158), // Neutral
		 1=> array( 10,  35, 192), // Amazon-Empire 
		 2=> array(196, 150,  18), // Argentina     
		 3=> array( 10,  49, 255), // Australia     
		 4=> array(111,  13,   3), // Brazil        
		 5=> array(109,  19, 103), // California    
		 6=> array( 81,  81,   0), // Canada        
		 7=> array(  0, 200,  28), // Catholica     
		 8=> array(  0, 250,  44), // Central-Asia  
		 9=> array(185, 185, 185), // Colombia      
		10=> array(215,  57,  17), // Congo         
		11=> array(255, 156,   0), // Cuba          
		12=> array(255, 253,  51), // Egypt         
		13=> array(235,  83, 233), // Germany       
		14=> array(254, 254, 254), // Illinois      
		15=> array(251,  51, 131), // Inca-Empire   
		16=> array(115, 113,  14), // India         
		17=> array( 71, 251, 151), // Indonesia     
		18=> array(255, 145, 214), // Iran          
		19=> array( 71, 151, 251), // Japan         
		20=> array(  0, 182, 184), // Kenya         
		21=> array(  0, 122, 124), // Manchuria     
		22=> array(140, 186,  28), // Mexico        
		23=> array(104, 104, 104), // Nigeria       
		24=> array(197, 251,  67), // Oceania       
		25=> array(255,   0,   0), // Philippines   
		26=> array(  0, 101,  11), // Quebec        
		27=> array(162, 166, 254), // Russia        
		28=> array(255, 106, 109), // Sichuan-Empire
		29=> array(183,  96,  10), // Song-Empire   
		30=> array(  0, 181, 107), // South-Africa  
		31=> array( 90,  96, 173), // Texas         
		32=> array(195,  35, 104), // Thailand      
		33=> array( 20,  20, 162), // Turkey        
		34=> array(235, 196,  58), // United-Kingdom
		35=> array(186, 183, 108)  // United-States 
	);

	// The resources (map and default icons)
	protected function resources() {
		return array(
			'map'     =>'variants/WWIV/resources/map.png',
			'names'   =>'variants/WWIV/resources/mapNames.png',
			'army'    =>'variants/WWIV/resources/armyCongo.png',
			'fleet'   =>'variants/WWIV/resources/fleetCongo.png',
			'standoff'=>'images/icons/cross.png'
		);
	}
	
	// Better color function for all the different colors.
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
	
}

?>