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
var SelectedTerr;
var SelectedTerrName;
var orderInProgress;
var needOwnUnit; //stores if the next territorySelcetion has to be an own Unit
var needUnit;
var order; //stores Data for the active Order
//var orderSM; //stores Data for active supportMove Order
var SMcoords;   //stores the toTerr-Data for supportMoves, because coast is not known, because moving unit is not known 
var orderCounter;//needed for build-phase, where orders aren't connected to specific territories //also used to save coordinates in buildPhase

function orderData(oInd, fromTerr) {  //saves TerrIDs for one order
    this.orderIndex = oInd;
    this.fromTerr = fromTerr;
    this.toTerr;
    this.terr;
}

function resetOrder() {
    iM(" -NEXT ORDER- ");
    orderInProgress = null;
    SelectedTerr = null;
    SelectedTerrName = null;
    needOwnUnit = true;
    needUnit = true;
    SMcoords = null;
    order = null;
    drawImage();
    if((context.phase=="Builds")&&(MyOrders.length!=0)&&(MyOrders[0].type != "Destroy"))
        greyOutTerritories(MyOrders[0].arrayToChoices(SupplyCenters.select(function(n){return MyOrders.pluck("ToTerritory").indexOf(n)==-1}).pluck("id")));
    else if((context.phase=="Retreats")&&(MyOrders.length != 0))
        greyOutTerritories(MyOrders[0].arrayToChoices(MyOrders.pluck("Unit").pluck("terrID")));
    else
        greyOutTerritories();
}

function selectTerritory(event) {
    if (IAready && IAactivated) {
        orderMenu.hide();
        var coor = getCoor(event);
        if (orderInProgress != "sMove") { //for sMove, decisions about if the unit is moving to coast or not are not posible at this moment
            SelectedTerr = getTerritory(coor.x, coor.y);
            if(SelectedTerr != null) {
                SelectedTerrName = Territories.toObject()[SelectedTerr].name;
            }else{
                SelectedTerrName = null;
            }
            if (orderInProgress != null) {               //an order will be completed
                selectionValid(orderInProgress);
            } else {
                if (SelectedTerr != null){
                    iM(SelectedTerrName);
                    
                    //v BUILD PHASE
                    //if(ownUnit(SelectedTerr)||(unit(SelectedTerr)&&(Territories.get(SelectedTerr).type=="Coast")&&(Territories.get(SelectedTerr).Unit.type=="Army"))){
                        showOrderMenu(coor);
                    //}
                }
            }
            if (context.phase == "Builds") {   //saves coordinates for builds on coasts to detect the right coast
                SMcoords = coor;
            }
        } else {
            sMove1(coor);
        }
    }
}

function getCoor(event) {
//Coor Cursor
    /* //var xCur = event.clientX + window.pageXOffset;
     var xCur = event.pointerX();
     var yCur = event.clientY + window.pageYOffset;*/

//Coor Map
    /*var element = document.getElementById("mapImage");
     var xMap = 0;
     var yMap = 0;
     while (element.tagName != "BODY") {
     xMap += element.offsetLeft;
     yMap += element.offsetTop;
     element = element.offsetParent;
     }*/

//Coor Cursor on Map
//alert($("mapImage").cumulativeOffset().toArray()[0]);
//alert(xMap);
    var imgOffset = mapCanvas.cumulativeOffset().toArray();
    var x = event.pointerX() - imgOffset[0] + mapCanDiv.scrollLeft;
    var y = event.pointerY() - imgOffset[1] + mapCanDiv.scrollTop;
                    //$("mapCanDiv").appendChild(new Element('div',{'style':'top:'+y+'px;left:'+x+'px;position:absolute;background-color:white;width:100px;z-index:2;'}).appendChild(new Element('p')).update("Hey! I'm a test").parentNode);

    return{x: x, y: y};
}

function getTerritory(x, y) {
    var terrTable = Territories.toObject();
    //var ImageColors = jsonData[1];
    
    var color = getColor(x, y); //color -> color of clicked pixel
    
    for (var terrID in terrTable) {
        //if(terrTable[terrID].type == "Sea"){
            if ((sameColor(color, getColor(terrTable[terrID].smallMapX, terrTable[terrID].smallMapY))) && (terrTable[terrID].coast == "No")) {//||(terrTable[terrID].coast == "Parent"))) {
                return terrID;
            } else if ((sameColor(color, getColor(terrTable[terrID].smallMapX, terrTable[terrID].smallMapY))) && (terrTable[terrID].coast == "Parent")) {
                return checkCoast(terrID, terrTable, x, y);
            }
        //}
    }
    return null;
}

function checkCoast(terrID, terrTable, x, y) {
    //alert("HeW");
    if ((orderInProgress == null) || (orderInProgress == "sMove1")) { //sMove1 -> supported unit's origin
        if (context.phase == "Retreats") {
            for (var i = 0; i < RetreatingUnits.length; i++) {
                var retreatTerrID = checkRetreatingUnitCoast(RetreatingUnits[i], terrID);
                if (retreatTerrID != null) {
                    return retreatTerrID;
                }
            }
            return terrID;
        } else if (!coastUnit(terrID) || ((context.phase == "Builds") && !(MyOrders[0].type == "Destroy"))) {
            return terrID;
        } else {
            return terrTable[terrID].Unit.Territory.id;
        }
    } else {
        //alert("behind else");
        switch (orderInProgress) {//work with getMovableTerritoriesCache
            case "move":
            case "sMove2":
                //alert("I'm here");
                var territory = terrTable[order.fromTerr];
                if (territory.coast != "Child") {
                    if (territory.Unit != null) {       //if something went wrong ...
                        if (territory.Unit.type == "Fleet") {
                            //alert("fleet");

                            //multipleOptions//
                            return getCoast(terrID, order.fromTerr, terrTable, x, y);
                        }
                    }
                    return terrID;
                } else {
                    //alert("fleet");
                    //multipleOptions - unit from coast -> only fleet possible
                    return getCoast(terrID, order.fromTerr, terrTable, x, y);
                }
                /*if (Units.toObject()[getTerrStatus(order.fromTerr).unitID].type == "army") {
                 return terrTable[terrID].name;
                 } else {
                 //if (multipleOptions()) {
                 return terrTable[terrID].name;
                 //}
                 }*/
                break;
            case "retreat":
                var unit = getUnitByTerrID[order.fromTerr];
                var territory = unit.Territory;
                if (territory.coast != "Child") {
                    if (unit.type == "Fleet") {
                        //alert("fleet");

                        //multipleOptions//
                        return getCoast(terrID, order.fromTerr, terrTable, x, y);
                    }
                    return terrID;
                } else {
                    //alert("fleet");
                    //multipleOptions - unit from coast -> only fleet possible
                    return getCoast(terrID, order.fromTerr, terrTable, x, y);
                }
                break;
            default:    //e.g. sHold, sMove
                return terrID;
                break;
        }
    }
}

