/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
var mapCanvas;
var mapImg;
var canvasCtx;
var img;

function IAmap() {
    if(IAactivated) {
        if(mapCanvas == null) {
            mapCanvas = new Element("canvas", {'id':'mapCanvas', 'width':mapImg.getWidth(), 'height':mapImg.getHeight(), 'onClick':'selectTerritory(event)'}).insert("<p>Your Browser does not support HTML 5! You can not use InteractiveMap! Please Reload the page!</p>");
            mapImg.replace(mapCanvas);
            mapCanvas = $("mapCanvas");
        
            if(mapCanvas.getContext) {
                canvasCtx = mapCanvas.getContext('2d');
           
                img = new Image();
                var link = 'map.php?gameID='+context.gameID+'&turn='+context.turn+'&hideMoves';
                img.observe('load', function() {
                    drawImage();
                });
                canvasCtx.fillText("Loading ...",1,20);
                img.src = link;
            
            
            /*canvasCtx.fillStyle = "rgb(200,0,0)";
            canvasCtx.fillRect (10, 10, 55, 50);
 
            canvasCtx.fillStyle = "rgba(0, 0, 200, 0.5)";
            canvasCtx.fillRect (30, 30, 55, 50);*/
            }
        }else{
            mapImg.replace(mapCanvas);
        }
    }else{
        mapCanvas.replace(mapImg);
    }
} 

function drawImage() {      //draws the image with entered orders
    canvasCtx.drawImage(img,0,0);
    for(var i=0; i<MyOrders.length; i++){ //checks for each order, if setted and draws it if setted
        if(MyOrders[i].isComplete) {    //checks if order is complete
            //alert(i+" is Complete");
            switch(MyOrders[i].type){       //type hold not handled because nothing to draw
                case "Move": drawMove(MyOrders[i].Unit.terrID,MyOrders[i].toTerrID); break;
                case "Support hold": drawSupportHold(MyOrders[i].Unit.terrID,MyOrders[i].toTerrID); break;
                case "Support move": drawSupportMove(MyOrders[i].Unit.terrID,MyOrders[i].fromTerrID,MyOrders[i].toTerrID); break;
                case "Convoy": drawConvoy(MyOrders[i].Unit.terrID,MyOrders[i].fromTerrID,MyOrders[i].toTerrID); break; 
                case "Destroy": drawDestroyedUnit(MyOrders[i].toTerrID); break;
                case "Build Fleet": case "Build Army": drawCreateUnit(MyOrders[i].toTerrID, MyOrders[i].type); break;
            }
        }
    }      
}

function drawMove(fromTerrID,toTerrID) {
    var terrTabl = Territories.toObject();
    
    var start = {x: terrTabl[fromTerrID].smallMapX, y: terrTabl[fromTerrID].smallMapY};
    var end = {x: terrTabl[toTerrID].smallMapX, y: terrTabl[toTerrID].smallMapY};
    drawOrderArrow(start, end,'Move');
    /*canvasCtx.beginPath();
    canvasCtx.moveTo(terrTabl[fromTerrID].x, terrTabl[fromTerrID].y);
    canvasCtx.lineTo(terrTabl[toTerrID].x, terrTabl[toTerrID].y);
    canvasCtx.strokeStyle = "rgb(196,32,0)";
    canvasCtx.lineWidth = 1;
    canvasCtx.stroke();
    canvasCtx.beginPath();*/
    
}

function drawSupportHold(fromTerrID, toTerrID) {
    /*var fromTerrID = order.fromTerr;
    //var toTerrID = order.toTerr;      //only if fleet is not on coast*/
    var terrTabl = Territories.toObject();
    toTerrID = terrTabl[toTerrID].Unit.terrID;   //for units on coasts
    
    
    var start = {x: terrTabl[fromTerrID].smallMapX, y: terrTabl[fromTerrID].smallMapY};
    var end = {x: terrTabl[toTerrID].smallMapX, y: terrTabl[toTerrID].smallMapY};
    drawOrderArrow(start, end,'Support hold');
}



