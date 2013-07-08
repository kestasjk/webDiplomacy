<?php
/*
	Copyright (C) 2010 Emmanuele Ravaioli

	This file is part of the Economic variant for webDiplomacy

	The Economic variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Economic variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---
	
	Changelog:
	1.0:   initial release
	1.0.1: fixed: spelling error on the large map
	1.1: New naming (Economic -> ClassicEconomic)
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ClassicEconomicVariant extends WDVariant {
	public $id=53;
	public $mapID=53;
	public $name='ClassicEconomic';
	public $fullName='Classic - Economic';
	public $description='A variant to the standard Diplomacy map comprising an economic warfield!';
	public $author='Emmanuele Ravaioli (Tadar Es Darden)';
	public $adapter ='Emmanuele Ravaioli';
	public $version ='1.1';

	public $countries=array('England', 'France', 'Italy', 'Germany', 'Austria', 'Turkey', 'Russia');

	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap'] = 'ClassicEconomic';
		$this->variantClasses['adjudicatorPreGame'] = 'ClassicEconomic';
	}
	
	// Change the number of supply centers required for winning the game.
	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 24;
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1901);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1901);
		};';
	}
}

?>