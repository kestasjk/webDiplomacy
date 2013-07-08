<?php
// This is file installs the map data for the TreatyOfVerdun variant
defined('IN_CODE') or die('This script can not be run by itself.');
require_once("variants/install.php");

InstallTerritory::$Territories=array();
$countries=$this->countries;
$territoryRawData=array(
	array('Baltic Sea'			  , 'Sea', 'No',    0, 335,  35,335,  35),
	array('English Channel'		  , 'Sea', 'No',    0, 269, 194,269, 194),
	array('Atlantic Ocean'		  , 'Sea', 'No',    0,  47, 494, 47, 494),
	array('Mediterranean Sea'	  , 'Sea', 'No',    0, 384, 614,384, 614),
	array('Adriatic Sea'		  , 'Sea', 'No',    0, 623, 618,623, 618),
	array('Anglosaxons'			  , 'Coast', 'Yes', 0, 160, 165,160, 165),
	array('Bretons'				  , 'Coast', 'Yes', 0,  60, 340, 60, 340),
	array('Moors'				  , 'Coast', 'Yes', 0,  69, 642, 69, 642),
	array('Papal States'		  , 'Coast', 'Yes', 0, 556, 634,556, 634),
	array('Croats'				  , 'Coast', 'Yes', 0, 650, 545,650, 545),
	array('Serbs'				  , 'Coast', 'No',  0, 726, 534,726, 534),
	array('Avars'				  , 'Land', 'No',   0, 672, 431,672, 431),
	array('Slavs'				  , 'Coast', 'Yes', 0, 619, 239,619, 239),
	array('Danes'				  , 'Coast', 'No',  0, 463,  69,463,  69),
	array('Flanders'			  , 'Coast', 'Yes', 3, 260, 271,260, 271),
	array('Neustria'			  , 'Coast', 'Yes', 3, 198, 366,198, 366),
	array('Neustria (North Coast)', 'Coast', 'No',  3, 150, 322,150, 322),
	array('Neustria (South Coast)', 'Coast', 'No',  3, 124, 421,124, 421),
	array('Burgundy'			  , 'Land', 'No',   3, 287, 374,287, 374),
	array('Aquitaine'			  , 'Coast', 'Yes', 3, 168, 493,168, 493),
	array('Gascony'				  , 'Coast', 'No',  3,  83, 589, 83, 589),
	array('Septimania'			  , 'Coast', 'No',  3, 211, 619,211, 619),
	array('Saxony'				  , 'Coast', 'Yes', 1, 480, 183,480, 183),
	array('Saxony (East Coast)'	  , 'Coast', 'No',  1, 486, 120,486, 120),
	array('Saxony (West Coast)'	  , 'Coast', 'No',  1, 450, 114,450, 114),
	array('Westphalia'			  , 'Land', 'No',   1, 410, 188,410, 188),
	array('Thuringia'			  , 'Land', 'Yes',  1, 425, 259,425, 259),
	array('Franconia'			  , 'Land', 'Yes',  1, 456, 331,456, 331),
	array('Swabia'				  , 'Land', 'No',   1, 419, 417,419, 417),
	array('Bavaria'				  , 'Land', 'No',   1, 601, 436,601, 436),
	array('Friesland'			  , 'Coast', 'No',  2, 378, 160,378, 160),
	array('Lorraine'			  , 'Land', 'No',   2, 356, 333,356, 333),
	array('Transjurania'		  , 'Land', 'Yes',  2, 387, 468,387, 468),
	array('Provence'			  , 'Land', 'No',   2, 321, 500,321, 500),
	array('Lombardy'			  , 'Coast', 'Yes', 2, 430, 538,430, 538),
	array('Tuscany'				  , 'Coast', 'Yes', 2, 511, 494,511, 494),
	array('Tuscany (East Coast)'  , 'Coast', 'No',  2, 592, 520,592, 520),
	array('Tuscany (West Coast)'  , 'Coast', 'No',  2, 473, 598,473, 598)
);

foreach($territoryRawData as $territoryRawRow)
{
	list($name, $type, $supply, $countryID, $x, $y, $sx, $sy)=$territoryRawRow;
	new InstallTerritory($name, $type, $supply, $countryID, $x, $y, $sx, $sy);
}
unset($territoryRawData);

