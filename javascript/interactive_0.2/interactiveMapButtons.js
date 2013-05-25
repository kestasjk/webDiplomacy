/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */     //PROBLEM: FILE NOT LOADED!
var orderLine;

/*function createButtons() {
    
}*/



function createButtonInterface(){
$("mapstore").parentNode.insertBefore(new Element('div',{'id':'IA'}), $("mapstore"));

var colgroup = new Element('colgroup');
colgroup.appendChild(new Element('col',{'style':'width:20%'}));
colgroup.appendChild(new Element('col',{'style':'width:75%'}));
colgroup.appendChild(new Element('col',{'style':'width:5%'}));

var tr1 = new Element('tr').appendChild(new Element('td').appendChild(new Element('button',{'id':'IAswitch', 'onclick':'IAactivate()', 'disabled':'true'}).update("LOADING ...")).parentNode).parentNode;
tr1.appendChild(orderButtons()).parentNode.appendChild(new Element('td').appendChild(new Element('a',{'href':'javascript/interactive/help.html'}).appendChild(new Element('button').update("?")).parentNode).parentNode);

var tr2 = new Element('tr').appendChild(new Element('td').appendChild(new Element("Button",{'id':'ResetOrder', 'onclick':'resetOrder()', 'disabled':'true'})).update("Reset Order").parentNode).parentNode;
orderLine = new Element("p",{'style':'background-color:white;text-align:left;'}).update("Order-Line: ").hide();
tr2.appendChild(new Element('td').appendChild(orderLine).parentNode);

$("IA").appendChild(new Element('table',{'id':'IAtable', 'style':'margin-left:auto; margin-right:auto;width:80%'})).appendChild(colgroup).parentNode.appendChild(tr1).parentNode.appendChild(tr2);
}

function orderButtons(){
    var orderButtons = new Element("td",{'id':'orderButtons'});
    switch(context.phase) {
        case "Diplomacy":   orderButtons.appendChild(new Element('button',{'id':'hold', 'onclick':'hold()', 'disabled':'true'}).update("HOLD"));
                            orderButtons.appendChild(new Element('button',{'id':'move', 'onclick':'move()', 'disabled':'true'}).update("MOVE"));
                            orderButtons.appendChild(new Element('button',{'id':'sHold', 'onclick':'sHold()', 'disabled':'true'}).update("SUPPORT HOLD"));
                            orderButtons.appendChild(new Element('button',{'id':'sMove', 'onclick':'sMove()', 'disabled':'true'}).update("SUPPORT MOVE"));
                            orderButtons.appendChild(new Element('button',{'id':'convoy', 'onclick':'convoy()', 'disabled':'true'}).update("CONVOY"));
                            break;
        case "Builds":      if(MyOrders.length == 0){
                                orderButtons.appendChild(new Element('p').update("No orders this phase!"));
                            }else if(MyOrders[0].type == "Destroy"){
                                orderButtons.appendChild(new Element('button',{'id':'destroy', 'onclick':'destroy()', 'disabled':'true'}).update("DESTROY"));
                            }else{
                                orderButtons.appendChild(new Element('button',{'id':'buildArmy', 'onclick':'buildArmy()', 'disabled':'true'}).update("BUILD ARMY"));
                                orderButtons.appendChild(new Element('button',{'id':'buildFleet', 'onclick':'buildFleet()', 'disabled':'true'}).update("BUILD FLEET"));
                                orderButtons.appendChild(new Element('button',{'id':'wait', 'onclick':'wait()', 'disabled':'true'}).update("WAIT"));
                            }
    }
    return orderButtons;
}

function activateButton() {
    IAready = true;
    $("IAswitch").innerHTML = "activate IA";
    $("IAswitch").disabled = false;
}


function IAswitch() {
    var buttons = $("orderButtons").childNodes;
    if (IAactivated) {
        for(var i=0; i<buttons.length; i++){
            buttons[i].disabled = false;
        }
        $("ResetOrder").disabled = false;
        orderLine.show();
        $("IAswitch").innerHTML = "deactivate IA";
    } else {
        for(var i=0; i<buttons.length; i++){
            buttons[i].disabled = true;
        }
        $("ResetOrder").disabled = true;
        orderLine.hide();
        $("IAswitch").innerHTML = "activate IA";
    }
}


