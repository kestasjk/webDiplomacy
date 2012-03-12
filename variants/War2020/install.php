<?php
// This is file installs the map data for the War2020 variant
defined('IN_CODE') or die('This script can not be run by itself.');
require_once("variants/install.php");

InstallTerritory::$Territories=array();
$countries=$this->countries;
$territoryRawData=array(
	array('USA', 'Coast', 'Yes', 2, 278, 344, 173, 222),
	array('Mexico', 'Coast', 'Yes', 0, 270, 408, 177, 264),
	array('USA (West Coast)', 'Coast', 'No', 0, 177, 301, 113, 208),
	array('USA (East Coast)', 'Coast', 'No', 0, 300, 356, 174, 220),
	array('Canada', 'Coast', 'No', 0, 251, 207, 149, 123),
	array('Arctic Ocean', 'Sea', 'No', 0, 568, 49, 350, 36),
	array('North Pacific Ocean', 'Sea', 'No', 0, 1127, 326, 698, 203),
	array('South Pacific Ocean', 'Sea', 'No', 0, 188, 650, 79, 402),
	array('OAS', 'Coast', 'Yes', 3, 355, 556, 219, 293),
	array('North Atlantic Ocean', 'Sea', 'No', 0, 443, 304, 277, 177),
	array('South Atlantic Ocean', 'Sea', 'No', 0, 525, 645, 300, 395),
	array('Congo', 'Coast', 'Yes', 0, 600, 466, 369, 292),
	array('Congo (West Coast)', 'Coast', 'No', 0, 547, 431, 367, 291),
	array('Congo (East Coast)', 'Coast', 'No', 0, 654, 513, 407, 320),
	array('South Africa', 'Coast', 'Yes', 5, 611, 562, 376, 317),
	array('OPEC', 'Coast', 'Yes', 7, 650, 405, 375, 243),
	array('European Union', 'Coast', 'Yes', 4, 552, 312, 346, 194),
	array('Mediterranean', 'Sea', 'No', 0, 602, 347, 360, 208),
	array('Eastern Europe', 'Coast', 'Yes', 0, 644, 234, 401, 148),
	array('Eastern Europe (North Coast)', 'Coast', 'No', 0, 638, 190, 398, 122),
	array('Eastern Europe (South Coast)', 'Coast', 'No', 0, 652, 302, 388, 197),
	array('Russia', 'Coast', 'Yes', 9, 850, 122, 474, 160),
	array('Mongolia', 'Land', 'No', 0, 879, 301, 519, 170),
	array('China', 'Coast', 'Yes', 8, 877, 354, 533, 224),
	array('Pakistan', 'Coast', 'Yes', 0, 747, 369, 465, 235),
	array('India', 'Coast', 'Yes', 6, 787, 416, 492, 258),
	array('South East Asia', 'Coast', 'Yes', 0, 919, 476, 546, 265),
	array('Korea', 'Coast', 'Yes', 0, 958, 299, 589, 198),
	array('Japan', 'Coast', 'Yes', 10, 968, 351, 605, 221),
	array('Australia', 'Coast', 'Yes', 1, 911, 554, 619, 356),
	array('Indian Ocean', 'Sea', 'No', 0, 776, 505, 508, 379),
	array('Antarctica', 'Coast', 'No', 0, 833, 787, 511, 501),
	array('McMurdo Base', 'Coast', 'Yes', 0, 354, 757, 182, 518),
	array('aus', 'Coast', 'No', 1, 699, 12, 438, 9),
	array('usaa', 'Coast', 'No', 2, 745, 13, 470, 10),
	array('oass', 'Coast', 'No', 3, 791, 13, 499, 8),
	array('eu', 'Coast', 'No', 4, 834, 13, 524, 9),
	array('SAf', 'Coast', 'No', 5, 872, 13, 550, 7),
	array('Indi', 'Coast', 'No', 6, 919, 13, 574, 10),
	array('opecc', 'Coast', 'No', 7, 978, 13, 613, 10),
	array('chin', 'Coast', 'No', 8, 1038, 14, 650, 10),
	array('russ', 'Coast', 'No', 9, 1103, 13, 696, 12),
	array('jap', 'Coast', 'No', 10, 1166, 11, 730, 9)
);

foreach($territoryRawData as $territoryRawRow)
{
	list($name, $type, $supply, $countryID, $x, $y, $sx, $sy)=$territoryRawRow;
	new InstallTerritory($name, $type, $supply, $countryID, $x, $y, $sx, $sy);
}
unset($territoryRawData);

