Content of download package:
----------------------------
- README
- required files for installation
- example for "WWIV" variant

Installation:
-------------

- Copy the "interactiveMap" folder in the main directory of your webdip.


- Insert the following lines into the "jsLoadBoard" function of "board/orders/orderinterface.php":

"libHTML::$footerIncludes[] = '../interactiveMap/javascript/interactiveMap.js';
 libHTML::$footerIncludes[] = '../interactiveMap/javascript/interactiveMapDraw.js';
 libHTML::$footerIncludes[] = '../interactiveMap/javascript/interactiveMapOrders.js';
 libHTML::$footerIncludes[] = '../interactiveMap/javascript/interactiveMapButtons.js';"

and into the "libHTML::$footerScript[]"-String beneath the "footerIncludes:
"loadIA();"

Finally the code inside your function should look like:
"protected function jsLoadBoard() {          
		libHTML::$footerIncludes[] = 'board/model.js';
		libHTML::$footerIncludes[] = 'board/load.js';
		libHTML::$footerIncludes[] = 'orders/order.js';
		libHTML::$footerIncludes[] = 'orders/phase'.$this->phase.'.js';
		libHTML::$footerIncludes[] = '../'.libVariant::$Variant->territoriesJSONFile();
                //added
                libHTML::$footerIncludes[] = '../interactiveMap/javascript/interactiveMap.js';
                libHTML::$footerIncludes[] = '../interactiveMap/javascript/interactiveMapDraw.js';
                libHTML::$footerIncludes[] = '../interactiveMap/javascript/interactiveMapOrders.js';
                libHTML::$footerIncludes[] = '../interactiveMap/javascript/interactiveMapButtons.js';

		libHTML::$footerScript[] = '
		loadTerritories();
		loadBoardTurnData();
		loadModel();
		loadBoard();
		loadOrdersModel();
		loadOrdersForm();
		loadOrdersPhase();
                loadIA();
		';//^added
	}"



IA_smallMap.png:
----------------

To detect the clicked territory, each territory must have a unique color. In the normal "smallMap.png" only the land territories are colored. To get a complete colored map as "IA_smallMap.png" the "IAgetMap.php" script checks, if such an image exists in the directory of the variant, and creates it, if it does not exist. 
As the territories are colored automatically, every territory needs to be separated by a black border. If this is not the case, the flood fill function will color two territories at once which will raise an error. Depending on the map, it could be necessary to over-work the produced map. Make sure every territory keeps its unique color.
You can use "IAgetMap.php" to create a map without using the interface by opening "IAgetMap.php?variantID=[yourVarID]" in your browser. The colored map should appear and is saved in the directory of the variant if it was not saved before.


IA_mapData.map:
---------------

As javascript can not work with image-palettes (or at least I do not know how it could work), every pixel that should by greyed-out during the order-giving has to be checked manually. The fastest way to do this is using a flood fill algorithm, that needs coordinates for each seperated part of a territory. This coordinates are saved as json-file ("IA_mapData.map").
The file will be created automatically at runtime with "IAgetMapTerrDat.php".


Variant-specific drawOrder functions:
-------------------------------------

Some variants changed the drawOrder functions of "drawMap.php". As the basic functions are rewritten in JavaScript for the interface, you have to add the changed functions manually. 
An example for the "WWIV" variant is in the download package.

- create a new JavaScript file (example: "WWIV/resources/interactiveMapDrawExtension.js")

- create a function in the following way:
"function extension(order, fromTerrID, toTerrID, terrID){}"

- place your code inside this function as it is directly called by the interface script. Note that toTerrID and terrID are not defined for some order types!

- place the file inside the html-code by appending it to the "footerIncludes"-array. This has to be done as an extension of the php orderInterface-class (example: "WWIV/classes/OrderInterface.php"):
"class InteractiveMapDrawExtension_OrderInterface extends OrderInterface 
{
        protected function jsLoadBoard()
	{
		global $Variant;

		parent::jsLoadBoard();
                
		libHTML::$footerIncludes[] = '../variants/'.$Variant->name.'/resources/interactiveMapDrawExtension.js';
	}
}"
Note that you have to define the variant-specific OrderInterface-class in the "variant.php" file, if this is not done yet (example: "WWIV/variant.php").