/*
	Copyright (C) 2013 Tobias Florin

	This file is part of the InterActive-Map mod for webDiplomacy

	The InterActive-Map mod for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The InterActive-Map mod for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
*/

    //php code
    /*public function drawSupportMove($terr, $fromTerr, $toTerr, $success)
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
	}*/

function extension(order, fromTerrID, toTerrID, terrID){
    switch (order) {
        case 'supportMove':     //in php code above: public function drawSupportMove
            WrapArrowX(fromTerrID, toTerrID, terrID);
            drawSupportMove('warpTerr1','warpFrom1', 'warpTo1', true); //true: the extension function is skipped for this call
            drawSupportMove('warpTerr2','warpFrom2', 'warpTo2', true);
            return false;   //aborts the drawOrder function that called the extension
        case 'convoy':
            WrapArrowX(fromTerrID, toTerrID, terrID);
            drawConvoy('warpTerr1','warpFrom1', 'warpTo1', true);
            drawConvoy('warpTerr2','warpFrom2', 'warpTo2', true);
            return false;
        case 'move': 
            WrapArrowX(fromTerrID, toTerrID);
            drawMove('warpFrom1', 'warpTo1', true);
            drawMove('warpFrom2', 'warpTo2', true);
            return false;
        case 'retreat': 
            WrapArrowX(fromTerrID, toTerrID);
            drawRetreat('warpFrom1', 'warpTo1', true);
            drawRetreat('warpFrom2', 'warpTo2', true);
            return false;
        case 'supportHold': 
            WrapArrowX(fromTerrID, toTerrID);
            drawSupportHold('warpFrom1', 'warpTo1', true);
            drawSupportHold('warpFrom2', 'warpTo2', true);
            return false;
    }
    
    
    return true;    //if the drawOrder function that called the extension is not changed it will be completed normally
}

function WrapArrowX(fromTerrID, toTerrID, terrID){
    var terrTable = Territories.toObject();
        
    //list($startX, $startY) = $this->territoryPositions[$fromTerr];
    //list($endX  , $endY  ) = $this->territoryPositions[$toTerr];
    var start = {x: parseInt(terrTable[fromTerrID].smallMapX), y: parseInt(terrTable[fromTerrID].smallMapY)};
    var end = {x: parseInt(terrTable[toTerrID].smallMapX), y: parseInt(terrTable[toTerrID].smallMapY)};
    
    /*if (abs($startX-$endX) > $this->map['width'] * 1/2)
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
		}*/
    if(Math.abs(start.x - end.x) > IAmapCan.width * 1/2){
        var left = {
            x: (start.x<end.x)?start.x:end.x,
            y: (start.x<end.x)?start.y:end.y
        }
        var right = {
            x: (start.x>end.x)?start.x:end.x,
            y: (start.x>end.x)?start.y:end.y
        }
        var drawToLeftX = 0;
        var drawToRightX = IAmapCan.width;
        // Ratio of diff(left side and left x) and diff (right side and right x)
        var ratioLeft = left.x / (left.x + drawToRightX - right.x);
	var ratioRight = 1.0 - ratioLeft;
	if (left.y > right.y) { // Downward slope
            var drawToLeftY = left.y - (Math.abs(left.y-right.y) * ratioLeft);
            var drawToRightY = right.y + (Math.abs(left.y-right.y) * ratioRight);
	} else { // Upward Slope
            var drawToLeftY = left.y + (Math.abs(left.y-right.y) * ratioLeft);
            var drawToRightY = right.y - (Math.abs(left.y-right.y) * ratioRight);
	}
	if (start.x == left.x) {
            Territories.set('warpFrom1', {smallMapX: left.x, smallMapY: left.y});
            Territories.set('warpTo1', {smallMapX: drawToLeftX, smallMapY: drawToLeftY});
            Territories.set('warpFrom2', {smallMapX: drawToRightX, smallMapY: drawToRightY});
            Territories.set('warpTo2', {smallMapX: right.x, smallMapY: right.y});
	} else  {
            Territories.set('warpFrom1', {smallMapX: drawToLeftX, smallMapY: drawToLeftY});
            Territories.set('warpTo1', {smallMapX: left.x, smallMapY: left.y});
            Territories.set('warpFrom2', {smallMapX: right.x, smallMapY: right.y});	
            Territories.set('warpTo2', {smallMapX: drawToRightX, smallMapY: drawToRightY});
        }
    }
    /*} else {
			$this->territoryPositions['WarpFrom1'] = $this->territoryPositions[$fromTerr];
			$this->territoryPositions['WarpTo1']   = $this->territoryPositions[$toTerr];
			$this->territoryPositions['WarpFrom2'] = $this->territoryPositions[$fromTerr];
			$this->territoryPositions['WarpTo2']   = $this->territoryPositions[$toTerr];
		}*/
    else{
        Territories.set('warpFrom1', {smallMapX: start.x, smallMapY: start.y});
        Territories.set('warpTo1', {smallMapX: end.x, smallMapY: end.y});
        Territories.set('warpFrom2', {smallMapX: start.x, smallMapY: start.y});
        Territories.set('warpTo2',{smallMapX: end.x, smallMapY: end.y});
    }
		/*if ($terr != 0)
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
		}*/
    // If I have a support-move or convoy
    terrTable = Territories.toObject(); 
    if(typeof terrID !== 'undefined'){
        // If I have two arrows check which one to point to:
	if (!((terrTable.warpFrom1.smallMapX == terrTable.warpFrom2.smallMapX) && (terrTable.warpFrom1.smallMapY == terrTable.warpFrom2.smallMapY)))
	{	
            var unit = {x: terrTable[terrID].smallMapX, y: terrTable[terrID].smallMapY};
            var dist1 = Math.abs(unit.x - left.x)  + Math.abs(unit.y - left.y)  + Math.abs(unit.x - drawToLeftX)  + Math.abs(unit.y - drawToLeftY);
            var dist2 = Math.abs(unit.x - right.x) + Math.abs(unit.y - right.y) + Math.abs(unit.x - drawToRightX) + Math.abs(unit.y - drawToRightY);
            
            if(dist1 < dist2) {
                Territories.set('warpFrom2', terrTable.warpFrom1);
                Territories.set('warpTo2', terrTable.warpTo1);
            } else {
                Territories.set('warpFrom1', terrTable.warpFrom2);
                Territories.set('warpTo1', terrTable.warpTo2);
            }
            Territories.set('warpTerr1', {smallMapX: unit.x, smallMapY: unit.y});
            Territories.set('warpTerr2', {smallMapX: unit.x, smallMapY: unit.y});
        }
        // Maybe the Support/Convoy arrow needs to be split too...
	else
        {
            Territories.set('supTo', {smallMapX: end.x - (end.x - start.x) / 3, smallMapY: end.y - (end.y - start.y) /3});
            WrapArrowX(terrID, 'supTo');
            terrTable = Territories.toObject();
            Territories.set('warpTerr1', terrTable.warpFrom1);
            Territories.set('warpFrom1', terrTable.warpTo1);
 
            Territories.set('warpTerr2', terrTable.warpFrom2);
            Territories.set('warpFrom2', terrTable.warpTo2);

	}
    }
}