function checkRetreatingUnitCoast(unit, terrID) {
    if (unit.Territory.id == terrID) {
        return unit.Territory.id;
        /*} else {
         var coasts = getChildren(terrID);
         for (var i = 0; i < coasts.length; i++) {
         if (unit.Territory.id == coasts[i].id) {
         return unit.Territory.name;
         }
         }
         return null;*/
    } else if (unit.Territory.coastParentID == terrID) {
        return unit.Territory.id;
    }
    return null;
    /*if(unit.Territory.coast == "No"){
     if(unit.Territory.id == terrID){
     return unit.Territory.name;
     }else{
     return null;
     }
     } else if (unit.Territory.coast == "Parent") {
     
     }*/
}

/*
 * Get all coasts of the clicked Territory, that can be reached by the moving fleet.
 * To get the coast, the possible borderTerritories are examined. 
 * If more coasts of the territory are possible, the selected coast is detected by the coords of the click and the terr.coords.
 */

function getCoast(terrID, fromTerrID, terrTable, x, y) {      //get all coasts of the clicked Territory, that can be reached by the moving fleet
    var coasts = new Array();
    var fromTerrBorders = terrTable[fromTerrID].CoastalBorders;
    for (var i = 0; i < fromTerrBorders.length; i++) {
        var checkedTerr = terrTable[fromTerrBorders[i].id];  //the Territory, which is inspected
        if (checkedTerr.coast == "Child") {
            if (checkedTerr.coastParentID == terrID) {
                coasts.push(checkedTerr);
            }
        }
    }
    if (coasts == null) { //if something went wrong :(
        return terrID;
    } else if (coasts.length == 1) {   //only one coast can be selected
        return coasts[0].id;
    } else {  //more coasts are possible
        //alert("more possible");
        return getCoastByCoords(coasts, x, y).id;
    }
}

function getCoastByCoords(coasts, x, y) {
    var distance = new Array();
    for (var i = 0; i < coasts.length; i++) {               //calculate distance form clicked point for each coast
        var xdiff = Math.abs(x - coasts[i].smallMapX);
        var ydiff = Math.abs(y - coasts[i].smallMapY);
        distance[i] = Math.sqrt(xdiff * xdiff + ydiff * ydiff);
        //alert("distance of "+coasts[i].name+": "+distance[i]);
    }
    var shortInd = 0;       //stores the index of the coast with the smallest distance
    for (var i = 1; i < distance.length; i++) {     //gets the index of the coast with the smallest distance to the clicked point
        if (distance[i] < distance[i - 1]) {
            shortInd = i;
        }
    }
    return coasts[shortInd];
}

function coastUnit(terrID) {
    var territory = Territories.toObject()[terrID];
    if (territory.unitID == null) { //no unit in this territory, neither on coast nor on main territory
        return false;
    } else {
        if (territory.Unit.Territory.coast == "Child") {  //unit/fleet placed on coast
            return true;
        } else {
            return false;
        }
    }

}

/*function getTerrStatus(terrID) {  //won't work with "untouched"/neutral territories
 for (var i = 0; i < TerrStatus.length; i++) {
 if (TerrStatus[i].id == terrID)
 return TerrStatus[i];
 }
 return null;
 }*/

/*function getTerritoryID(name) {
    var terrTable = Territories.toObject();
    for (var terrID in terrTable) {
        if (name == terrTable[terrID].name)
            return terrID;
    }
    return null;
}*/

function getUnitType(terrID) {
    var units = Units.toObject();
    for (var unitID in units) {
        if ((units[unitID].terrID == terrID))
            return units[unitID].type;
    }
    return null;
}

function sameColor(c1, c2) {
    for (var i = 0; (i < c1.length) && (i < c2.length); i++) {
        if (c1[i] !== c2[i])
            return false;
    }
    return true;
    //return (c1.toString()===c2.toString());
}

function getColor(x, y) {
    var pixelPos = (y*IAmapDat.width*4+x*4);
    return [IAmapDat.data[pixelPos], IAmapDat.data[pixelPos+1], IAmapDat.data[pixelPos+2], IAmapDat.data[pixelPos+3]];//getImageData(x, y, 1, 1).data;
}

function unit(terrID) {
    /*var units = Units.toObject();
     var terrStat = getTerrStatus(getTerritoryID(terrName));
     if (terrStat!=null){
     if(getTerrStatus(getTerritoryID(terrName)).unitID != null) {
     return true;
     }
     }
     for (var unitID in units) {     //if terrName is a Coast (not detected by code above)
     if (units[unitID].terrID == getTerritoryID(terrName))
     return true;
     }*/
    var territory = Territories.toObject()[terrID];
    if (territory.coast != "Child") {
        if (territory.unitID != null) {
            return true;
        }
    } else if (territory.coastParent.unitID != null) {
        return true;
    }
    return false;
}

function ownUnit(terrID) {
    var territory = Territories.toObject()[terrID];
    if (territory.coast != "Child") {
        if (territory.unitID != null) {
            if (territory.Unit.countryID == context.countryID) {
                return true;
            }
        }
    } else if (territory.coastParent.unitID != null) {
        if (territory.coastParent.Unit.countryID == context.countryID) {
            return true;
        }
    }
    return false;
}

function ownRetreatUnit(terrID) {
    var territory = Territories.toObject()[terrID];
    if (getUnits().indexOf(terrID) != -1) {
        return true;
    }/*else{ 
     var coasts = getChildren(getTerritoryID(terrName));
     for(var i=0; i<coasts.length; i++){
     if(getUnits().indexOf(coasts[i].name) != -1){
     return true;
     }
     }
     }*/
    /*if (territory.unitID != null) {
     if (territory.Unit.countryID == context.countryID) {
     return true;
     }
     }*/
    //}else{
    /*} else if (territory.coastParent.unitID != null) {
     if (territory.coastParent.Unit.countryID == context.countryID) {
     return true;
     }
     }*/
    return false;

}

function getUnits() {
    /*var orderUnits = new Array();
     for (var orderIndex = 0; orderEle.select("tr")[orderIndex] != null; orderIndex++) {
     var oBegin = orderEle.select("tr")[orderIndex].select("td")[1].select("span")[1].innerHTML;
     /*if (oBegin.charAt(12) == " ")  //Order for fleet: The fleet at_ -> 13th Element of String is " " - army has one element less
     orderUnits[orderIndex] = oBegin.substring(13, oBegin.length - 1);
     else
     orderUnits[orderIndex] = oBegin.substring(12, oBegin.length - 1);/
     oBegin = oBegin.sub('The fleet at ', '').strip();
     oBegin = oBegin.sub('The army at ', '').strip();
     orderUnits[orderIndex] = oBegin;
     }*/
    var orderUnits = new Array();
    for (var i = 0; i < MyOrders.length; i++) {
        orderUnits.push(MyOrders[i].Unit.Territory.id);
        //alert(MyOrders[i].Unit.Territory.name);
    }
    return orderUnits;
}

