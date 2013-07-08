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

//map as canvas element
var mapCanvas;
//div with scrollbars for canvas element
var mapCanDiv;
//map as image element
var mapImg;
//mapCanvas 2d context
var canvasCtx;
//image of the map
var img;

//canvas element for grey-out of territories
var mapGreyOutCanvas;
//mapGreyOutCanvas 2d context
var goCtx;
//array of greyed out territories
var greyedOut = new Array();

var greyOutActivated;

/*
 * creates the canvas element and loads the image of the map
 * replaces the image element
 */
function IAmap() {
    if (IAactivated) {
        if (mapCanvas == null) {
            mapCanvas = new Element("canvas", {'id': 'mapCanvas', 'width': IAmapCan.width, 'height': IAmapCan.height, 'onClick': 'selectTerritory(event)', 'style': 'left:0px;top:0px;position:absolute'}).insert("<p>Your Browser does not support Canvas! You can not use InteractiveMap! Please Reload the page!</p>");
            mapCanDiv = new Element("div", {'id': 'mapCanDiv', 'style': 'height:' + (new Number(mapImg.height) + 10) + 'px;width:' + (new Number(mapImg.width) + 10) + 'px;overflow:auto;margin:0 auto;position:relative'}).appendChild(mapCanvas).parentNode;
            mapImg.replace(mapCanDiv);
            scrollbarsRemoved = false;
            mapGreyOutCanvas = mapCanvas.clone(false);
            //mapCanOffset = mapCanvas.cumulativeOffset().toArray();
            mapGreyOutCanvas.setStyle({
                position: 'absolute',
                left: "0px", //mapCanOffset[0]+"px",
                top: "0px", //mapCanOffset[1]+"px"
                zIndex: mapCanvas.style.zIndex + 1.0
            }).hide;
            //mapCanDiv.appendChild(new Element("div",{})).appendChild(mapGreyOutCanvas);
            mapCanDiv.appendChild(mapGreyOutCanvas);

            if (mapCanvas.getContext) {
                canvasCtx = mapCanvas.getContext('2d');
                goCtx = mapGreyOutCanvas.getContext('2d');

                img = new Image();
                img.observe('load', function() {
                    drawImage();
                });
                canvasCtx.fillText("Loading ...", 1, 20);
                var link = 'map.php?gameID=' + context.gameID + '&turn=' + context.turn + '&hideMoves';
                img.src = link;

            }
        } else {
            mapImg.replace(mapCanDiv);
        }
    } else {
        mapCanDiv.replace(mapImg);
    }
}

/*
 * draws the image with entered orders (all what is set in MyOrders)
 */
function drawImage() {
    //draw the basic map
    canvasCtx.drawImage(img, 0, 0);
    //check for each order, if setted and draws it if setted
    for (var i = 0; i < MyOrders.length; i++) {
        if (MyOrders[i].isComplete) {
            switch (MyOrders[i].type) {       //type "Hold" not handled because nothing to draw
                case "Move":
                    drawMove(MyOrders[i].Unit.terrID, MyOrders[i].toTerrID, false);
                    break;
                case "Support hold":
                    drawSupportHold(MyOrders[i].Unit.terrID, MyOrders[i].toTerrID, false);
                    break;
                case "Support move":
                    drawSupportMove(MyOrders[i].Unit.terrID, MyOrders[i].fromTerrID, MyOrders[i].toTerrID, false);
                    break;
                case "Convoy":
                    drawConvoy(MyOrders[i].Unit.terrID, MyOrders[i].fromTerrID, MyOrders[i].toTerrID, false);
                    break;
                case "Destroy":
                    drawDestroyedUnit(MyOrders[i].toTerrID, false);
                    break;
                case "Build Fleet":
                case "Build Army":
                    drawCreateUnit(MyOrders[i].toTerrID, MyOrders[i].type, false);
                    break;
                case "Disband":
                    drawDislodgedUnit(MyOrders[i].Unit.terrID), false;
                    break;
                case "Retreat":
                    drawRetreat(MyOrders[i].Unit.terrID, MyOrders[i].toTerrID, false);
                    break;
            }
        }
        //for the retreat-phase, the retreating units of the player are drawn, as they are not on the basic map
        if (context.phase == "Retreats") {
            drawRetreatUnit(MyOrders[i].Unit.terrID, MyOrders[i].Unit.type, false);
        }
    }
}

