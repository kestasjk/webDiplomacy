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

//HTML-Element where the orders, setted via the interface, are shown (and other informations as well)
var orderLine;

var orderMenu;

var scrollbarsRemoved;

var options;

/*
 * creates the button interface above the map
 */
function createButtonInterface() {
    $("mapstore").parentNode.insertBefore(new Element('div', {'id': 'IA'}), $("mapstore"));
    var colgroup = new Element('colgroup');
    colgroup.appendChild(new Element('col', {'style': 'width:20%'}));
    colgroup.appendChild(new Element('col', {'style': 'width:75%'}));
    colgroup.appendChild(new Element('col', {'style': 'width:5%'}));
//first row of table
    var tr1 = new Element('tr').appendChild(new Element('td').appendChild(new Element('button', {'id': 'IAswitch', 'onclick': 'IAactivate()', 'disabled': 'true'}).update("LOADING ...")).parentNode).parentNode;
    tr1.appendChild(orderButtons()).parentNode.appendChild(new Element('td', {'style': 'text-align:right'}).appendChild(new Element('a', {'href': 'interactiveMap/html/help.html', 'target':'_blank'}).appendChild(new Element('button').update("?")).parentNode).parentNode);
//second row of table
    var tr2 = new Element('tr').appendChild(new Element('td').appendChild(new Element("Button", {'id': 'ResetOrder', 'onclick': 'resetOrder()', 'disabled': 'true'})).update("Reset Order").parentNode).parentNode;
    orderLine = new Element('p', {'style': 'background-color:white;text-align:left;'}).update("Order-Line: ").hide();
    tr2.appendChild(new Element('td').appendChild(orderLine).parentNode);
    tr2.appendChild(new Element('td').appendChild(new Element("Button", {'id': 'options', 'onclick': 'showOptions(this)', 'disabled': 'true', 'style': 'text-align:right'})).update("Opt.").parentNode);
    $("IA").appendChild(new Element('table', {'id': 'IAtable', 'style': 'margin-left:auto; margin-right:auto;width:80%'})).appendChild(colgroup).parentNode.appendChild(tr1).parentNode.appendChild(tr2);

    orderLine.setStyle({'height': orderLine.getHeight() + "px", 'overflow': 'auto'})
}

/*
 * creates the specific order-buttons for each phase
 */
function orderButtons() {
    var orderButtons = new Element("td", {'id': 'orderButtons'});
    switch (context.phase) {
        case "Diplomacy":
            orderButtons.appendChild(new Element('button', {'id': 'hold', 'onclick': 'hold()', 'disabled': 'true'}).update("HOLD"));
            orderButtons.appendChild(new Element('button', {'id': 'move', 'onclick': 'move()', 'disabled': 'true'}).update("MOVE"));
            orderButtons.appendChild(new Element('button', {'id': 'sHold', 'onclick': 'sHold()', 'disabled': 'true'}).update("SUPPORT HOLD"));
            orderButtons.appendChild(new Element('button', {'id': 'sMove', 'onclick': 'sMove()', 'disabled': 'true'}).update("SUPPORT MOVE"));
            orderButtons.appendChild(new Element('button', {'id': 'convoy', 'onclick': 'convoy()', 'disabled': 'true'}).update("CONVOY"));
            break;
        case "Builds":
            if (MyOrders.length == 0) {
                orderButtons.appendChild(new Element('p').update("No orders this phase!"));
            } else if (MyOrders[0].type == "Destroy") {
                orderButtons.appendChild(new Element('button', {'id': 'destroy', 'onclick': 'destroy()', 'disabled': 'true'}).update("DESTROY"));
            } else {
                orderButtons.appendChild(new Element('button', {'id': 'buildArmy', 'onclick': 'buildArmy()', 'disabled': 'true'}).update("BUILD ARMY"));
                orderButtons.appendChild(new Element('button', {'id': 'buildFleet', 'onclick': 'buildFleet()', 'disabled': 'true'}).update("BUILD FLEET"));
                orderButtons.appendChild(new Element('button', {'id': 'wait', 'onclick': 'wait()', 'disabled': 'true'}).update("WAIT"));
            }
            break;
        case "Retreats":
            if (MyOrders.length == 0) {
                orderButtons.appendChild(new Element('p').update("No orders this phase!"));
            } else {
                orderButtons.appendChild(new Element('button', {'id': 'retreat', 'onclick': 'retreat()', 'disabled': 'true'}).update("RETREAT"));
                orderButtons.appendChild(new Element('button', {'id': 'disband', 'onclick': 'disband()', 'disabled': 'true'}).update("DISBAND"));
            }
    }
    return orderButtons;
}

