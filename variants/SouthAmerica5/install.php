<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the South America 5-Player variant for webDiplomacy

	The South America 5-Player variant for webDiplomacy" is free software:
	you can redistribute it and/or modify it under the terms of the GNU Affero
	General Public License as published by the Free Software Foundation, either 
	version 3 of the License, or (at your option) any later version.

	The South America 5-Player variant for webDiplomacy is distributed in the hope
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
	array('Barranquilla'            , 'Coast' , 'Yes' , 205,  84, 99, 35 , 'Colombia' ),
	array('Medellin'                , 'Coast' , 'Yes' , 190, 155, 85, 68 , 'Colombia' ),
	array('Bogota'                  , 'Land'  , 'Yes' , 270, 170,140, 94 , 'Colombia' ),
	array('Valle Magdalena'         , 'Coast' , 'No'  , 165, 210, 90, 95 , 'Colombia' ),
	array('Cordillera Oriental'     , 'Land'  , 'No'  , 250, 245,125,130 , 'Colombia' ),
	array('Chiclayo'                , 'Coast' , 'Yes' , 165, 388, 74,176 , 'Peru'     ),
	array('Montana'                 , 'Land'  , 'No'  , 230, 323,101,163 , 'Peru'     ),
	array('Lima'                    , 'Coast' , 'Yes' , 215, 495,100,225 , 'Peru'     ),
	array('Arequipa'                , 'Coast' , 'Yes' , 280, 530,127,240 , 'Peru'     ),
	array('Acre'                    , 'Land'  , 'No'  , 310, 360,144,173 , 'Brazil'   ),
	array('Amazon Basin'            , 'Land'  , 'No'  , 405, 287,183,156 , 'Brazil'   ),
	array('Roraima'                 , 'Land'  , 'No'  , 550, 275,225,116 , 'Brazil'   ),
	array('Belem'                   , 'Coast' , 'Yes' , 690, 297,350,155 , 'Brazil'   ),
	array('Manaus'                  , 'Land'  , 'Yes' , 485, 340,260,175 , 'Brazil'   ),
	array('Mato Grosso'             , 'Land'  , 'No'  , 510, 447,239,222 , 'Brazil'   ),
	array('Goias'                   , 'Land'  , 'No'  , 645, 547,310,247 , 'Brazil'   ),
	array('Rio de Janeiro'          , 'Coast' , 'Yes' , 750, 643,348,310 , 'Brazil'   ),
	array('Rio Grande do Sul'       , 'Coast' , 'No'  , 610, 768,286,373 , 'Brazil'   ),
	array('Desierto Atacama'        , 'Coast' , 'No'  , 320, 622,148,295 , 'Chile'    ),
	array('Antofagasta'             , 'Coast' , 'Yes' , 330, 685,160,331 , 'Chile'    ),
	array('Santiago'                , 'Coast' , 'Yes' , 315, 860,145,385 , 'Chile'    ),
	array('Concepcion'              , 'Coast' , 'Yes' , 310, 960,142,470 , 'Chile'    ),
	array('Tierra del Fuego'        , 'Coast' , 'No'  , 350,1233,168,573 , 'Chile'    ),
	array('Gran Chaco'              , 'Land'  , 'No'  , 375, 750,177,362 , 'Argentina'),
	array('Pampas'                  , 'Land'  , 'No'  , 375, 863,182,413 , 'Argentina'),
	array('Patagonia'               , 'Coast' , 'No'  , 385,1037,178,499 , 'Argentina'),
	array('Mesopotamia'             , 'Land'  , 'No'  , 485, 740,227,353 , 'Argentina'),
	array('Santa Fe'                , 'Land'  , 'Yes' , 470, 815,209,372 , 'Argentina'),
	array('Buenos Aires'            , 'Coast' , 'Yes' , 477, 892,242,432 , 'Argentina'),
	array('Mar del Plata'           , 'Coast' , 'Yes' , 490, 950,210,463 , 'Argentina'),
	array('Costa Rica'              , 'Coast' , 'Yes' ,  40,  37, 25, 35 , 'Neutral'  ),
	array('Costa Rica (North Coast)', 'Coast' , 'No'  ,  50,  35, 33, 30 , 'Neutral'  ),
	array('Costa Rica (South Coast)', 'Coast' , 'No'  ,  30,  80, 15, 40 , 'Neutral'  ),
	array('Panama'                  , 'Coast' , 'No'  , 100,  90, 50, 50 , 'Neutral'  ),
	array('Trujillo'                , 'Coast' , 'No'  , 320,  93,145, 29 , 'Neutral'  ),
	array('Caracas'                 , 'Coast' , 'Yes' , 415, 100,195, 44 , 'Neutral'  ),
	array('Orinoco Springs'         , 'Land'  , 'No'  , 380, 195,177, 91 , 'Neutral'  ),
	array('Guyana'                  , 'Coast' , 'Yes' , 560, 170,265, 74 , 'Neutral'  ),
	array('Ecuador'                 , 'Coast' , 'Yes' , 165, 275, 59,124 , 'Neutral'  ),
	array('La Paz'                  , 'Land'  , 'Yes' , 370, 535,175,238 , 'Neutral'  ),
	array('Yungas'                  , 'Land'  , 'No'  , 460, 565,219,270 , 'Neutral'  ),
	array('Potosi'                  , 'Land'  , 'Yes' , 390, 635,172,303 , 'Neutral'  ),
	array('Paraguay'                , 'Land'  , 'Yes' , 505, 685,230,311 , 'Neutral'  ),
	array('Uruguay'                 , 'Coast' , 'Yes' , 555, 858,257,400 , 'Neutral'  ),
	array('Islas Juan Fernandez'    , 'Coast' , 'Yes' , 150, 905, 60,425 , 'Neutral'  ),
	array('Caribbean Sea'           , 'Sea'   , 'No'  , 180,  25,168, 15 , 'Neutral'  ),
	array('Mid Atlantic Ocean'      , 'Sea'   , 'No'  , 620,  67,335, 55 , 'Neutral'  ),
	array('Brazilian Sea'           , 'Sea'   , 'No'  , 860, 278,420,145 , 'Neutral'  ),
	array('Southwest Atlantic'      , 'Sea'   , 'No'  , 765, 900,354,427 , 'Neutral'  ),
	array('Coast of Argentina'      , 'Sea'   , 'No'  , 490,1080,242,481 , 'Neutral'  ),
	array('Scotia Sea'              , 'Sea'   , 'No'  , 130,1130, 82,515 , 'Neutral'  ),
	array('Southeast Pacific'       , 'Sea'   , 'No'  ,  50, 730, 22,325 , 'Neutral'  ),
	array('Bahia da Coquimbo'       , 'Sea'   , 'No'  , 250, 793,121,394 , 'Neutral'  ),
	array('Bahia da Arica'          , 'Sea'   , 'No'  , 180, 613, 90,315 , 'Neutral'  ),
	array('Galapagos Sea'           , 'Sea'   , 'No'  ,  80, 290, 35,110 , 'Neutral'  ),
	array('Golfo de Panama'         , 'Sea'   , 'No'  , 130, 168, 65, 75 , 'Neutral'  )
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
	array( 'Barranquilla'            , 'Caribbean Sea'      , 'Yes', 'No'  ),
	array( 'Barranquilla'            , 'Trujillo'           , 'Yes', 'Yes' ),
	array( 'Barranquilla'            , 'Bogota'             , 'No' , 'Yes' ),
	array( 'Barranquilla'            , 'Medellin'           , 'No' , 'Yes' ),
	array( 'Barranquilla'            , 'Panama'             , 'Yes', 'Yes' ),
	array( 'Medellin'                , 'Bogota'             , 'No' , 'Yes' ),
	array( 'Medellin'                , 'Valle Magdalena'    , 'Yes', 'Yes' ),
	array( 'Medellin'                , 'Panama'             , 'Yes', 'Yes' ),
	array( 'Medellin'                , 'Golfo de Panama'    , 'Yes', 'No'  ),
	array( 'Bogota'                  , 'Trujillo'           , 'No' , 'Yes' ),
	array( 'Bogota'                  , 'Orinoco Springs'    , 'No' , 'Yes' ),
	array( 'Bogota'                  , 'Acre'               , 'No' , 'Yes' ),
	array( 'Bogota'                  , 'Cordillera Oriental', 'No' , 'Yes' ),
	array( 'Bogota'                  , 'Valle Magdalena'    , 'No' , 'Yes' ),
	array( 'Valle Magdalena'         , 'Golfo de Panama'    , 'Yes', 'No'  ),
	array( 'Valle Magdalena'         , 'Cordillera Oriental', 'No' , 'Yes' ),
	array( 'Valle Magdalena'         , 'Montana'            , 'No' , 'Yes' ),
	array( 'Valle Magdalena'         , 'Ecuador'            , 'Yes', 'Yes' ),
	array( 'Cordillera Oriental'     , 'Acre'               , 'No' , 'Yes' ),
	array( 'Cordillera Oriental'     , 'Montana'            , 'No' , 'Yes' ),
	array( 'Chiclayo'                , 'Galapagos Sea'      , 'Yes', 'No'  ),
	array( 'Chiclayo'                , 'Ecuador'            , 'Yes', 'Yes' ),
	array( 'Chiclayo'                , 'Montana'            , 'No' , 'Yes' ),
	array( 'Chiclayo'                , 'Arequipa'           , 'No' , 'Yes' ),
	array( 'Chiclayo'                , 'Lima'               , 'Yes', 'Yes' ),
	array( 'Montana'                 , 'Ecuador'            , 'No' , 'Yes' ),
	array( 'Montana'                 , 'Acre'               , 'No' , 'Yes' ),
	array( 'Montana'                 , 'Arequipa'           , 'No' , 'Yes' ),
	array( 'Lima'                    , 'Galapagos Sea'      , 'Yes', 'No'  ),
	array( 'Lima'                    , 'Arequipa'           , 'Yes', 'Yes' ),
	array( 'Lima'                    , 'Bahia da Arica'     , 'Yes', 'No'  ),
	array( 'Arequipa'                , 'Acre'               , 'No' , 'Yes' ),
	array( 'Arequipa'                , 'La Paz'             , 'No' , 'Yes' ),
	array( 'Arequipa'                , 'Bahia da Arica'     , 'Yes', 'No'  ),
	array( 'Arequipa'                , 'Desierto Atacama'   , 'Yes', 'Yes' ),
	array( 'Acre'                    , 'Orinoco Springs'    , 'No' , 'Yes' ),
	array( 'Acre'                    , 'Amazon Basin'       , 'No' , 'Yes' ),
	array( 'Acre'                    , 'La Paz'             , 'No' , 'Yes' ),
	array( 'Amazon Basin'            , 'Orinoco Springs'    , 'No' , 'Yes' ),
	array( 'Amazon Basin'            , 'Roraima'            , 'No' , 'Yes' ),
	array( 'Amazon Basin'            , 'Manaus'             , 'No' , 'Yes' ),
	array( 'Amazon Basin'            , 'Mato Grosso'        , 'No' , 'Yes' ),
	array( 'Amazon Basin'            , 'La Paz'             , 'No' , 'Yes' ),
	array( 'Roraima'                 , 'Orinoco Springs'    , 'No' , 'Yes' ),
	array( 'Roraima'                 , 'Caracas'            , 'No' , 'Yes' ),
	array( 'Roraima'                 , 'Guyana'             , 'No' , 'Yes' ),
	array( 'Roraima'                 , 'Belem'              , 'No' , 'Yes' ),
	array( 'Roraima'                 , 'Manaus'             , 'No' , 'Yes' ),
	array( 'Belem'                   , 'Guyana'             , 'Yes', 'Yes' ),
	array( 'Belem'                   , 'Mid Atlantic Ocean' , 'Yes', 'No'  ),
	array( 'Belem'                   , 'Brazilian Sea'      , 'Yes', 'No'  ),
	array( 'Belem'                   , 'Manaus'             , 'No' , 'Yes' ),
	array( 'Belem'                   , 'Goias'              , 'No' , 'Yes' ),
	array( 'Belem'                   , 'Rio de Janeiro'     , 'Yes', 'Yes' ),
	array( 'Manaus'                  , 'Mato Grosso'        , 'No' , 'Yes' ),
	array( 'Manaus'                  , 'Goias'              , 'No' , 'Yes' ),
	array( 'Mato Grosso'             , 'La Paz'             , 'No' , 'Yes' ),
	array( 'Mato Grosso'             , 'Yungas'             , 'No' , 'Yes' ),
	array( 'Mato Grosso'             , 'Goias'              , 'No' , 'Yes' ),
	array( 'Goias'                   , 'Yungas'             , 'No' , 'Yes' ),
	array( 'Goias'                   , 'Paraguay'           , 'No' , 'Yes' ),
	array( 'Goias'                   , 'Rio Grande do Sul'  , 'No' , 'Yes' ),
	array( 'Goias'                   , 'Rio de Janeiro'     , 'No' , 'Yes' ),
	array( 'Rio de Janeiro'          , 'Brazilian Sea'      , 'Yes', 'No'  ),
	array( 'Rio de Janeiro'          , 'Rio Grande do Sul'  , 'Yes', 'Yes' ),
	array( 'Rio Grande do Sul'       , 'Paraguay'           , 'No' , 'Yes' ),
	array( 'Rio Grande do Sul'       , 'Mesopotamia'        , 'No' , 'Yes' ),
	array( 'Rio Grande do Sul'       , 'Santa Fe'           , 'No' , 'Yes' ),
	array( 'Rio Grande do Sul'       , 'Uruguay'            , 'Yes', 'Yes' ),
	array( 'Rio Grande do Sul'       , 'Brazilian Sea'      , 'Yes', 'No'  ),
	array( 'Desierto Atacama'        , 'La Paz'             , 'No' , 'Yes' ),
	array( 'Desierto Atacama'        , 'Potosi'             , 'No' , 'Yes' ),
	array( 'Desierto Atacama'        , 'Antofagasta'        , 'Yes', 'Yes' ),
	array( 'Desierto Atacama'        , 'Bahia da Arica'     , 'Yes', 'No'  ),
	array( 'Antofagasta'             , 'Potosi'             , 'No' , 'Yes' ),
	array( 'Antofagasta'             , 'Gran Chaco'         , 'No' , 'Yes' ),
	array( 'Antofagasta'             , 'Santiago'           , 'Yes', 'Yes' ),
	array( 'Antofagasta'             , 'Bahia da Coquimbo'  , 'Yes', 'No'  ),
	array( 'Antofagasta'             , 'Bahia da Arica'     , 'Yes', 'No'  ),
	array( 'Santiago'                , 'Gran Chaco'         , 'No' , 'Yes' ),
	array( 'Santiago'                , 'Pampas'             , 'No' , 'Yes' ),
	array( 'Santiago'                , 'Patagonia'          , 'No' , 'Yes' ),
	array( 'Santiago'                , 'Concepcion'         , 'Yes', 'Yes' ),
	array( 'Santiago'                , 'Bahia da Coquimbo'  , 'Yes', 'No'  ),
	array( 'Concepcion'              , 'Patagonia'          , 'No' , 'Yes' ),
	array( 'Concepcion'              , 'Tierra del Fuego'   , 'Yes', 'Yes' ),
	array( 'Concepcion'              , 'Scotia Sea'         , 'Yes', 'No'  ),
	array( 'Concepcion'              , 'Bahia da Coquimbo'  , 'Yes', 'No'  ),
	array( 'Tierra del Fuego'        , 'Patagonia'          , 'Yes', 'Yes' ),
	array( 'Tierra del Fuego'        , 'Southwest Atlantic' , 'Yes', 'No'  ),
	array( 'Tierra del Fuego'        , 'Scotia Sea'         , 'Yes', 'No'  ),
	array( 'Tierra del Fuego'        , 'Coast of Argentina' , 'Yes', 'No'  ),
	array( 'Gran Chaco'              , 'Potosi'             , 'No' , 'Yes' ),
	array( 'Gran Chaco'              , 'Mesopotamia'        , 'No' , 'Yes' ),
	array( 'Gran Chaco'              , 'Santa Fe'           , 'No' , 'Yes' ),
	array( 'Gran Chaco'              , 'Buenos Aires'       , 'No' , 'Yes' ),
	array( 'Gran Chaco'              , 'Pampas'             , 'No' , 'Yes' ),
	array( 'Pampas'                  , 'Buenos Aires'       , 'No' , 'Yes' ),
	array( 'Pampas'                  , 'Mar del Plata'      , 'No' , 'Yes' ),
	array( 'Pampas'                  , 'Patagonia'          , 'No' , 'Yes' ),
	array( 'Patagonia'               , 'Mar del Plata'      , 'Yes', 'Yes' ),
	array( 'Patagonia'               , 'Coast of Argentina' , 'Yes', 'No'  ),
	array( 'Mesopotamia'             , 'Potosi'             , 'No' , 'Yes' ),
	array( 'Mesopotamia'             , 'Paraguay'           , 'No' , 'Yes' ),
	array( 'Mesopotamia'             , 'Santa Fe'           , 'No' , 'Yes' ),
	array( 'Santa Fe'                , 'Uruguay'            , 'No' , 'Yes' ),
	array( 'Santa Fe'                , 'Buenos Aires'       , 'No' , 'Yes' ),
	array( 'Buenos Aires'            , 'Uruguay'            , 'Yes', 'Yes' ),
	array( 'Buenos Aires'            , 'Coast of Argentina' , 'Yes', 'No'  ),
	array( 'Buenos Aires'            , 'Mar del Plata'      , 'Yes', 'Yes' ),
	array( 'Mar del Plata'           , 'Coast of Argentina' , 'Yes', 'No'  ),
	array( 'Panama'                  , 'Caribbean Sea'      , 'Yes', 'No'  ),
	array( 'Panama'                  , 'Galapagos Sea'      , 'Yes', 'No'  ),
	array( 'Panama'                  , 'Golfo de Panama'    , 'Yes', 'No'  ),
	array( 'Trujillo'                , 'Caribbean Sea'      , 'Yes', 'No'  ),
	array( 'Trujillo'                , 'Caracas'            , 'Yes', 'Yes' ),
	array( 'Trujillo'                , 'Orinoco Springs'    , 'No' , 'Yes' ),
	array( 'Caracas'                 , 'Caribbean Sea'      , 'Yes', 'No'  ),
	array( 'Caracas'                 , 'Mid Atlantic Ocean' , 'Yes', 'No'  ),
	array( 'Caracas'                 , 'Guyana'             , 'Yes', 'Yes' ),
	array( 'Caracas'                 , 'Orinoco Springs'    , 'No' , 'Yes' ),
	array( 'Guyana'                  , 'Mid Atlantic Ocean' , 'Yes', 'No'  ),
	array( 'Ecuador'                 , 'Golfo de Panama'    , 'Yes', 'No'  ),
	array( 'Ecuador'                 , 'Galapagos Sea'      , 'Yes', 'No'  ),
	array( 'La Paz'                  , 'Yungas'             , 'No' , 'Yes' ),
	array( 'La Paz'                  , 'Paraguay'           , 'No' , 'Yes' ),
	array( 'La Paz'                  , 'Potosi'             , 'No' , 'Yes' ),
	array( 'Yungas'                  , 'Paraguay'           , 'No' , 'Yes' ),
	array( 'Potosi'                  , 'Paraguay'           , 'No' , 'Yes' ),
	array( 'Uruguay'                 , 'Brazilian Sea'      , 'Yes', 'No'  ),
	array( 'Uruguay'                 , 'Coast of Argentina' , 'Yes', 'No'  ),
	array( 'Islas Juan Fernandez'    , 'Bahia da Arica'     , 'Yes', 'No'  ),
	array( 'Islas Juan Fernandez'    , 'Bahia da Coquimbo'  , 'Yes', 'No'  ),
	array( 'Islas Juan Fernandez'    , 'Scotia Sea'         , 'Yes', 'No'  ),
	array( 'Islas Juan Fernandez'    , 'Southeast Pacific'  , 'Yes', 'No'  ),
	array( 'Caribbean Sea'           , 'Mid Atlantic Ocean' , 'Yes', 'No'  ),
	array( 'Mid Atlantic Ocean'      , 'Brazilian Sea'      , 'Yes', 'No'  ),
	array( 'Mid Atlantic Ocean'      , 'Southwest Atlantic' , 'Yes', 'No'  ),
	array( 'Brazilian Sea'           , 'Southwest Atlantic' , 'Yes', 'No'  ),
	array( 'Brazilian Sea'           , 'Coast of Argentina' , 'Yes', 'No'  ),
	array( 'Southwest Atlantic'      , 'Coast of Argentina' , 'Yes', 'No'  ),
	array( 'Southwest Atlantic'      , 'Scotia Sea'         , 'Yes', 'No'  ),
	array( 'Scotia Sea'              , 'Bahia da Coquimbo'  , 'Yes', 'No'  ),
	array( 'Scotia Sea'              , 'Southeast Pacific'  , 'Yes', 'No'  ),
	array( 'Southeast Pacific'       , 'Galapagos Sea'      , 'Yes', 'No'  ),
	array( 'Southeast Pacific'       , 'Bahia da Arica'     , 'Yes', 'No'  ),
	array( 'Bahia da Coquimbo'       , 'Bahia da Arica'     , 'Yes', 'No'  ),
	array( 'Bahia da Arica'          , 'Galapagos Sea'      , 'Yes', 'No'  ),
	array( 'Galapagos Sea'           , 'Golfo de Panama'    , 'Yes', 'No'  ),
	array( 'Costa Rica'              , 'Panama'             , 'No' , 'Yes' ),
	array( 'Costa Rica (North Coast)', 'Caribbean Sea'      , 'Yes', 'No'  ),
	array( 'Costa Rica (North Coast)', 'Panama'             , 'Yes', 'No'  ),
	array( 'Costa Rica (South Coast)', 'Southeast Pacific'  , 'Yes', 'No'  ),
	array( 'Costa Rica (South Coast)', 'Galapagos Sea'      , 'Yes', 'No'  ),
	array( 'Costa Rica (South Coast)', 'Panama'             , 'Yes', 'No'  )
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