function isNeighbor(terrID1, terrID2) {
    var territory = Territories.toObject()[terrID1];
    for (var i = 0; i < territory.Borders.length; i++) {
        if (territory.Borders[i].id == terrID2) {
            return true;
        }
    }
    for (var i = 0; i < territory.CoastalBorders.length; i++) { //checks also CoastalBorders!
        if (territory.CoastalBorders[i].id == terrID2) {
            return true;
        }
    }
    return false;
}

function getDropDown(index, name) {
    var orderSeg = orderEle.select("tr")[index].select("td")[1].select("span");
    switch (name) {
        case 'type':
            var dropDown = orderSeg[2].select("select")[0];
            break;
        case 'toTerrID':
            var dropDown = orderSeg[3].select("select")[0];
            break;
        case 'fromTerrID':
            var dropDown = orderSeg[4].select("select")[0];
            break;
        case 'viaConvoy':
            var dropDown = orderSeg[5].select("select")[0];
            break;
    }
    return dropDown;
}

function setOrder(index, name, value) {
    var DropDown = getDropDown(index, name);
    var options = DropDown.options;
    for (var i = 0; i < options.length; i++) {
        if (options[i].value == value) {
            DropDown.setValue(value);
            return true;
        }
    }
    return false;
}

///*if(orderSeg[3]!=null)*/ orderSeg[3].removeChild(orderSeg[3].innerHTML); orderSeg[3].removeChild(orderSeg[3].getelementsByTagName("select")[0]);

function selectionValid(order) {
    if (SelectedTerr == null) {
        alert("No Territory selected!");
        return false;
    } else if (!unit(SelectedTerr) && needUnit) {
        alert("No unit selected (" + SelectedTerrName + ")!");
        return false;
    } else if (!ownUnit(SelectedTerr) && needOwnUnit && (order != 'convoy')) {      //for convoys, foreign units as a start unit has to be allowed, because convoys can be only setted this way!
        alert("No own unit selected (" + SelectedTerrName + ")!");
        return false;
    } else if (((order == 'disband') || ((order == 'retreat') && (orderInProgress == null))) && !ownRetreatUnit(SelectedTerr)) {
        alert("No own unit selected, that has to retreat (" + SelectedTerrName + ")!");
        return false;
    } else if (orderInProgress != null) {
        if (orderInProgress != order) {
            alert(" Different order not finished!");
            return false;
        } else {
            switch (order) {
                case 'move':
                    move2();
                    break;
                case 'sHold':
                    sHold2();
                    break;
                case 'sMove1':
                    sMove2();
                    break;
                    /*case 'sMove2':
                     sMove2();
                     break;*/
                case 'convoy':
                    convoy2();
                    break;
                    /*case 'convoy2':
                     convoy3();
                     break;*/
                case 'retreat':
                    retreat2();
                    break;
            }
            return false;
        }
    } else if ((order == "convoy") && (getUnitType(SelectedTerr) != "Army")) {
        alert("No army selected!");
        return false;
    } else {
        return true;
    }
}

function hold() {
    if (selectionValid('hold')) {
        var oInd = getUnits().indexOf(SelectedTerr);
        var orderPart = 'type';
        var orderValue = 'Hold';
        if (setOrder(oInd, orderPart, orderValue)) {
            orderInProgress = 'hold';
            var MyOrder = MyOrders[oInd];
            var DropDown = getDropDown(oInd, orderPart);
            /*var changedName = MyOrder.requirements.find( function(namae) {
             return ( DropDown.name == 'orderForm['+MyOrder.id+']['+namae+']' );
             },MyOrder);
             #*/
            DropDown.setStyle({backgroundColor: '#ffd4c9'});
            MyOrder.inputValue(orderPart, DropDown.getValue());
            iM(" holds");
            resetOrder();
        } else {
            alert("'" + orderValue + "' as '" + orderPart + "' could not be selected! Order reset!");
            resetOrder();
        }
    }
//var element = document.getElementsByTagName("select");
    /*iM(element[0].childNodes[0].text);
     var ele2 = document.getElementById("orderFormElement").getElementsByTagName("tr")[0].getElementsByTagName("td")[1].getElementsByTagName("span")[1].innerHTML;
     iM(ele2);//innerHTML);
     if(ele2.match(new RegExp(save))){
     alert('it fits!')
     }
     iM('TEST');*/
}

function move() {
    if (selectionValid('move')) {
        var oInd = getUnits().indexOf(SelectedTerr);
        var orderPart = 'type';
        var orderValue = 'Move';
        if (setOrder(oInd, orderPart, orderValue)) {
            orderInProgress = 'move';
            //alert(MyOrders[0].id);

            var MyOrder = MyOrders[oInd];
            var DropDown = getDropDown(oInd, orderPart);
            DropDown.setStyle({backgroundColor: '#ffd4c9'});
            MyOrder.inputValue(orderPart, DropDown.getValue());
            order = new orderData(oInd, SelectedTerr);
            needOwnUnit = false;
            needUnit = false;
            iM(" moves to ");
            greyOutTerritories(MyOrder.arrayToChoices(MyOrder.Unit.getMovableTerritories().pluck("id")));
        } else {
            alert("'" + orderValue + "' as '" + orderPart + "' could not be selected! Order reset!");
            resetOrder();
        }
    }
}

function movePossible(fromID, toID, coastTreatment) { //could be make shorter toTerrChoices (MyOrders)
    var territory = Territories.toObject()[fromID];
    if (territory.coast != "Child") {
        var unit = territory.Unit;
    } else {
        var unit = territory.coastParent.Unit;
    }

    if (unit.type == "Fleet") {
        if (coastTreatment != "ignoreCoasts") {
            for (var i = 0; i < territory.CoastalBorders.length; i++) {
                if (territory.CoastalBorders[i].id == toID) {
                    return territory.CoastalBorders[i].f;
                }
            }
        } else {
            for (var i = 0; i < territory.Borders.length; i++) {
                if (territory.Borders[i].id == toID) {
                    return territory.Borders[i].f;
                }
            }
        }
    } else {
        for (var i = 0; i < territory.Borders.length; i++) {
            if (territory.Borders[i].id == toID) {
                return territory.Borders[i].a;
            }
        }
    }
}

