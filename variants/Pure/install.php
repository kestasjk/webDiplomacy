<?php
// This file will install the map data for the Pure variant

require_once("variants/install.php");
InstallTerritory::$Territories=array();
$countries=$this->countries;
$territoryRawData=array(
	array('London', 'Land', 'Yes', 720, 300, 365, 145, 'England'),
	array('Rome', 'Land', 'Yes', 115, 590, 60, 290, 'Italy'),
	array('Moscow', 'Land', 'Yes', 50, 300, 25, 145, 'Russia'),
	array('Berlin', 'Land', 'Yes', 390, 720, 195, 360, 'Germany'),
	array('Paris', 'Land', 'Yes', 670, 590, 335, 290, 'France'),
	array('Vienna', 'Land', 'Yes', 530, 70, 260, 45, 'Austria'),
	array('Constantinople', 'Land', 'Yes', 240, 70, 125, 45, 'Turkey')
);

foreach($territoryRawData as $territoryRawRow)
{
	list($name, $type, $supply, $x, $y, $sx, $sy, $country)=$territoryRawRow;
	if( $country=='Neutral' )
		 $countryID=0;
	else
		 $countryID=$this->countryID($country);
	new InstallTerritory($name, $type, $supply, $countryID, $x, $y, $sx, $sy);
}
unset($territoryRawData);		

$bordersRawData=array(
	array('London','Rome','No','Yes'),
	array('London','Moscow','No','Yes'),
	array('London','Berlin','No','Yes'),
	array('London','Paris','No','Yes'),
	array('London','Vienna','No','Yes'),
	array('London','Constantinople','No','Yes'),
	array('Rome','London','No','Yes'),
	array('Rome','Moscow','No','Yes'),
	array('Rome','Berlin','No','Yes'),
	array('Rome','Paris','No','Yes'),
	array('Rome','Vienna','No','Yes'),
	array('Rome','Constantinople','No','Yes'),
	array('Moscow','London','No','Yes'),
	array('Moscow','Rome','No','Yes'),
	array('Moscow','Berlin','No','Yes'),
	array('Moscow','Paris','No','Yes'),
	array('Moscow','Vienna','No','Yes'),
	array('Moscow','Constantinople','No','Yes'),
	array('Berlin','London','No','Yes'),
	array('Berlin','Rome','No','Yes'),
	array('Berlin','Moscow','No','Yes'),
	array('Berlin','Paris','No','Yes'),
	array('Berlin','Vienna','No','Yes'),
	array('Berlin','Constantinople','No','Yes'),
	array('Paris','London','No','Yes'),
	array('Paris','Rome','No','Yes'),
	array('Paris','Moscow','No','Yes'),
	array('Paris','Berlin','No','Yes'),
	array('Paris','Vienna','No','Yes'),
	array('Paris','Constantinople','No','Yes'),
	array('Vienna','London','No','Yes'),
	array('Vienna','Rome','No','Yes'),
	array('Vienna','Moscow','No','Yes'),
	array('Vienna','Berlin','No','Yes'),
	array('Vienna','Paris','No','Yes'),
	array('Vienna','Constantinople','No','Yes'),
	array('Constantinople','London','No','Yes'),
	array('Constantinople','Rome','No','Yes'),
	array('Constantinople','Moscow','No','Yes'),
	array('Constantinople','Berlin','No','Yes'),
	array('Constantinople','Paris','No','Yes'),
	array('Constantinople','Vienna','No','Yes')
);

foreach($bordersRawData as $borderRawRow)
{
	 list($from, $to, $fleets, $armies)=$borderRawRow;
	 InstallTerritory::$Territories[$from]->addBorder(InstallTerritory::$Territories[$to]  ,$fleets,$armies);
}
unset($bordersRawData);

InstallTerritory::runSQL($this->mapID);
InstallCache::terrJSON($this->territoriesJSONFile(),$this->mapID);
?>
