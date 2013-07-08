<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Fantasy World variant for webDiplomacy

	The Fantasy World variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Fantasy World variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class FantasyWorldVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'Arafura'      => array('Babar'=>'Army','Serang'=>'Army','Davao'=>'Fleet'),
		'Hamra'        => array('Summan'=>'Army','Marzuq'=>'Army','Karet'=>'Fleet'),
		'Ishfahan'     => array('Zahedan'=>'Army','Zarand'=>'Army','Dezful'=>'Fleet'),
		'Jylland'      => array('Kassel'=>'Army','Thisted'=>'Army','Farberg'=>'Fleet'),
		'Kyushu'       => array('Shibata'=>'Army','Takada'=>'Army','Nemuro'=>'Fleet'),
		'Lugulu'       => array('Pagalu'=>'Army','Pebane'=>'Army','Eshowe'=>'Fleet'),
		'Ming-tao'     => array('Hanyin'=>'Army','Mingshui'=>'Army','Lintao'=>'Fleet'),
		'New Foundland'=> array('Columbus'=>'Army','Albany'=>'Army','Washington'=>'Fleet'),
		'Orleans'      => array('Rennes'=>'Army','Amiens'=>'Army','Charente'=>'Fleet'),
		'Rajasthan'    => array('Shahpur'=>'Army','Nizamri'=>'Army','Tanjor'=>'Army','Jaipur'=>'Fleet'),
		'Sakhalin'     => array('Star'=>'Fleet','Sudzha'=>'Fleet','Usovo'=>'Fleet'),
		'Valparaiso'   => array('Veracruz'=>'Army','Cartagena'=>'Fleet','Cordoba'=>'Fleet')
	);

}