function move2() {
    var oInd = order.orderIndex;
    if (!isNeighbor(order.fromTerr, SelectedTerr)) {
        alert(SelectedTerrName + " not neighbor of " + Territories.toObject()[order.fromTerr].name + " (use CONVOY instead of MOVE for moves via convoy)!");
    } else if (!movePossible(order.fromTerr, SelectedTerr)) {
        alert("Selected unit can not move to " + SelectedTerrName + " (wrong type)!");
    } else {
        var orderPart = 'toTerrID';
        /*if (getUnitType(getTerritoryID(terrName)) == "Fleet") {     //check for fleets if Territory has Coasts
         var orderValue = getTerritoryID(terrName);
         } else {
         var orderValue = getTerritoryID(terrName);
         }*/
        var orderValue = SelectedTerr;
        if (setOrder(oInd, orderPart, orderValue)) {
            orderInProgress = 'move2';
            var MyOrder = MyOrders[oInd];
            var DropDown = getDropDown(oInd, orderPart);
            DropDown.setStyle({backgroundColor: '#ffd4c9'});
            MyOrder.inputValue(orderPart, DropDown.getValue());

            if (!MyOrder.isComplete) {    //viaConvoy has to be selected
                if (setOrder(oInd, "viaConvoy", "No")) {   //Convoys can be set via Convoy-Button only 
                    var DropDown = getDropDown(oInd, "viaConvoy");
                    DropDown.setStyle({backgroundColor: '#ffd4c9'});
                    MyOrder.inputValue("viaConvoy", DropDown.getValue());
                } else {
                    alert("'No' as 'viaConvoy' could not be selected! Order reset!");
                }
            }

            order.toTerr = orderValue;
            iM(SelectedTerrName);
            resetOrder();
        } else {
            alert("'" + SelectedTerrName + "' as '" + orderPart + "' could not be selected! Order reset!");
            resetOrder();
        }
    }
}

function sHold() {
    if (selectionValid('move')) {
        var oInd = getUnits().indexOf(SelectedTerr);
        var orderPart = 'type';
        var orderValue = 'Support hold';
        if (setOrder(oInd, orderPart, orderValue)) {
            orderInProgress = 'sHold';
            //alert(MyOrders[0].id);

            var MyOrder = MyOrders[oInd];
            var DropDown = getDropDown(oInd, orderPart);
            DropDown.setStyle({backgroundColor: '#ffd4c9'});
            MyOrder.inputValue(orderPart, DropDown.getValue());
            order = new orderData(oInd, SelectedTerr);
            needOwnUnit = false;
            needUnit = true;
            iM(" supports the holding unit in ");
            greyOutTerritories(MyOrder.toTerrChoices);
        } else {
            alert("'" + orderValue + "' as '" + orderPart + "' could not be selected! Order reset!");
            resetOrder();
        }
    }
}

function sHold2() {
    if (!isNeighbor(order.fromTerr, SelectedTerr)) {
        alert(SelectedTerrName + " not neighbor of " + Territories.toObject()[order.fromTerr].name + "!");
    } else if (!movePossible(order.fromTerr, SelectedTerr)) {
        alert("Selected unit can not support unit in " + SelectedTerrName + " (wrong type)!");
    } else {
        var oInd = order.orderIndex;
        var orderPart = 'toTerrID';
        var orderValue = SelectedTerr;
        if (setOrder(oInd, orderPart, orderValue)) {
            orderInProgress = 'sHold2';
            var MyOrder = MyOrders[oInd];
            var DropDown = getDropDown(oInd, orderPart);
            DropDown.setStyle({backgroundColor: '#ffd4c9'});
            MyOrder.inputValue(orderPart, DropDown.getValue());
            order.toTerr = orderValue;
            iM(SelectedTerrName);
            resetOrder();
        } else {
            alert("'" + SelectedTerrName + "' as '" + orderPart + "' could not be selected! Order reset!");
            resetOrder();
        }
    }
}

function sMove() {
    if (selectionValid('sMove')) {
        var oInd = getUnits().indexOf(SelectedTerr);
        var orderPart = 'type';
        var orderValue = 'Support move';
        if (setOrder(oInd, orderPart, orderValue)) {
            orderInProgress = 'sMove';
            //alert(MyOrders[0].id);

            var MyOrder = MyOrders[oInd];
            var DropDown = getDropDown(oInd, orderPart);
            DropDown.setStyle({backgroundColor: '#ffd4c9'});
            MyOrder.inputValue(orderPart, DropDown.getValue());
            order = new orderData(oInd, SelectedTerr);
            order.terr = SelectedTerr;
            needOwnUnit = false;
            needUnit = false;
            iM(" supports the moving unit to ");
            greyOutTerritories(MyOrder.toTerrChoices);
        } else {
            alert("'" + orderValue + "' as '" + orderPart + "' could not be selected! Order reset!");
            resetOrder();
        }
    }
}

function sMove1(coor) {
    SelectedTerr = getTerritory(coor.x, coor.y);
    SelectedTerrName = Territories.toObject()[SelectedTerr].name
    if (!isNeighbor(order.terr, SelectedTerr)) {
        alert(SelectedTerrName + " not neighbor of " + Territories.toObject()[order.terr].name + "!");
    } else if (!movePossible(order.terr, SelectedTerr, "ignoreCoasts")) {
        alert("Selected unit can not support move to " + SelectedTerrName + " (wrong type)!");
    } else {
        iM(SelectedTerrName + " from ");
        //greyOutTerritories(MyOrders[order.oInd].fromTerrChoices);NotThatEasy
        var MyOrder = MyOrders[order.orderIndex];
        var fromTerr = Units.get(MyOrder.unitID).getSupportMoveFromChoices(Territories.get(SelectedTerr));
        greyOutTerritories(MyOrder.arrayToChoices(fromTerr));
        SMcoords = coor;
        orderInProgress = "sMove1";
        needUnit = true;
    }
}

function getParentID(terrID) {
    var territory = Territories.toObject()[terrID];
    if (territory.coast == "Child") {
        return territory.coastParentID;
    } else {
        return terrID;
    }
}

function convoyPossible(fromID, toID) {
    var territory = Territories.toObject()[fromID];
    if (territory.coast != "Child") {
        var unit = territory.Unit;
    } else {
        var unit = territory.coastParent.Unit;
    }
    if (unit.type == "Fleet") {
        return false;
    } else if (territory.ConvoyGroup == null) {
        return false;
    } else if (territory.ConvoyGroup.Coasts) {
        for (var i = 0; i < territory.ConvoyGroup.Coasts.length; i++) {
            if (territory.ConvoyGroup.Coasts[i].id == toID) {
                return true;
            }
        }
    }
    return false;
}