/*
 * creates the menu that appears when a user clicks on the map
 */
function createOrderMenu() {
    if (typeof orderMenu == "undefined") {
        orderMenu = new Element('div', {'id': 'orderMenu'});
        orderMenu.setStyle({
            position: 'absolute',
            zIndex: mapGreyOutCanvas.style.zIndex + 1.0,
            width: '200px'
                    //backgroundColor: 'white'
        });
        var orderMenuOpt = {
            'id': '',
            'src': '',
            'title': '',
            'style': 'margin-left:5px;\n\
                background-color:LightGrey;\n\
                border:1px solid Grey',
            'onmouseover': 'this.setStyle({"backgroundColor":"GhostWhite"})',
            'onmouseout': 'this.setStyle({"backgroundColor":"LightGrey"})',
            'onmousedown': 'this.setStyle({"backgroundColor":"LightBlue"})',
            'onmouseup': 'orderMenu.hide()',
            'onclick': ''
        };

        switch (context.phase) {
            case "Diplomacy":
                orderMenuOpt.id = 'imgHold';
                orderMenuOpt.src = 'interactiveMap/images/Hold.png';
                orderMenuOpt.onclick = 'hold()';
                orderMenuOpt.title = 'hold';
                orderMenu.appendChild(new Element('img', orderMenuOpt));

                orderMenuOpt.id = 'imgMove';
                orderMenuOpt.src = 'interactiveMap/images/Move.png';
                orderMenuOpt.onclick = 'move()';
                orderMenuOpt.title = 'move';
                orderMenu.appendChild(new Element('img', orderMenuOpt));

                orderMenuOpt.id = 'imgSHold';
                orderMenuOpt.src = 'interactiveMap/images/SupportHold.png';
                orderMenuOpt.onclick = 'sHold()';
                orderMenuOpt.title = 'support hold';
                orderMenu.appendChild(new Element('img', orderMenuOpt));

                orderMenuOpt.id = 'imgSMove';
                orderMenuOpt.src = 'interactiveMap/images/SupportMove.png';
                orderMenuOpt.onclick = 'sMove()';
                orderMenuOpt.title = 'support move';
                orderMenu.appendChild(new Element('img', orderMenuOpt));

                orderMenuOpt.id = 'imgConvoy';
                orderMenuOpt.src = 'interactiveMap/images/Convoy.png';
                orderMenuOpt.onclick = 'convoy()';
                orderMenuOpt.title = 'convoy';
                orderMenu.appendChild(new Element('img', orderMenuOpt));
                break;
            case "Builds":
                if (MyOrders.length == 0) {
                    orderMenu.appendChild(new Element('p', {'style': 'background-color:LightGrey;border:1px solid Grey'}).update("No orders this phase!"));
                } else if (MyOrders[0].type == "Destroy") {
                    orderMenuOpt.id = 'imgDestroy';
                    orderMenuOpt.src = 'interactiveMap/images/Destroy.png';
                    orderMenuOpt.onclick = 'destroy()';
                    orderMenuOpt.title = 'destroy';
                    orderMenu.appendChild(new Element('img', orderMenuOpt));
                } else {
                    orderMenuOpt.id = 'imgBuildArmy';
                    orderMenuOpt.src = 'interactiveMap/images/BuildArmy.png';
                    orderMenuOpt.onclick = 'buildArmy()';
                    orderMenuOpt.title = 'build army';
                    orderMenu.appendChild(new Element('img', orderMenuOpt));

                    orderMenuOpt.id = 'imgBuildFleet';
                    orderMenuOpt.src = 'interactiveMap/images/BuildFleet.png';
                    orderMenuOpt.onclick = 'buildFleet()';
                    orderMenuOpt.title = 'build fleet';
                    orderMenu.appendChild(new Element('img', orderMenuOpt));

                    orderMenuOpt.id = 'imgWait';
                    orderMenuOpt.src = 'interactiveMap/images/Hold.png';
                    orderMenuOpt.onclick = 'wait()';
                    orderMenuOpt.title = 'wait/postpone build';
                    orderMenu.appendChild(new Element('img', orderMenuOpt));
                }
                break;
            case "Retreats":
                if (MyOrders.length == 0) {
                    orderMenu.appendChild(new Element('p', {'style': 'background-color:LightGrey;border:1px solid Grey'}).update("No orders this phase!"));
                } else {
                    orderMenuOpt.id = 'imgRetreat';
                    orderMenuOpt.src = 'interactiveMap/images/Retreat.png';
                    orderMenuOpt.onclick = 'retreat()';
                    orderMenuOpt.title = 'retreat';
                    orderMenu.appendChild(new Element('img', orderMenuOpt));

                    orderMenuOpt.id = 'imgDisband';
                    orderMenuOpt.src = 'interactiveMap/images/Destroy.png';
                    orderMenuOpt.onclick = 'disband()';
                    orderMenuOpt.title = 'disband';
                    orderMenu.appendChild(new Element('img', orderMenuOpt));
                }
        }

        $('mapCanDiv').appendChild(orderMenu).hide();
    }
}