//The following is translated (partly) from drawMap.php
/*public function drawSupportMove($terrID, $fromTerrID, $toTerrID, $success)
	{
		if ( $this->smallmap and !$success ) return;

		// Our toX and toY are 1/3 of the way between the two territories
		list($fromX, $fromY) = $this->territoryPositions[$fromTerrID];
		list($toX, $toY) = $this->territoryPositions[$toTerrID];

		$toX -= ( $toX - $fromX ) / 3;
		$toY -= ( $toY - $fromY ) / 3;

		list($fromX, $fromY) = $this->territoryPositions[$terrID];

		$this->drawOrderArrow(array($fromX, $fromY), array($toX, $toY), 'Support move');

		if ( !$success ) $this->drawFailure(array($fromX, $fromY), array($toX, $toY));
	}*/

function drawSupportMove(terrID, fromTerrID, toTerrID){
    var terrTabl = Territories.toObject();
    
    var suppUnit = terrTabl[fromTerrID].Unit;    //the supported Unit
    fromTerrID = suppUnit.terrID;   //for units on coasts
    if(suppUnit.countryID == context.countryID){     //the following code would cause problems with foreign units as their Order is not known
        var toTerrIDnew = suppUnit.Order.toTerrID;   //for units on coasts
        //alert(toTerrIDnew);
        //alert(toTerrID);
        if((toTerrIDnew==toTerrID)||(terrTabl[toTerrIDnew].coastParentID==toTerrID)){        //checks, if different ID isn't only caused by a coast
            //alert("I'm here");
            toTerrID = toTerrIDnew;
        }
    }
    
    var fromX = terrTabl[fromTerrID].smallMapX;
    var fromY = terrTabl[fromTerrID].smallMapY;
    var toX = terrTabl[toTerrID].smallMapX;
    var toY = terrTabl[toTerrID].smallMapY;
    
    toX -= (toX-fromX)/3;
    toY -= (toY-fromY)/3;
    
    var start = {x: terrTabl[terrID].smallMapX, y: terrTabl[terrID].smallMapY};
    var end = {x: toX, y: toY};
    drawOrderArrow(start, end,'Support move');
}

/*public function drawConvoy($terrID, $fromTerrID, $toTerrID, $success)
	{
		if ( $this->smallmap and !$success ) return;

		// Our toX and toY are 1/3 of the way between the two territories
		list($fromX, $fromY) = $this->territoryPositions[$fromTerrID];
		list($toX, $toY) = $this->territoryPositions[$toTerrID];

		$toX -= ( $toX - $fromX ) / 3;
		$toY -= ( $toY - $fromY ) / 3;

		list($fromX, $fromY) = $this->territoryPositions[$terrID];

		$this->drawOrderArrow(array($fromX, $fromY), array($toX, $toY), 'Convoy');

		if ( !$success ) $this->drawFailure(array($fromX, $fromY), array($toX, $toY));
	}*/

function drawConvoy(terrID, fromTerrID, toTerrID) {
    var terrTabl = Territories.toObject();
    
    var fromX = terrTabl[fromTerrID].smallMapX;
    var fromY = terrTabl[fromTerrID].smallMapY;
    var toX = terrTabl[toTerrID].smallMapX;
    var toY = terrTabl[toTerrID].smallMapY;
    
    toX -= (toX-fromX)/3;
    toY -= (toY-fromY)/3;
    
    var start = {x: terrTabl[terrID].smallMapX, y: terrTabl[terrID].smallMapY};
    var end = {x: toX, y: toY};
    drawOrderArrow(start, end,'Convoy');
}


var orderArrows = {
		//array(0, 153, 2)
		'Move': {'color': new Array(196,32,0),  //0, 153, 2),//
						'thickness': new Array(2,4),
						'headAngle': Math.PI/7,
						'headStart': 0.1,
						'headLength': new Array(12,30),
						'border': new Array(0,0)
                                        },
		'Support hold': {'color': new Array(67,206,16),
						'thickness': new Array(2,4),
						'headAngle': Math.PI/2,
						'headStart': 0.2,
						'headLength': new Array(8,24),
						'border': new Array(0,0)
					},
                'Support move': {'color': new Array(249,249,47),
						'thickness': new Array(2,4),
						'headAngle': Math.PI/7,
						'headStart': 0.4,
						'headLength': new Array(12,30),
						'border': new Array(0,0)
					},
                'Convoy': {'color': new Array(4,113,160),
						'thickness': new Array(2,4),
						'headAngle': Math.PI/7,
						'headStart': 0.1,
						'headLength': new Array(0,0),
						'border': new Array(0,0)
					},
                'Retreat': {'color': new Array(198,39,159),
						'thickness': new Array(2,4),
						'headAngle': Math.PI/7,
						'headStart': 0.1,
						'headLength': new Array(12,30),
						'border': new Array(0,0)
					}
                 };
                 

