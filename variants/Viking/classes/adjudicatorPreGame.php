<?php
/*
	Copyright (C) 2011 kaner406 / Oliver Auth

	This file is part of the Viking variant for webDiplomacy

	The Viking variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Viking variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class VikingVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Norge'               => array('Skaalholt'  =>'Army' ,'Dyvlin'  =>'Fleet','Noirmoutier'=>'Army' ,'Hafrsfjord'=>'Fleet','Nidaros'     =>'Army' ,'Katanes'  =>'Army' ,'Kirkby'    =>'Fleet','Skiringssal' =>'Army' ),
		'Danmark'             => array('Kalmar'     =>'Fleet','Reval'   =>'Fleet','Jomsborg'   =>'Army' ,'Hedeby'    =>'Fleet','Dorestad'    =>'Fleet','Rouen'    =>'Army' ,'Skegness'  =>'Fleet','Jorvik'      =>'Army' ),
		'Sverige'             => array('Uppsala'    =>'Army' ,'Birka'   =>'Fleet','Visby'      =>'Army' ,'Gdansk'    =>'Fleet','Aldeigjuborg'=>'Army' ,'Holmgard' =>'Fleet','Rostov'    =>'Army' ,'Kjonugard'   =>'Army' ),
		'Slavic Nations'      => array('Arkhangelsk'=>'Army' ,'Vladimir'=>'Army' ,'Kaffa'      =>'Fleet','Beograd'   =>'Army' ,'Praha'       =>'Army' ,'Breslau'  =>'Army' ,'Przemysl'  =>'Army' ,'Vitebsk'     =>'Army' ),
		'Arab Caliphates'     => array('Trebizond'  =>'Army' ,'Antakya' =>'Army' ,'Palmyra'    =>'Fleet','Tunis'     =>'Fleet','Palermo'     =>'Fleet','Sevilla'  =>'Fleet','Lisboa'    =>'Army' ,'Cordoba'     =>'Army' ),
		'Eastern Roman Empire'=> array('Syracuse'   =>'Fleet','Napoli'  =>'Fleet','Venezia'    =>'Army' ,'Spalato'   =>'Army' ,'Athinai'     =>'Fleet','Miklagard'=>'Army' ,'Ankara'    =>'Army' ,'Smyrna'      =>'Fleet'),
		'Burgundy'            => array('Groningen'  =>'Army' ,'Koln'    =>'Army' ,'Verdun'     =>'Army' ,'Avignon'   =>'Army' ,'Marseilles'  =>'Fleet','Genova'   =>'Army' ,'Pisa'      =>'Army' ,'Roma'        =>'Army' ),
		'France'              => array('Paris'      =>'Army' ,'Tours'   =>'Army' ,'Nantes'     =>'Fleet' ,'Toulouse'  =>'Army' ,'Hamburg'     =>'Army' ,'Frankfurt'=>'Army' ,'St. Gallen'=>'Army' ,'Graz'        =>'Army' ),
		'Neutral units'       => array('London'     =>'Army' ,'Buda'    =>'Army' ,'Pest'       =>'Army' ,'Alexandria'=>'Army' ,'Jorsal'      =>'Army')
	);
}

?>
