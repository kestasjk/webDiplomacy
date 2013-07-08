<?php
/*
    Copyright (C) 2004-2009 Kestas J. Kuliukas

	This file is part of webDiplomacy.

    webDiplomacy is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    webDiplomacy is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('IN_CODE') or die('This script can not be run by itself.');

class ClassicVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
		'England' => array(
					'Edinburgh'=>'Fleet', 'Liverpool'=>'Army', 'London'=>'Fleet'
				),
		'France' => array(
					'Brest'=>'Fleet', 'Paris'=>'Army', 'Marseilles'=>'Army'
				),
		'Italy' => array(
					'Venice'=>'Army', 'Rome'=>'Army', 'Naples'=>'Fleet'
				),
		'Germany' => array(
					'Kiel'=>'Fleet', 'Berlin'=>'Army', 'Munich'=>'Army'
				),
		'Austria' => array(
					'Vienna'=>'Army', 'Trieste'=>'Fleet', 'Budapest'=>'Army'
				),
		'Turkey' => array(
					'Smyrna'=>'Army', 'Ankara'=>'Fleet', 'Constantinople'=>'Army'
				),
		'Russia' => array(
					'Moscow'=>'Army', 'St. Petersburg (South Coast)'=>'Fleet', 'Warsaw'=>'Army', 'Sevastopol'=>'Fleet'
				)
		);

}