/*protected function drawOrderArrow(array $start, array $end, $moveType )
	{
		list($startX, $startY) = $start;
		list($endX, $endY) = $end;

		$this->updateBoundaries($startX, $startY);
		$this->updateBoundaries($endX, $endY);

		$params = &$this->orderArrows[$moveType];

		$borderColor = $this->colors['border'];

		// Some orders want to start the head further up the arrow ( support move)
		$headStartX = $endX-($endX-$startX)*$params['headStart'];
		$headStartY = $endY-($endY-$startY)*$params['headStart'];

		// Get the angle that the order arrow is pointing in, so that the head can be added that way
		$rad = $this->lineAngle($start, $end);

		/*
		 * Now we construct the head facing to the right, so that it can be
		 * aligned afterwards
		 /
		$head = array(
				0,0,
				-1*$params['headLength'] * cos($params['headAngle']), -1*$params['headLength'] * sin($params['headAngle']),
				-1*$params['headLength'] * cos($params['headAngle']), $params['headLength'] * sin($params['headAngle']),
			);
		$head = self::rotate($head, array(0,0), $rad);

		// Rotation done, now move it into place
		for($i=0; $i<6; $i+=2)
		{
			$head[$i] += $headStartX;
			$head[$i+1] += $headStartY;
		}

		if ( $params['border'] != 0 )
		{
			// Borders are drawn first, so they don't overlap, and are made a little longer
			self::imagelinethick($this->map['image'], $startX, $startY, $endX, $endY, $borderColor, $params['border']);
			self::imagelinethick($this->map['image'], $head[2], $head[3], $headStartX, $headStartY, $borderColor, $params['border']);
			self::imagelinethick($this->map['image'], $head[4], $head[5], $headStartX, $headStartY, $borderColor, $params['border']);
		}

		self::imagelinethick($this->map['image'], $startX, $startY, $endX, $endY, $params['color'], $params['thickness']);
		self::imagelinethick($this->map['image'], $head[2], $head[3], $headStartX, $headStartY, $params['color'], $params['thickness']);
		self::imagelinethick($this->map['image'], $head[4], $head[5], $headStartX, $headStartY, $params['color'], $params['thickness']);
	}*/

function drawOrderArrow(start, end, moveType) {
    var startX = start.x;
    var startY = start.y;
    var endX = end.x;
    var endY = end.y;
    var params = orderArrows[moveType];
    
    var headStartX = endX-(endX-startX)*params['headStart'];
    var headStartY = endY-(endY-startY)*params['headStart'];
    
    var rad = lineAngle(start, end);
    
    var head = new Array(
                            0,0,    
                            -1*params['headLength'][0]*Math.cos(params['headAngle']), -1*params['headLength'][0]*Math.sin(params['headAngle']),
                            -1*params['headLength'][0]*Math.cos(params['headAngle']), params['headLength'][0]*Math.sin(params['headAngle'])
			);
    
    head = rotate(head, new Array(0,0), rad);
                            
    for(var i=0; i<6; i+=2){
        head[i] += headStartX;
        head[i+1] += headStartY;
    }
    
    imageLine(startX, startY, endX, endY, params['color'], params['thickness']);
    imageLine(head[2], head[3], headStartX, headStartY, params['color'], params['thickness']);
    imageLine(head[4], head[5], headStartX, headStartY, params['color'], params['thickness']);
}   

/*protected static function rotate(array $coordinates, array $rotateAround, $rotateBy)
	{
		/*
		 * y gets lower as it goes up, which can cause confusion
		 /
		$transformMatrix = array(
				array(cos($rotateBy),-1*sin($rotateBy)),
				array(sin($rotateBy),cos($rotateBy))
			);

		$coordNum = count($coordinates);
		for($i=0; $i<$coordNum; $i+=2)
		{
			$x = $coordinates[$i];
			$y = $coordinates[$i+1];

			$x -= $rotateAround[0];
			$y -= $rotateAround[1];

			$newX = $x*$transformMatrix[0][0] + $y*$transformMatrix[0][1];
			$y = $x*$transformMatrix[1][0] + $y*$transformMatrix[1][1];
			$x = $newX;

			$x += $rotateAround[0];
			$y += $rotateAround[1];

			$coordinates[$i] = $x;
			$coordinates[$i+1] = $y;
		}

		return $coordinates;
	}*/

