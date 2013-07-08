<?php
/*
	Copyright (C) 2012 Mikalis Kamaritis / Oliver Auth

	This file is part of the Europe 1939 variant for webDiplomacy

	The Europe 1939 variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Europe 1939 variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Europe1939Variant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Britain' 		=> 	array('Edinburgh'=>'Fleet','Northern Ireland'=>'Fleet','London'=>'Fleet','Cairo'=>'Fleet','Baghdad'=>'Army'),
		'France' 		=> 	array('Brest'=>'Fleet','Paris'=>'Army','Lyon'=>'Army','Marseilles'=>'Army','Algiers'=>'Fleet'),
		'Germany' 		=> 	array('Cologne'=>'Army','Kiel'=>'Fleet','Berlin'=>'Army','Munich'=>'Army','Vienna'=>'Army'),
		'Spain' 		=> 	array('Seville'=>'Fleet','Madrid'=>'Army','Barcelona'=>'Army','Tangiers'=>'Fleet'),
		'Italy' 		=> 	array('Naples'=>'Fleet','Rome'=>'Army','Milan'=>'Army','Albania'=>'Army','Tripoli'=>'Fleet'),
		'Poland'		=> 	array('Danzig'=>'Fleet','Warsaw'=>'Army','Krakow'=>'Army','Wroclaw'=>'Army'),
		'USSR' 			=> 	array('Leningrad'=>'Fleet','Arkhangelsk'=>'Army','Moscow'=>'Army','Stalingrad'=>'Army','Sevastopol'=>'Fleet'),
		'Turkey' 		=> 	array('Istanbul'=>'Army','Izmir'=>'Fleet','Ankara'=>'Fleet','Adana'=>'Army'),
		'Neutral units'	=> 	array('Serbia' => 'Army'),
	);

}

?>
