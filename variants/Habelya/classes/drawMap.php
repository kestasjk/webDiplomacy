<?php

defined('IN_CODE') or die('This script can not be run by itself.');

// All order arrows needs adjustment for the warparound
class WarpAround_drawMap extends drawMap
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

class HabelyaVariant_drawMap extends WarpAround_drawMap {

	protected $countryColors = array(
		0 => array(226, 198, 158), // Neutral
  		1 => array(234, 234, 175), // Ruins of Holgii
		2 => array(121, 175, 198), // Gernavia
 		3 => array(120, 120, 120), // Old Bramia
  		4 => array(206, 153, 103), // Elenian Empire
  		5 => array(114, 146, 103), // Socialist Glock
  		6 => array(196, 143, 133), // Kingdom of Saltz
  		7 => array( 64, 108, 128), // The Hacklers
  		8 => array(168, 126, 159), // Trylika
 	);
	
	public function __construct($smallmap)
	{
		// Map is too big, so up the memory-limit
		parent::__construct($smallmap);
		if ( !$this->smallmap )
			ini_set('memory_limit',"32M");
	}

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/Habelya/resources/smallmap.png',
				'army'    =>'contrib/smallarmy.png',
				'fleet'   =>'contrib/smallfleet.png',
				'names'   =>'variants/Habelya/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'     =>'variants/Habelya/resources/map.png',
				'army'    =>'contrib/army.png',
				'fleet'   =>'contrib/fleet.png',
				'names'   =>'variants/Habelya/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}

}

?>