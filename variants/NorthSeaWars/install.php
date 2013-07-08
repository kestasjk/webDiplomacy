<?php
// This is file installs the map data for the NorthSeaWars variant
defined('IN_CODE') or die('This script can not be run by itself.');
require_once("variants/install.php");

InstallTerritory::$Territories=array();
$countries=$this->countries;
$territoryRawData=array(
	array('South Britanny', 'Coast', 'Yes', 1, 239, 708, 239, 708),
	array('Cymru', 'Land', 'Yes', 1, 106, 688, 106, 688),
	array('Albion', 'Coast', 'No', 0, 204, 749, 204, 749),
	array('North Britanny', 'Coast', 'No', 1, 186, 583, 186, 583),
	array('Alba', 'Coast', 'Yes', 0, 125, 457, 125, 457),
	array('Channel', 'Sea', 'No', 0, 289, 788, 289, 788),
	array('Lower North Sea', 'Sea', 'No', 0, 372, 577, 372, 577),
	array('West North Sea', 'Sea', 'No', 0, 258, 530, 258, 530),
	array('Upper North Sea', 'Sea', 'No', 0, 302, 366, 302, 366),
	array('East North Sea', 'Sea', 'No', 0, 470, 445, 470, 445),
	array('Frisia', 'Coast', 'Yes', 3, 497, 602, 497, 602),
	array('Batavia', 'Coast', 'Yes', 0, 442, 665, 442, 665),
	array('Menapia', 'Coast', 'Yes', 2, 387, 743, 387, 743),
	array('West Belgica', 'Coast', 'No', 2, 365, 793, 365, 793),
	array('East Belgica', 'Land', 'No', 2, 538, 776, 538, 776),
	array('Germania Inferior', 'Land', 'No', 0, 553, 700, 553, 700),
	array('Germania Superior', 'Land', 'Yes', 2, 614, 793, 614, 793),
	array('Magna Germania', 'Land', 'Yes', 0, 649, 641, 649, 641),
	array('Amsivaria', 'Coast', 'Yes', 3, 563, 551, 563, 551),
	array('Sealand', 'Coast', 'No', 0, 672, 398, 672, 398),
	array('Limfjorden', 'Coast', 'Yes', 0, 564, 307, 564, 307),
	array('Skagerrak', 'Sea', 'No', 0, 534, 246, 534, 246),
	array('Vestland', 'Coast', 'No', 4, 418, 126, 418, 126),
	array('Ostland', 'Coast', 'No', 4, 528, 139, 528, 139),
	array('Sorland', 'Coast', 'Yes', 4, 454, 217, 454, 217),
	array('Gotaland', 'Coast', 'Yes', 4, 667, 238, 667, 238),
	array('wood', 'Coast', 'Yes', 0, 174, 122, 174, 122),
	array('iron', 'Coast', 'Yes', 0, 302, 123, 302, 123),
	array('grains', 'Coast', 'Yes', 0, 258, 189, 258, 189),
	array('Central North Sea', 'Sea', 'No', 0, 355, 465, 355, 465),
	array('Jutland', 'Coast', 'No', 0, 565, 423, 565, 423),
	array('Jutland (East Coast)', 'Coast', 'No', 0, 602, 448, 602, 448),
	array('Jutland (West Coast)', 'Coast', 'No', 0, 554, 439, 554, 439),
	array('Central North Sea (2)', 'Sea', 'No', 0, 237, 144, 237, 144)
);

foreach($territoryRawData as $territoryRawRow)
{
	list($name, $type, $supply, $countryID, $x, $y, $sx, $sy)=$territoryRawRow;
	new InstallTerritory($name, $type, $supply, $countryID, $x, $y, $sx, $sy);
}
unset($territoryRawData);

