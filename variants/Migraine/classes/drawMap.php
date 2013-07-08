<?php
/*
	Copyright (C) 2010 Carey Jensen / Oliver Auth

	This file is part of the Migraine variant for webDiplomacy

	The Migraine variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Migraine variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General 
	Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
		
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class MigraineVariant_drawMap extends drawMap {

	protected $countryColors = array(
		0 => array(226, 198, 158), /* Neutral */
		1 => array(164, 196, 153), /* Italy   */  /* Beta    */
		2 => array(121, 175, 198), /* France  */  /* Delta   */
		3 => array( 64, 108, 128), /* Balkan  */  /* Gamma   */
		4 => array(196, 143, 133), /* Austria */  /* Kappa   */
		5 => array(206, 153, 103), /* Lowland */  /* Lambda  */
		6 => array(234, 234, 175), /* Turkey  */  /* Sigma   */
		7 => array(168, 126, 159), /* Russia  */  /* Theta   */
		8 => array(114, 146, 103), /* Norway  */  /* Zeta    */
	);

	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'     =>'variants/Migraine/resources/smallmap.png',
				'army'    =>'variants/Migraine/resources/smallarmy.png',
				'fleet'   =>'variants/Migraine/resources/smallfleet.png',
				'names'   =>'variants/Migraine/resources/smallmapNames.png',
				'standoff'=>'images/icons/cross.png'
			);
		}
		else
		{
			return array(
				'map'     =>'variants/Migraine/resources/map.png',
				'army'    =>'variants/Migraine/resources/army.png',
				'fleet'   =>'variants/Migraine/resources/fleet.png',
				'names'   =>'variants/Migraine/resources/mapNames.png',
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
	
	// Draw the flags behind the units for a better readability
	public function countryFlag($terrName, $countryID)
	{
		$flagBlackback = $this->color(array(0, 0, 0));

		$flagColor = $this->color($this->countryColors[$countryID]);

		list($x, $y) = $this->territoryPositions[$terrName];

		$coordinates = array(
			'top-left' => array( 
							'x'=>$x-intval($this->fleet['width']/2+2)+1,
							'y'=>$y-intval($this->fleet['height']/2+2)+1
							),
			'bottom-right' => array(
							'x'=>$x+intval($this->fleet['width']/2+2)-1,
							'y'=>$y+intval($this->fleet['height']/2+2)-1
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

	// Draw 2 lines if a unit moves from one side of the map to the other (function made by gilgatex)
	private function getWrapLines($startX, $startY, $endX, $endY) 
	{
		$lines = array();
		if (abs($startY-$endY) > ($this->map['height'] * 1/2) && abs($startX-$endX) > ($this->map['width'] * 1/2))
		{
			// Now the hard part... wrapping both
			$leftX = ($startX<$endX?$startX:$endX);
			$leftY = ($startX<$endX?$startY:$endY);
			$rightX = ($startX>$endX?$startX:$endX);
			$rightY = ($startX>$endX?$startY:$endY);
			$topX = ($startY<$endY?$startX:$endX);
			$topY = ($startY<$endY?$startY:$endY);
			$bottomX = ($startY>$endY?$startX:$endX);
			$bottomY = ($startY>$endY?$startY:$endY);
			$ratioTop = $topY / ($this->map['height'] - abs($topY-$bottomY));
			$ratioBottom = ($this->map['height']-$bottomY) / ($this->map['height'] - abs($topY-$bottomY));
			$ratioLeft = $leftX / ($this->map['width'] - abs($leftX-$rightX));
			$ratioRight = ($this->map['width']-$rightX) / ($this->map['height'] - abs($leftX-$rightX));

			$slope = abs($startY-$endY)/abs($startX-$endX);
			if ($bottomX == $rightX) {
				if ($slope > 1) {
					$topLeftX = $topX - (abs($topX-$bottomX) * $ratioTop);
					$topLeftY = 0;
					$bottomRightX = $bottomX + (abs($topX-$bottomX) * $ratioBottom);
					$bottomRightY = $this->map['height'];
				} else {
					$topLeftX = 0;
					$topLeftY = $leftY - (abs($leftY-$rightY) * $ratioLeft);
					$bottomRightX = $this->map['width'];
					$bottomRightY = $bottomX + (abs($topX-$bottomX) * $ratioBottom);
				}
				if ($bottomX == $startX) {
					$lines[] = (array("x1"=>$startX, "y1"=>$startY, "x2"=>$bottomRightX, "y2"=>$bottomRightY));
					$lines[] = (array("x1"=>$topLeftX, "y1"=>$topLeftY, "x2"=>$endX, "y2"=>$endY));
				} else {
					$lines[] = (array("x1"=>$startX, "y1"=>$startY, "x2"=>$topLeftX, "y2"=>$topLeftY));
					$lines[] = (array("x1"=>$bottomRightX, "y1"=>$bottomRightY, "x2"=>$endX, "y2"=>$endY));
				}
			} else {
				if ($slope > 1) {
					$topRightX = $topX + (abs($topX-$bottomX) * $ratioTop);
					$topRightY = 0;
					$bottomLeftX = $bottomX - (abs($topX-$bottomX) * $ratioBottom);
					$bottomLeftY = $this->map['height'];
				} else {
					$topRightX = $this->map['width'];
					$topRightY = $rightY - (abs($leftY-$rightY) * $ratioRight);
					$bottomLeftX = 0;
					$bottomLeftY = $leftY + (abs($leftY-$rightY) * $ratioLeft);
				}
				if ($bottomX == $startX) {
					$lines[] = (array("x1"=>$startX, "y1"=>$startY, "x2"=>$bottomLeftX, "y2"=>$bottomLeftY));
					$lines[] = (array("x1"=>$topRightX, "y1"=>$topRightY, "x2"=>$endX, "y2"=>$endY));
				} else {
					$lines[] = (array("x1"=>$startX, "y1"=>$startY, "x2"=>$topRightX, "y2"=>$topRightY));
					$lines[] = (array("x1"=>$bottomLeftX, "y1"=>$bottomLeftY, "x2"=>$endX, "y2"=>$endY));
				}
			}
		}
		else
		{
			$lines[] = (array("x1"=>$startX, "y1"=>$startY, "x2"=>$endX, "y2"=>$endY));
		}
		return $lines;
	}

	public function drawSupportMove($terr, $fromTerr, $toTerr, $success)
	{
		global $Game;

		// Our toX and toY are 1/3 of the way between the two territories
		list($fromX, $fromY) = $this->territoryPositions[$fromTerr];
		list($toX, $toY) = $this->territoryPositions[$toTerr];

		$lines = $this->getWrapLines($fromX, $fromY, $toX, $toY);

		if (count($lines) > 1) {
			list($unitX, $unitY) = $this->territoryPositions[$terr];
			// Get possible edge coordinates
			$x1 = $lines[0]['x2'];
			$y1 = $lines[0]['y2'];
			$x2 = $lines[1]['x1'];
			$y2 = $lines[1]['y1'];

			// Draw to the nearest edge
			if (sqrt(abs($unitX-$x1)+abs($unitY-$y1)) > sqrt(abs($unitX-$x2)+abs($unitY-$y2))) {
				$toX = $x2;
				$toY = $y2;
			} else {
				$toX = $x1;
				$toY = $y1;
			}
		} else {
			$toX -= ( $toX - $fromX ) / 3;
			$toY -= ( $toY - $fromY ) / 3;
		}
			
		list($fromX, $fromY) = $this->territoryPositions[$terr];

		$this->drawOrderArrow(array($fromX, $fromY), array($toX, $toY), 'Support move', true);
		
		if ( !$success ) $this->drawFailure(array($fromX, $fromY), array($toX, $toY));
	}
	
	/**
	 * Draw a convoy arrow
	 * @param string $terr Territory convoying from
	 * @param string $fromTerr Territory convoyed unit convoyed from
	 * @param string $toTerr Territory convoyed unit convoyed to
	 * @param bool $success Convoy successful or not
	 */
	public function drawConvoy($terr, $fromTerr, $toTerr, $success)
	{
		//if ( $this->smallmap and !$success ) return;
		
		// Our toX and toY are 1/3 of the way between the two territories
		list($fromX, $fromY) = $this->territoryPositions[$fromTerr];
		list($toX, $toY) = $this->territoryPositions[$toTerr];
		
		$lines = $this->getWrapLines($fromX, $fromY, $toX, $toY);

		if (count($lines) > 1) {
			$toY = $lines[1]['y1'];

			list($unitX, $unitY) = $this->territoryPositions[$terr];

			if ($unitX > $this->map['width'] / 2 ) {
				$toX = ($lines[1]['x1'] > $lines[1]['x2'] ? $lines[1]['x1'] : $lines[1]['x2']);
			} else {
				$toX = ($lines[1]['x1'] > $lines[1]['x2'] ? $lines[1]['x2'] : $lines[1]['x1']);
			}
		} else {
			$toX -= ( $toX - $fromX ) / 3;
			$toY -= ( $toY - $fromY ) / 3;
		}
			
		list($fromX, $fromY) = $this->territoryPositions[$terr];

		$this->drawOrderArrow(array($fromX, $fromY), array($toX, $toY), 'Convoy', true);
		
		if ( !$success ) $this->drawFailure(array($fromX, $fromY), array($toX, $toY));
	}
	
	/**
	 * Draw a move arrow
	 * @param string $fromTerr Territory moving unit moved from
	 * @param string $toTerr Territory moving unit moved to
	 * @param bool $success Move successful or not
	 */
	public function drawMove($fromTerr, $toTerr, $success)
	{
		list($fromX, $fromY) = $this->territoryPositions[$fromTerr];
		list($toX, $toY) = $this->territoryPositions[$toTerr];
		
		$lines = $this->getWrapLines($fromX, $fromY, $toX, $toY);

		foreach ($lines as $key => $line)
		{
			$tail = ($key+1 == count($lines));

			$this->drawOrderArrow(array($line['x1'], $line['y1']), array($line['x2'], $line['y2']), 'Move', $tail);
		}
		
		// Draw failure based on the last line (the line that has the tail)
		if ( !$success ) $this->drawFailure(
			array($lines[count($lines)-1]['x1'], $lines[count($lines)-1]['y1']), 
			array($lines[count($lines)-1]['x2'], $lines[count($lines)-1]['y2'])
		);		
	}
	
	/**
	 * Draw a retreat arrow
	 * @param string $fromTerr Retreating from
	 * @param string $toTerr Retreating to
	 * @param bool $success Retreat successful or not
	 */
	public function drawRetreat($fromTerr, $toTerr, $success)
	{
		list($fromX, $fromY) = $this->territoryPositions[$fromTerr];
		list($toX, $toY) = $this->territoryPositions[$toTerr];
		
		// Rotate the arrow slightly, so that head-to-heads are more clear
		//list($fromX, $fromY, $toX, $toY) = self::rotate(array($fromX, $fromY, $toX, $toY), 
		//	array($fromX-($fromX-$toX)/2, $fromY-($fromY-$toY)/2), M_PI/15); 
		
		$lines = $this->getWrapLines($fromX, $fromY, $toX, $toY);

		foreach ($lines as $key => $line)
		{
			$tail = ($key+1 == count($lines));

			$this->drawOrderArrow(array($line['x1'], $line['y1']), array($line['x2'], $line['y2']), 'Retreat', $tail);
		}
		
		// Draw failure based on the last line (the line that has the tail)
		if ( !$success ) {
			$this->drawFailure(
				array($lines[count($lines)-1]['x1'], $lines[count($lines)-1]['y1']), 
				array($lines[count($lines)-1]['x2'], $lines[count($lines)-1]['y2'])
			);	
			$this->drawDestroyedUnit($fromTerr);
		}
	}

	/**
	 * Draw a support hold arrow
	 * @param string $fromTerr Territory supporting unit supporting from
	 * @param string $toTerr Territory supporting unit supported to
	 * @param bool $success Support successful or not
	 */
	public function drawSupportHold($fromTerr, $toTerr, $success)
	{
		
		list($fromX, $fromY) = $this->territoryPositions[$fromTerr];
		list($toX, $toY) = $this->territoryPositions[$toTerr];

		$lines = $this->getWrapLines($fromX, $fromY, $toX, $toY);

		foreach ($lines as $key => $line)
		{
			$tail = ($key+1 == count($lines));
			$this->drawOrderArrow(array($line['x1'], $line['y1']), array($line['x2'], $line['y2']), 'Support hold', $tail);
		}
		
		// Draw failure based on the last line (the line that has the tail)
		if ( !$success ) $this->drawFailure(
			array($lines[count($lines)-1]['x1'], $lines[count($lines)-1]['y1']), 
			array($lines[count($lines)-1]['x2'], $lines[count($lines)-1]['y2'])
		);
	}
	
	
}

?>