<?php
/*
	Copyright (C) 2010 Carey Jensen / Kestas J. Kuliukas / Oliver Auth

	This file is part of the World variant for webDiplomacy

	The World variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The World variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
*/

class WorldVariant_drawMap extends drawMap {

	protected $countryColors = array(
		 0 =>  array(226, 198, 158), /*  Global             */
		 1 =>  array(252,   2,   4), /*  Argentina          */
		 2 =>  array(  4, 254,   4), /*  Brazil             */
		 3 =>  array(252,   2, 252), /*  China              */
		 4 =>  array(  4, 130, 252), /*  Europe             */
		 5 =>  array(  4,  66, 132), /*  Frozen-Antarctica  */
		 6 =>  array(252, 130, 132), /*  Ghana              */
		 7 =>  array(  4, 130, 132), /*  India              */
		 8 =>  array(100,  98,   4), /*  Kenya              */
		 9 =>  array(132,   2, 132), /*  Libya              */
		10 =>  array(196, 194, 252), /*  Near-East          */
		11 =>  array( 36,  98,  68), /*  Pacific-Russia     */
		12 =>  array(100, 130,   4), /*  Quebec             */
		13 =>  array(252, 254, 252), /*  Russia             */
		14 =>  array(  4, 254, 252), /*  South-Africa       */
		15 =>  array(252,  98,  68), /*  USA                */
		16 =>  array(252, 254,  68), /*  Western-Canada     */
		17 =>  array(132, 130, 132), /*  Oz                 */
	);
	
	protected function resources() {
		if( $this->smallmap )
		{
			return array(
				'map'=>l_s('variants/World/resources/smallmap.png'),
				'army'=>l_s('contrib/smallarmy.png'),
				'fleet'=>l_s('contrib/smallfleet.png'),
				'names'=>l_s('variants/World/resources/smallmapNames.png'),
				'standoff'=>l_s('images/icons/cross.png')
			);
		}
		else
		{
			return array(
				'map'=>l_s('variants/World/resources/map.png'),
				'army'=>l_s('contrib/smallarmy.png'),
				'fleet'=>l_s('contrib/smallfleet.png'),
				'names'=>l_s('variants/World/resources/mapNames.png'),
				'standoff'=>l_s('images/icons/cross.png')
			);
		}
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
		}

		return $colorRes;
	}	
	
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

	protected $orderArrows = array(
		'Move' => array(
			'color'     =>array(196,32,0),
			'thickness' =>array(1,1),
			'headAngle' =>7,
			'headStart' =>.1,
			'headLength'=>array(12,12),
			'border'    =>array(0,0)
		),
		'Support hold' => array(
			'color'     =>array(67,206,16), 
			'thickness' =>array(1,1),
			'headAngle' =>2, 
			'headStart' =>0.2,
			'headLength'=>array(8,8),
			'border'    =>array(0,0)
		),
		'Support move' => array(
			'color'     =>array(249,249,47), 
			'thickness' =>array(1,1),
			'headAngle' =>7,
			'headStart' =>0.4,
			'headLength'=>array(12,12),
			'border'    =>array(0,0)
		),
		'Convoy' => array(
			'color'     =>array(4,113,160), 
			'thickness' =>array(1,1),
			'headAngle' =>7,
			'headStart' =>.1,
			'headLength'=>array(0,0),
			'border'    =>array(0,0)
		),
		'Retreat' => array(
			'color'     =>array(198,39,159), 
			'thickness' =>array(1,1),
			'headAngle' =>7,
			'headStart' =>.1,
			'headLength'=>array(12,12),
			'border'    =>array(0,0)
		)
	);
	
	protected function drawFailure(array $from, array $to)
	{
		$height = $this->army['height']/2;
		$width = $this->army['width']/2;

		$coords = array($height, $width, -1*$height, -1*$width, 
						$height, -1*$width, -1*$height, $width);
		
		$rad = $this->lineAngle($from, $to);
		
		/*
		 * The marker is currently pointing upwards, so PI must be added to 
		 * $rad to cancel this out
		 */
		$coords = $this->rotate($coords, array(0,0), $rad+M_PI);
		
		/*
		 * The marker is rotated correctly, and now must be moved into place
		 * before getting drawn
		 */
		$x = $from[0] - ( $from[0] - $to[0] )/3;
		$y = $from[1] - ( $from[1] - $to[1] )/3;
		
		for($i=0; $i<8; $i+=2)
		{
			$coords[$i] += $x;
			$coords[$i+1] += $y;
		}
		
		if ( $this->smallmap )
			$thickness = 1;
		else
			$thickness = 2;
		
		$this->drawCross($coords, $this->colors['standoff'], $thickness);
	}
	
	private function getWrapLines($startX, $startY, $endX, $endY) 
	{
		$lines = array();
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
				$lines[] = (array("x1"=>$leftX, "y1"=>$leftY, "x2"=>$drawToLeftX, "y2"=>$drawToLeftY));
				$lines[] = (array("x1"=>$drawToRightX, "y1"=>$drawToRightY, "x2"=>$rightX, "y2"=>$rightY));
			} else  {
				$lines[] = (array("x1"=>$rightX, "y1"=>$rightY, "x2"=>$drawToRightX, "y2"=>$drawToRightY));
				$lines[] = (array("x1"=>$drawToLeftX, "y1"=>$drawToLeftY, "x2"=>$leftX, "y2"=>$leftY));
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