/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
var terrSel;
var terrName;
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
    terrName = null;
    terrSel = false;
    needOwnUnit = true;
    needUnit = true;
    SMcoords = null;
    order = null;
    drawImage();
}

function selectTerritory(event) {
    if (IAready && IAactivated) {
        var coor = getCoor(event);
        if (orderInProgress != "sMove") { //for sMove, decisions about if the unit is moving to coast or not are not posible at this moment
            terrName = getTerritoryName(coor.x, coor.y);
            if (terrName != null) {
                terrSel = true;
                //iM(terrName);
            } else {
                terrSel = false;
            }
            if (orderInProgress != null) {               //an order will be completed
                selectionValid(orderInProgress);
            } else {
                if (terrName != null)
                    iM(terrName);
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
    var imgOffset = $("mapCanvas").cumulativeOffset().toArray();
    var x = event.pointerX() - imgOffset[0];
    var y = event.pointerY() - imgOffset[1];
    return{x: x, y: y};
}

function getTerritoryName(x, y) {
    var terrTabl = Territories.toObject();
    //var ImageColors = jsonData[1];

    var color = getColor(x, y); //color -> color of clicked pixel

    for (var terrID in terrTabl) {
        if ((sameColor(color, getColor(terrTabl[terrID].smallMapX, terrTabl[terrID].smallMapY))) && ((terrTabl[terrID].coast == "No"))) {//||(terrTabl[terrID].coast == "Parent"))) {
            return terrTabl[terrID].name;
        } else if ((sameColor(color, getColor(terrTabl[terrID].smallMapX, terrTabl[terrID].smallMapY))) && ((terrTabl[terrID].coast == "Parent"))) {
            return checkCoast(terrID, terrTabl, x, y);
        }
    }
    return null;
}

function checkCoast(terrID, terrTabl, x, y) {
    //alert("HeW");
    if ((orderInProgress == null) || (orderInProgress == "sMove1")) { //sMove1 -> supported unit's origin
        if (!coastUnit(terrID) || ((context.phase == "Builds") && !(MyOrders[0].type == "Destroy"))) {
            return terrTabl[terrID].name;
        } else {
            return terrTabl[terrID].Unit.Territory.name;
        }
    } else {
        //alert("behind else");
        switch (orderInProgress) {
            case "move":
            case "sMove2":
                //alert("I'm here");
                var territory = terrTabl[order.fromTerr];
                if (territory.coast != "Child") {
                    if (territory.Unit != null) {       //if something went wrong ...
                        if (territory.Unit.type == "Fleet") {
                            //alert("fleet");

                            //multipleOptions//
                            return getCoast(terrID, order.fromTerr, terrTabl, x, y);
                        }
                    }
                    return terrTabl[terrID].name;
                } else {
                    //alert("fleet");
                    //multipleOptions - unit from coast -> only fleet possible
                    return getCoast(terrID, order.fromTerr, terrTabl, x, y);
                }
                /*if (Units.toObject()[getTerrStatus(order.fromTerr).unitID].type == "army") {
                 return terrTabl[terrID].name;
                 } else {
                 //if (multipleOptions()) {
                 return terrTabl[terrID].name;
                 //}
                 }*/
                break;
            default:    //e.g. sHold, sMove
                return terrTabl[terrID].name;
                break;
        }
    }
}

/*
 * Get all coasts of the clicked Territory, that can be reached by the moving fleet.
 * To get the coast, the possible borderTerritories are examined. 
 * If more coasts of the territory are possible, the selected coast is detected by the coords of the click and the terr.coords.
 */

function getCoast(terrID, fromTerrID, terrTabl, x, y) {      //get all coasts of the clicked Territory, that can be reached by the moving fleet
    var coasts = new Array();
    var fromTerrBorders = terrTabl[fromTerrID].CoastalBorders;
    for (var i = 0; i < fromTerrBorders.length; i++) {
        var checkedTerr = terrTabl[fromTerrBorders[i].id];  //the Territory, which is inspected
        if (checkedTerr.coast == "Child") {
            if (checkedTerr.coastParentID == terrID) {
                coasts.push(checkedTerr);
            }
        }
    }
    if (coasts == null) { //if something went wrong :(
        return terrTabl[terrID].name;
    } else if (coasts.length == 1) {   //only one coast can be selected
        return coasts[0].name;
    } else {  //more coasts are possible
        //alert("more possible");
        return getCoastByCoords(coasts, x, y).name;
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

function getTerritoryID(name) {
    var terrTabl = Territories.toObject();
    for (var terrID in terrTabl) {
        if (name == terrTabl[terrID].name)
            return terrID;
    }
    return null;
}

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
        if (c1[i] != c2[i])
            return false;
    }
    return true;
}

function getColor(x, y) {
    return IAmapCtx.getImageData(x, y, 1, 1).data;
}

function unit(terrName) {
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
    var territory = Territories.toObject()[getTerritoryID(terrName)];
    if (territory.coast != "Child") {
        if (territory.unitID != null) {
            return true;
        }
    } else if (territory.coastParent.unitID != null) {
        return true;
    }
    return false;
}

function ownUnit(terrName) {
    var territory = Territories.toObject()[getTerritoryID(terrName)];
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

function getUnits() {
    var orderUnits = new Array();
    for (var orderIndex = 0; orderEle.select("tr")[orderIndex] != null; orderIndex++) {
        var oBegin = orderEle.select("tr")[orderIndex].select("td")[1].select("span")[1].innerHTML;
        /*if (oBegin.charAt(12) == " ")  //Order for fleet: The fleet at_ -> 13th Element of String is " " - army has one element less
         orderUnits[orderIndex] = oBegin.substring(13, oBegin.length - 1);
         else
         orderUnits[orderIndex] = oBegin.substring(12, oBegin.length - 1);*/
        oBegin = oBegin.sub('The fleet at ', '').strip();
        oBegin = oBegin.sub('The army at ', '').strip();
        orderUnits[orderIndex] = oBegin;
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
    if (terrName == null || !terrSel) {
        alert("No Territory selected!");
        return false;
    } else if (!unit(terrName) && needUnit) {
        alert("No unit selected (" + terrName + ")!");
        return false;
    } else if (!ownUnit(terrName) && needOwnUnit && (order != 'convoy')) {      //for convoys, foreign units as a start unit has to be allowed, because convoys can be only setted this way!
        alert("No own unit selected (" + terrName + ")!");
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
            }
            return false;
        }
    } else if ((order == "convoy") && (getUnitType(getTerritoryID(terrName)) != "Army")) {
        alert("No army selected!");
        return false;
    } else {
        return true;
    }
}

function hold() {
    if (selectionValid('hold')) {
        var oInd = getUnits().indexOf(terrName);
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
        var oInd = getUnits().indexOf(terrName);
        var orderPart = 'type';
        var orderValue = 'Move';
        if (setOrder(oInd, orderPart, orderValue)) {
            orderInProgress = 'move';
            //alert(MyOrders[0].id);

            var MyOrder = MyOrders[oInd];
            var DropDown = getDropDown(oInd, orderPart);
            DropDown.setStyle({backgroundColor: '#ffd4c9'});
            MyOrder.inputValue(orderPart, DropDown.getValue());
            order = new orderData(oInd, getTerritoryID(terrName));
            needOwnUnit = false;
            needUnit = false;
            iM(" moves to ");
        } else {
            alert("'" + orderValue + "' as '" + orderPart + "' could not be selected! Order reset!");
            resetOrder();
        }
    }
}

function movePossible(fromID, toID, coastTreatment) {
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
    if (!isNeighbor(order.fromTerr, getTerritoryID(terrName))) {
        alert(terrName + " not neighbor of " + Territories.toObject()[order.fromTerr].name + " (use CONVOY instead of MOVE for moves via convoy)!");
    } else if (!movePossible(order.fromTerr, getTerritoryID(terrName))) {
        alert("Selected unit can not move to " + terrName + " (wrong type)!");
    } else {
        var orderPart = 'toTerrID';
        /*if (getUnitType(getTerritoryID(terrName)) == "Fleet") {     //check for fleets if Territory has Coasts
         var orderValue = getTerritoryID(terrName);
         } else {
         var orderValue = getTerritoryID(terrName);
         }*/
        var orderValue = getTerritoryID(terrName);
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
            iM(terrName);
            resetOrder();
        } else {
            alert("'" + terrName + "' as '" + orderPart + "' could not be selected! Order reset!");
            resetOrder();
        }
    }
}

function sHold() {
    if (selectionValid('move')) {
        var oInd = getUnits().indexOf(terrName);
        var orderPart = 'type';
        var orderValue = 'Support hold';
        if (setOrder(oInd, orderPart, orderValue)) {
            orderInProgress = 'sHold';
            //alert(MyOrders[0].id);

            var MyOrder = MyOrders[oInd];
            var DropDown = getDropDown(oInd, orderPart);
            DropDown.setStyle({backgroundColor: '#ffd4c9'});
            MyOrder.inputValue(orderPart, DropDown.getValue());
            order = new orderData(oInd, getTerritoryID(terrName));
            needOwnUnit = false;
            needUnit = true;
            iM(" supports the holding unit in ");
        } else {
            alert("'" + orderValue + "' as '" + orderPart + "' could not be selected! Order reset!");
            resetOrder();
        }
    }
}

function sHold2() {
    if (!isNeighbor(order.fromTerr, getTerritoryID(terrName))) {
        alert(terrName + " not neighbor of " + Territories.toObject()[order.fromTerr].name + "!");
    } else if (!movePossible(order.fromTerr, getTerritoryID(terrName))) {
        alert("Selected unit can not support unit in " + terrName + " (wrong type)!");
    } else {
        var oInd = order.orderIndex;
        var orderPart = 'toTerrID';
        var orderValue = getTerritoryID(terrName);
        if (setOrder(oInd, orderPart, orderValue)) {
            orderInProgress = 'sHold2';
            var MyOrder = MyOrders[oInd];
            var DropDown = getDropDown(oInd, orderPart);
            DropDown.setStyle({backgroundColor: '#ffd4c9'});
            MyOrder.inputValue(orderPart, DropDown.getValue());
            order.toTerr = orderValue;
            iM(terrName);
            resetOrder();
        } else {
            alert("'" + terrName + "' as '" + orderPart + "' could not be selected! Order reset!");
            resetOrder();
        }
    }
}

function sMove() {
    if (selectionValid('sMove')) {
        var oInd = getUnits().indexOf(terrName);
        var orderPart = 'type';
        var orderValue = 'Support move';
        if (setOrder(oInd, orderPart, orderValue)) {
            orderInProgress = 'sMove';
            //alert(MyOrders[0].id);

            var MyOrder = MyOrders[oInd];
            var DropDown = getDropDown(oInd, orderPart);
            DropDown.setStyle({backgroundColor: '#ffd4c9'});
            MyOrder.inputValue(orderPart, DropDown.getValue());
            order = new orderData(oInd, getTerritoryID(terrName));
            order.terr = getTerritoryID(terrName);
            needOwnUnit = false;
            needUnit = false;
            iM(" supports the moving unit to ");
        } else {
            alert("'" + orderValue + "' as '" + orderPart + "' could not be selected! Order reset!");
            resetOrder();
        }
    }
}

function sMove1(coor) {
    terrName = getTerritoryName(coor.x, coor.y);
    if (!isNeighbor(order.terr, getTerritoryID(terrName))) {
        alert(terrName + " not neighbor of " + Territories.toObject()[order.terr].name + "!");
    } else if (!movePossible(order.terr, getTerritoryID(terrName), "ignoreCoasts")) {
        alert("Selected unit can not support move to " + terrName + " (wrong type)!");
    } else {
        iM(terrName + " from ");
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
    var fromTerrName = terrName;
    var fromTerrID = getTerritoryID(fromTerrName);    //last selected Terr is toTerr, because Coordinates for fromTerr are only saved in SMcoords
    order.fromTerr = fromTerrID;        //saved in order.fromTerr for coastTerritory-Movements

    orderInProgress = 'sMove2';         //needed to detect the rigth coast-treatment in checkCoast()
    var toTerrName = getTerritoryName(SMcoords.x, SMcoords.y);
    var toTerrID = getTerritoryID(toTerrName);    //gets fromTerrID with saved SMCoords
    order.toTerr = toTerrID;

    var cp = convoyPossible(order.fromTerr, order.toTerr);
    if ((!isNeighbor(order.fromTerr, order.toTerr)) && !cp) {
        orderInProgress = 'sMove1';
        alert("Unit in " + terrName + " can not move to " + Territories.toObject()[order.toTerr].name + "!");
    } else if (!movePossible(order.fromTerr, order.toTerr) && !cp) {
        orderInProgress = 'sMove1';
        alert("Unit in " + terrName + " can not move to " + Territories.toObject()[order.toTerr].name + " (wrong type)!");
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

                moveSupport(fromTerrName, toTerrID);
                iM(terrName);
                resetOrder();
            } else {
                alert("'" + terrName + "' as '" + orderPart + "' could not be selected! Order reset!");
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

function moveSupport(fromTerrName, toTerrID) {  //sets a move with Data from sMove2()

    //if (selectionValid('move')) {
    if (ownUnit(fromTerrName)) {  //only enters order to move for own units
        if (!orderAlreadySet(getTerritoryID(fromTerrName), toTerrID, 'Move')) {
            var oInd = getUnits().indexOf(fromTerrName);
            var orderPart = 'type';
            var orderValue = 'Move';
            if (setOrder(oInd, orderPart, orderValue)) {
                orderInProgress = 'move';
                //alert(MyOrders[0].id);

                var MyOrder = MyOrders[oInd];
                var DropDown = getDropDown(oInd, orderPart);
                DropDown.setStyle({backgroundColor: '#ffd4c9'});
                MyOrder.inputValue(orderPart, DropDown.getValue());
                order = new orderData(oInd, getTerritoryID(terrName));
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
        alert("'" + terrName + "' as '" + orderPart + "' could not be selected! Order reset!");
        resetOrder();
    }
}

function convoy() {
    if (selectionValid('convoy')) {
        if (ownUnit(terrName)) {  //only sets order to move for own units
            var oInd = getUnits().indexOf(terrName);
            var orderPart = 'type';
            var orderValue = 'Move';
            if (setOrder(oInd, orderPart, orderValue)) {
                orderInProgress = 'convoy';
                //alert(MyOrders[0].id);

                var MyOrder = MyOrders[oInd];
                var DropDown = getDropDown(oInd, orderPart);
                DropDown.setStyle({backgroundColor: '#ffd4c9'});
                MyOrder.inputValue(orderPart, DropDown.getValue());
                order = new orderData(oInd, getTerritoryID(terrName));
                order.terr = new Array();   //stores the convoyPath
                order.terr.push(order.fromTerr);
                needOwnUnit = false;
                needUnit = false;
                iM(" moves via ");
            } else {
                alert("'" + orderValue + "' as '" + orderPart + "' could not be selected! Order reset!");
                resetOrder();
            }
        } else {
            orderInProgress = 'convoy';
            order = new orderData(null, getTerritoryID(terrName));
            order.terr = new Array();   //stores the convoyPath
            order.terr.push(order.fromTerr);
            needOwnUnit = false;
            needUnit = false;
            iM(" moves via ");
        }
    }
}

function convoy2() {
    var terrTabl = Territories.toObject();
    var terrID = getTerritoryID(terrName);
    var unitType = getUnitType(terrID);
    if (terrTabl[terrID].type != "Sea") {
        if (!isNeighbor(order.terr[order.terr.length - 1], terrID)) {
            alert(terrName + " not neighbor of " + terrTabl[order.terr[order.terr.length - 1]].name + "!");
        } else {
            if (order.terr.length > 1) {
                convoy3(terrID);  //handles the final building of Convoy
            } else {
                alert("No fleet selected (" + terrName + ")!");
            }
        }
    } else {  //unit type has to be fleet (unit() tested with selectionValid()
        if (!unit(terrName)) {
            alert("No Unit selected (" + terrName + ")!");
        } else {
            if (!isNeighbor(order.terr[order.terr.length - 1], terrID)) {
                alert("Fleet (" + terrName + ") not neighbor of " + terrTabl[order.terr[order.terr.length - 1]].name + "!");
            } else {
                if (terrTabl[terrID].type != "Sea") {
                    alert("Convoying fleet not in Sea-Territory");
                } else {
                    if (order.terr.length > 1) {
                        iM(", ")
                    }
                    iM(terrName);
                    order.terr.push(terrID);
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

            iM(" to " + terrName);
            order.toTerr = orderValue;
            setConvoy();
            resetOrder();
        } else {
            alert("'" + terrName + "' as '" + orderPart + "' could not be selected! Order reset!");
            resetOrder();
        }
    } else {
        orderInProgress = 'convoy2';
        iM(" to " + terrName);
        order.toTerr = terrID;
        setConvoy();
        resetOrder();
    }
}

function setConvoy() {
    var terrTabl = Territories.toObject();
    for (var i = 1; i < order.terr.length; i++) { //set convoy-order for every fleet in convoy-path (order.terr[0] -> moving army)
        var terrName = terrTabl[order.terr[i]].name;
        if (ownUnit(terrName)) {    //only set convoys for own units
            var oInd = getUnits().indexOf(terrName);
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
                        alert("'" + terrTabl[orderValue].name + "' as '" + orderPart + "' could not be selected! Order reset!");
                        resetOrder();
                    }
                } else {
                    alert("'" + terrTabl[orderValue].name + "' as '" + orderPart + "' could not be selected! Order reset!");
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
        var orderValue = getTerritoryID(terrName);
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
    if ((territory.countryID == context.countryID) && territory.supply) {
        return territory.type;
    } else {
        return null;
    }
}

function buildArmy() {
    needUnit = false;
    needOwnUnit = false;
    if (selectionValid('build army')) {
        var SCtype = getSCtype(getTerritoryID(terrName));
        if (SCtype == null) {
            alert("No own supply center selected (" + terrName + ")!");
        } else {
            if (orderCounter == null) {
                orderCounter = 0;
            } else {
                orderCounter++;
            }
            var oInd = orderCounter % MyOrders.length;
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
                orderValue = getTerritoryID(terrName);
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
    var terrTabl = Territories.toObject();
    var coasts = new Array();
    for (var terrIndex in terrTabl) {
        if ((terrTabl[terrIndex].coastParentID == terrID) && (terrTabl[terrIndex].coastParentID != terrTabl[terrIndex].id)) {
            coasts.push(terrTabl[terrIndex]);
        }
    }
    return coasts;
}

function buildFleet() {
    needUnit = false;
    needOwnUnit = false;
    if (selectionValid('build fleet')) {
        var SCtype = getSCtype(getTerritoryID(terrName));
        if (SCtype == null) {
            alert("No own supply center selected (" + terrName + ")!");
        } else if (SCtype != "Coast") {
            alert("No coastal supply center selected (" + terrName + ")!");
        } else {
            if (orderCounter == null) {
                orderCounter = 0;
            } else {
                orderCounter++;
            }
            var oInd = orderCounter % MyOrders.length;
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
                var toTerrID = getTerritoryID(terrName);
                if (Territories.toObject()[toTerrID].coast == "Parent") {
                    toTerrID = getCoastByCoords(getChildren(Territories.toObject()[terrID]), SMcoords.x, SMcoords.y);
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
    if (orderCounter == null) {
        orderCounter = 0;
    } else {
        orderCounter++;
    }
    var oInd = orderCounter % MyOrders.length;
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
        iM(" - Build No. "+(oInd+1)+" is postponed");
        resetOrder();
    } else {
        alert("'" + orderValue + "' as '" + orderPart + "' could not be selected! Order reset!");
        resetOrder();
    }
}