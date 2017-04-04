/*
 * What have to be changed:
 * 
 * - add 'transform' button in interface
 * - add 'transform' button in order-menu on map
 * - add image for 'transform' button on map
 * - add function to set 'transform' order
 * - add function to draw 'transform' order
 * 
 */

var imgArmy = new Image();
imgArmy.observe('load',function(){
    var canvas = new Element('canvas',{'width':imgArmy.width,'height':imgArmy.height});
    var ctx = canvas.getContext('2d');
    ctx.drawImage(imgArmy,0,0);
    setTransparent(ctx);
    
    imgArmy = canvas;
});
imgArmy.src = 'variants/KnownWorld_901/resources/army.png';

var imgFleet = new Image();
imgFleet.observe('load',function(){
    var canvas = new Element('canvas',{'width':imgFleet.width,'height':imgFleet.height});
    var ctx = canvas.getContext('2d');
    ctx.drawImage(imgFleet,0,0);
    setTransparent(ctx);
    
    imgFleet = canvas;
});
imgFleet.src = 'variants/KnownWorld_901/resources/fleet.png';

function setTransparent(ctx){
    var imgData = ctx.getImageData(0,0,ctx.canvas.width,ctx.canvas.height);
    for(var i=0; i<imgData.data.length; i+=4){
        var r = imgData.data[i];
        var g = imgData.data[i+1];
        var b = imgData.data[i+2];
        
        if(r===255 && g===255 && b===255)
            imgData.data[i+3] = 0;
    }
    
    ctx.putImageData(imgData,0,0);
}