$bordersRawData=array(
	array('South Britanny','Cymru','No','Yes'),
	array('South Britanny','Albion','Yes','Yes'),
	array('South Britanny','North Britanny','Yes','Yes'),
	array('South Britanny','Channel','Yes','No'),
	array('South Britanny','Lower North Sea','Yes','No'),
	array('South Britanny','West North Sea','Yes','No'),
	array('Cymru','Albion','No','Yes'),
	array('Cymru','North Britanny','No','Yes'),
	array('Albion','Channel','Yes','No'),
	array('North Britanny','Alba','Yes','Yes'),
	array('North Britanny','West North Sea','Yes','No'),
	array('Alba','West North Sea','Yes','No'),
	array('Alba','Upper North Sea','Yes','No'),
	array('Channel','Lower North Sea','Yes','No'),
	array('Channel','Menapia','Yes','No'),
	array('Channel','West Belgica','Yes','No'),
	array('Lower North Sea','West North Sea','Yes','No'),
	array('Lower North Sea','East North Sea','Yes','No'),
	array('Lower North Sea','Frisia','Yes','No'),
	array('Lower North Sea','Batavia','Yes','No'),
	array('Lower North Sea','Menapia','Yes','No'),
	array('Lower North Sea','Central North Sea','Yes','No'),
	array('West North Sea','Upper North Sea','Yes','No'),
	array('West North Sea','Central North Sea','Yes','No'),
	array('Upper North Sea','East North Sea','Yes','No'),
	array('Upper North Sea','Vestland','Yes','No'),
	array('Upper North Sea','Sorland','Yes','No'),
	array('Upper North Sea','Central North Sea','Yes','No'),
	array('East North Sea','Frisia','Yes','No'),
	array('East North Sea','Amsivaria','Yes','No'),
	array('East North Sea','Limfjorden','Yes','No'),
	array('East North Sea','Skagerrak','Yes','No'),
	array('East North Sea','Sorland','Yes','No'),
	array('East North Sea','Central North Sea','Yes','No'),
	array('East North Sea','Jutland (West Coast)','Yes','No'),
	array('Frisia','Batavia','Yes','Yes'),
	array('Frisia','Germania Inferior','No','Yes'),
	array('Frisia','Amsivaria','Yes','Yes'),
	array('Batavia','Menapia','Yes','Yes'),
	array('Batavia','Germania Inferior','No','Yes'),
	array('Menapia','West Belgica','Yes','Yes'),
	array('Menapia','East Belgica','No','Yes'),
	array('Menapia','Germania Inferior','No','Yes'),
	array('West Belgica','East Belgica','No','Yes'),
	array('East Belgica','Germania Inferior','No','Yes'),
	array('East Belgica','Germania Superior','No','Yes'),
	array('Germania Inferior','Germania Superior','No','Yes'),
	array('Germania Inferior','Magna Germania','No','Yes'),
	array('Germania Inferior','Amsivaria','No','Yes'),
	array('Germania Superior','Magna Germania','No','Yes'),
	array('Magna Germania','Amsivaria','No','Yes'),
	array('Magna Germania','Sealand','No','Yes'),
	array('Amsivaria','Sealand','No','Yes'),
	array('Amsivaria','Jutland','No','Yes'),
	array('Amsivaria','Jutland (West Coast)','Yes','No'),
	array('Sealand','Limfjorden','Yes','Yes'),
	array('Sealand','Skagerrak','Yes','No'),
	array('Sealand','Gotaland','Yes','Yes'),
	array('Sealand','Jutland','No','Yes'),
	array('Sealand','Jutland (East Coast)','Yes','No'),
	array('Limfjorden','Skagerrak','Yes','No'),
	array('Limfjorden','Jutland','No','Yes'),
	array('Limfjorden','Jutland (East Coast)','Yes','No'),
	array('Limfjorden','Jutland (West Coast)','Yes','No'),
	array('Skagerrak','Ostland','Yes','No'),
	array('Skagerrak','Sorland','Yes','No'),
	array('Skagerrak','Gotaland','Yes','No'),
	array('Vestland','Ostland','No','Yes'),
	array('Vestland','Sorland','Yes','Yes'),
	array('Ostland','Sorland','Yes','Yes'),
	array('Ostland','Gotaland','Yes','Yes'),
	array('wood','iron','Yes','Yes'),
	array('wood','grains','Yes','Yes'),
	array('iron','grains','Yes','Yes'),
	array('Central North Sea','wood','Yes','No'),
	array('Central North Sea','iron','Yes','No'),
	array('Central North Sea','grains','Yes','No')
);

foreach($bordersRawData as $borderRawRow)
{
	list($from, $to, $fleets, $armies)=$borderRawRow;
	InstallTerritory::$Territories[$to]  ->addBorder(InstallTerritory::$Territories[$from],$fleets,$armies);
}
unset($bordersRawData);

// Custom footer not changed by edit tool

InstallTerritory::runSQL($this->mapID);
InstallCache::terrJSON($this->territoriesJSONFile(),$this->mapID);

// Change the links to CentralNorthSea from the economic-resources.
global $DB;
$DB->sql_put('DELETE FROM wD_Borders        WHERE mapID='.$this->mapID.' AND fromTerrID=27 AND toTerrID=30');
$DB->sql_put('DELETE FROM wD_CoastalBorders WHERE mapID='.$this->mapID.' AND fromTerrID=27 AND toTerrID=30');
$DB->sql_put('DELETE FROM wD_Borders        WHERE mapID='.$this->mapID.' AND fromTerrID=28 AND toTerrID=30');
$DB->sql_put('DELETE FROM wD_CoastalBorders WHERE mapID='.$this->mapID.' AND fromTerrID=28 AND toTerrID=30');
$DB->sql_put('DELETE FROM wD_Borders        WHERE mapID='.$this->mapID.' AND fromTerrID=29 AND toTerrID=30');
$DB->sql_put('DELETE FROM wD_CoastalBorders WHERE mapID='.$this->mapID.' AND fromTerrID=29 AND toTerrID=30');

?>