function sMove2() {
    //alert("I'm here");
    var oInd = order.orderIndex;
    var fromTerrID = SelectedTerr;    //last selected Terr is toTerr, because Coordinates for fromTerr are only saved in SMcoords
    var fromTerrName = SelectedTerrName;
    order.fromTerr = fromTerrID;        //saved in order.fromTerr for coastTerritory-Movements

    orderInProgress = 'sMove2';         //needed to detect the rigth coast-treatment in checkCoast()
    var toTerrID = getTerritory(SMcoords.x, SMcoords.y);
    var toTerrName = Territories.toObject()[toTerrID].name;    //gets fromTerrID with saved SMCoords
    order.toTerr = toTerrID;

    var cp = convoyPossible(order.fromTerr, order.toTerr);
    if ((!isNeighbor(order.fromTerr, order.toTerr)) && !cp) {
        orderInProgress = 'sMove1';
        alert("Unit in " + SelectedTerrName + " can not move to " + Territories.toObject()[order.toTerr].name + "!");
    } else if (!movePossible(order.fromTerr, order.toTerr) && !cp) {
        orderInProgress = 'sMove1';
        alert("Unit in " + SelectedTerrName + " can not move to " + Territories.toObject()[order.toTerr].name + " (wrong type)!");
    } else {

        //sMove to:
        var orderPart = 'toTerrID';
        var orderValue = getParentID(toTerrID);
        if (setOrder(oInd, orderPart, orderValue)) {
            var MyOrder = MyOrders[oInd];
            var DropDown = getDropDown(oInd, orderPart);
            DropDown.setStyle({backgroundColor: '#ffd4c9'});
            MyOrder.inputValue(orderPart, DropDown.getValue());
            //order.toTerr = orderValue;

            //sMove from:
            orderPart = 'fromTerrID';
            orderValue = getParentID(fromTerrID);
            if (setOrder(oInd, orderPart, orderValue)) {
                orderInProgress = 'sMove3';
                //var MyOrder = MyOrders[oInd];
                DropDown = getDropDown(oInd, orderPart);
                DropDown.setStyle({backgroundColor: '#ffd4c9'});
                MyOrder.inputValue(orderPart, DropDown.getValue());
                //orderSM.fromTerr = orderValue;
                //drawSupportHold();
                //resetOrder();

                moveSupport(fromTerrID, toTerrID);
                iM(SelectedTerrName);
                resetOrder();
            } else {
                alert("'" + SelectedTerrName + "' as '" + orderPart + "' could not be selected! Order reset!");
                resetOrder();
            }
        } else {
            alert("'" + toTerrName + "' as '" + orderPart + "' could not be selected! Order reset!");
            resetOrder();
        }
    }
    //order.toTerr ...
    //drawSupportHold();
    //needOwnUnit = false;
    //needUnit = true;
    //resetOrder();
    /*var orderPart = 'toTerrID';
     var orderValue = getTerritoryID(terrName);
     if (setOrder(oInd, orderPart, orderValue)) {
     //Move();   //later, because a bit complicated
     orderInProgress = 'sMove2';
     var MyOrder = MyOrders[oInd];
     var DropDown = getDropDown(oInd, orderPart);
     DropDown.setStyle({backgroundColor: '#ffd4c9'});
     MyOrder.inputValue(orderPart, DropDown.getValue());
     orderSM.toTerr = orderValue;
     //drawSupportHold();
     needOwnUnit = false;
     needUnit = true;
     //resetOrder();
     } else {
     alert("'" + terrName + "' as '" + orderPart + "' could not be selected! Order reset!");
     resetOrder();
     }*/
}

function orderAlreadySet(fromID, toID, type) {
    var territory = Territories.toObject()[fromID];
    if (territory.coast != "Child") {
        if ((territory.Unit.Order.toTerrID == toID) && (territory.Unit.Order.type == type)) {
            return true;
        } else {
            return false;
        }
    } else {
        if ((territory.coastParent.Unit.Order.toTerrID == toID) && (territory.coastParent.Unit.Order.type == type)) {
            return true;
        } else {
            return false;
        }
    }
}

function moveSupport(fromTerrID, toTerrID) {  //sets a move with Data from sMove2()

    //if (selectionValid('move')) {
    if (ownUnit(fromTerrID)) {  //only enters order to move for own units
        if (!orderAlreadySet(fromTerrID, toTerrID, 'Move')) {
            var oInd = getUnits().indexOf(fromTerrID);
            var orderPart = 'type';
            var orderValue = 'Move';
            if (setOrder(oInd, orderPart, orderValue)) {
                orderInProgress = 'move';
                //alert(MyOrders[0].id);

                var MyOrder = MyOrders[oInd];
                var DropDown = getDropDown(oInd, orderPart);
                DropDown.setStyle({backgroundColor: '#ffd4c9'});
                MyOrder.inputValue(orderPart, DropDown.getValue());
                order = new orderData(oInd, SelectedTerr);
                needOwnUnit = false;
                needUnit = false;
                //iM(" moves to ");
                moveSupport2(toTerrID);
            } else {
                alert("'" + orderValue + "' as '" + orderPart + "' could not be selected! Order reset!");
                resetOrder();
            }
        }
    }
    //}
}

function moveSupport2(toTerrID) {
    var oInd = order.orderIndex;
    var orderPart = 'toTerrID';
    /*if (getUnitType(getTerritoryID(terrName)) == "Fleet") {     //check for fleets if Territory has Coasts
     var orderValue = getTerritoryID(terrName);
     } else {
     var orderValue = getTerritoryID(terrName);
     }*/
    var orderValue = toTerrID;
    if (setOrder(oInd, orderPart, orderValue)) {
        orderInProgress = 'move2';
        var MyOrder = MyOrders[oInd];
        var DropDown = getDropDown(oInd, orderPart);
        DropDown.setStyle({backgroundColor: '#ffd4c9'});
        MyOrder.inputValue(orderPart, DropDown.getValue());

        if (!MyOrder.isComplete) {    //viaConvoy has to be selected
            if (setOrder(oInd, "viaConvoy", "No")) {   //Convoys can be set via Convoy-Button only 
                var DropDown = getDropDown(oInd, "viaConvoy");
                DropDown.setStyle({backgroundColor: '#ffd4c9'});
                MyOrder.inputValue("viaConvoy", DropDown.getValue());
            } else {
                alert("'No' as 'viaConvoy' could not be selected! Order reset!");
            }
        }

        order.toTerr = orderValue;
        //drawMove();
        //resetOrder();
    } else {
        alert("'" + SelectedTerrName + "' as '" + orderPart + "' could not be selected! Order reset!");
        resetOrder();
    }
}

