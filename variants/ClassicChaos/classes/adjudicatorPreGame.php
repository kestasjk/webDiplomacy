<?php
/*
	Copyright (C) 2010 Carey Jensen / Oliver Auth

	This file is part of the Chaos variant for webDiplomacy

	The Chaos variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Chaos variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
		
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ClassicChaosVariant_adjudicatorPreGame extends adjudicatorPreGame {

	protected $countryUnits = array(
			'Ankara'         => array(),
			'Belgium'        => array(),
			'Berlin'         => array(),
			'Brest'          => array(),
			'Budapest'       => array(),
			'Bulgaria'       => array(),
			'Constantinople' => array(),
			'Denmark'        => array(),
			'Edinburgh'      => array(),
			'Greece'         => array(),
			'Holland'        => array(),
			'Kiel'           => array(),
			'Liverpool'      => array(),
			'London'         => array(),
			'Marseilles'     => array(),
			'Moscow'         => array(),
			'Munich'         => array(),
			'Naples'         => array(),
			'Norway'         => array(),
			'Paris'          => array(),
			'Portugal'       => array(),
			'Rome'           => array(),
			'Rumania'        => array(),
			'Serbia'         => array(),
			'Sevastopol'     => array(),
			'Smyrna'         => array(),
			'Spain'          => array(),
			'St-Petersburg'  => array(),
			'Sweden'         => array(),
			'Trieste'        => array(),
			'Tunis'          => array(),
			'Venice'         => array(),
			'Vienna'         => array(),
			'Warsaw'         => array()
		);

	// Disabled; no initial units or occupations
	protected function assignUnits() { }

	protected function assignUnitOccupations() { }

}

?>