function greyOutTerritories(terrChoices) {
    if (greyOutActivated) {
        var width = IAmapDat.width;
        var height = IAmapDat.height;
        goCtx.clearRect(0, 0, width, height);
        if(typeof terrChoices != 'undefined'){
        goCtx.fillStyle="rgba(0,0,0,0.4)";
        goCtx.fillRect(0, 0, width, height);
        
        
        //var terrTable = Territories.toObject();
        var imgData = goCtx.getImageData(0, 0, width, height);
        
        var terrChoicesArray = terrChoices.keys();
        for(var i=0; i<terrChoicesArray.length; i++){
            var terrID = terrChoicesArray[i];
            if(Territories.get(terrID).coast == "Child"){
                terrID = Territories.get(terrID).coastParentID;
            }
            //alert(terrID);
            for(var j = 0; j<IAmapTerrDat[terrID].length; j++){
                imgData = floodfillTransp(IAmapTerrDat[terrID][j][0],IAmapTerrDat[terrID][j][1],imgData);
            }
        }
        //var terrColor = new Object();
        //terrColor[0] = "0,0,0,0"; //black

       /* var newGreyedOut = new Array();
        for (var terrID in terrTable) {
            if ((terrChoices.keys().indexOf(terrID) == -1)) {
                newGreyedOut.push(terrID);
                if (greyedOut.indexOf(terrID) == -1) {
                    var y = parseInt(terrTable[terrID].smallMapY);
                    if (checkArea == undefined) {
                        var checkArea = {upper: y, lower: y};
                    } else if (y > checkArea.upper) {
                        checkArea.upper = y;
                    } else if (y < checkArea.lower) {
                        checkArea.lower = y;
                    }
                    terrColor[getColor(terrTable[terrID].smallMapX, y).toString()] = "greyOut";
                }
            }
        }
        for (var i = 0; i < greyedOut.length; i++) {
            if (newGreyedOut.indexOf(greyedOut[i]) == -1) {
                var y = parseInt(terrTable[greyedOut[i]].smallMapY);
                if (checkArea == undefined) {
                    var checkArea = {upper: y, lower: y};
                } else if (y > checkArea.upper) {
                    checkArea.upper = y;
                } else if (y < checkArea.lower) {
                    checkArea.lower = y;
                }
                terrColor[getColor(terrTable[greyedOut[i]].smallMapX, y).toString()] = "transparent";
            }
        }
        greyedOut = newGreyedOut;*/
        
        /*
         * 
         * changed
         */
        /*for(var terrID in terrTable){
            if(terrChoices.keys().indexOf(terrID) != -1){
                var y = parseInt(terrTable[terrID].smallMapY);
                var x = parseInt(terrTable[terrID].smallMapX);
                if (checkArea == undefined) {
                    var checkArea = {upper: y, lower: y, left: x, right: x};
                } else {
                    if (y > checkArea.upper) {
                        checkArea.upper = y;
                    } else if (y < checkArea.lower) {
                        checkArea.lower = y;
                    }
                    if (x > checkArea.right) {
                        checkArea.right = x;
                    } else if (x < checkArea.left) {
                        checkArea.left = x;
                    }
                }
                terrColor[getColor(x, y).toString()] = "transparent";
            }
        }*/
        /*
         * end changed
         */

        /*if (checkArea.upper >= height) {
            checkArea.upper = height - 1;
        } else if (checkArea.lower < 0) {
            checkArea.lower = 0;
        }
        if (checkArea.right >= width) {
            checkArea.right = width - 1;
        } else if (checkArea.left < 0) {
            checkArea.left = 0;
        }*/

        /*var lastColor;
        var lastColorInd = 0;
        var handledColors = new Object();*/
        /*var start = (checkArea.lower * width * 4);
        var end = (checkArea.upper * width * 4);
        var forStart = start;
        var forEnd = end;
        var pixelPos = start;
        var lineCount = 0;
        while(pixelPos < end){
        //for (var pixelPos = forStart; pixelPos < forEnd; pixelPos += 4) {
            var forLineStart = (checkArea.left*4+pixelPos);
            var forLineEnd = (checkArea.right*4+pixelPos);
            for (pixelPos = forLineStart; pixelPos < forLineEnd; pixelPos += 4){
                var currentColor = IAmapDat.data[pixelPos] + "," + IAmapDat.data[pixelPos + 1] + "," + IAmapDat.data[pixelPos + 2] + "," + IAmapDat.data[pixelPos + 3];
                switch (terrColor[currentColor]) {
                    case undefined:
                        break;
                    case 'greyOut':
                        imgData.data[pixelPos + 3] = 125;
                        break;
                    case 'transparent':
                        imgData.data[pixelPos + 3] = 0;
                        break;
                }
            }
            pixelPos = start+(4*width*(++lineCount));
        }
        var lineOnTop = true;
        while (lineOnTop) {
            lineOnTop = false;
            for (var pixelPos = forStart - (width * 4); pixelPos < forStart; pixelPos += 4) {
                var currentColor = IAmapDat.data[pixelPos] + "," + IAmapDat.data[pixelPos + 1] + "," + IAmapDat.data[pixelPos + 2] + "," + IAmapDat.data[pixelPos + 3];
                switch (terrColor[currentColor]) {
                    case undefined:
                        break;
                    case 'greyOut':
                        lineOnTop = true;
                        imgData.data[pixelPos + 3] = 125;
                        break;
                    case 'transparent':
                        lineOnTop = true;
                        imgData.data[pixelPos + 3] = 0;
                        break;
                }
            }
            forStart = forStart - (width * 4);
        }
        var lineOnBottom = true;
        while (lineOnBottom) {
            lineOnBottom = false;
            for (var pixelPos = forEnd; pixelPos < forEnd + (width * 4); pixelPos += 4) {
                var currentColor = IAmapDat.data[pixelPos] + "," + IAmapDat.data[pixelPos + 1] + "," + IAmapDat.data[pixelPos + 2] + "," + IAmapDat.data[pixelPos + 3];
                switch (terrColor[currentColor]) {
                    case undefined:
                        break;
                    case 'greyOut':
                        lineOnBottom = true;
                        imgData.data[pixelPos + 3] = 125;
                        break;
                    case 'transparent':
                        lineOnBottom = true;
                        imgData.data[pixelPos + 3] = 0;
                        break;
                }
            }
            forEnd = forEnd + (width * 4);
        }
        var lineOnLeft = true;
        while (lineOnLeft) {
            lineOnLeft = false;
            for (var pixelPos = (start + (checkArea.left)*4); pixelPos < end; pixelPos += (width*4)) {
                var currentColor = IAmapDat.data[pixelPos] + "," + IAmapDat.data[pixelPos + 1] + "," + IAmapDat.data[pixelPos + 2] + "," + IAmapDat.data[pixelPos + 3];
                switch (terrColor[currentColor]) {
                    case undefined:
                        break;
                    case 'greyOut':
                        lineOnLeft = true;
                        imgData.data[pixelPos + 3] = 125;
                        break;
                    case 'transparent':
                        lineOnLeft= true;
                        imgData.data[pixelPos + 3] = 0;
                        break;
                }
            }
            checkArea.left--;
        }
        var lineOnRight = true;
        while (lineOnRight) {
            lineOnRight = false;
            for (var pixelPos = (start + (checkArea.right)*4); pixelPos < end; pixelPos += (width*4)) {
                var currentColor = IAmapDat.data[pixelPos] + "," + IAmapDat.data[pixelPos + 1] + "," + IAmapDat.data[pixelPos + 2] + "," + IAmapDat.data[pixelPos + 3];
                switch (terrColor[currentColor]) {
                    case undefined:
                        break;
                    case 'greyOut':
                        lineOnRight = true;
                        imgData.data[pixelPos + 3] = 125;
                        break;
                    case 'transparent':
                        lineOnRight= true;
                        imgData.data[pixelPos + 3] = 0;
                        break;
                }
            }
            checkArea.right++;
        }*/
        goCtx.putImageData(imgData, 0, 0);
        //goCtx.putImageData(IAmapDat, 0, 0);

    }
    }
}

