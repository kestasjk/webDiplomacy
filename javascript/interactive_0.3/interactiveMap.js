
/*function show_coords(event, gameID)
 {
 var x= event.clientX + window.pageXOffset;
 var y= event.clientY + window.pageYOffset;
 
 var element=document.getElementById("mapImage");
 var xmap=0;
 var ymap=0;
 
 while(element.tagName != "BODY") {
 xmap += element.offsetLeft;
 ymap += element.offsetTop;
 element = element.offsetParent;
 }
 
 var posOnMapX = x - xmap;
 var posOnMapY = y - ymap;
 
 iM("X coords: " + x + ", Y coords: " + y + " X coordsMap: " + xmap + " Y coordsMap: " + ymap + " XOffset: " + window.pageXOffset + " YOffset: " + window.pageYOffset + " posOnMapX: " + posOnMapX + " posOnMapY: " + posOnMapY);
 
 getTerritoryName(posOnMapX, posOnMapY, gameID);
 }*/
//var gameID;
var IAmapCtx;
//var IAmapLoaded;

//var jsonData;
var orderEle;

var IAready;
var IAactivated;



//initializes interactiveMap
function loadIA(IAmapPNG) {
    IAready = false;
    IAactivated = false;

    //gameID = game;
    ///turn = newTurn;

    terrSel = false;
    needOwnUnit = true;
    needUnit = true;

    //IAmapLoaded = false;
    createButtonInterface();
    loadIAmap(IAmapPNG);
    
    //ordersHTML.sendUpdates('ajax.php');
    //getData();
}


function loadIAmap(IAmapPNG) {
    var imgIAmap = new Image();
    imgIAmap.observe('load', function() {
        mapImg = $("mapImage");
        var IAmap = new Element("canvas", {'width': mapImg.getWidth(), 'height': mapImg.getHeight()});
        IAmapCtx = IAmap.getContext("2d");
        IAmapCtx.drawImage(imgIAmap, 0, 0);
        activateButton();
    });
    imgIAmap.src = IAmapPNG;
}

/*function getData() {
 new Ajax.Request('interactiveMap.php?gameID=' + gameID, {
 method: 'post',
 asynchronous: true,
 onSuccess: function(response) {
 //jsonData = eval("(" + xmlhttp.responseText + ")");
 //document.write(response.responseText);//iM("Hihi");
 jsonData = response.responseText.evalJSON();
 getMaps();
 },
 onFailure: function(response) {
 document.write(response.responseText);
 }
 });
 /*var xmlhttp;
 if (window.XMLHttpRequest)
 {// code for IE7+, Firefox, Chrome, Opera, Safari
 xmlhttp = new XMLHttpRequest();
 }
 else
 {// code for IE6, IE5
 xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
 }
 xmlhttp.onreadystatechange = function()
 {
 if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
 {
 alert(xmlhttp.responseText);
 jsonData = eval("(" + xmlhttp.responseText + ")");
 getMaps(gameID);
 //document.write(xmlhttp.responseText);
 
 }
 }
 xmlhttp.open("GET", "interactiveMap.php?gameID=" + gameID, true);
 xmlhttp.send();
 
 //alert('itWorks!')*/
//}

/*function getMaps() {
 //document.images[0].src = img.src;
 
 var readyStateCheckInterval = setInterval(function() {      //checks, if page is completely loaded (inclusive IAmap)
 if(IAmapLoaded){
 clearInterval(readyStateCheckInterval);
 activateButton();
 }
 }, 10);
 }*/

function IAactivate() {
    IAactivated = !IAactivated;
    if (IAactivated) {
        //orderEle = document.getElementById("orderFormElement");
        orderEle = $("orderFormElement");
    }
    IAswitch();
    IAmap();
}

function iM(content) {
    insertMessage(content);
}

function insertMessage(content) {
    if (orderLine != null)
        if (orderInProgress != null) {
            orderLine.innerHTML += content;
        } else {
            orderLine.innerHTML = "Order-Line: " + content;
        }

    else
        $("sendbox").value += content;
}
//<textarea id="sendbox" tabindex="1" name="newmessage" style="width:98% !important" width="100%" rows="3"></textarea>
