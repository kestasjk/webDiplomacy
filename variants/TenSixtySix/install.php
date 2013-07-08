﻿<?php
// This is file installs the map data for the TenSixtySix variant
defined('IN_CODE') or die('This script can not be run by itself.');
require_once("variants/install.php");

InstallTerritory::$Territories=array();
$countries=$this->countries;
$territoryRawData=array(
	array('Stamford Bridge', 'Coast', 'No', 416, 569, 207, 286, 'English'),
	array('York', 'Coast', 'Yes', 412, 493, 206, 249, 'English'),
	array('The Dales', 'Land', 'No', 373, 489, 187, 234, 'English'),
	array('Heart of England', 'Land', 'No', 361, 593, 180, 296, 'English'),
	array('Hadrian s Wall', 'Land', 'No', 398, 436, 196, 217, 'Norwegians'),
	array('Gwynedd and Lakes District', 'Coast', 'Yes', 334, 483, 161, 248, 'Neutral units'),
	array('Wales', 'Coast', 'No', 252, 540, 129, 271, 'Neutral'),
	array('East Anglia', 'Coast', 'No', 446, 603, 223, 303, 'English'),
	array('London', 'Coast', 'Yes', 383, 667, 183, 321, 'English'),
	array('Hastings', 'Coast', 'No', 344, 696, 171, 348, 'English'),
	array('Winchester', 'Coast', 'Yes', 221, 628, 114, 318, 'English'),
	array('Winchester (South Coast)', 'Coast', 'No', 273, 671, 116, 326, 'English'),
	array('Winchester (West Coast)', 'Coast', 'No', 222, 612, 125, 314, 'English'),
	array('Cornwall', 'Coast', 'No', 168, 635, 76, 322, 'Neutral'),
	array('Edinburgh', 'Coast', 'Yes', 413, 398, 205, 190, 'Neutral units'),
	array('Glasgow', 'Coast', 'Yes', 313, 370, 154, 189, 'Neutral units'),
	array('Highlands and Islands', 'Coast', 'No', 408, 210, 204, 105, 'Neutral'),
	array('Oxford', 'Coast', 'Yes', 300, 616, 150, 308, 'English'),
	array('Dublin', 'Coast', 'Yes', 216, 396, 107, 170, 'Neutral units'),
	array('Waterford', 'Coast', 'No', 140, 488, 41, 242, 'Neutral'),
	array('Firth of Clyde', 'Sea', 'No', 292, 348, 147, 174, 'Neutral'),
	array('Irish Sea', 'Sea', 'No', 236, 484, 114, 242, 'Neutral'),
	array('Mid Atlantic Ocean', 'Sea', 'No', 58, 612, 29, 306, 'Neutral'),
	array('North Atlantic Ocean', 'Sea', 'No', 138, 285, 26, 172, 'Neutral'),
	array('Bristol Channel', 'Sea', 'No', 209, 584, 106, 295, 'Neutral'),
	array('North English Channel', 'Sea', 'No', 294, 714, 149, 356, 'Neutral'),
	array('South English Channel', 'Sea', 'No', 364, 745, 155, 384, 'Neutral'),
	array('Strait of Dover', 'Sea', 'No', 398, 723, 197, 363, 'Neutral'),
	array('Thames Estuary', 'Sea', 'No', 425, 678, 218, 338, 'Neutral'),
	array('Southwest North Sea', 'Sea', 'No', 552, 590, 265, 306, 'Neutral'),
	array('Northwest North Sea', 'Sea', 'No', 494, 456, 241, 228, 'Neutral'),
	array('Shetland and Orkneys', 'Sea', 'No', 531, 276, 256, 145, 'Neutral'),
	array('Norwegian Sea', 'Sea', 'No', 576, 67, 301, 36, 'Neutral'),
	array('Northeast North Sea', 'Sea', 'No', 643, 285, 329, 92, 'Neutral'),
	array('Southeast North Sea', 'Sea', 'No', 650, 472, 335, 232, 'Neutral'),
	array('Heligoland Bight', 'Sea', 'No', 702, 564, 356, 278, 'Neutral'),
	array('Channel Islands', 'Sea', 'No', 200, 767, 105, 379, 'Neutral'),
	array('Skagerrak', 'Sea', 'No', 826, 378, 426, 183, 'Neutral'),
	array('Baltic Sea', 'Sea', 'No', 953, 631, 503, 290, 'Neutral'),
	array('Denmark', 'Coast', 'Yes', 804, 478, 405, 239, 'Neutral units'),
	array('Danish Islands', 'Coast', 'No', 863, 559, 432, 280, 'Neutral'),
	array('Götaland', 'Coast', 'No', 956, 534, 475, 260, 'Neutral'),
	array('Sweden', 'Coast', 'Yes', 1044, 417, 529, 196, 'Neutral units'),
	array('Finnland', 'Coast', 'No', 1133, 348, 565, 177, 'Neutral'),
	array('Oslo', 'Coast', 'Yes', 898, 320, 450, 171, 'Norwegians'),
	array('Bergen', 'Coast', 'No', 731, 222, 367, 110, 'Norwegians'),
	array('Trondheim', 'Coast', 'Yes', 798, 98, 402, 49, 'Norwegians'),
	array('Kaupang', 'Coast', 'Yes', 812, 337, 408, 168, 'Norwegians'),
	array('Free Cities Passage', 'Coast', 'No', 768, 626, 386, 312, 'Neutral'),
	array('County of Flanders', 'Coast', 'Yes', 519, 733, 256, 366, 'Neutral units'),
	array('Duchy of Brittany', 'Coast', 'Yes', 143, 757, 74, 382, 'Neutral units'),
	array('Ecclesiastes', 'Coast', 'No', 430, 740, 216, 371, 'Neutral'),
	array('Rouen', 'Coast', 'Yes', 398, 765, 200, 382, 'Normans'),
	array('Caen', 'Coast', 'Yes', 366, 788, 180, 390, 'Normans'),
	array('Bayeux', 'Coast', 'Yes', 262, 772, 131, 386, 'Normans'),
	array('Mont Saint-Michel', 'Coast', 'No', 221, 799, 112, 401, 'Normans'),
	array('Maine', 'Coast', 'No', 310, 853, 141, 426, 'Neutral'),
	array('Free Cities Passage (West Coast)', 'Coast', 'No', 629, 635, 315, 318, 'Neutral'),
	array('Free Cities Passage (East Coast)', 'Coast', 'No', 808, 614, 402, 308, 'Neutral'),
	array('Oxford (Atlantic Coast)', 'Coast', 'No', 281, 613, 141, 306, 'English'),
	array('Oxford (Thames Coast)', 'Coast', 'No', 325, 636, 163, 319, 'English'),
	array('Danish Islands (fake)', 'Land', 'No', 840, 520, 420, 260, 'Neutral'),
	array('Sweden (fake)', 'Coast', 'No', 964, 360, 482, 180, 'Neutral'),
	array('Dublin (fake)', 'Coast', 'No', 242, 372, 121, 186, 'Neutral'),
	array('Oxford (fake)', 'Coast', 'No', 330, 640, 165, 320, 'Neutral'),
	array('London (fake)', 'Coast', 'No', 334, 652, 167, 326, 'Neutral'),
	array('Caen (fake)', 'Coast', 'No', 332, 788, 166, 394, 'Neutral'),
	array('Maine (fake)', 'Coast', 'No', 388, 856, 194, 428, 'Neutral'),
	array('Trondheim (fake)', 'Coast', 'No', 1030, 98, 515, 49, 'Neutral')
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
	array('Stamford Bridge','York','Yes','Yes'),
	array('Stamford Bridge','Heart of England','No','Yes'),
	array('Stamford Bridge','East Anglia','Yes','Yes'),
	array('Stamford Bridge','Southwest North Sea','Yes','No'),
	array('Stamford Bridge','Northwest North Sea','Yes','No'),
	array('York','Stamford Bridge','Yes','Yes'),
	array('York','The Dales','No','Yes'),
	array('York','Heart of England','No','Yes'),
	array('York','Hadrian s Wall','No','Yes'),
	array('York','Northwest North Sea','Yes','No'),
	array('The Dales','York','No','Yes'),
	array('The Dales','Heart of England','No','Yes'),
	array('The Dales','Hadrian s Wall','No','Yes'),
	array('The Dales','Gwynedd and Lakes District','No','Yes'),
	array('Heart of England','Stamford Bridge','No','Yes'),
	array('Heart of England','York','No','Yes'),
	array('Heart of England','The Dales','No','Yes'),
	array('Heart of England','Gwynedd and Lakes District','No','Yes'),
	array('Heart of England','Wales','No','Yes'),
	array('Heart of England','East Anglia','No','Yes'),
	array('Heart of England','London','No','Yes'),
	array('Heart of England','Oxford','No','Yes'),
	array('Hadrian s Wall','York','No','Yes'),
	array('Hadrian s Wall','The Dales','No','Yes'),
	array('Hadrian s Wall','Gwynedd and Lakes District','No','Yes'),
	array('Hadrian s Wall','Edinburgh','No','Yes'),
	array('Hadrian s Wall','Glasgow','No','Yes'),
	array('Gwynedd and Lakes District','The Dales','No','Yes'),
	array('Gwynedd and Lakes District','Heart of England','No','Yes'),
	array('Gwynedd and Lakes District','Hadrian s Wall','No','Yes'),
	array('Gwynedd and Lakes District','Wales','Yes','Yes'),
	array('Gwynedd and Lakes District','Irish Sea','Yes','No'),
	array('Wales','Heart of England','No','Yes'),
	array('Wales','Gwynedd and Lakes District','Yes','Yes'),
	array('Wales','Oxford','No','Yes'),
	array('Wales','Irish Sea','Yes','No'),
	array('Wales','Mid Atlantic Ocean','Yes','No'),
	array('Wales','Bristol Channel','Yes','No'),
	array('Wales','Oxford (Atlantic Coast)','Yes','No'),
	array('East Anglia','Stamford Bridge','Yes','Yes'),
	array('East Anglia','Heart of England','No','Yes'),
	array('East Anglia','London','Yes','Yes'),
	array('East Anglia','Thames Estuary','Yes','No'),
	array('East Anglia','Southwest North Sea','Yes','No'),
	array('London','Heart of England','No','Yes'),
	array('London','East Anglia','Yes','Yes'),
	array('London','Hastings','Yes','Yes'),
	array('London','Winchester','No','Yes'),
	array('London','Oxford','No','Yes'),
	array('London','Thames Estuary','Yes','No'),
	array('London','Oxford (Thames Coast)','Yes','No'),
	array('Hastings','London','Yes','Yes'),
	array('Hastings','Winchester','No','Yes'),
	array('Hastings','Winchester (South Coast)','Yes','No'),
	array('Hastings','North English Channel','Yes','No'),
	array('Hastings','Strait of Dover','Yes','No'),
	array('Hastings','Thames Estuary','Yes','No'),
	array('Winchester','London','No','Yes'),
	array('Winchester','Hastings','No','Yes'),
	array('Winchester','Cornwall','No','Yes'),
	array('Winchester','Oxford','No','Yes'),
	array('Winchester (South Coast)','Hastings','Yes','No'),
	array('Winchester (South Coast)','Cornwall','Yes','No'),
	array('Winchester (South Coast)','North English Channel','Yes','No'),
	array('Winchester (West Coast)','Cornwall','Yes','No'),
	array('Winchester (West Coast)','Bristol Channel','Yes','No'),
	array('Winchester (West Coast)','Oxford (Atlantic Coast)','Yes','No'),
	array('Cornwall','Winchester','No','Yes'),
	array('Cornwall','Winchester (South Coast)','Yes','No'),
	array('Cornwall','Winchester (West Coast)','Yes','No'),
	array('Cornwall','Mid Atlantic Ocean','Yes','No'),
	array('Cornwall','Bristol Channel','Yes','No'),
	array('Cornwall','North English Channel','Yes','No'),
	array('Edinburgh','Hadrian s Wall','No','Yes'),
	array('Edinburgh','Glasgow','No','Yes'),
	array('Edinburgh','Highlands and Islands','Yes','Yes'),
	array('Edinburgh','Northwest North Sea','Yes','No'),
	array('Edinburgh','Shetland and Orkneys','Yes','No'),
	array('Glasgow','Hadrian s Wall','No','Yes'),
	array('Glasgow','Edinburgh','No','Yes'),
	array('Glasgow','Highlands and Islands','Yes','Yes'),
	array('Glasgow','Firth of Clyde','Yes','No'),
	array('Glasgow','Irish Sea','Yes','No'),
	array('Highlands and Islands','Edinburgh','Yes','Yes'),
	array('Highlands and Islands','Glasgow','Yes','Yes'),
	array('Highlands and Islands','Firth of Clyde','Yes','No'),
	array('Highlands and Islands','North Atlantic Ocean','Yes','No'),
	array('Highlands and Islands','Shetland and Orkneys','Yes','No'),
	array('Oxford','Heart of England','No','Yes'),
	array('Oxford','Wales','No','Yes'),
	array('Oxford','London','No','Yes'),
	array('Oxford','Winchester','No','Yes'),
	array('Dublin','Waterford','Yes','Yes'),
	array('Dublin','Firth of Clyde','Yes','No'),
	array('Dublin','Irish Sea','Yes','No'),
	array('Dublin','North Atlantic Ocean','Yes','No'),
	array('Waterford','Dublin','Yes','Yes'),
	array('Waterford','Irish Sea','Yes','No'),
	array('Waterford','Mid Atlantic Ocean','Yes','No'),
	array('Waterford','North Atlantic Ocean','Yes','No'),
	array('Firth of Clyde','Glasgow','Yes','No'),
	array('Firth of Clyde','Highlands and Islands','Yes','No'),
	array('Firth of Clyde','Dublin','Yes','No'),
	array('Firth of Clyde','Irish Sea','Yes','No'),
	array('Firth of Clyde','North Atlantic Ocean','Yes','No'),
	array('Irish Sea','Gwynedd and Lakes District','Yes','No'),
	array('Irish Sea','Wales','Yes','No'),
	array('Irish Sea','Glasgow','Yes','No'),
	array('Irish Sea','Dublin','Yes','No'),
	array('Irish Sea','Waterford','Yes','No'),
	array('Irish Sea','Firth of Clyde','Yes','No'),
	array('Irish Sea','Mid Atlantic Ocean','Yes','No'),
	array('Mid Atlantic Ocean','Wales','Yes','No'),
	array('Mid Atlantic Ocean','Cornwall','Yes','No'),
	array('Mid Atlantic Ocean','Waterford','Yes','No'),
	array('Mid Atlantic Ocean','Irish Sea','Yes','No'),
	array('Mid Atlantic Ocean','North Atlantic Ocean','Yes','No'),
	array('Mid Atlantic Ocean','Bristol Channel','Yes','No'),
	array('Mid Atlantic Ocean','North English Channel','Yes','No'),
	array('Mid Atlantic Ocean','Channel Islands','Yes','No'),
	array('Mid Atlantic Ocean','Duchy of Brittany','Yes','No'),
	array('North Atlantic Ocean','Highlands and Islands','Yes','No'),
	array('North Atlantic Ocean','Dublin','Yes','No'),
	array('North Atlantic Ocean','Waterford','Yes','No'),
	array('North Atlantic Ocean','Firth of Clyde','Yes','No'),
	array('North Atlantic Ocean','Mid Atlantic Ocean','Yes','No'),
	array('North Atlantic Ocean','Shetland and Orkneys','Yes','No'),
	array('North Atlantic Ocean','Norwegian Sea','Yes','No'),
	array('Bristol Channel','Wales','Yes','No'),
	array('Bristol Channel','Winchester (West Coast)','Yes','No'),
	array('Bristol Channel','Cornwall','Yes','No'),
	array('Bristol Channel','Mid Atlantic Ocean','Yes','No'),
	array('Bristol Channel','Oxford (Atlantic Coast)','Yes','No'),
	array('North English Channel','Hastings','Yes','No'),
	array('North English Channel','Winchester (South Coast)','Yes','No'),
	array('North English Channel','Cornwall','Yes','No'),
	array('North English Channel','Mid Atlantic Ocean','Yes','No'),
	array('North English Channel','South English Channel','Yes','No'),
	array('North English Channel','Strait of Dover','Yes','No'),
	array('North English Channel','Channel Islands','Yes','No'),
	array('South English Channel','North English Channel','Yes','No'),
	array('South English Channel','Strait of Dover','Yes','No'),
	array('South English Channel','Channel Islands','Yes','No'),
	array('South English Channel','Rouen','Yes','No'),
	array('South English Channel','Caen','Yes','No'),
	array('South English Channel','Bayeux','Yes','No'),
	array('Strait of Dover','Hastings','Yes','No'),
	array('Strait of Dover','North English Channel','Yes','No'),
	array('Strait of Dover','South English Channel','Yes','No'),
	array('Strait of Dover','Thames Estuary','Yes','No'),
	array('Strait of Dover','Southwest North Sea','Yes','No'),
	array('Strait of Dover','County of Flanders','Yes','No'),
	array('Strait of Dover','Ecclesiastes','Yes','No'),
	array('Strait of Dover','Rouen','Yes','No'),
	array('Thames Estuary','East Anglia','Yes','No'),
	array('Thames Estuary','London','Yes','No'),
	array('Thames Estuary','Hastings','Yes','No'),
	array('Thames Estuary','Strait of Dover','Yes','No'),
	array('Thames Estuary','Southwest North Sea','Yes','No'),
	array('Southwest North Sea','Stamford Bridge','Yes','No'),
	array('Southwest North Sea','East Anglia','Yes','No'),
	array('Southwest North Sea','Strait of Dover','Yes','No'),
	array('Southwest North Sea','Thames Estuary','Yes','No'),
	array('Southwest North Sea','Northwest North Sea','Yes','No'),
	array('Southwest North Sea','Northeast North Sea','Yes','No'),
	array('Southwest North Sea','Southeast North Sea','Yes','No'),
	array('Southwest North Sea','Heligoland Bight','Yes','No'),
	array('Southwest North Sea','County of Flanders','Yes','No'),
	array('Southwest North Sea','Free Cities Passage (West Coast)','Yes','No'),
	array('Northwest North Sea','Stamford Bridge','Yes','No'),
	array('Northwest North Sea','York','Yes','No'),
	array('Northwest North Sea','Edinburgh','Yes','No'),
	array('Northwest North Sea','Southwest North Sea','Yes','No'),
	array('Northwest North Sea','Shetland and Orkneys','Yes','No'),
	array('Northwest North Sea','Northeast North Sea','Yes','No'),
	array('Northwest North Sea','Southeast North Sea','Yes','No'),
	array('Shetland and Orkneys','Edinburgh','Yes','No'),
	array('Shetland and Orkneys','Highlands and Islands','Yes','No'),
	array('Shetland and Orkneys','North Atlantic Ocean','Yes','No'),
	array('Shetland and Orkneys','Northwest North Sea','Yes','No'),
	array('Shetland and Orkneys','Norwegian Sea','Yes','No'),
	array('Shetland and Orkneys','Northeast North Sea','Yes','No'),
	array('Norwegian Sea','North Atlantic Ocean','Yes','No'),
	array('Norwegian Sea','Shetland and Orkneys','Yes','No'),
	array('Norwegian Sea','Northeast North Sea','Yes','No'),
	array('Norwegian Sea','Trondheim','Yes','No'),
	array('Northeast North Sea','Southwest North Sea','Yes','No'),
	array('Northeast North Sea','Northwest North Sea','Yes','No'),
	array('Northeast North Sea','Shetland and Orkneys','Yes','No'),
	array('Northeast North Sea','Norwegian Sea','Yes','No'),
	array('Northeast North Sea','Southeast North Sea','Yes','No'),
	array('Northeast North Sea','Skagerrak','Yes','No'),
	array('Northeast North Sea','Bergen','Yes','No'),
	array('Northeast North Sea','Trondheim','Yes','No'),
	array('Northeast North Sea','Kaupang','Yes','No'),
	array('Southeast North Sea','Southwest North Sea','Yes','No'),
	array('Southeast North Sea','Northwest North Sea','Yes','No'),
	array('Southeast North Sea','Northeast North Sea','Yes','No'),
	array('Southeast North Sea','Heligoland Bight','Yes','No'),
	array('Southeast North Sea','Skagerrak','Yes','No'),
	array('Southeast North Sea','Denmark','Yes','No'),
	array('Heligoland Bight','Southwest North Sea','Yes','No'),
	array('Heligoland Bight','Southeast North Sea','Yes','No'),
	array('Heligoland Bight','Denmark','Yes','No'),
	array('Heligoland Bight','Free Cities Passage (West Coast)','Yes','No'),
	array('Channel Islands','Mid Atlantic Ocean','Yes','No'),
	array('Channel Islands','North English Channel','Yes','No'),
	array('Channel Islands','South English Channel','Yes','No'),
	array('Channel Islands','Duchy of Brittany','Yes','No'),
	array('Channel Islands','Bayeux','Yes','No'),
	array('Channel Islands','Mont Saint-Michel','Yes','No'),
	array('Skagerrak','Northeast North Sea','Yes','No'),
	array('Skagerrak','Southeast North Sea','Yes','No'),
	array('Skagerrak','Denmark','Yes','No'),
	array('Skagerrak','Danish Islands','Yes','No'),
	array('Skagerrak','Götaland','Yes','No'),
	array('Skagerrak','Oslo','Yes','No'),
	array('Skagerrak','Kaupang','Yes','No'),
	array('Baltic Sea','Denmark','Yes','No'),
	array('Baltic Sea','Danish Islands','Yes','No'),
	array('Baltic Sea','Götaland','Yes','No'),
	array('Baltic Sea','Sweden','Yes','No'),
	array('Baltic Sea','Finnland','Yes','No'),
	array('Baltic Sea','Free Cities Passage (East Coast)','Yes','No'),
	array('Denmark','Southeast North Sea','Yes','No'),
	array('Denmark','Heligoland Bight','Yes','No'),
	array('Denmark','Skagerrak','Yes','No'),
	array('Denmark','Baltic Sea','Yes','No'),
	array('Denmark','Danish Islands','Yes','Yes'),
	array('Denmark','Free Cities Passage','No','Yes'),
	array('Denmark','Free Cities Passage (West Coast)','Yes','No'),
	array('Denmark','Free Cities Passage (East Coast)','Yes','No'),
	array('Danish Islands','Skagerrak','Yes','No'),
	array('Danish Islands','Baltic Sea','Yes','No'),
	array('Danish Islands','Denmark','Yes','Yes'),
	array('Danish Islands','Götaland','Yes','Yes'),
	array('Götaland','Skagerrak','Yes','No'),
	array('Götaland','Baltic Sea','Yes','No'),
	array('Götaland','Danish Islands','Yes','Yes'),
	array('Götaland','Sweden','Yes','Yes'),
	array('Götaland','Oslo','Yes','Yes'),
	array('Sweden','Baltic Sea','Yes','No'),
	array('Sweden','Götaland','Yes','Yes'),
	array('Sweden','Finnland','Yes','Yes'),
	array('Sweden','Oslo','No','Yes'),
	array('Sweden','Trondheim','No','Yes'),
	array('Finnland','Baltic Sea','Yes','No'),
	array('Finnland','Sweden','Yes','Yes'),
	array('Finnland','Trondheim','No','Yes'),
	array('Oslo','Skagerrak','Yes','No'),
	array('Oslo','Götaland','Yes','Yes'),
	array('Oslo','Sweden','No','Yes'),
	array('Oslo','Trondheim','No','Yes'),
	array('Oslo','Kaupang','Yes','Yes'),
	array('Bergen','Northeast North Sea','Yes','No'),
	array('Bergen','Trondheim','Yes','Yes'),
	array('Bergen','Kaupang','Yes','Yes'),
	array('Trondheim','Norwegian Sea','Yes','No'),
	array('Trondheim','Northeast North Sea','Yes','No'),
	array('Trondheim','Sweden','No','Yes'),
	array('Trondheim','Finnland','No','Yes'),
	array('Trondheim','Oslo','No','Yes'),
	array('Trondheim','Bergen','Yes','Yes'),
	array('Trondheim','Kaupang','No','Yes'),
	array('Kaupang','Northeast North Sea','Yes','No'),
	array('Kaupang','Skagerrak','Yes','No'),
	array('Kaupang','Oslo','Yes','Yes'),
	array('Kaupang','Bergen','Yes','Yes'),
	array('Kaupang','Trondheim','No','Yes'),
	array('Free Cities Passage','Denmark','No','Yes'),
	array('Free Cities Passage','County of Flanders','No','Yes'),
	array('County of Flanders','Strait of Dover','Yes','No'),
	array('County of Flanders','Southwest North Sea','Yes','No'),
	array('County of Flanders','Free Cities Passage','No','Yes'),
	array('County of Flanders','Ecclesiastes','Yes','Yes'),
	array('County of Flanders','Free Cities Passage (West Coast)','Yes','No'),
	array('Duchy of Brittany','Mid Atlantic Ocean','Yes','No'),
	array('Duchy of Brittany','Channel Islands','Yes','No'),
	array('Duchy of Brittany','Mont Saint-Michel','Yes','Yes'),
	array('Duchy of Brittany','Maine','No','Yes'),
	array('Ecclesiastes','Strait of Dover','Yes','No'),
	array('Ecclesiastes','County of Flanders','Yes','Yes'),
	array('Ecclesiastes','Rouen','Yes','Yes'),
	array('Ecclesiastes','Caen','No','Yes'),
	array('Rouen','South English Channel','Yes','No'),
	array('Rouen','Strait of Dover','Yes','No'),
	array('Rouen','Ecclesiastes','Yes','Yes'),
	array('Rouen','Caen','Yes','Yes'),
	array('Caen','South English Channel','Yes','No'),
	array('Caen','Ecclesiastes','No','Yes'),
	array('Caen','Rouen','Yes','Yes'),
	array('Caen','Bayeux','Yes','Yes'),
	array('Caen','Maine','Yes','Yes'),
	array('Bayeux','South English Channel','Yes','No'),
	array('Bayeux','Channel Islands','Yes','No'),
	array('Bayeux','Caen','Yes','Yes'),
	array('Bayeux','Mont Saint-Michel','Yes','Yes'),
	array('Bayeux','Maine','No','Yes'),
	array('Mont Saint-Michel','Channel Islands','Yes','No'),
	array('Mont Saint-Michel','Duchy of Brittany','Yes','Yes'),
	array('Mont Saint-Michel','Bayeux','Yes','Yes'),
	array('Mont Saint-Michel','Maine','No','Yes'),
	array('Maine','Duchy of Brittany','No','Yes'),
	array('Maine','Caen','Yes','Yes'),
	array('Maine','Bayeux','No','Yes'),
	array('Maine','Mont Saint-Michel','No','Yes'),
	array('Free Cities Passage (West Coast)','Southwest North Sea','Yes','No'),
	array('Free Cities Passage (West Coast)','Heligoland Bight','Yes','No'),
	array('Free Cities Passage (West Coast)','Denmark','Yes','No'),
	array('Free Cities Passage (West Coast)','County of Flanders','Yes','No'),
	array('Free Cities Passage (East Coast)','Baltic Sea','Yes','No'),
	array('Free Cities Passage (East Coast)','Denmark','Yes','No'),
	array('Oxford (Atlantic Coast)','Wales','Yes','No'),
	array('Oxford (Atlantic Coast)','Winchester (West Coast)','Yes','No'),
	array('Oxford (Atlantic Coast)','Bristol Channel','Yes','No'),
	array('Oxford (Thames Coast)','London','Yes','No')
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
