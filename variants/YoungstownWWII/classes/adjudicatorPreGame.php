<?php
/*
	Copyright (C) 2013 Arjun Sarathy / Oliver Auth

	This file is part of the Youngstown World War II variant for webDiplomacy

	The Youngstown World War II variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Youngstown World War II variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class YoungstownWWIIVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'British Empire'  => array('Edinburgh'  =>'Fleet', 'Liverpool' =>'Fleet', 'London'=>'Fleet', 'Cairo' =>'Army', 'Aden' =>'Fleet', 'Singapore' =>'Fleet', 'Delhi' =>'Army', 'Calcutta' =>'Army', 'Madras' =>'Fleet'),
		'French Empire'   => array('Brest'=>'Fleet', 'Paris'   =>'Army', 'Marseille'=>'Army', 'Bordeaux' =>'Army', 'Algiers' =>'Fleet', 'Saigon' => 'Fleet', 'Phnom Penh' =>'Army', 'Lyons' =>'Army'),
		'Italian Empire'  => array('Naples'=>'Fleet', 'Rome'   =>'Army', 'Milan'=>'Army', 'Tirane' =>'Army', 'Tripoli' =>'Fleet', 'Mogadishu' =>'Fleet', 'Addis Adaba' =>'Army'),
		'Japanese Empire' => array( 'Shendong'=>'Fleet', 'Seoul'=>'Army', 'Kyoto' =>'Army', 'Tokyo' =>'Army', 'Osaka' =>'Fleet', 'Shanghai' =>'Fleet', 'Peking' =>'Army'),
		'Germany'         => array('Kiel'=>'Fleet', 'Berlin'   =>'Fleet', 'Munich'=>'Army', 'Cologne' =>'Army', 'Vienna' =>'Army', 'Prague' =>'Army', 'Breslau' =>'Army'),
		'Soviet Union'    => array('Leningrad (South Coast)'=>'Fleet', 'Moscow'   =>'Army', 'Sevastopol'=>'Fleet', 'Stalingrad' =>'Army', 'Omsk' =>'Army', 'Irkutsk' =>'Army', 'Vladivostok' =>'Fleet')
	);

}