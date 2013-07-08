<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Youngstown - Redux variant for webDiplomacy

	The Youngstown - Redux variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Youngstown - Redux variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class YoungstownReduxVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Austria' => array('Sarajevo'=>'Fleet', 'Vienna'=>'Army', 'Trieste'=>'Army', 'Budapest'=>'Army'),
		'France' => array('Brest'=>'Fleet', 'Paris'=>'Army', 'Marseilles'=>'Army', 'Saigon'=>'Fleet'),
		'Russia' => array('Saint Petersburg (South Coast)'=>'Fleet', 'Vladivostok'=>'Fleet', 'Sevastopol'=>'Fleet', 'Moscow'=>'Army', 'Omsk'=>'Army', 'Warsaw'=>'Army'),
		'Britain' => array('London'=>'Fleet', 'Edinburgh'=>'Fleet', 'Liverpool'=>'Fleet', 'Aden'=>'Fleet', 'Singapore'=>'Fleet'),
		'Germany' => array('Kiel'=>'Fleet', 'Tsingtao'=>'Fleet', 'Berlin'=>'Army', 'Munich'=>'Army', 'Cologne'=>'Army'),
		'Turkey' => array('Ankara'=>'Fleet', 'Constantinople'=>'Army', 'Mecca'=>'Army', 'Bagdad'=>'Army'),
		'Italy' => array('Mogadishu'=>'Fleet', 'Naples'=>'Fleet', 'Rome'=>'Army', 'Milan'=>'Army'),
		'India' => array('Bombay'=>'Fleet', 'Madras'=>'Fleet', 'Delhi'=>'Army', 'Calcutta'=>'Army'),
		'Japan' => array('Osaka'=>'Fleet', 'Tokyo'=>'Fleet', 'Sapporo'=>'Fleet', 'Kyoto'=>'Army'),
		'China' => array('Peking'=>'Army', 'Shanghai'=>'Fleet', 'Wuhan'=>'Army', 'Guangzhou'=>'Army'),
	);

}

?>