function convoy() {
    if (selectionValid('convoy')) {
        if (ownUnit(SelectedTerr)) {  //only sets order to move for own units
            var oInd = getUnits().indexOf(SelectedTerr);
            var orderPart = 'type';
            var orderValue = 'Move';
            if (setOrder(oInd, orderPart, orderValue)) {
                orderInProgress = 'convoy';
                //alert(MyOrders[0].id);

                var MyOrder = MyOrders[oInd];
                var DropDown = getDropDown(oInd, orderPart);
                DropDown.setStyle({backgroundColor: '#ffd4c9'});
                MyOrder.inputValue(orderPart, DropDown.getValue());
                order = new orderData(oInd, SelectedTerr);
                order.terr = new Array();   //stores the convoyPath
                order.terr.push(order.fromTerr);
                needOwnUnit = false;
                needUnit = false;
                iM(" moves via ");
            } else {
                alert("'" + orderValue + "' as '" + orderPart + "' could not be selected! Order reset!");
                resetOrder();
                return;
            }
        } else {
            orderInProgress = 'convoy';
            order = new orderData(null, SelectedTerr);
            order.terr = new Array();   //stores the convoyPath
            order.terr.push(order.fromTerr);
            needOwnUnit = false;
            needUnit = false;
            iM(" moves via ");
        }
        var convoyTerritories = Territories.get(SelectedTerr).getBorderUnits().select(function(n) {return n.Territory.type == "Sea"}).pluck("terrID");
        greyOutTerritories(MyOrders[0].arrayToChoices(convoyTerritories));
    }
}

function convoy2() {
    var terrTable = Territories.toObject();
    var terrID = SelectedTerr;
    var unitType = getUnitType(terrID);
    if (terrTable[terrID].type != "Sea") {
        if (!isNeighbor(order.terr[order.terr.length - 1], terrID)) {
            alert(SelectedTerrName + " not neighbor of " + terrTable[order.terr[order.terr.length - 1]].name + "!");
        } else {
            if (order.terr.length > 1) {
                convoy3(terrID);  //handles the final building of Convoy
            } else {
                alert("No fleet selected (" + SelectedTerrName + ")!");
            }
        }
    } else {  //unit type has to be fleet (unit() tested with selectionValid()
        if (!unit(SelectedTerr)) {
            alert("No Unit selected (" + SelectedTerrName + ")!");
        } else {
            if (!isNeighbor(order.terr[order.terr.length - 1], terrID)) {
                alert("Fleet (" + SelectedTerrName + ") not neighbor of " + terrTable[order.terr[order.terr.length - 1]].name + "!");
            } else {
                if (terrTable[terrID].type != "Sea") {
                    alert("Convoying fleet not in Sea-Territory");
                } else {
                    if (order.terr.length > 1) {
                        iM(", ")
                    }
                    iM(SelectedTerrName);
                    order.terr.push(terrID);
                    
                    var convoyTerritories = Territories.get(SelectedTerr).getBorderUnits().select(function(n) {return n.Territory.type == "Sea"}).pluck("terrID");
                    convoyTerritories = convoyTerritories.concat(Territories.get(SelectedTerr).getBorderTerritories().select(function(n) {return n.type == "Coast"}).pluck("id"));
                    convoyTerritories = convoyTerritories.select(function(n) {return order.terr.indexOf(n)==-1});
                    greyOutTerritories(MyOrders[0].arrayToChoices(convoyTerritories));
                }
            }
        }
    }
    /*var orderPart = 'toTerrID';
     var orderValue = getTerritoryID(terrName);
     if (setOrder(oInd, orderPart, orderValue)) {
     orderInProgress = 'move2';
     var MyOrder = MyOrders[oInd];
     var DropDown = getDropDown(oInd, orderPart);
     DropDown.setStyle({backgroundColor: '#ffd4c9'});
     MyOrder.inputValue(orderPart, DropDown.getValue());
     
     if(!MyOrder.isComplete){    //viaConvoy has to be selected
     if(setOrder(oInd, "viaConvoy", "No")){   //Convoys can be set via Convoy-Button only 
     var DropDown = getDropDown(oInd, "viaConvoy");
     DropDown.setStyle({backgroundColor: '#ffd4c9'});
     MyOrder.inputValue("viaConvoy", DropDown.getValue());
     }else{
     alert("'No' as 'viaConvoy' could not be selected! Order reset!");
     }
     }
     
     order.toTerr = orderValue;
     //drawMove();
     resetOrder();
     } else {
     alert("'" + terrName + "' as '" + orderPart + "' could not be selected! Order reset!");
     resetOrder();
     }*/
}

function convoy3(terrID) {
    var oInd = order.orderIndex;

    if (oInd != null) {   //if null, moving unit a foreign unit -> order can't be setted
        //order for army first
        var orderPart = 'toTerrID';
        var orderValue = terrID;
        if (setOrder(oInd, orderPart, orderValue)) {
            orderInProgress = 'convoy2';
            var MyOrder = MyOrders[oInd];
            var DropDown = getDropDown(oInd, orderPart);
            DropDown.setStyle({backgroundColor: '#ffd4c9'});
            MyOrder.inputValue(orderPart, DropDown.getValue());

            if (!MyOrder.isComplete) {    //viaConvoy has to be selected
                if (setOrder(oInd, "viaConvoy", "Yes")) {   //Convoys can be set via Convoy-Button only 
                    var DropDown = getDropDown(oInd, "viaConvoy");
                    DropDown.setStyle({backgroundColor: '#ffd4c9'});
                    MyOrder.inputValue("viaConvoy", DropDown.getValue());
                } else {
                    alert("'Yes' as 'viaConvoy' could not be selected! Order reset!");
                }
            }

            iM(" to " + SelectedTerrName);
            order.toTerr = orderValue;
            setConvoy();
            resetOrder();
        } else {
            alert("'" + SelectedTerrName + "' as '" + orderPart + "' could not be selected! Order reset!");
            resetOrder();
        }
    } else {
        orderInProgress = 'convoy2';
        iM(" to " + SelectedTerrName);
        order.toTerr = terrID;
        setConvoy();
        resetOrder();
    }
}

function setConvoy() {
    var terrTable = Territories.toObject();
    for (var i = 1; i < order.terr.length; i++) { //set convoy-order for every fleet in convoy-path (order.terr[0] -> moving army)
        var terrID = order.terr[i];
        if (ownUnit(terrID)) {    //only set convoys for own units
            var oInd = getUnits().indexOf(terrID);
            var orderPart = 'type';
            var orderValue = 'Convoy';
            if (setOrder(oInd, orderPart, orderValue)) {
                var MyOrder = MyOrders[oInd];
                var DropDown = getDropDown(oInd, orderPart);
                DropDown.setStyle({backgroundColor: '#ffd4c9'});
                MyOrder.inputValue(orderPart, DropDown.getValue());

                orderPart = 'toTerrID';
                orderValue = order.toTerr;
                if (setOrder(oInd, orderPart, orderValue)) {
                    DropDown = getDropDown(oInd, orderPart);
                    DropDown.setStyle({backgroundColor: '#ffd4c9'});
                    MyOrder.inputValue(orderPart, DropDown.getValue());

                    orderPart = 'fromTerrID';
                    orderValue = order.fromTerr;
                    if (setOrder(oInd, orderPart, orderValue)) {
                        DropDown = getDropDown(oInd, orderPart);
                        DropDown.setStyle({backgroundColor: '#ffd4c9'});
                        MyOrder.inputValue(orderPart, DropDown.getValue());
                    } else {
                        alert("'" + terrTable[orderValue].name + "' as '" + orderPart + "' could not be selected! Order reset!");
                        resetOrder();
                    }
                } else {
                    alert("'" + terrTable[orderValue].name + "' as '" + orderPart + "' could not be selected! Order reset!");
                    resetOrder();
                }
            } else {
                alert("'" + orderValue + "' as '" + orderPart + "' could not be selected! Order reset!");
                resetOrder();
            }
        }
    }
}

