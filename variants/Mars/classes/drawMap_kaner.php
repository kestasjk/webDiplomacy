<?php

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

class MarsVariant_drawMap extends drawMap {

	protected $countryColors = array(
		0  => array(212, 169, 127), /* Neutral  */ 
		1  => array(239, 196, 228), /* Amazonia */
		2  => array(121, 175, 198), /* Mareotia  */
		3  => array(164, 196, 153), /* Noachtia  */
  		4  => array(160, 138, 117), /* Cydonia */
  		5  => array(196, 143, 133), /* Arkadia */
  		6  => array(234, 234, 175), /* Alborian */
	);

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/Mars/resources/smallmap.png',
				'army'    =>'contrib/smallarmy.png',
				'fleet'   =>'contrib/smallfleet.png',
				'names'   =>'variants/Mars/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
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

		}
		else
		{
			return array(
				'map'     =>'variants/Mars/resources/map.png',
				'army'    =>'contrib/army.png',
				'fleet'   =>'contrib/fleet.png',
				'names'   =>'variants/Mars/resources/mapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
	}

}

?>