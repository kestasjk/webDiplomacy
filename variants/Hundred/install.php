<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Hundred variant for webDiplomacy

	The Hundred variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Hundred variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

require_once("variants/install.php");

InstallTerritory::$Territories=array();
$countries=$this->countries;
$territoryRawData=array(
	array('Scotland'                 , 'Coast' , 'Yes' ,150, 98, 90, 57, 'Neutral' ),
	array('The Pale'                 , 'Coast' , 'No'  , 25,195, 15,105, 'England' ),
	array('Northumbria'              , 'Coast' , 'No'  ,215,215,125,125, 'England' ),
	array('Northumbria (West Coast)' , 'Coast' , 'No'  ,195,210,105,111, 'Neutral' ),
	array('Northumbria (East Coast)' , 'Coast' , 'No'  ,235,170,135, 98, 'Neutral' ),
	array('Wales'                    , 'Coast' , 'No'  ,177,255,103,145, 'England' ),
	array('Anglia'                   , 'Coast' , 'No'  ,260,240,150,146, 'England' ),
	array('Devon'                    , 'Coast' , 'Yes' ,205,302,125,179, 'England' ),
	array('London'                   , 'Coast' , 'Yes' ,267,291,155,165, 'England' ),
	array('Brittany'                 , 'Coast' , 'Yes' ,241,428,142,247, 'Neutral' ),
	array('Normandy'                 , 'Coast' , 'Yes' ,285,384,182,216, 'England' ),
	array('Calais'                   , 'Coast' , 'Yes' ,361,340,215,192, 'England' ),
	array('Flanders'                 , 'Coast' , 'Yes' ,415,314,240,177, 'Burgundy'),
	array('Holland'                  , 'Coast' , 'Yes' ,450,275,262,152, 'Burgundy'),
	array('Friesland'                , 'Coast' , 'No'  ,485,252,285,146, 'Neutral' ),
	array('Guyenne'                  , 'Coast' , 'Yes' ,280,565,155,315, 'England' ),
	array('Poitou'                   , 'Land'  , 'No'  ,310,510,180,292, 'France'  ),
	array('Anjou'                    , 'Land'  , 'No'  ,292,442,170,252, 'France'  ),
	array('Orleanais'                , 'Land'  , 'Yes' ,360,460,220,257, 'France'  ),
	array('Paris'                    , 'Land'  , 'Yes' ,375,400,218,225, 'France'  ),
	array('Dijon'                    , 'Land'  , 'Yes' ,450,390,263,221, 'Burgundy'),
	array('Charolais'                , 'Land'  , 'No'  ,444,448,258,254, 'Burgundy'),
	array('Luxembourg'               , 'Land'  , 'Yes' ,490,340,275,190, 'Burgundy'),
	array('Lorraine'                 , 'Land'  , 'No'  ,510,407,300,232, 'Neutral' ),
	array('Alsace'                   , 'Land'  , 'No'  ,575,407,335,242, 'Neutral' ),
	array('Castile'                  , 'Coast' , 'Yes' ,150,655, 90,380, 'Neutral' ),
	array('Aragon'                   , 'Coast' , 'No'  ,290,648,170,363, 'Neutral' ),
	array('Aragon (North Coast)'     , 'Coast' , 'No'  ,230,605,131,341, 'Neutral' ),
	array('Aragon (South Coast)'     , 'Coast' , 'No'  ,330,690,187,388, 'Neutral' ),
	array('Toulouse'                 , 'Coast' , 'Yes' ,350,587,213,347, 'France'  ),
	array('Limousin'                 , 'Land'  , 'No'  ,365,527,215,303, 'France'  ),
	array('Dauphine'                 , 'Land'  , 'Yes' ,452,517,263,292, 'France'  ),
	array('Cantons'                  , 'Land'  , 'Yes' ,540,467,298,266, 'Neutral' ),
	array('Provence'                 , 'Coast' , 'No'  ,450,567,265,320, 'France'  ),
	array('Savoy'                    , 'Coast' , 'No'  ,550,527,320,298, 'Neutral' ),
	array('Minch'                    , 'Sea'   , 'No'  , 38, 95, 25, 50, 'Neutral' ),
	array('North Sea'                , 'Sea'   , 'No'  ,340, 84,193, 40, 'Neutral' ),
	array('Irish Sea'                , 'Sea'   , 'No'  ,110,223, 60,130, 'Neutral' ),
	array('The Wash'                 , 'Sea'   , 'No'  ,380,207,225,118, 'Neutral' ),
	array('Atlantic Sea'             , 'Sea'   , 'No'  , 35,543, 20,285, 'Neutral' ),
	array('Bristol Channel'          , 'Sea'   , 'No'  , 90,353, 50,210, 'Neutral' ),
	array('English Channel'          , 'Sea'   , 'No'  ,195,355,110,213, 'Neutral' ),
	array('Strait of Dover'          , 'Sea'   , 'No'  ,370,275,205,170, 'Neutral' ),
	array('Biscay'                   , 'Sea'   , 'No'  ,130,490, 85,285, 'Neutral' ),
	array('Mediterranean'            , 'Sea'   , 'No'  ,460,680,260,375, 'Neutral' )
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
	array('Wales'                   , 'Devon'          , 'Yes', 'Yes' ),
	array('Devon'                   , 'London'         , 'Yes', 'Yes' ),
	array('London'                  , 'Anglia'         , 'Yes', 'Yes' ),
	array('Brittany'                , 'Normandy'       , 'Yes', 'Yes' ),
	array('Normandy'                , 'Calais'         , 'Yes', 'Yes' ),
	array('Calais'                  , 'Flanders'       , 'Yes', 'Yes' ),
	array('Flanders'                , 'Holland'        , 'Yes', 'Yes' ),
	array('Holland'                 , 'Friesland'      , 'Yes', 'Yes' ),
	array('Brittany'                , 'Guyenne'        , 'Yes', 'Yes' ),
	array('Toulouse'                , 'Provence'       , 'Yes', 'Yes' ),
	array('Savoy'                   , 'Provence'       , 'Yes', 'Yes' ),
	array('London'                  , 'Calais'         , 'Yes', 'Yes' ),
	array('Devon'                   , 'Northumbria'    , 'No' , 'Yes' ),
	array('Devon'                   , 'Anglia'         , 'No' , 'Yes' ),
	array('Orleanais'               , 'Brittany'       , 'No' , 'Yes' ),
	array('Orleanais'               , 'Normandy'       , 'No' , 'Yes' ),
	array('Orleanais'               , 'Paris'          , 'No' , 'Yes' ),
	array('Orleanais'               , 'Dauphine'       , 'No' , 'Yes' ),
	array('Orleanais'               , 'Limousin'       , 'No' , 'Yes' ),
	array('Orleanais'               , 'Poitou'         , 'No' , 'Yes' ),
	array('Anjou'                   , 'Brittany'       , 'No' , 'Yes' ),
	array('Anjou'                   , 'Normandy'       , 'No' , 'Yes' ),
	array('Anjou'                   , 'Orleanais'      , 'No' , 'Yes' ),
	array('Charolais'               , 'Paris'          , 'No' , 'Yes' ),
	array('Charolais'               , 'Dijon'          , 'No' , 'Yes' ),
	array('Charolais'               , 'Dauphine'       , 'No' , 'Yes' ),
	array('Dijon'                   , 'Calais'         , 'No' , 'Yes' ),
	array('Dijon'                   , 'Flanders'       , 'No' , 'Yes' ),
	array('Dijon'                   , 'Luxembourg'     , 'No' , 'Yes' ),
	array('Dijon'                   , 'Lorraine'       , 'No' , 'Yes' ),
	array('Dijon'                   , 'Cantons'        , 'No' , 'Yes' ),
	array('Dijon'                   , 'Dauphine'       , 'No' , 'Yes' ),
	array('Dijon'                   , 'Paris'          , 'No' , 'Yes' ),
	array('Luxembourg'              , 'Friesland'      , 'No' , 'Yes' ),
	array('Luxembourg'              , 'Holland'        , 'No' , 'Yes' ),
	array('Luxembourg'              , 'Flanders'       , 'No' , 'Yes' ),
	array('Luxembourg'              , 'Lorraine'       , 'No' , 'Yes' ),
	array('Poitou'                  , 'Brittany'       , 'No' , 'Yes' ),
	array('Poitou'                  , 'Guyenne'        , 'No' , 'Yes' ),
	array('Poitou'                  , 'Toulouse'       , 'No' , 'Yes' ),
	array('Poitou'                  , 'Limousin'       , 'No' , 'Yes' ),
	array('Limousin'                , 'Toulouse'       , 'No' , 'Yes' ),
	array('Limousin'                , 'Provence'       , 'No' , 'Yes' ),
	array('Limousin'                , 'Dauphine'       , 'No' , 'Yes' ),
	array('Dauphine'                , 'Provence'       , 'No' , 'Yes' ),
	array('Dauphine'                , 'Savoy'          , 'No' , 'Yes' ),
	array('Dauphine'                , 'Cantons'        , 'No' , 'Yes' ),
	array('Dauphine'                , 'Paris'          , 'No' , 'Yes' ),
	array('Cantons'                 , 'Lorraine'       , 'No' , 'Yes' ),
	array('Cantons'                 , 'Savoy'          , 'No' , 'Yes' ),
	array('Paris'                   , 'Normandy'       , 'No' , 'Yes' ),
	array('Paris'                   , 'Calais'         , 'No' , 'Yes' ),
	array('Guyenne'                 , 'Toulouse'       , 'No' , 'Yes' ),
	array('Scotland'                , 'Irish Sea'      , 'Yes', 'No'  ),
	array('Minch'                   , 'North Sea'      , 'Yes', 'No'  ),
	array('Minch'                   , 'Scotland'       , 'Yes', 'No'  ),
	array('Minch'                   , 'Irish Sea'      , 'Yes', 'No'  ),
	array('North Sea'               , 'Scotland'       , 'Yes', 'No'  ),
	array('North Sea'               , 'Anglia'         , 'Yes', 'No'  ),
	array('North Sea'               , 'The Wash'       , 'Yes', 'No'  ),
	array('Bristol Channel'         , 'Irish Sea'      , 'Yes', 'No'  ),
	array('Bristol Channel'         , 'Wales'          , 'Yes', 'No'  ),
	array('Bristol Channel'         , 'Devon'          , 'Yes', 'No'  ),
	array('Bristol Channel'         , 'English Channel', 'Yes', 'No'  ),
	array('Bristol Channel'         , 'Brittany'       , 'Yes', 'No'  ),
	array('Bristol Channel'         , 'Biscay'         , 'Yes', 'No'  ),
	array('Irish Sea'               , 'Wales'          , 'Yes', 'No'  ),
	array('English Channel'         , 'Devon'          , 'Yes', 'No'  ),
	array('English Channel'         , 'London'         , 'Yes', 'No'  ),
	array('English Channel'         , 'Strait of Dover', 'Yes', 'No'  ),
	array('English Channel'         , 'Normandy'       , 'Yes', 'No'  ),
	array('English Channel'         , 'Brittany'       , 'Yes', 'No'  ),
	array('The Wash'                , 'Anglia'         , 'Yes', 'No'  ),
	array('The Wash'                , 'Strait of Dover', 'Yes', 'No'  ),
	array('The Wash'                , 'Holland'        , 'Yes', 'No'  ),
	array('The Wash'                , 'Friesland'      , 'Yes', 'No'  ),
	array('Strait of Dover'         , 'Anglia'         , 'Yes', 'No'  ),
	array('Strait of Dover'         , 'London'         , 'Yes', 'No'  ),
	array('Strait of Dover'         , 'Normandy'       , 'Yes', 'No'  ),
	array('Strait of Dover'         , 'Calais'         , 'Yes', 'No'  ),
	array('Strait of Dover'         , 'Flanders'       , 'Yes', 'No'  ),
	array('Strait of Dover'         , 'Holland'        , 'Yes', 'No'  ),
	array('Biscay'                  , 'Brittany'       , 'Yes', 'No'  ),
	array('Biscay'                  , 'Guyenne'        , 'Yes', 'No'  ),
	array('Biscay'                  , 'Castile'        , 'Yes', 'No'  ),
	array('Mediterranean'           , 'Castile'        , 'Yes', 'No'  ),
	array('Mediterranean'           , 'Toulouse'       , 'Yes', 'No'  ),
	array('Mediterranean'           , 'Provence'       , 'Yes', 'No'  ),
	array('Mediterranean'           , 'Savoy'          , 'Yes', 'No'  ),
	array('The Pale'                , 'Minch'          , 'Yes', 'No'  ),
	array('The Pale'                , 'Irish Sea'      , 'Yes', 'No'  ),
	array('The Pale'                , 'Atlantic Sea'   , 'Yes', 'No'  ),
	array('Atlantic Sea'            , 'Irish Sea'      , 'Yes', 'No'  ),
	array('Atlantic Sea'            , 'Bristol Channel', 'Yes', 'No'  ),
	array('Atlantic Sea'            , 'Biscay'         , 'Yes', 'No'  ),
	array('Atlantic Sea'            , 'Castile'        , 'Yes', 'No'  ),
	array('Atlantic Sea'            , 'Mediterranean'  , 'Yes', 'No'  ),
	array('Alsace'                  , 'Lorraine'       , 'No' , 'Yes' ),
	array('Alsace'                  , 'Cantons'        , 'No' , 'Yes' ),
	array('Northumbria'             , 'Scotland'       , 'No' , 'Yes' ),
	array('Northumbria'             , 'Wales'          , 'No' , 'Yes' ),
	array('Northumbria'             , 'Anglia'         , 'No' , 'Yes' ),
	array('Northumbria (West Coast)', 'Scotland'       , 'Yes', 'No'  ),
	array('Northumbria (West Coast)', 'Wales'          , 'Yes', 'No'  ),
	array('Northumbria (West Coast)', 'Irish Sea'      , 'Yes', 'No'  ),
	array('Northumbria (East Coast)', 'Scotland'       , 'Yes', 'No'  ),
	array('Northumbria (East Coast)', 'Anglia'         , 'Yes', 'No'  ),
	array('Northumbria (East Coast)', 'North Sea'      , 'Yes', 'No'  ),
	array('Aragon'                  , 'Guyenne'        , 'No' , 'Yes' ),
	array('Aragon'                  , 'Castile'        , 'No' , 'Yes' ),
	array('Aragon'                  , 'Toulouse'       , 'No' , 'Yes' ),
	array('Aragon (North Coast)'    , 'Guyenne'        , 'Yes', 'No'  ),
	array('Aragon (North Coast)'    , 'Castile'        , 'Yes', 'No'  ),
	array('Aragon (North Coast)'    , 'Biscay'         , 'Yes', 'No'  ),
	array('Aragon (South Coast)'    , 'Castile'        , 'Yes', 'No'  ),
	array('Aragon (South Coast)'    , 'Toulouse'       , 'Yes', 'No'  ),
	array('Aragon (South Coast)'    , 'Mediterranean'  , 'Yes', 'No'  )
);

foreach($bordersRawData as $borderRawRow)
{
	list($from, $to, $fleets, $armies)=$borderRawRow;
	InstallTerritory::$Territories[$to]  ->addBorder(InstallTerritory::$Territories[$from],$fleets,$armies);
	InstallTerritory::$Territories[$from]->addBorder(InstallTerritory::$Territories[$to]  ,$fleets,$armies);
}
unset($bordersRawData);

InstallTerritory::runSQL($this->mapID);
InstallCache::terrJSON($this->territoriesJSONFile(),$this->mapID);

?>