function rotate(coordinates, rotateAround, rotateBy){
    var transformMatrix = new Array(
                        new Array(Math.cos(rotateBy),-1*Math.sin(rotateBy)),
                        new Array(Math.sin(rotateBy),Math.cos(rotateBy))
                    );
    var coordNum = coordinates.length;
    for(var i=0; i<coordNum; i+=2){
        var x = coordinates[i];
        var y = coordinates[i+1];
        
        x -= rotateAround[0];
        y -= rotateAround[1];
        
        var newX = x*transformMatrix[0][0] + y*transformMatrix[0][1];
        y = x*transformMatrix[1][0] + y*transformMatrix[1][1];
        x = newX;
        
        x += rotateAround[0];
        y += rotateAround[1];
        
        coordinates[i] = x;
        coordinates[i+1] = y;
    }
    return coordinates;
}

/*protected static function lineAngle(array $from, array $to)
	{
		$xdiff = $from[0] - $to[0];
		// xdiff > 0 = from > to = to on the left of from = rad between +/- PI and +/-1/2*PI

		$ydiff = $from[1] - $to[1];
		// ydiff > 0 = from > to = to above from = rad between -PI and 0

		if ( $ydiff > 0 ) // The radians angle is negative. The arrow points up
		{
			if ( $xdiff == 0 ) // Don't let this cause a div by zero error
			{
				return (-1/2)*M_PI;
			}
			elseif ( $xdiff > 0 )
			{
				/*
				 * The arrow points left; the radians angle is between -PI and -(1/2)*PI
				 /

				return ( M_PI + abs(atan(abs($ydiff/$xdiff))) );
			}
			else
			{
				// The radians angle is between 0 and -PI
				return -1*abs(atan(abs($ydiff/$xdiff)));
			}
		}
		else
		{
			// The value is positive
			if ( $xdiff == 0 )
			{
				return M_PI/2;
			}
			elseif ( $xdiff > 0 )
			{
				// The arrow points left; the radians angle is between PI and 2*PI
				return ( M_PI - abs(atan(abs($ydiff/$xdiff))) );
			}
			else
			{
				// The radians angle is between 0 and PI
				return abs(atan(abs($ydiff/$xdiff)));
			}
		}
	}*/

function lineAngle (from, to) {
    var xdiff = from.x - to.x;
    var ydiff = from.y - to.y;
    
    if(ydiff > 0){
        if(xdiff == 0){
            return ((-1/2)*Math.PI);
        }else if(xdiff > 0){
            return (Math.PI + Math.abs(Math.atan(Math.abs(ydiff/xdiff))));
        }else{
            return (-1*Math.abs(Math.atan(Math.abs(ydiff/xdiff))));
        }
    }else{
        if (xdiff == 0){
            return (Math.PI/2);
        }else if(xdiff > 0){
            return (Math.PI - Math.abs(Math.atan(Math.abs(ydiff/xdiff))) );
        }else{
            return (Math.abs(Math.atan(Math.abs(ydiff/xdiff))));
        }
    }    
}


/*protected static function imagelinethick($image, $x1, $y1, $x2, $y2, $color, $thick = 1)
	{
		imagesetthickness($image, $thick);
		imageline($image, $x1, $y1, $x2, $y2, $color);
		imagesetthickness($image, 1);
	}*/
        
function imageLine(x1, y1, x2, y2, color, thick) {
    thick = (typeof thick !== 'undefined') ? thick : 2;
    
    canvasCtx.beginPath();
    canvasCtx.moveTo(x1, y1);
    canvasCtx.lineTo(x2, y2);
    canvasCtx.strokeStyle = "rgb("+color[0]+","+color[1]+","+color[2]+")";
    canvasCtx.lineWidth = thick;
    canvasCtx.stroke();  
}

/**
	 * Return an array of coordinates for an n sided symmetrical polygon, which has 0,0 at its center.
	 *
	 * @param int $corners The number of corners in the polygon
	 * @param int $outerRadius The distance to draw the outer-most points
	 * @param int[optional] $innerRadius The option to draw half the points at a the given radius
	 *
	 * @return array x,y,x,y matrix of polygon coordinates
	 *
	protected static function polygonMap($corners, $outerRadius, $innerRadius=false)
	{
		$coords = array();

		for($i=0;$i<$corners;$i++)
		{
			$coords[]=0; // x coord is 0

			if ( $i%2 != 0 and $innerRadius != false )
				$coords[]=$innerRadius;
			else
				$coords[]=$outerRadius;

			$coords = self::rotate($coords, array(0,0), (2*M_PI/$corners));
		}

		return $coords;
	}*/

