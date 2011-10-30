<?php
/*
	Copyright (C) 2010 Oliver Auth, Orathaic

	This file is part of the Italy+ Vs England+ Vs Russia variant for webDiplomacy

	The Italy+ Vs England+ Vs Russia variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The France Vs Germany Vs Austria variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---
	
	Changelog:
	1.0:   initial version
	1.0.1: No fixed bet anymore (moved the function in the main webdip code)
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ClassicIERVariant extends WDVariant {
	public $id         =49;
	public $mapID      =49;
	public $name       ='ClassicIER';
	public $fullName   ='Classic - Italy+ Vs England+ Vs Russia';
	public $description='The standard Diplomacy map of Europe, but with Russia, England and Italy as the only playable countries. To offset the balance of Russia\'s four builds, England gians Holland as a home suppply center while Italy gains trieste - each for based on historical links.';
	public $adapter    = 'Orathaic';
	public $version    = '1.0.1';

	public $countries=array('England','Italy','Russia');

	public function __construct() {
		parent::__construct();
		$this->variantClasses['drawMap']               = 'ClassicIER';
		$this->variantClasses['adjudicatorPreGame']    = 'ClassicIER';
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