$bordersRawData=array(
	array('Baltic Sea','English Channel','Yes','No'),
	array('Baltic Sea','Anglosaxons','Yes','No'),
	array('Baltic Sea','Slavs','Yes','No'),
	array('Baltic Sea','Danes','Yes','No'),
	array('Baltic Sea','Saxony (East Coast)','Yes','No'),
	array('Baltic Sea','Saxony (West Coast)','Yes','No'),
	array('English Channel','Atlantic Ocean','Yes','No'),
	array('English Channel','Anglosaxons','Yes','No'),
	array('English Channel','Flanders','Yes','No'),
	array('English Channel','Neustria (North Coast)','Yes','No'),
	array('English Channel','Saxony (West Coast)','Yes','No'),
	array('English Channel','Friesland','Yes','No'),
	array('Atlantic Ocean','Anglosaxons','Yes','No'),
	array('Atlantic Ocean','Bretons','Yes','No'),
	array('Atlantic Ocean','Moors','Yes','No'),
	array('Atlantic Ocean','Neustria (North Coast)','Yes','No'),
	array('Atlantic Ocean','Neustria (South Coast)','Yes','No'),
	array('Atlantic Ocean','Aquitaine','Yes','No'),
	array('Atlantic Ocean','Gascony','Yes','No'),
	array('Mediterranean Sea','Moors','Yes','No'),
	array('Mediterranean Sea','Papal States','Yes','No'),
	array('Mediterranean Sea','Septimania','Yes','No'),
	array('Mediterranean Sea','Lombardy','Yes','No'),
	array('Mediterranean Sea','Tuscany (West Coast)','Yes','No'),
	array('Adriatic Sea','Papal States','Yes','No'),
	array('Adriatic Sea','Croats','Yes','No'),
	array('Adriatic Sea','Serbs','Yes','No'),
	array('Adriatic Sea','Tuscany (East Coast)','Yes','No'),
	array('Bretons','Neustria','No','Yes'),
	array('Bretons','Neustria (North Coast)','Yes','No'),
	array('Bretons','Neustria (South Coast)','Yes','No'),
	array('Moors','Gascony','Yes','Yes'),
	array('Moors','Septimania','Yes','Yes'),
	array('Papal States','Tuscany','No','Yes'),
	array('Papal States','Tuscany (East Coast)','Yes','No'),
	array('Papal States','Tuscany (West Coast)','Yes','No'),
	array('Croats','Serbs','Yes','Yes'),
	array('Croats','Avars','No','Yes'),
	array('Croats','Bavaria','No','Yes'),
	array('Croats','Tuscany','No','Yes'),
	array('Croats','Tuscany (East Coast)','Yes','No'),
	array('Serbs','Avars','No','Yes'),
	array('Serbs','Slavs','No','Yes'),
	array('Avars','Slavs','No','Yes'),
	array('Avars','Bavaria','No','Yes'),
	array('Slavs','Saxony','No','Yes'),
	array('Slavs','Saxony (East Coast)','Yes','No'),
	array('Slavs','Thuringia','No','Yes'),
	array('Slavs','Bavaria','No','Yes'),
	array('Danes','Saxony','No','Yes'),
	array('Danes','Saxony (East Coast)','Yes','No'),
	array('Danes','Saxony (West Coast)','Yes','No'),
	array('Flanders','Neustria','No','Yes'),
	array('Flanders','Neustria (North Coast)','Yes','No'),
	array('Flanders','Burgundy','No','Yes'),
	array('Flanders','Friesland','Yes','Yes'),
	array('Flanders','Lorraine','No','Yes'),
	array('Neustria','Burgundy','No','Yes'),
	array('Neustria','Aquitaine','No','Yes'),
	array('Neustria (South Coast)','Aquitaine','Yes','No'),
	array('Burgundy','Aquitaine','No','Yes'),
	array('Burgundy','Lorraine','No','Yes'),
	array('Burgundy','Transjurania','No','Yes'),
	array('Burgundy','Provence','No','Yes'),
	array('Aquitaine','Gascony','Yes','Yes'),
	array('Aquitaine','Septimania','No','Yes'),
	array('Aquitaine','Provence','No','Yes'),
	array('Gascony','Septimania','No','Yes'),
	array('Septimania','Provence','No','Yes'),
	array('Septimania','Lombardy','Yes','Yes'),
	array('Saxony','Westphalia','No','Yes'),
	array('Saxony','Thuringia','No','Yes'),
	array('Saxony','Friesland','No','Yes'),
	array('Saxony (West Coast)','Friesland','Yes','No'),
	array('Westphalia','Thuringia','No','Yes'),
	array('Westphalia','Friesland','No','Yes'),
	array('Thuringia','Franconia','No','Yes'),
	array('Thuringia','Bavaria','No','Yes'),
	array('Thuringia','Friesland','No','Yes'),
	array('Thuringia','Lorraine','No','Yes'),
	array('Franconia','Swabia','No','Yes'),
	array('Franconia','Bavaria','No','Yes'),
	array('Franconia','Lorraine','No','Yes'),
	array('Swabia','Bavaria','No','Yes'),
	array('Swabia','Lorraine','No','Yes'),
	array('Swabia','Transjurania','No','Yes'),
	array('Swabia','Lombardy','No','Yes'),
	array('Swabia','Tuscany','No','Yes'),
	array('Bavaria','Tuscany','No','Yes'),
	array('Friesland','Lorraine','No','Yes'),
	array('Lorraine','Transjurania','No','Yes'),
	array('Transjurania','Provence','No','Yes'),
	array('Transjurania','Lombardy','No','Yes'),
	array('Provence','Lombardy','No','Yes'),
	array('Lombardy','Tuscany','No','Yes'),
	array('Lombardy','Tuscany (West Coast)','Yes','No')
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
