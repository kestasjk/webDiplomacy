<?php
/*
	Copyright (C) 2010 Cian O Rathaille

	This file is part of the Germany Vs Italy variant for webDiplomacy

	The Germany Vs Italy variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Germany Vs Italy variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---

	Changelog:
	1.0:   initial version  - derived from France Versus Austria Version 1.0.1
	1.0.1: fixed bet to "1" D-point to prevent abuse
	1.0.2: fixed: spelling error on smallmap
	1.0.3: fixed: sc error on smallmap
	1.0.4: No fixed bet anymore (moved the function in the main webdip code)
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ClassicGvIVariant extends WDVariant {
	public $id         =23;
	public $mapID      =23;
	public $name       ='ClassicGvI';
	public $fullName   ='Classic - Germany vs Italy';
	public $description='The standard Diplomacy map of Europe, but only Germany and Italy.';
	public $adapter    = 'Orathaic';
	public $version    = '1.0.4';

	public $countries=array('Germany','Italy');

	public function __construct() {
		parent::__construct();
		$this->variantClasses['drawMap']            = 'ClassicGvI';
		$this->variantClasses['adjudicatorPreGame'] = 'ClassicGvI';
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