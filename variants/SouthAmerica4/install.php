<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the South America 4-Player variant for webDiplomacy

	The South America 4-Player variant for webDiplomacy" is free software:
	you can redistribute it and/or modify it under the terms of the GNU Affero
	General Public License as published by the Free Software Foundation, either
	version 3 of the License, or (at your option) any later version.

	The South America 4-Player variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

require_once("variants/install.php");

InstallTerritory::$Territories=array();
$countries=$this->countries;
$territoryRawData=array(
	array('Barranquilla'         , 'Coast' , 'Yes' , 210,  80,110, 35 , 'Colombia' ),
	array('Medellin'             , 'Coast' , 'Yes' , 195, 150, 90, 83 , 'Colombia' ),
	array('Bogota'               , 'Land'  , 'Yes' , 270, 160,125, 75 , 'Colombia' ),
	array('Cordillera Oriental'  , 'Land'  , 'No'  , 250, 245,125,120 , 'Colombia' ),
	array('Amazon Basin'         , 'Land'  , 'No'  , 385, 290,171,150 , 'Brazil'   ),
	array('Roraima'              , 'Land'  , 'No'  , 550, 275,223,114 , 'Brazil'   ),
	array('Belem'                , 'Coast' , 'Yes' , 670, 280,298,115 , 'Brazil'   ),
	array('Manaus'               , 'Land'  , 'Yes' , 485, 350,230,177 , 'Brazil'   ),
	array('Mato Grosso'          , 'Land'  , 'No'  , 550, 480,267,205 , 'Brazil'   ),
	array('Brasilia'             , 'Land'  , 'Yes' , 645, 550,315,230 , 'Brazil'   ),
	array('Pernambuco'           , 'Coast' , 'No'  , 800, 400,390,200 , 'Brazil'   ),
	array('Pantanal'             , 'Land'  , 'No'  , 610, 640,300,293 , 'Brazil'   ),
	array('Rio de Janeiro'       , 'Coast' , 'Yes' , 750, 645,345,310 , 'Brazil'   ),
	array('Rio Grande do Sul'    , 'Coast' , 'No'  , 615, 770,290,370 , 'Brazil'   ),
	array('Antofagasta'          , 'Coast' , 'Yes' , 315, 685,153,310 , 'Chile'    ),
	array('Santiago'             , 'Coast' , 'Yes' , 315, 860,144,369 , 'Chile'    ),
	array('Concepcion'           , 'Coast' , 'Yes' , 300, 960,142,470 , 'Chile'    ),
	array('Tierra del Fuego'     , 'Coast' , 'No'  , 340,1235,168,573 , 'Chile'    ),
	array('Gran Chaco'           , 'Land'  , 'No'  , 375, 750,180,350 , 'Argentina'),
	array('Pampas'               , 'Land'  , 'No'  , 375, 865,182,414 , 'Argentina'),
	array('Patagonia'            , 'Coast' , 'No'  , 385,1040,180,500 , 'Argentina'),
	array('Santa Fe'             , 'Land'  , 'Yes' , 470, 810,220,370 , 'Argentina'),
	array('Buenos Aires'         , 'Coast' , 'Yes' , 480, 905,240,431 , 'Argentina'),
	array('Mar del Plata'        , 'Coast' , 'Yes' , 460, 955,212,465 , 'Argentina'),
	array('Panama'               , 'Coast' , 'No'  , 100,  90, 50, 50 , 'Neutral'  ),
	array('Maracaibo'            , 'Coast' , 'Yes' , 310,  95,145, 35 , 'Neutral'  ),
	array('Caracas'              , 'Coast' , 'Yes' , 415, 100,195, 40 , 'Neutral'  ),
	array('Orinoco Springs'      , 'Land'  , 'No'  , 370, 163,177, 95 , 'Neutral'  ),
	array('Guyana'               , 'Coast' , 'Yes' , 560, 170,265, 75 , 'Neutral'  ),
	array('Ecuador'              , 'Coast' , 'Yes' , 170, 280, 80,130 , 'Neutral'  ),
	array('Montana'              , 'Coast' , 'No'  , 195, 360, 97,153 , 'Neutral'  ),
	array('Lima'                 , 'Coast' , 'Yes' , 215, 495, 97,227 , 'Neutral'  ),
	array('Arequipa'             , 'Coast' , 'Yes' , 280, 530,127,242 , 'Neutral'  ),
	array('La Paz'               , 'Land'  , 'Yes' , 370, 535,175,255 , 'Neutral'  ),
	array('Yungas'               , 'Land'  , 'No'  , 470, 570,210,260 , 'Neutral'  ),
	array('Paraguay'             , 'Land'  , 'Yes' , 540, 690,230,305 , 'Neutral'  ),
	array('Uruguay'              , 'Coast' , 'Yes' , 555, 860,252,399 , 'Neutral'  ),
	array('Islas Juan Fernandez' , 'Coast' , 'Yes' , 127, 905, 60,425 , 'Neutral'  ),
	array('Islas Malvinas'       , 'Coast' , 'Yes' , 485,1210,229,564 , 'Neutral'  ),
	array('Caribbean Sea'        , 'Sea'   , 'No'  , 180,  25,168, 15 , 'Neutral'  ),
	array('Mid Atlantic Ocean'   , 'Sea'   , 'No'  , 620,  70,320, 40 , 'Neutral'  ),
	array('Brazilian Sea'        , 'Sea'   , 'No'  , 825, 260,434,140 , 'Neutral'  ),
	array('Southwest Atlantic'   , 'Sea'   , 'No'  , 765, 900,354,427 , 'Neutral'  ),
	array('Coast of Argentina'   , 'Sea'   , 'No'  , 490,1080,235,490 , 'Neutral'  ),
	array('Scotia Sea'           , 'Sea'   , 'No'  , 130,1130, 65,525 , 'Neutral'  ),
	array('Southeast Pacific'    , 'Sea'   , 'No'  ,  50, 730, 62,320 , 'Neutral'  ),
	array('Galapagos Sea'        , 'Sea'   , 'No'  ,  50, 400, 30,175 , 'Neutral'  ),
	array('Golfo de Panama'      , 'Sea'   , 'No'  , 120, 170, 60, 75 , 'Neutral'  )
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
	array( 'Barranquilla'        , 'Caribbean Sea'        , 'Yes' , 'No'  ),
	array( 'Barranquilla'        , 'Maracaibo'            , 'Yes' , 'Yes' ),
	array( 'Barranquilla'        , 'Bogota'               , 'No'  , 'Yes' ),
	array( 'Barranquilla'        , 'Medellin'             , 'No'  , 'Yes' ),
	array( 'Barranquilla'        , 'Panama'               , 'Yes' , 'Yes' ),
	array( 'Medellin'            , 'Bogota'               , 'No'  , 'Yes' ),
	array( 'Medellin'            , 'Cordillera Oriental'  , 'No'  , 'Yes' ),
	array( 'Medellin'			, 'Ecuador'				  , 'Yes' , 'Yes' ),
	array( 'Medellin'            , 'Galapagos Sea'        , 'Yes' , 'No'  ),
	array( 'Medellin'            , 'Golfo de Panama'      , 'Yes' , 'No'  ),
	array( 'Medellin'            , 'Panama'               , 'Yes' , 'Yes' ),
	array( 'Bogota'              , 'Maracaibo'            , 'No'  , 'Yes' ),
	array( 'Bogota'              , 'Orinoco Springs'      , 'No'  , 'Yes' ),
	array( 'Bogota'              , 'Cordillera Oriental'  , 'No'  , 'Yes' ),
	array( 'Cordillera Oriental' , 'Orinoco Springs'      , 'No'  , 'Yes' ),
	array( 'Cordillera Oriental' , 'Amazon Basin'         , 'No'  , 'Yes' ),
	array( 'Cordillera Oriental' , 'Montana'              , 'No'  , 'Yes' ),
	array( 'Cordillera Oriental' , 'Ecuador'			  , 'No'  , 'Yes' ),
	array( 'Amazon Basin'        , 'Orinoco Springs'      , 'No'  , 'Yes' ),
	array( 'Amazon Basin'        , 'Roraima'              , 'No'  , 'Yes' ),
	array( 'Amazon Basin'        , 'Manaus'               , 'No'  , 'Yes' ),
	array( 'Amazon Basin'        , 'La Paz'               , 'No'  , 'Yes' ),
	array( 'Amazon Basin'        , 'Arequipa'             , 'No'  , 'Yes' ),
	array( 'Amazon Basin'        , 'Montana'              , 'No'  , 'Yes' ),
	array( 'Roraima'             , 'Orinoco Springs'      , 'No'  , 'Yes' ),
	array( 'Roraima'             , 'Caracas'              , 'No'  , 'Yes' ),
	array( 'Roraima'             , 'Guyana'               , 'No'  , 'Yes' ),
	array( 'Roraima'             , 'Belem'                , 'No'  , 'Yes' ),
	array( 'Roraima'             , 'Brasilia'             , 'No'  , 'Yes' ),
	array( 'Roraima'             , 'Mato Grosso'          , 'No'  , 'Yes' ),
	array( 'Roraima'             , 'Manaus'               , 'No'  , 'Yes' ),
	array( 'Belem'               , 'Guyana'               , 'Yes' , 'Yes' ),
	array( 'Belem'               , 'Mid Atlantic Ocean'   , 'Yes' , 'No'  ),
	array( 'Belem'               , 'Brazilian Sea'        , 'Yes' , 'No'  ),
	array( 'Belem'               , 'Pernambuco'           , 'Yes' , 'Yes' ),
	array( 'Belem'               , 'Brasilia'             , 'No'  , 'Yes' ),
	array( 'Manaus'              , 'Mato Grosso'          , 'No'  , 'Yes' ),
	array( 'Manaus'              , 'La Paz'               , 'No'  , 'Yes' ),
	array( 'Mato Grosso'         , 'Brasilia'             , 'No'  , 'Yes' ),
	array( 'Mato Grosso'         , 'Yungas'               , 'No'  , 'Yes' ),
	array( 'Mato Grosso'         , 'La Paz'               , 'No'  , 'Yes' ),
	array( 'Brasilia'            , 'Pernambuco'           , 'No'  , 'Yes' ),
	array( 'Brasilia'            , 'Rio de Janeiro'       , 'No'  , 'Yes' ),
	array( 'Brasilia'            , 'Pantanal'             , 'No'  , 'Yes' ),
	array( 'Brasilia'            , 'Paraguay'             , 'No'  , 'Yes' ),
	array( 'Brasilia'            , 'Yungas'               , 'No'  , 'Yes' ),
	array( 'Pernambuco'          , 'Brazilian Sea'        , 'Yes' , 'No'  ),
	array( 'Pernambuco'          , 'Rio de Janeiro'       , 'Yes' , 'Yes' ),
	array( 'Pantanal'            , 'Rio de Janeiro'       , 'No'  , 'Yes' ),
	array( 'Pantanal'            , 'Rio Grande do Sul'    , 'No'  , 'Yes' ),
	array( 'Pantanal'            , 'Paraguay'             , 'No'  , 'Yes' ),
	array( 'Rio de Janeiro'      , 'Brazilian Sea'        , 'Yes' , 'No'  ),
	array( 'Rio de Janeiro'      , 'Southwest Atlantic'   , 'Yes' , 'No'  ),
	array( 'Rio de Janeiro'      , 'Rio Grande do Sul'    , 'Yes' , 'Yes' ),
	array( 'Rio Grande do Sul'   , 'Southwest Atlantic'   , 'Yes' , 'No'  ),
	array( 'Rio Grande do Sul'   , 'Uruguay'              , 'Yes' , 'Yes' ),
	array( 'Rio Grande do Sul'   , 'Santa Fe'             , 'No'  , 'Yes' ),
	array( 'Rio Grande do Sul'   , 'Paraguay'             , 'No'  , 'Yes' ),
	array( 'Antofagasta'         , 'Arequipa'             , 'Yes' , 'Yes' ),
	array( 'Antofagasta'         , 'La Paz'               , 'No'  , 'Yes' ),
	array( 'Antofagasta'         , 'Gran Chaco'           , 'No'  , 'Yes' ),
	array( 'Antofagasta'         , 'Santiago'             , 'Yes' , 'Yes' ),
	array( 'Antofagasta'         , 'Southeast Pacific'    , 'Yes' , 'No'  ),
	array( 'Santiago'            , 'Gran Chaco'           , 'No'  , 'Yes' ),
	array( 'Santiago'            , 'Pampas'               , 'No'  , 'Yes' ),
	array( 'Santiago'            , 'Patagonia'            , 'No'  , 'Yes' ),
	array( 'Santiago'            , 'Concepcion'           , 'Yes' , 'Yes' ),
	array( 'Santiago'            , 'Southeast Pacific'    , 'Yes' , 'No'  ),
	array( 'Concepcion'			, 'Patagonia'            , 'No'  , 'Yes' ),
	array( 'Concepcion'          , 'Tierra del Fuego'     , 'Yes' , 'Yes' ),
	array( 'Concepcion'          , 'Scotia Sea'           , 'Yes' , 'No'  ),
	array( 'Concepcion'          , 'Southeast Pacific'    , 'Yes' , 'No'  ),
	array( 'Tierra del Fuego'    , 'Patagonia'            , 'Yes' , 'Yes' ),
	array( 'Tierra del Fuego'    , 'Coast of Argentina'   , 'Yes' , 'No'  ),
	array( 'Tierra del Fuego'    , 'Southwest Atlantic'   , 'Yes' , 'No'  ),
	array( 'Tierra del Fuego'    , 'Scotia Sea'           , 'Yes' , 'No'  ),
	array( 'Gran Chaco'          , 'La Paz'               , 'No'  , 'Yes' ),
	array( 'Gran Chaco'          , 'Paraguay'             , 'No'  , 'Yes' ),
	array( 'Gran Chaco'          , 'Santa Fe'             , 'No'  , 'Yes' ),
	array( 'Gran Chaco'          , 'Pampas'               , 'No'  , 'Yes' ),
	array( 'Pampas'              , 'Santa Fe'             , 'No'  , 'Yes' ),
	array( 'Pampas'              , 'Buenos Aires'         , 'No'  , 'Yes' ),
	array( 'Pampas'              , 'Mar del Plata'        , 'No'  , 'Yes' ),
	array( 'Pampas'              , 'Patagonia'            , 'No'  , 'Yes' ),
	array( 'Patagonia'           , 'Mar del Plata'        , 'Yes' , 'Yes' ),
	array( 'Patagonia'           , 'Coast of Argentina'   , 'Yes' , 'No'  ),
	array( 'Santa Fe'            , 'Paraguay'             , 'No'  , 'Yes' ),
	array( 'Santa Fe'            , 'Uruguay'              , 'No'  , 'Yes' ),
	array( 'Santa Fe'            , 'Buenos Aires'         , 'No'  , 'Yes' ),
	array( 'Mar del Plata'       , 'Buenos Aires'         , 'Yes' , 'Yes' ),
	array( 'Mar del Plata'       , 'Coast of Argentina'   , 'Yes' , 'No'  ),
	array( 'Buenos Aires'        , 'Uruguay'              , 'Yes' , 'Yes' ),
	array( 'Buenos Aires'        , 'Coast of Argentina'   , 'Yes' , 'No'  ),
	array( 'Ecuador'             , 'Galapagos Sea'        , 'Yes' , 'No'  ),
	array( 'Montana'             , 'Ecuador'              , 'Yes' , 'Yes' ),
	array( 'Montana'             , 'Arequipa'             , 'No'  , 'Yes' ),
	array( 'Montana'             , 'Lima'                 , 'Yes' , 'Yes' ),
	array( 'Montana'             , 'Galapagos Sea'        , 'Yes' , 'No'  ),
	array( 'Lima'                , 'Arequipa'             , 'Yes' , 'Yes' ),
	array( 'Lima'                , 'Southeast Pacific'    , 'Yes' , 'No'  ),
	array( 'Lima'                , 'Galapagos Sea'        , 'Yes' , 'No'  ),
	array( 'Arequipa'            , 'La Paz'               , 'No'  , 'Yes' ),
	array( 'Arequipa'            , 'Southeast Pacific'    , 'Yes' , 'No'  ),
	array( 'La Paz'              , 'Yungas'               , 'No'  , 'Yes' ),
	array( 'La Paz'              , 'Paraguay'             , 'No'  , 'Yes' ),
	array( 'Yungas'              , 'Paraguay'             , 'No'  , 'Yes' ),
	array( 'Uruguay'             , 'Southwest Atlantic'   , 'Yes' , 'No'  ),
	array( 'Uruguay'             , 'Coast of Argentina'   , 'Yes' , 'No'  ),
	array( 'Panama'              , 'Caribbean Sea'        , 'Yes' , 'No'  ),
	array( 'Panama'              , 'Golfo de Panama'      , 'Yes' , 'No'  ),
	array( 'Panama'              , 'Galapagos Sea'        , 'Yes' , 'No'  ),
	array( 'Maracaibo'           , 'Caribbean Sea'        , 'Yes' , 'No'  ),
	array( 'Maracaibo'           , 'Caracas'              , 'Yes' , 'Yes' ),
	array( 'Maracaibo'           , 'Orinoco Springs'      , 'No'  , 'Yes' ),
	array( 'Caracas'             , 'Caribbean Sea'        , 'Yes' , 'No'  ),
	array( 'Caracas'             , 'Mid Atlantic Ocean'   , 'Yes' , 'No'  ),
	array( 'Caracas'             , 'Guyana'               , 'Yes' , 'Yes' ),
	array( 'Caracas'             , 'Orinoco Springs'      , 'No'  , 'Yes' ),
	array( 'Guyana'              , 'Mid Atlantic Ocean'   , 'Yes' , 'No'  ),
	array( 'Caribbean Sea'       , 'Mid Atlantic Ocean'   , 'Yes' , 'No'  ),
	array( 'Mid Atlantic Ocean'  , 'Brazilian Sea'        , 'Yes' , 'No'  ),
	array( 'Brazilian Sea'       , 'Southwest Atlantic'   , 'Yes' , 'No'  ),
	array( 'Southwest Atlantic'  , 'Coast of Argentina'   , 'Yes' , 'No'  ),
	array( 'Southwest Atlantic'  , 'Islas Malvinas'       , 'Yes' , 'No'  ),
	array( 'Southwest Atlantic'  , 'Scotia Sea'           , 'Yes' , 'No'  ),
	array( 'Coast of Argentina'  , 'Islas Malvinas'       , 'Yes' , 'No'  ),
	array( 'Scotia Sea'          , 'Southeast Pacific'    , 'Yes' , 'No'  ),
	array( 'Scotia Sea'          , 'Islas Juan Fernandez' , 'Yes' , 'No'  ),
	array( 'Southeast Pacific'   , 'Islas Juan Fernandez' , 'Yes' , 'No'  ),
	array( 'Southeast Pacific'   , 'Galapagos Sea'        , 'Yes' , 'No'  ),
	array( 'Galapagos Sea'       , 'Golfo de Panama'      , 'Yes' , 'No'  )
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