$bordersRawData=array(
	array('USA','Mexico','No','Yes'),
	array('USA','Canada','No','Yes'),
	array('Mexico','USA (West Coast)','Yes','No'),
	array('Mexico','USA (East Coast)','Yes','No'),
	array('Mexico','North Pacific Ocean','Yes','No'),
	array('Mexico','South Pacific Ocean','Yes','No'),
	array('Mexico','OAS','Yes','Yes'),
	array('Mexico','North Atlantic Ocean','Yes','No'),
	array('Mexico','South Atlantic Ocean','Yes','No'),
	array('USA (West Coast)','Canada','Yes','No'),
	array('USA (West Coast)','Arctic Ocean','Yes','No'),
	array('USA (West Coast)','North Pacific Ocean','Yes','No'),
	array('USA (East Coast)','Canada','Yes','No'),
	array('USA (East Coast)','North Atlantic Ocean','Yes','No'),
	array('Canada','Arctic Ocean','Yes','No'),
	array('Canada','North Atlantic Ocean','Yes','No'),
	array('Arctic Ocean','North Pacific Ocean','Yes','No'),
	array('Arctic Ocean','North Atlantic Ocean','Yes','No'),
	array('Arctic Ocean','Eastern Europe (North Coast)','Yes','No'),
	array('Arctic Ocean','Russia','Yes','No'),
	array('North Pacific Ocean','South Pacific Ocean','Yes','No'),
	array('North Pacific Ocean','Russia','Yes','No'),
	array('North Pacific Ocean','China','Yes','No'),
	array('North Pacific Ocean','South East Asia','Yes','No'),
	array('North Pacific Ocean','Korea','Yes','No'),
	array('North Pacific Ocean','Japan','Yes','No'),
	array('South Pacific Ocean','OAS','Yes','No'),
	array('South Pacific Ocean','South Atlantic Ocean','Yes','No'),
	array('South Pacific Ocean','South East Asia','Yes','No'),
	array('South Pacific Ocean','Australia','Yes','No'),
	array('South Pacific Ocean','Indian Ocean','Yes','No'),
	array('South Pacific Ocean','Antarctica','Yes','No'),
	array('South Pacific Ocean','McMurdo Base','Yes','No'),
	array('OAS','South Atlantic Ocean','Yes','No'),
	array('North Atlantic Ocean','South Atlantic Ocean','Yes','No'),
	array('North Atlantic Ocean','Congo (West Coast)','Yes','No'),
	array('North Atlantic Ocean','OPEC','Yes','No'),
	array('North Atlantic Ocean','European Union','Yes','No'),
	array('North Atlantic Ocean','Mediterranean','Yes','No'),
	array('North Atlantic Ocean','Eastern Europe (North Coast)','Yes','No'),
	array('South Atlantic Ocean','Congo (West Coast)','Yes','No'),
	array('South Atlantic Ocean','South Africa','Yes','No'),
	array('South Atlantic Ocean','Indian Ocean','Yes','No'),
	array('South Atlantic Ocean','Antarctica','Yes','No'),
	array('South Atlantic Ocean','McMurdo Base','Yes','No'),
	array('Congo','South Africa','No','Yes'),
	array('Congo','OPEC','No','Yes'),
	array('Congo (West Coast)','South Africa','Yes','No'),
	array('Congo (West Coast)','OPEC','Yes','No'),
	array('Congo (East Coast)','South Africa','Yes','No'),
	array('Congo (East Coast)','OPEC','Yes','No'),
	array('Congo (East Coast)','Indian Ocean','Yes','No'),
	array('South Africa','Indian Ocean','Yes','No'),
	array('OPEC','Mediterranean','Yes','No'),
	array('OPEC','Eastern Europe','No','Yes'),
	array('OPEC','Eastern Europe (South Coast)','Yes','No'),
	array('OPEC','Russia','No','Yes'),
	array('OPEC','Pakistan','Yes','Yes'),
	array('OPEC','Indian Ocean','Yes','No'),
	array('European Union','Mediterranean','Yes','No'),
	array('European Union','Eastern Europe','No','Yes'),
	array('European Union','Eastern Europe (North Coast)','Yes','No'),
	array('European Union','Eastern Europe (South Coast)','Yes','No'),
	array('Mediterranean','Eastern Europe (South Coast)','Yes','No'),
	array('Eastern Europe','Russia','No','Yes'),
	array('Eastern Europe (North Coast)','Russia','Yes','No'),
	array('Russia','Mongolia','No','Yes'),
	array('Russia','China','No','Yes'),
	array('Russia','Pakistan','No','Yes'),
	array('Russia','Korea','Yes','Yes'),
	array('Mongolia','China','No','Yes'),
	array('Mongolia','Korea','No','Yes'),
	array('China','Pakistan','No','Yes'),
	array('China','India','No','Yes'),
	array('China','South East Asia','Yes','Yes'),
	array('China','Korea','Yes','Yes'),
	array('Pakistan','India','Yes','Yes'),
	array('Pakistan','Indian Ocean','Yes','No'),
	array('India','South East Asia','Yes','Yes'),
	array('India','Indian Ocean','Yes','No'),
	array('South East Asia','Australia','Yes','Yes'),
	array('South East Asia','Indian Ocean','Yes','No'),
	array('Korea','Japan','Yes','Yes'),
	array('Australia','Indian Ocean','Yes','No'),
	array('Indian Ocean','Antarctica','Yes','No'),
	array('Antarctica','McMurdo Base','Yes','Yes')
);

foreach($bordersRawData as $borderRawRow)
{
	list($from, $to, $fleets, $armies)=$borderRawRow;
	InstallTerritory::$Territories[$to]  ->addBorder(InstallTerritory::$Territories[$from],$fleets,$armies);
}
unset($bordersRawData);

InstallTerritory::runSQL($this->mapID);
InstallCache::terrJSON($this->territoriesJSONFile(),$this->mapID);
?>