/*
 * adds the needed options and make the orderMenu visible
 */
function showOrderMenu(coor) {
    orderMenu.setStyle({
        top: (coor.y + 25) + 'px',
        left: getOrderMenuPos(coor) + 'px'
    });
    switch (context.phase) {
        case 'Builds':
            if (MyOrders.length != 0) {
                if (MyOrders[0].type == "Destroy") {
                    if (ownUnit(SelectedTerr)) {
                        orderMenu.show();
                    }
                } else {
                    var SCtype = getSCtype(SelectedTerr);
                    if ((SCtype != null) && (!unit(SelectedTerr))) {
                        if (SCtype != "Coast")
                            $("imgBuildFleet").hide();
                        else
                            $("imgBuildFleet").show();
                        orderMenu.show();
                    }
                }
            }
            break;
        case 'Diplomacy':
            $("imgMove").show();
            $("imgHold").show();
            $("imgSMove").show();
            $("imgSHold").show();
            $("imgConvoy").show();
            if (unit(SelectedTerr)) {
                if (ownUnit(SelectedTerr)) {//||(unit(SelectedTerr)&&(Territories.get(SelectedTerr).type=="Coast")&&(Territories.get(SelectedTerr).Unit.type=="Army")))
                    if ((Territories.get(SelectedTerr).coastParent.Unit.type == "Fleet") || (Territories.get(SelectedTerr).type != "Coast"))
                        $("imgConvoy").hide();
                    orderMenu.show();
                } else {
                    if ((Territories.get(SelectedTerr).type == "Coast") && (Territories.get(SelectedTerr).Unit.type == "Army")) {
                        $("imgMove").hide();
                        $("imgHold").hide();
                        $("imgSMove").hide();
                        $("imgSHold").hide();
                        $("imgConvoy").show();
                        orderMenu.show();
                    }
                }
            }
            break;
        case 'Retreats':
            if (MyOrders.length != 0) {
                if (ownRetreatUnit(SelectedTerr))
                    orderMenu.show();
            }
            break;
    }
}

function getOrderMenuPos(coor) {
    if (coor.x < 100)
        return 0;
    else if (coor.x > (mapCanvas.width - 100))
        return (mapCanvas.width - 200);
    else
        return (coor.x - 100);
}

/*
 * enables/disables the activate-Button
 */
