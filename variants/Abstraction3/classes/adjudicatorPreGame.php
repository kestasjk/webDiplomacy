<?php
/*
	Copyright (C) 2012 Oliver Auth

	This file is part of the Abstraction III variant for webDiplomacy

	The Abstraction III variant for webDiplomacy is free software:
	you can redistribute it and/or modify it under the terms of the GNU Affero
	General Public License as published by the Free Software Foundation, either
	version 3 of the License, or (at your option) any later version.

	The Abstraction III variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
	If you have questions or suggestions send me a mail: Oliver.Auth@rhoen.de
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Abstraction3Variant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Austria' => array('Zara'=>'Fleet', 'Vienna'=>'Army', 'Trieste'=>'Army', 'Budapest'=>'Army'),
		'France' => array('Brest'=>'Fleet', 'Paris'=>'Army', 'Marseilles'=>'Army', 'Algiers'=>'Army'),
		'Russia' => array('Saint Petersburg'=>'Fleet', 'Sevastopol'=>'Army', 'Moscow'=>'Army', 'Arkhangelsk'=>'Army', 'Warsaw'=>'Army'),
		'Britain' => array('London'=>'Fleet', 'Edinburgh'=>'Fleet', 'Liverpool'=>'Army', 'Gibraltar'=>'Fleet', 'Cairo'=>'Fleet'),
		'Germany' => array('Kiel'=>'Fleet', 'Berlin'=>'Army', 'Munich'=>'Army', 'Cologne'=>'Army'),
		'Turkey' => array('Smyrna'=>'Fleet', 'Constantinople'=>'Army', 'Angora'=>'Army', 'Bagdad'=>'Army'),
		'Italy' => array('Bari'=>'Fleet', 'Naples'=>'Fleet', 'Rome'=>'Army', 'Milan'=>'Army'),
	);

}

?>