function destroy() {
    /*needUnit = true;
     needOwnUnit = true;*/
    if (selectionValid('destroy')) {
        if (orderCounter == null) {
            orderCounter = 0;
        } else {
            orderCounter++;
        }
        var oInd = orderCounter % MyOrders.length;
        var orderPart = 'toTerrID';
        var orderValue = Territories.toObject()[SelectedTerr].coastParentID;
        if (setOrder(oInd, orderPart, orderValue)) {
            orderInProgress = 'destroy';
            var MyOrder = MyOrders[oInd];
            var DropDown = getDropDown(oInd, orderPart);
            /*var changedName = MyOrder.requirements.find( function(namae) {
             return ( DropDown.name == 'orderForm['+MyOrder.id+']['+namae+']' );
             },MyOrder);
             #*/
            DropDown.setStyle({backgroundColor: '#ffd4c9'});
            MyOrder.inputValue(orderPart, DropDown.getValue());
            iM(" is destroyed");
            resetOrder();
        } else {
            alert("'" + orderValue + "' as '" + orderPart + "' could not be selected! Order reset!");
            resetOrder();
        }
    }
}

function getSCtype(terrID) {
    var territory = Territories.toObject()[terrID];
    if(SupplyCenters.indexOf(territory)!=-1){//if ((territory.countryID == context.countryID) && territory.supply) {
        return territory.type;
    } else {
        return null;
    }
}

function getOrderIndexBuilds(terrID) {
    terrID = Territories.toObject()[terrID].coastParentID;
    var newOrder = 0;

    for (var i = 0; i < MyOrders.length; i++) {
        if (MyOrders[i].ToTerritory != null) {
            var terr = MyOrders[i].ToTerritory;
            if (terr.coastParentID == terrID) {
                return i;
            }
        } else {
            newOrder = i;
        }
    }
    return newOrder;
}

function buildArmy() {
    needUnit = false;
    needOwnUnit = false;
    if (selectionValid('build army')) {
        var SCtype = getSCtype(SelectedTerr);
        if (SCtype == null) {
            alert("No own supply center selected (" + SelectedTerrName + ")!");
        } else if (unit(SelectedTerr)) {
            alert("Supply Center (" + SelectedTerrName + ") is occupied by anohter unit!");
        } else {
            var oInd = getOrderIndexBuilds(SelectedTerr);//orderCounter % MyOrders.length;
            var orderPart = 'type';
            var orderValue = 'Build Army';
            if (setOrder(oInd, orderPart, orderValue)) {
                orderInProgress = 'build army';
                var MyOrder = MyOrders[oInd];
                var DropDown = getDropDown(oInd, orderPart);
                /*var changedName = MyOrder.requirements.find( function(namae) {
                 return ( DropDown.name == 'orderForm['+MyOrder.id+']['+namae+']' );
                 },MyOrder);
                 #*/
                DropDown.setStyle({backgroundColor: '#ffd4c9'});
                MyOrder.inputValue(orderPart, DropDown.getValue());

                orderPart = 'toTerrID';
                orderValue = SelectedTerr;
                if (setOrder(oInd, orderPart, orderValue)) {
                    var MyOrder = MyOrders[oInd];
                    var DropDown = getDropDown(oInd, orderPart);
                    /*var changedName = MyOrder.requirements.find( function(namae) {
                     return ( DropDown.name == 'orderForm['+MyOrder.id+']['+namae+']' );
                     },MyOrder);
                     #*/
                    DropDown.setStyle({backgroundColor: '#ffd4c9'});
                    MyOrder.inputValue(orderPart, DropDown.getValue());
                    iM(" is chosen to build an army");
                    resetOrder();
                } else {
                    alert("'" + orderValue + "' as '" + orderPart + "' could not be selected! Order reset!");
                    resetOrder();
                }
            } else {
                alert("'" + orderValue + "' as '" + orderPart + "' could not be selected! Order reset!");
                resetOrder();
            }
        }
    }
}

function getChildren(terrID) {
    var terrTable = Territories.toObject();
    var coasts = new Array();
    for (var terrIndex in terrTable) {
        if ((terrTable[terrIndex].coastParentID == terrID) && (terrTable[terrIndex].coastParentID != terrTable[terrIndex].id)) {
            coasts.push(terrTable[terrIndex]);
        }
    }
    return coasts;
}

function buildFleet() {
    needUnit = false;
    needOwnUnit = false;
    if (selectionValid('build fleet')) {
        var SCtype = getSCtype(SelectedTerr);
        if (SCtype == null) {
            alert("No own supply center selected (" + SelectedTerrName + ")!");
        } else if (SCtype != "Coast") {
            alert("No coastal supply center selected (" + SelectedTerrName + ")!");
        } else if (unit(SelectedTerr)) {
            alert("Supply Center (" + SelectedTerrName + ") is occupied by anohter unit!");
        } else {
            var oInd = getOrderIndexBuilds(SelectedTerr);//orderCounter % MyOrders.length;
            var orderPart = 'type';
            var orderValue = 'Build Fleet';
            if (setOrder(oInd, orderPart, orderValue)) {
                orderInProgress = 'build fleet';
                var MyOrder = MyOrders[oInd];
                var DropDown = getDropDown(oInd, orderPart);
                /*var changedName = MyOrder.requirements.find( function(namae) {
                 return ( DropDown.name == 'orderForm['+MyOrder.id+']['+namae+']' );
                 },MyOrder);
                 #*/
                DropDown.setStyle({backgroundColor: '#ffd4c9'});
                MyOrder.inputValue(orderPart, DropDown.getValue());

                orderPart = 'toTerrID';
                var toTerrID = SelectedTerr;
                if (Territories.toObject()[toTerrID].coast == "Parent") {
                    var toTerr = getCoastByCoords(getChildren(toTerrID), SMcoords.x, SMcoords.y);
                    iM(toTerr.name.sub(Territories.toObject()[toTerrID].name, ''));
                    toTerrID = toTerr.id;
                }
                orderValue = toTerrID;
                if (setOrder(oInd, orderPart, orderValue)) {
                    var MyOrder = MyOrders[oInd];
                    var DropDown = getDropDown(oInd, orderPart);
                    /*var changedName = MyOrder.requirements.find( function(namae) {
                     return ( DropDown.name == 'orderForm['+MyOrder.id+']['+namae+']' );
                     },MyOrder);
                     #*/
                    DropDown.setStyle({backgroundColor: '#ffd4c9'});
                    MyOrder.inputValue(orderPart, DropDown.getValue());
                    iM(" is chosen to build a fleet");
                    resetOrder();
                } else {
                    alert("'" + orderValue + "' as '" + orderPart + "' could not be selected! Order reset!");
                    resetOrder();
                }
            } else {
                alert("'" + orderValue + "' as '" + orderPart + "' could not be selected! Order reset!");
                resetOrder();
            }
        }
    }
}