function floodfillTransp(startX,startY,imgData){
    var width = IAmapDat.width;
    var pixelPos = ((startY*width)+startX)*4;
    var IAcolor = IAmapDat.data[pixelPos] + "," + IAmapDat.data[pixelPos + 1] + "," + IAmapDat.data[pixelPos + 2] + "," + IAmapDat.data[pixelPos + 3];
    
    var pixelStack = [[startX,startY]];
    
    while(pixelStack.length){
        var newPos = pixelStack.pop();
        var x = newPos[0];
        var y = newPos[1];
        pixelPos = ((y*width)+x)*4;
        if(imgData.data[pixelPos+3]!=0){
            if(IAcolor == IAmapDat.data[pixelPos] + "," + IAmapDat.data[pixelPos + 1] + "," + IAmapDat.data[pixelPos + 2] + "," + IAmapDat.data[pixelPos + 3]){
                imgData.data[pixelPos+3]=0;
                pixelStack.push([x  ,y+1]);
                pixelStack.push([x+1,y  ]);
                pixelStack.push([x  ,y-1]);
                pixelStack.push([x-1,y  ]);
            }
        }
    }
    
   

    return imgData;
}

function drawRetreatUnit(terrID, type, skipExt) {
    var terrTable = Territories.toObject();
    var complete = true;

    if ((typeof extension == 'function') && !skipExt) {
        complete = extension('drawRetreatUnit', terrID);
    }

    if (complete) {
        var position = {x: terrTable[terrID].smallMapX, y: terrTable[terrID].smallMapY};

        canvasCtx.fillStyle = "rgb(255,255,255)";
        canvasCtx.fillRect(position.x - 5, position.y - 8, 7, 8);

        canvasCtx.fillStyle = "rgb(255,0,0)";
        if (type == "Fleet") {
            canvasCtx.fillText("F", position.x - 5, position.y);
        } else {
            canvasCtx.fillText("A", position.x - 5, position.y);
        }
    }
}

