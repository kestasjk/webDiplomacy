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

//2d context of hidden map to detect territories
var IAmapCtx;

//canvas element of the hidden map
var IAmapCan;

//imageData of the hidden map
var IAmapDat;

var orderEle;

//territoryPositions of each part of a territory
var IAmapTerrDat;

//true: everythind for the interface is loaded
var IAready;
//true: the interface is activated and ready for use
var IAactivated;



//initializes interactiveMap
function loadIA() {
    IAready = false;
    IAactivated = false;
    
    greyOutActivated = false;

    //the setting for the first territory-selection
    terrSel = false;
    needOwnUnit = true;
    needUnit = true;

    //the HTML-Element for the order-input (via drop-down)
    orderEle = $("orderFormElement");

    createButtonInterface();
    loadIAmap();

    //the HTML-Element of the map
    mapImg = $("mapImage");
}


/*
 * loads a hidden blank map (without names and units) from the variant ressources that is used to detect the selected territory later
 * @param {string} IAmapPNG (the path for the blank map)
 */
function loadIAmap() {
    var imgIAmap = new Image();
    imgIAmap.observe('load', function() {
        //new canvas element, which stores the blank map
        IAmapCan = new Element("canvas", {'width': imgIAmap.width, 'height': imgIAmap.height});
        IAmapCtx = IAmapCan.getContext("2d");
        IAmapCtx.drawImage(imgIAmap, 0, 0);
        IAmapDat = IAmapCtx.getImageData(0,0,imgIAmap.width,imgIAmap.height);
        //$("mapImage").replace(imgIAmap);
        /*if(colorSea)
            colorSeaTerritories();*/
        activateButton();
    });
    imgIAmap.onerror = function() {
        var alertWindow = window.open('interactiveMap/php/IAgetMap.php?gameID='+context.gameID,'','height=100, width=500, scrollbars=yes');
        alertWindow.focus();
    };
    imgIAmap.src = 'interactiveMap/php/IAgetMap.php?gameID='+context.gameID;
}

/*
 * activates/deactivates the interface (enables/disables buttons, loads and replaces map)
 */
function IAactivate() {
    IAactivated = !IAactivated;
    IAswitch();
    IAmap();
    createOrderMenu();
}


/*
 * prints messages in the "Order-Line"-Element or if not available in the "sendbox"-Element (Game-Messages)
 */
function iM(content) {
    insertMessage(content);
}

function insertMessage(content) {
    if (orderLine != null){
        if (orderInProgress != null) {
            orderLine.innerHTML += content;
        } else {
            orderLine.innerHTML = "Order-Line: " + content;
        }
        orderLine.scrollTop = orderLine.scrollHeight;

    }else{
        $("sendbox").value += content;
    }
}