function loadIAtransform() {

    function addTransformButton() {
        $('orderButtons').appendChild(new Element('button', {'id': 'transform', 'class': 'buttonIA form-submit', 'onclick': 'interactiveMap.sendOrder("Transform")', 'disabled': 'true'}).update("TRANSFORM"));
    }

    function addOrderMenuTransformButton() {
        interactiveMap.interface.orderMenu.create = function() {
            if (typeof interactiveMap.interface.orderMenu.element == "undefined") {
                interactiveMap.interface.orderMenu.element = new Element('div', {'id': 'orderMenu'});
                interactiveMap.interface.orderMenu.element.setStyle({
                    position: 'absolute',
                    zIndex: interactiveMap.visibleMap.greyOutLayer.canvasElement.style.zIndex + 1,
                    width: '12px'
                            //width: '200px'
                            //backgroundColor: 'white'
                });
                var orderMenuOpt = {
                    'id': '',
                    'src': '',
                    'title': '',
                    'style': 'margin-left:5px;\n\
                background-color:LightGrey;\n\
                border:1px solid Grey;\n\
                display:none;',
                    'onmouseover': 'this.setStyle({"backgroundColor":"GhostWhite"})',
                    'onmouseout': 'this.setStyle({"backgroundColor":"LightGrey"})',
                    'onmousedown': 'this.setStyle({"backgroundColor":"LightBlue"})',
                    'onmouseup': 'interactiveMap.interface.orderMenu.element.hide()',
                    'onclick': ''
                };

                switch (context.phase) {
                    case "Diplomacy":
                        orderMenuOpt.id = 'imgHold';
                        orderMenuOpt.src = interactiveMap.parameters.imgHold;
                        orderMenuOpt.onclick = 'interactiveMap.sendOrder("Hold")';
                        orderMenuOpt.title = 'hold';
                        interactiveMap.interface.orderMenu.element.appendChild(new Element('img', orderMenuOpt)).observe('load', function() {
                            interactiveMap.interface.orderMenu.showElement(this);
                        });

                        orderMenuOpt.id = 'imgMove';
                        orderMenuOpt.src = interactiveMap.parameters.imgMove;
                        orderMenuOpt.onclick = 'interactiveMap.sendOrder("Move")';
                        orderMenuOpt.title = 'move';
                        interactiveMap.interface.orderMenu.element.appendChild(new Element('img', orderMenuOpt)).observe('load', function() {
                            interactiveMap.interface.orderMenu.showElement(this);
                        });

                        orderMenuOpt.id = 'imgSHold';
                        orderMenuOpt.src = interactiveMap.parameters.imgSHold;
                        orderMenuOpt.onclick = 'interactiveMap.sendOrder("Support hold")';
                        orderMenuOpt.title = 'support hold';
                        interactiveMap.interface.orderMenu.element.appendChild(new Element('img', orderMenuOpt)).observe('load', function() {
                            interactiveMap.interface.orderMenu.showElement(this);
                        });

                        orderMenuOpt.id = 'imgSMove';
                        orderMenuOpt.src = interactiveMap.parameters.imgSMove;
                        orderMenuOpt.onclick = 'interactiveMap.sendOrder("Support move")';
                        orderMenuOpt.title = 'support move';
                        interactiveMap.interface.orderMenu.element.appendChild(new Element('img', orderMenuOpt)).observe('load', function() {
                            interactiveMap.interface.orderMenu.showElement(this);
                        });

                        orderMenuOpt.id = 'imgConvoy';
                        orderMenuOpt.src = interactiveMap.parameters.imgConvoy;
                        orderMenuOpt.onclick = 'interactiveMap.sendOrder("Convoy")';
                        orderMenuOpt.title = 'convoy';
                        interactiveMap.interface.orderMenu.element.appendChild(new Element('img', orderMenuOpt)).observe('load', function() {
                            interactiveMap.interface.orderMenu.showElement(this);
                        });

                        orderMenuOpt.id = 'imgTransform';
                        orderMenuOpt.src = 'variants/KnownWorld_901/interactiveMap/IA_transform.png';
                        orderMenuOpt.onclick = 'interactiveMap.sendOrder("Transform")';
                        orderMenuOpt.title = 'transform';
                        interactiveMap.interface.orderMenu.element.appendChild(new Element('img', orderMenuOpt)).observe('load', function() {
                            interactiveMap.interface.orderMenu.showElement(this);
                        });
                        break;
                }
                $('mapCanDiv').appendChild(interactiveMap.interface.orderMenu.element).hide();
            }
        };

        interactiveMap.interface.orderMenu.show = function(coor) {
            function getPosition(coor) {
                var width = interactiveMap.interface.orderMenu.element.getWidth();
                if (coor.x < width / 2)
                    return 0;
                else if (coor.x > (interactiveMap.visibleMap.mainLayer.canvasElement.width - width / 2))
                    return (interactiveMap.visibleMap.mainLayer.canvasElement.width - width);
                else
                    return (coor.x - width / 2);
            }

            switch (context.phase) {
                case 'Diplomacy':
                    interactiveMap.interface.orderMenu.showElement($("imgMove"));
                    interactiveMap.interface.orderMenu.showElement($("imgHold"));
                    interactiveMap.interface.orderMenu.showElement($("imgSMove"));
                    interactiveMap.interface.orderMenu.showElement($("imgSHold"));
                    interactiveMap.interface.orderMenu.showElement($("imgConvoy"));
                    interactiveMap.interface.orderMenu.showElement($("imgTransform"));
                    if (interactiveMap.currentOrder != null) {//||(unit(interactiveMap.selectedTerritoryID)&&(Territories.get(interactiveMap.selectedTerritoryID).type=="Coast")&&(Territories.get(interactiveMap.selectedTerritoryID).Unit.type=="Army")))
                        if ((interactiveMap.currentOrder.Unit.type == "Fleet") || (Territories.get(interactiveMap.selectedTerritoryID).type != "Coast"))
                            interactiveMap.interface.orderMenu.hideElement($("imgConvoy"));
                        if ((interactiveMap.currentOrder.Unit.Territory.type !== "Coast") || !interactiveMap.currentOrder.Unit.Territory.coastParent.supply)
                            interactiveMap.interface.orderMenu.hideElement($("imgTransform"));
                        interactiveMap.interface.orderMenu.element.show();
                    } else {
                        if ((Territories.get(interactiveMap.selectedTerritoryID).type == "Coast") && !Object.isUndefined(Territories.get(interactiveMap.selectedTerritoryID).Unit) && (Territories.get(interactiveMap.selectedTerritoryID).Unit.type == "Army")) {
                            interactiveMap.interface.orderMenu.hideElement($("imgMove"));
                            interactiveMap.interface.orderMenu.hideElement($("imgHold"));
                            interactiveMap.interface.orderMenu.hideElement($("imgSMove"));
                            interactiveMap.interface.orderMenu.hideElement($("imgSHold"));
                            interactiveMap.interface.orderMenu.hideElement($("imgTransform"));
                            interactiveMap.interface.orderMenu.showElement($("imgConvoy"));
                            interactiveMap.interface.orderMenu.element.show();
                        }
                    }
                    break;
            }

            var height = interactiveMap.interface.orderMenu.element.getHeight();
            interactiveMap.interface.orderMenu.element.setStyle({
                top: (((coor.y + 25 + height) > interactiveMap.visibleMap.mainLayer.canvasElement.height) ? interactiveMap.visibleMap.mainLayer.canvasElement.height - height : coor.y + 25) + 'px',
                left: getPosition(coor) + 'px'
            });
        };
    }

    function addSetTransform() {

        MyOrders.pluck('interactiveMap').map(function(IA) {
            IA.setOrder = function(value) {
                interactiveMap.interface.orderMenu.element.hide();

                if (this.orderType != null) {
                    interactiveMap.errorMessages.uncompletedOrder();
                    return;
                }

                if (value == "Convoy")
                    if (this.Order.Unit.Territory.type != "Coast") {
                        interactiveMap.errorMessages.noCoast(this.Order.Unit.terrID);
                        interactiveMap.abortOrder();
                        return;
                    } else if (this.Order.Unit.type != "Army") {
                        interactiveMap.errorMessages.noArmy(this.Order.Unit.terrID);
                        interactiveMap.abortOrder();
                        return;
                    }

                this.orderType = value;

                if (value === "Transform") { //get special transform code for order value
                    value = "Transform_"+(parseInt(this.Order.Unit.Territory.coastParentID) + 1000);

                    //Check if unit is an army on coast with two (or more) coasts (player will have to select the coast with extra click)
                    if (this.Order.Unit.type === 'Army' && this.Order.Unit.Territory.coast === "Parent") {
                        interactiveMap.insertMessage(" on coast ");
                        interactiveMap.greyOut.draw(new Array(this.Order.Unit.terrID));
                        return;
                    }
                }

                value = (value == "Convoy") ? "Move" : value;

                this.enterOrder('type', value);
            };

            IA.printType = function() {
                switch (this.orderType) {
                    case "Hold":
                        interactiveMap.insertMessage(" holds", true);
                        break;
                    case "Move":
                        interactiveMap.insertMessage(" moves to ");
                        break;
                    case "Support hold":
                        interactiveMap.insertMessage(" supports the holding unit in ");
                        break;
                    case "Support move":
                        interactiveMap.insertMessage(" supports the moving unit to ");
                        break;
                    case "Convoy":
                        interactiveMap.insertMessage(" moves via ");
                        break;

                    case "Transform":
                        interactiveMap.insertMessage(" transforms to " + ((this.Order.Unit.type === 'Army') ? interactiveMap.parameters.fleetName : interactiveMap.parameters.armyName), true);
                        break;
                }
            };
            
            IA.setOrderPart = function(terrID, coordinates) {
                switch (this.orderType) {
                    case "Move":
                        this.setMove(terrID, coordinates);
                        break;
                    case "Support hold":
                        this.setSupportHold(terrID);
                        break;
                    case "Support move":
                        this.setSupportMove(terrID, coordinates);
                        break;
                    case "Support move from":
                        this.setSupportMoveFrom(terrID);
                        break;
                    case "Convoy":
                        this.setConvoy(terrID);
                        break;
                    case "Transform":
                        this.setTransformCoast(terrID, coordinates);
                        break;
                }
            };
            
            IA.setTransformCoast = function(terrID, coordinates){
                if (terrID != this.Order.Unit.terrID) {
                    alert(interactiveMap.parameters.armyName + " in " + this.Order.Unit.Territory.name + " can not transform to " + interactiveMap.parameters.fleetName + " on " + Territories.get(terrID).name + " (not the same territory)");
                    return;
                }
                
                terrID = this.getCoastByCoords(Territories.filter(function(t){return t[1].coastParentID == terrID && t[1].coastParentID != t[1].id;}).pluck("1"), coordinates).id;
                
                interactiveMap.insertMessage(Territories.get(terrID).name.match(/\((.*)\)/)[1]);
                this.enterOrder('type', "Transform_"+(parseInt(terrID) + 1000));
            };
        });
    }

    function addDrawTransform() {
        function drawArmy(terrID){
                interactiveMap.visibleMap.mainLayer.context.drawImage(imgArmy,Territories.get(terrID).smallMapX-(imgArmy.width/2), Territories.get(terrID).smallMapY-(imgArmy.height/2));
        }
        
        function drawFleet(terrID){
                interactiveMap.visibleMap.mainLayer.context.drawImage(imgFleet,Territories.get(terrID).smallMapX-(imgFleet.width/2), Territories.get(terrID).smallMapY-(imgFleet.height/2));
        }
        
        function drawTransform(terrID)
        {
		var darkblue  = [40, 80,130];
		var lightblue = [70,150,230];
		
		var x = Territories.get(terrID).smallMapX;
                var y = Territories.get(terrID).smallMapY;
		
		var width=imgFleet.width+imgFleet.width/2;
		
                filledcircle(x,y,width,darkblue);
                filledcircle(x,y,width-2,lightblue);
                
                if(Territories.get(terrID).coastParent.Unit.type === 'Army')
                    drawFleet(terrID);
                else
                    drawArmy(terrID);
	}
        
        function filledcircle(x,y,width,color)
        {
                interactiveMap.visibleMap.mainLayer.context.beginPath();
                interactiveMap.visibleMap.mainLayer.context.arc(x,y,width/2,0,2*Math.PI);
                interactiveMap.visibleMap.mainLayer.context.closePath();
                interactiveMap.visibleMap.mainLayer.context.fillStyle = "rgb(" + color[0] + "," + color[1] + "," + color[2] + ")";
                interactiveMap.visibleMap.mainLayer.context.fill();
        }
        
        var draw2 = interactiveMap.draw;

        interactiveMap.draw = function() {
            draw2();


            for (var i = 0; i < MyOrders.length; i++) {
                if (MyOrders[i].isComplete && MyOrders[i].type.include('Transform')) {
                    drawTransform(parseInt(MyOrders[i].type.sub('Transform_',''))-1000);
                }
            }
        }
    }

    addTransformButton();
    addOrderMenuTransformButton();
    addSetTransform();
    addDrawTransform();
}