function wait() {
    var oInd = getOrderIndexBuilds(SelectedTerr);//orderCounter % MyOrders.length;
    var orderPart = 'type';
    var orderValue = 'Wait';
    if (setOrder(oInd, orderPart, orderValue)) {
        orderInProgress = 'wait';
        var MyOrder = MyOrders[oInd];
        var DropDown = getDropDown(oInd, orderPart);
        /*var changedName = MyOrder.requirements.find( function(namae) {
         return ( DropDown.name == 'orderForm['+MyOrder.id+']['+namae+']' );
         },MyOrder);
         #*/
        DropDown.setStyle({backgroundColor: '#ffd4c9'});
        MyOrder.inputValue(orderPart, DropDown.getValue());
        iM(" - Build No. " + (oInd + 1) + " is postponed");
        resetOrder();
    } else {
        alert("'" + orderValue + "' as '" + orderPart + "' could not be selected! Order reset!");
        resetOrder();
    }
}

function setWait() {
    for (var i = 0; i < MyOrders.length; i++) {
        if (!MyOrders[i].isComplete) {
            if (setOrder(i, 'type', 'Wait')) {
                var MyOrder = MyOrders[i];
                var DropDown = getDropDown(i, 'type');
                DropDown.setStyle({backgroundColor: '#ffd4c9'});
                MyOrder.inputValue('type', DropDown.getValue());
            }
        }
    }
}

function disband() {
    needOwnUnit = false;    //territory occupied by other country at this point
    if (selectionValid('disband')) {
        var oInd = getUnits().indexOf(SelectedTerr);
        var orderPart = 'type';
        var orderValue = 'Disband';
        if (setOrder(oInd, orderPart, orderValue)) {
            orderInProgress = 'disband';
            var MyOrder = MyOrders[oInd];
            var DropDown = getDropDown(oInd, orderPart);
            /*var changedName = MyOrder.requirements.find( function(namae) {
             return ( DropDown.name == 'orderForm['+MyOrder.id+']['+namae+']' );
             },MyOrder);
             #*/
            DropDown.setStyle({backgroundColor: '#ffd4c9'});
            MyOrder.inputValue(orderPart, DropDown.getValue());
            iM(" disbands");
            resetOrder();
        } else {
            alert("'" + orderValue + "' as '" + orderPart + "' could not be selected! Order reset!");
            resetOrder();
        }
    }
}

function retreat() {
    needOwnUnit = false;
    if (selectionValid('retreat')) {
        var oInd = getUnits().indexOf(SelectedTerr);
        var orderPart = 'type';
        var orderValue = 'Retreat';
        if (setOrder(oInd, orderPart, orderValue)) {
            orderInProgress = 'retreat';
            //alert(MyOrders[0].id);

            var MyOrder = MyOrders[oInd];
            var DropDown = getDropDown(oInd, orderPart);
            DropDown.setStyle({backgroundColor: '#ffd4c9'});
            MyOrder.inputValue(orderPart, DropDown.getValue());
            order = new orderData(oInd, SelectedTerr);
            needOwnUnit = false;
            needUnit = false;
            iM(" retreats to ");
            greyOutTerritories(MyOrder.toTerrChoices)
        } else {
            alert("'" + orderValue + "' as '" + orderPart + "' could not be selected! Order reset!");
            resetOrder();
        }
    }
}

/*function choicePossible(oInd, type, value) {
 var order = MyOrders[oInd];
 switch (type) {
 case "toTerrID": var choices = order.toTerrChoices.toObject();
 for(var index in choices){
 if(index == value){
 return true;
 }
 }
 break;
 }
 return false;
 }*/ //usefull during ordersetting but not to get special alerts!


function getUnitByTerrID(terrID) {
    for (var i = 0; i < MyUnits.length; i++) {
        if (MyUnits[i].terrID == terrID) {
            return MyUnits[i];
        }

    }
    return null;
}

function retreatPossible(fromID, toID) {    //getMovableCache can be also used for movePossible //No, bacause is adjacentTest
    /*var index;
     for(var i=0; i<MyUnits.length; i++){
     if(MyUnits[i].terrID == fromID){
     index = i;
     break;
     }
     
     }*/
    //if
    var unit = getUnitByTerrID(fromID);
    var movableCache = unit.getMovableTerritoriesCache;
    for (var i = 0; i < movableCache.length; i++) {
        if (movableCache[i].id == toID) {
            if (!Object.isUndefined(movableCache[i].coastParent.standoff) && movableCache[i].coastParent.standoff)
                return false;
            else if (!Object.isUndefined(movableCache[i].coastParent.Unit))
                return false;
            else if (unit.Territory.coastParent.occupiedFromTerrID == movableCache[i].coastParent.id)
                return false;
            else
                return true;
        }
    }
    return false;

}

function retreat2() {
    var oInd = order.orderIndex;
    if (!isNeighbor(order.fromTerr, SelectedTerr)) {
        alert(SelectedTerrName + " not neighbor of " + Territories.toObject()[order.fromTerr].name + " (use CONVOY instead of MOVE for moves via convoy)!");
    } else if (!retreatPossible(order.fromTerr, SelectedTerr)) {
        alert("Selected unit can not move to " + SelectedTerrName + "!");
    } else {
        var orderPart = 'toTerrID';
        /*if (getUnitType(getTerritoryID(terrName)) == "Fleet") {     //check for fleets if Territory has Coasts
         var orderValue = getTerritoryID(terrName);
         } else {
         var orderValue = getTerritoryID(terrName);
         }*/
        var orderValue = SelectedTerr;
        if (setOrder(oInd, orderPart, orderValue)) {
            orderInProgress = 'retreat2';
            var MyOrder = MyOrders[oInd];
            var DropDown = getDropDown(oInd, orderPart);
            DropDown.setStyle({backgroundColor: '#ffd4c9'});
            MyOrder.inputValue(orderPart, DropDown.getValue());

            order.toTerr = orderValue;
            iM(SelectedTerrName);
            resetOrder();
        } else {
            alert("'" + SelectedTerrName + "' as '" + orderPart + "' could not be selected! Order reset!");
            resetOrder();
        }
    }
}