function drawMove(fromTerrID, toTerrID, skipExt) {
    var terrTable = Territories.toObject();
    var complete = true;

    if ((typeof extension == 'function') && !skipExt) {
        complete = extension('move', fromTerrID, toTerrID);
    }

    if (complete) {
        var start = {x: terrTable[fromTerrID].smallMapX, y: terrTable[fromTerrID].smallMapY};
        var end = {x: terrTable[toTerrID].smallMapX, y: terrTable[toTerrID].smallMapY};
        drawOrderArrow(start, end, 'Move');
    }
}

function drawSupportHold(fromTerrID, toTerrID, skipExt) {
    var terrTable = Territories.toObject();
    var complete = true;

    if (typeof terrTable[toTerrID].Unit !== 'undefined')
        toTerrID = terrTable[toTerrID].Unit.terrID;   //for units on coasts

    if ((typeof extension == 'function') && !skipExt) {
        complete = extension('supportHold', fromTerrID, toTerrID);
    }

    if (complete) {
        var start = {x: terrTable[fromTerrID].smallMapX, y: terrTable[fromTerrID].smallMapY};
        var end = {x: terrTable[toTerrID].smallMapX, y: terrTable[toTerrID].smallMapY};
        drawOrderArrow(start, end, 'Support hold');
    }
}

function drawRetreat(fromTerrID, toTerrID, skipExt) {
    var terrTable = Territories.toObject();
    var complete = true;

    if ((typeof extension == 'function') && !skipExt) {
        complete = extension('retreat', fromTerrID, toTerrID);
    }

    if (complete) {
        var start = {x: terrTable[fromTerrID].smallMapX, y: terrTable[fromTerrID].smallMapY};
        var end = {x: terrTable[toTerrID].smallMapX, y: terrTable[toTerrID].smallMapY};
        drawOrderArrow(start, end, 'Retreat');
    }
}



