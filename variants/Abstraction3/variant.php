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

	---

	Changelog:
	1.0: initial version
	1.0.6: new rules.html

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Abstraction3Variant extends WDVariant {
	public $id         =67;
	public $mapID      =67;
	public $name       ='Abstraction3';
	public $fullName   ='Abstraction III';
	public $description='';
	public $author     ='airborne';
	public $adapter    ='airborne';
	public $version    ='III';
	public $codeVersion='1.0.6';	
	
	public $countries=array('Austria','France','Germany','Italy','Britain','Russia','Turkey');	

	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'Abstraction3';
		$this->variantClasses['adjudicatorPreGame'] = 'Abstraction3';

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