function activateButton() {
    IAready = true;
    $("IAswitch").innerHTML = "activate IA";
    $("IAswitch").disabled = false;
}


/*
 * enables/disables the orderButtons
 * detects if phase is Builds and sets the orders to "wait" so the user can save at any time
 */
function IAswitch() {
    var buttons = $("orderButtons").childNodes;
    if (IAactivated) {
        for (var i = 0; i < buttons.length; i++) {
            buttons[i].disabled = false;
        }
        $("ResetOrder").disabled = false;
        orderLine.show();
        //$("largeMap").disabled = false;
        //$("greyOut").disabled = false;
        $("options").disabled = false;
        $("IAswitch").innerHTML = "deactivate IA";
        if (context.phase == "Builds") {
            setWait();
        }
    } else {
        for (var i = 0; i < buttons.length; i++) {
            buttons[i].disabled = true;
        }
        $("ResetOrder").disabled = true;
        orderLine.hide();
        //$("largeMap").disabled = true;
        //$("greyOut").disabled = true;
        $("options").disabled = true;
        $("IAswitch").innerHTML = "activate IA";
    }
}

/*
 * additional options
 */
function showOptions(optButton) {
    if (typeof options == 'undefined') {
        options = new Element('div');

        options.setStyle({
            position: 'fixed',
            top: "0%",
            left: "25%",
            right: "25%",
            backgroundColor: 'LightGrey',
            zIndex: '10',
            textAlign: 'center',
            display: 'none',
            border: '10px solid black'
        });

        options.appendChild(new Element("h1").update("InteractiveMap Options:"))
        options.appendChild(new Element("p").appendChild(new Element("button", {'id': 'largeMap', 'onclick': 'largeMap()'})).update("Toggle scrollbars on map").parentNode);
        options.appendChild(new Element("p").appendChild(new Element("Button", {'id': 'greyOut', 'onclick': 'greyOut()'})).update("Activate territory-grey-out").parentNode);
        options.appendChild(new Element("p").appendChild(new Element("Button", {'id': 'close', 'onclick': 'options.hide(); $("options").disabled = false'})).update("Close").parentNode);

        optButton.parentNode.appendChild(options);
    }
    optButton.disabled = true;
    options.show();
}

/*
 * removes scrollbars for large maps
 */
function largeMap() {
    scrollbarsRemoved = !scrollbarsRemoved;
    if (scrollbarsRemoved) {
        mapCanDiv.scrollTop = 0;
        mapCanDiv.scrollLeft = 0;
        mapCanDiv.setStyle({
            height: IAmapCan.height + 'px',
            width: IAmapCan.width + 'px',
            overflow: 'visible'
        });
    } else {
        mapCanDiv.setStyle({
            height: (new Number(mapImg.height) + 10) + 'px',
            width: (new Number(mapImg.width) + 10) + 'px',
            overflow: 'auto'
        });
    }
}


/*
 * toggles the greyOut of territories during the orders
 */
function greyOut() {
    greyOutActivated = !greyOutActivated;
    if (greyOutActivated) {
        if (typeof IAmapTerrDat == "undefined") {
            $("greyOut").update("...").disabled = true;
            new Ajax.Request('interactiveMap/php/IAgetMapTerrDat.php', {
                parameters: {"gameID": context.gameID},
                onSuccess: function(response) {
                    IAmapTerrDat = response.responseJSON;
                    mapGreyOutCanvas.show();
                    $("greyOut").update("Deactivate territory-grey-out").disabled = false;
                    resetOrder();
                },
                onFailure: function() {
                    var alertWindow = window.open('interactiveMap/php/IAgetMapTerrDat.php?gameID=' + context.gameID, '', 'height=100, width=500, scrollbars=yes');
                    alertWindow.focus();
                }
            });
        } else {
            mapGreyOutCanvas.show();
            $("greyOut").update("Deactivate territory-grey-out").disabled = false;
            resetOrder();
        }
    } else {
        mapGreyOutCanvas.hide();
        $("greyOut").update("Activate territory-grey-out");
    }
}