/*The following is translated (partly) from drawMap.php
 * 
 * first the function is shown as it is written in drawMap.php, then the translated
 */

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

function drawSupportMove(terrID, fromTerrID, toTerrID, skipExt) {
    var terrTable = Territories.toObject();
    var complete = true;

    if (typeof terrTable[fromTerrID].Unit !== 'undefined') {
        var suppUnit = terrTable[fromTerrID].Unit;    //the supported Unit
        fromTerrID = suppUnit.terrID;   //for units on coasts
        if ((suppUnit.countryID == context.countryID) && ((suppUnit.Order.type == 'Move') && suppUnit.Order.isComplete)) {     //the following code would cause problems with foreign units as their order is not known
            var toTerrIDnew = suppUnit.Order.toTerrID;   //for units on coasts
            if ((toTerrIDnew == toTerrID) || (terrTable[toTerrIDnew].coastParentID == toTerrID)) {        //checks, if different ID is only caused by a coast
                toTerrID = toTerrIDnew;
            }
        }
    }

    if ((typeof extension == 'function') && !skipExt) {
        complete = extension('supportMove', fromTerrID, toTerrID, terrID);
    }

    if (complete) {
        var fromX = terrTable[fromTerrID].smallMapX;
        var fromY = terrTable[fromTerrID].smallMapY;
        var toX = terrTable[toTerrID].smallMapX;
        var toY = terrTable[toTerrID].smallMapY;

        toX -= (toX - fromX) / 3;
        toY -= (toY - fromY) / 3;

        var start = {x: terrTable[terrID].smallMapX, y: terrTable[terrID].smallMapY};
        var end = {x: toX, y: toY};
        drawOrderArrow(start, end, 'Support move');
    }
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

function drawConvoy(terrID, fromTerrID, toTerrID, skipExt) {
    var terrTable = Territories.toObject();
    var complete = true;

    if ((typeof extension == 'function') && !skipExt) {
        complete = extension('convoy', fromTerrID, toTerrID, terrID);
    }

    if (complete) {
        var fromX = terrTable[fromTerrID].smallMapX;
        var fromY = terrTable[fromTerrID].smallMapY;
        var toX = terrTable[toTerrID].smallMapX;
        var toY = terrTable[toTerrID].smallMapY;

        toX -= (toX - fromX) / 3;
        toY -= (toY - fromY) / 3;

        var start = {x: terrTable[terrID].smallMapX, y: terrTable[terrID].smallMapY};
        var end = {x: toX, y: toY};
        drawOrderArrow(start, end, 'Convoy');
    }
}


var orderArrows = {
    //array(0, 153, 2)
    'Move': {'color': new Array(196, 32, 0), //0, 153, 2),//
        'thickness': new Array(2, 4),
        'headAngle': Math.PI / 7,
        'headStart': 0.1,
        'headLength': new Array(12, 30),
        'border': new Array(0, 0)
    },
    'Support hold': {'color': new Array(67, 206, 16),
        'thickness': new Array(2, 4),
        'headAngle': Math.PI / 2,
        'headStart': 0.2,
        'headLength': new Array(8, 24),
        'border': new Array(0, 0)
    },
    'Support move': {'color': new Array(249, 249, 47),
        'thickness': new Array(2, 4),
        'headAngle': Math.PI / 7,
        'headStart': 0.4,
        'headLength': new Array(12, 30),
        'border': new Array(0, 0)
    },
    'Convoy': {'color': new Array(4, 113, 160),
        'thickness': new Array(2, 4),
        'headAngle': Math.PI / 7,
        'headStart': 0.1,
        'headLength': new Array(0, 0),
        'border': new Array(0, 0)
    },
    'Retreat': {'color': new Array(198, 39, 159),
        'thickness': new Array(2, 4),
        'headAngle': Math.PI / 7,
        'headStart': 0.1,
        'headLength': new Array(12, 30),
        'border': new Array(0, 0)
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

    var headStartX = endX - (endX - startX) * params['headStart'];
    var headStartY = endY - (endY - startY) * params['headStart'];

    var rad = lineAngle(start, end);

    var head = new Array(
            0, 0,
            -1 * params['headLength'][0] * Math.cos(params['headAngle']), -1 * params['headLength'][0] * Math.sin(params['headAngle']),
            -1 * params['headLength'][0] * Math.cos(params['headAngle']), params['headLength'][0] * Math.sin(params['headAngle'])
            );

    head = rotate(head, new Array(0, 0), rad);

    for (var i = 0; i < 6; i += 2) {
        head[i] += headStartX;
        head[i + 1] += headStartY;
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

function rotate(coordinates, rotateAround, rotateBy) {
    var transformMatrix = new Array(
            new Array(Math.cos(rotateBy), -1 * Math.sin(rotateBy)),
            new Array(Math.sin(rotateBy), Math.cos(rotateBy))
            );
    var coordNum = coordinates.length;
    for (var i = 0; i < coordNum; i += 2) {
        var x = coordinates[i];
        var y = coordinates[i + 1];

        x -= rotateAround[0];
        y -= rotateAround[1];

        var newX = x * transformMatrix[0][0] + y * transformMatrix[0][1];
        y = x * transformMatrix[1][0] + y * transformMatrix[1][1];
        x = newX;

        x += rotateAround[0];
        y += rotateAround[1];

        coordinates[i] = x;
        coordinates[i + 1] = y;
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

function lineAngle(from, to) {
    var xdiff = from.x - to.x;
    var ydiff = from.y - to.y;

    if (ydiff > 0) {
        if (xdiff == 0) {
            return ((-1 / 2) * Math.PI);
        } else if (xdiff > 0) {
            return (Math.PI + Math.abs(Math.atan(Math.abs(ydiff / xdiff))));
        } else {
            return (-1 * Math.abs(Math.atan(Math.abs(ydiff / xdiff))));
        }
    } else {
        if (xdiff == 0) {
            return (Math.PI / 2);
        } else if (xdiff > 0) {
            return (Math.PI - Math.abs(Math.atan(Math.abs(ydiff / xdiff))));
        } else {
            return (Math.abs(Math.atan(Math.abs(ydiff / xdiff))));
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
    canvasCtx.strokeStyle = "rgb(" + color[0] + "," + color[1] + "," + color[2] + ")";
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

function polygonMap(corners, outerRadius, innerRadius) {
    innerRadius = (typeof innerRadius !== 'undefined') ? innerRadius : false;

    var coords = new Array();

    for (var i = 0; i < corners; i++) {
        coords.push(0);

        if ((i % 2 != 0) && (innerRadius != false)) {
            coords.push(innerRadius);
        } else {
            coords.push(outerRadius);
        }

        coords = rotate(coords, new Array(0, 0), (2 * Math.PI / corners));
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
function drawDestroyedUnit(terrID, skipExt) {
    var terrTable = Territories.toObject();
    var complete = true;

    if ((typeof extension == 'function') && !skipExt) {
        complete = extension('destroy', terrID);
    }

    if (complete) {
        var size = 7.5;    //army width (15) / 2

        if (typeof destroyedPolygon === 'undefined') {
            var blackInner = polygonMap(14, size, (size / 2));
            var blackOuter = polygonMap(14, (size - 1), ((size - 1) / 2));

            var inner = polygonMap(14, (size - 2), ((size - 2) / 2));
            var outer = polygonMap(14, (size - 3), ((size - 3) / 2));

            outer = rotate(outer, new Array(0, 0), (Math.PI / 7));
            blackOuter = rotate(blackOuter, new Array(0, 0), (Math.PI / 7));

            destroyedPolygon = {
                'blackInner': blackInner,
                'blackOuter': blackOuter,
                'inner': inner,
                'outer': outer};
        }

        var position = {x: terrTable[terrID].smallMapX, y: terrTable[terrID].smallMapY};

        drawPolygon(position, new Array(0, 0, 0), Object.create(destroyedPolygon['blackInner']));
        drawPolygon(position, new Array(0, 0, 0), Object.create(destroyedPolygon['blackOuter']));
        drawPolygon(position, new Array(255, 100, 0), Object.create(destroyedPolygon['inner']));
        drawPolygon(position, new Array(255, 0, 0), Object.create(destroyedPolygon['outer']));
    }
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

var dislodgedPolygon;
function drawDislodgedUnit(terrID, skipExt) {
    var terrTable = Territories.toObject();
    var complete = true;

    if ((typeof extension == 'function') && !skipExt) {
        complete = extension('dislodge', terrID);
    }

    if (complete) {
        var size = 4.5;    //army hight (9) / 2

        if (typeof dislodgedPolygon === 'undefined') {
            var blackInner = polygonMap(10, size, (size / 2));
            var blackOuter = polygonMap(10, (size - 2), ((size - 2) / 2));

            var inner = polygonMap(10, (size - 3), ((size - 3) / 2));
            var outer = polygonMap(10, (size - 4), ((size - 4) / 2));

            outer = rotate(outer, new Array(0, 0), (Math.PI / 5));
            blackOuter = rotate(blackOuter, new Array(0, 0), (Math.PI / 5));

            dislodgedPolygon = {
                'blackInner': blackInner,
                'blackOuter': blackOuter,
                'inner': inner,
                'outer': outer};
        }

        var position = {x: terrTable[terrID].smallMapX, y: terrTable[terrID].smallMapY};

        drawPolygon(position, new Array(0, 0, 0), Object.create(dislodgedPolygon['blackInner']), true);
        drawPolygon(position, new Array(0, 0, 0), Object.create(dislodgedPolygon['blackOuter']), true);
        drawPolygon(position, new Array(255, 150, 0), Object.create(dislodgedPolygon['inner']), true);
        drawPolygon(position, new Array(255, 80, 0), Object.create(dislodgedPolygon['outer']), true);
    }
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

function drawPolygon(position, color, polygon, small) {
    small = (typeof small !== 'undefined') ? small : false;

    var x = new Number(position.x); //position.x was type String, which caused problems later
    var y = new Number(position.y);

    var corners = polygon.length;

    if (small) {
        x += 7.5;    //army width (15) / 2
        y -= 4.5;     //army height (9) / 2
    }

    for (var i = 0; i < corners * 2; i += 2) {
        polygon[i] += x;
        polygon[i + 1] += y;
    }

    canvasCtx.beginPath();
    canvasCtx.moveTo(polygon[0], polygon[1]);
    for (var i = 2; i < polygon.length; i += 2) {
        canvasCtx.lineTo(polygon[i], polygon[i + 1]);
    }
    canvasCtx.closePath();
    canvasCtx.fillStyle = "rgb(" + color[0] + "," + color[1] + "," + color[2] + ")";
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
function drawCreateUnit(terrID, type, skipExt) {
    var terrTable = Territories.toObject();
    var complete = true;

    if ((typeof extension == 'function') && !skipExt) {
        complete = extension('build', terrID);
    }

    if (complete) {
        var position = {x: terrTable[terrID].smallMapX, y: terrTable[terrID].smallMapY};

        var size = 7.5;    //army width (15) / 2

        var blackStar = polygonMap(10, size - 2, (size / 2));
        blackStar = rotate(blackStar, new Array(0, 0), Math.PI);

        var whiteStar = polygonMap(10, (size - 2), (size / 2 - 1));
        whiteStar = rotate(whiteStar, new Array(0, 0), Math.PI);

        drawPolygon(position, new Array(0, 0, 0), blackStar, true);

        drawPolygon(position, new Array(255, 255, 0), whiteStar, true);

        canvasCtx.fillStyle = "rgb(255,0,0)";
        if (type == "Build Fleet") {
            canvasCtx.fillText("F", position.x - 5, position.y);
        } else {
            canvasCtx.fillText("A", position.x - 5, position.y);
        }
    }
}