function polygonMap(corners, outerRadius, innerRadius){
     innerRadius = (typeof innerRadius !== 'undefined') ? innerRadius : false;
     
     var coords = new Array();
     
     for(var i=0; i<corners; i++){
         coords.push(0);
         
         if((i%2 != 0)&&(innerRadius != false)){
            coords.push(innerRadius);
         }else{
            coords.push(outerRadius);
         }
         
         coords = rotate(coords, new Array(0,0), (2*Math.PI/corners));
     }
     
     return coords;
}

	/**
	 * Draws an orange explosion where a unit has been destroyed, intended to be drawn on-top of the unit
	 *
	 * @param string $terrID The territory to draw the destruction at
	 */
	/*public function drawDestroyedUnit($terrID)
	{
		$size = $this->army['width']/2;

		if( !isset($this->destroyedPolygon) )
		{
			$blackInner = self::polygonMap(14, $size, $size/2);
			$blackOuter = self::polygonMap(14, ($size-1), ($size-1)/2);

			$inner = self::polygonMap(14, ($size-2), ($size-2)/2);
			$outer = self::polygonMap(14, ($size-3), ($size-3)/2);

			$outer = self::rotate($outer, array(0,0), M_PI/7);
			$blackOuter = self::rotate($blackOuter, array(0,0), M_PI/7);

			$this->destroyedPolygon=array(
				'blackInner'=>$blackInner,
				'blackOuter'=>$blackOuter,
				'inner'=>$inner,
				'outer'=>$outer,
			);
		}

		$position = $this->territoryPositions[$terrID];

		$this->drawPolygon($position, array(0,0,0), $this->destroyedPolygon['blackInner']);
		$this->drawPolygon($position, array(0,0,0), $this->destroyedPolygon['blackOuter']);
		$this->drawPolygon($position, array(255,100,0), $this->destroyedPolygon['inner']);
		$this->drawPolygon($position, array(255,0,0), $this->destroyedPolygon['outer']);
	}
	private $destroyedPolygon;*/

var destroyedPolygon;
function drawDestroyedUnit(terrID){
    var terrTabl = Territories.toObject();
    
    var size = 7.5;    //army width (15) / 2
    
    if(typeof destroyedPolygon === 'undefined'){
        var blackInner = polygonMap(14, size, (size/2));
        var blackOuter = polygonMap(14, (size-1), ((size-1)/2));
        
        var inner = polygonMap(14, (size-2), ((size-2)/2));
        var outer = polygonMap(14, (size-3), ((size-3)/2));
        
        outer = rotate(outer, new Array(0,0), (Math.PI/7));
        blackOuter = rotate(blackOuter, new Array(0,0), (Math.PI/7));
        
        destroyedPolygon = {
            'blackInner':blackInner,
            'blackOuter':blackOuter,
            'inner':inner,
            'outer':outer};
    }
    
    var position = {x: terrTabl[terrID].smallMapX, y: terrTabl[terrID].smallMapY};
    
    drawPolygon(position, new Array(0,0,0), destroyedPolygon['blackInner']);
    drawPolygon(position, new Array(0,0,0), destroyedPolygon['blackOuter']);
    drawPolygon(position, new Array(255,100,0), destroyedPolygon['inner']); 
    drawPolygon(position, new Array(255,0,0), destroyedPolygon['outer']);
}



	/**
	 * Draws a small orange explosion where a unit has been dislodged, drawn to the upper right of the unit
	 * which took the place of the dislodged unit
	 *
	 * @param string $terrID The territory to draw the dislodgement marker at
	 */
	/*public function drawDislodgedUnit($terrID)
	{
		if ( $this->smallmap ) return;

		if( !isset($this->dislodgedPolygon) )
		{
			$size = $this->army['height']/2;

			$blackInner = self::polygonMap(10, $size, $size/2);
			$blackOuter = self::polygonMap(10, ($size-2), ($size-2)/2);

			$inner = self::polygonMap(10, ($size-3), ($size-3)/2);
			$outer = self::polygonMap(10, ($size-4), ($size-4)/2);

			$outer = self::rotate($outer, array(0,0), M_PI/5);
			$blackOuter = self::rotate($blackOuter, array(0,0), M_PI/5);

			$this->dislodgedPolygon=array(
				'blackInner'=>$blackInner,
				'blackOuter'=>$blackOuter,
				'inner'=>$inner,
				'outer'=>$outer,
			);
		}

		$position = $this->territoryPositions[$terrID];

		$this->drawPolygon($position, array(0,0,0), $this->dislodgedPolygon['blackInner'], true);
		$this->drawPolygon($position, array(0,0,0), $this->dislodgedPolygon['blackOuter'], true);
		$this->drawPolygon($position, array(255,150,0), $this->dislodgedPolygon['inner'], true);
		$this->drawPolygon($position, array(255,80,0), $this->dislodgedPolygon['outer'], true);
	}
	private $dislodgedPolygon;*/

