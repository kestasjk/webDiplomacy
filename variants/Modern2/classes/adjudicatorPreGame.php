<?php
/*
	Copyright (C) 2010 Carey Jensen / Oliver Auth

	This file is part of the Modern Diplomacy II variant for webDiplomacy

	The Modern Diplomacy II variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Modern Diplomacy II variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Modern2Variant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
			'Spain'   => array('Barcelona' =>'Fleet','Madrid'=>'Army' ,'Seville'  =>'Army' ),
			'Egypt'   => array('Alexandria'=>'Fleet','Cairo' =>'Fleet','Aswan'    =>'Army' ),
			'Poland'  => array('Warsaw'    =>'Army' ,'Krakow'=>'Army' ,'Gdansk'   =>'Fleet'),
  			'Britain' => array('Edinburgh' =>'Fleet','London'=>'Fleet','Liverpool'=>'Fleet','Gibraltar' =>'Fleet'),
  			'France'  => array('Marseille' =>'Army' ,'Paris' =>'Army' ,'Bordeaux' =>'Fleet','Lyon'      =>'Army' ),
  			'Italy'   => array('Milan'     =>'Army' ,'Rome'  =>'Army' ,'Naples'   =>'Fleet','Venice'    =>'Fleet'),
  			'Germany' => array('Hamburg'   =>'Fleet','Berlin'=>'Fleet','Munich'   =>'Army' ,'Frankfurt' =>'Army' ),
  			'Turkey'  => array('Istanbul'  =>'Army' ,'Ankara'=>'Fleet','Izmir'    =>'Fleet','Adana'     =>'Army' ),
			'Ukraine' => array('Sevastopol'=>'Fleet','Kiev'  =>'Army' ,'Odessa'   =>'Army' ,'Kharkov'   =>'Army' ),
  			'Russia'  => array('Moscow'    =>'Army' ,'Gorky' =>'Army' ,'Murmansk' =>'Fleet','Rostov'    =>'Fleet', 'St. Petersburg'=>'Fleet'),
		);

}