function drawDislodgedUnit(terrID){
    
}

	/**
	 * Draw a polygon to a certain color
	 * @param array $position x,y Coordinate of the center of the polygon
	 * @param array $color The color to use for the polygon
	 * @param array $polygon The x,y,x,y coordinate matrix for the polygon
	 * @param bool[optional] $small True if drawing onto small map
	 */
	/*protected function drawPolygon(array $position, array $color, array $polygon, $small=false)
	{
		list($x,$y) = $position;

		$corners = count($polygon) / 2;

		if ( $small )
		{
			$x += $this->army['width']/2;
			$y -= $this->army['height']/2;
		}

		for($i=0; $i<$corners*2; $i+=2)
		{
			$polygon[$i]+=$x;
			$polygon[$i+1]+=$y;
		}

		$color = $this->color($color);
		imagefilledpolygon($this->map['image'], $polygon, $corners, $color);
	}*/

function drawPolygon(position, color, polygon, small){

    small = (typeof small !== 'undefined') ? small : false;
    
    var x = new Number(position.x); //position.x was String, which caused problems later
    var y = new Number(position.y);
    
    var corners = polygon.length;
    
    if(small){
        x += 7.5;    //army width (15) / 2
        y -= 4.5;     //army height (9) / 2
    }
    
    for(var i=0; i<corners*2; i+=2){
        polygon[i]+=x;
        polygon[i+1]+=y;
    }
    
    canvasCtx.beginPath();
    canvasCtx.moveTo(polygon[0], polygon[1]);
    for(var i=2; i<polygon.length; i+=2){
        canvasCtx.lineTo(polygon[i], polygon[i+1]);
    }
    canvasCtx.closePath();
    canvasCtx.fillStyle = "rgb("+color[0]+","+color[1]+","+color[2]+")";
    canvasCtx.fill();
    
}

	/**
	 * Draws a small star where a unit has been created, drawn to the upper right of the new unit
	 *
	 * @param string $terrID The territory to draw the creation marker at
	 */
	/*public function drawCreatedUnit($terrID, $unitType)
	{
		$position = $this->territoryPositions[$terrID];

		$size = $this->army['height']/2;

		$blackStar = self::polygonMap(10, $size, $size/2);
		$blackStar = self::rotate($blackStar, array(0,0), M_PI);

		// Flip them the right way up
		$whiteStar = self::polygonMap(10, ($size-2), $size/2-1);
		$whiteStar = self::rotate($whiteStar, array(0,0), M_PI);


		// The outer black border star
		$this->drawPolygon($position, array(0,0,0), $blackStar, true);
		// The inner white star
		$this->drawPolygon($position, array(255,255,0), $whiteStar, true);

		$this->addUnit($terrID, $unitType);
	}*/
function drawCreateUnit(terrID, type){
    var terrTabl = Territories.toObject();
    var position = {x: terrTabl[terrID].smallMapX, y: terrTabl[terrID].smallMapY};
    
    var size = 7.5;    //army width (15) / 2
    
    var blackStar = polygonMap(10, size-2, (size/2));
    blackStar = rotate(blackStar, new Array(0,0), Math.PI);
    
    var whiteStar = polygonMap(10, (size-2), (size/2-1));
    whiteStar = rotate(whiteStar, new Array(0,0), Math.PI);

    drawPolygon(position, new Array(0,0,0), blackStar, true);
    
    drawPolygon(position, new Array(255,255,0), whiteStar, true);
    
    canvasCtx.fillStyle = "rgb(255,0,0)";
    if(type == "Build Fleet"){
        canvasCtx.fillText("F",position.x-5,position.y);
    }else{
        canvasCtx.fillText("A",position.x-5,position.y);
    }
}