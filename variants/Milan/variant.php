<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Milan variant for webDiplomacy

	The Milan variant for webDiplomacy is free software: you can redistribute it
	and/or modify it under the terms of the GNU Affero General Public License as
	published by the Free Software Foundation, either version 3 of the License, 
	or (at your option) any later version.

	The "Milan variant for webDiplomacy" is distributed in the hope that it will 
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---

	Changelog:
	1.0: initial release
	1.1: starting unit from Venetia moved to Milan.
	1.2: updating the initial-maps to reflect the new starting units, fixed static.sql glitch
	1.5: new webdip v.97 code
	1.6: minor tweaks
	1.6.1: small adjustments for the new variant.php code
	1.6.2: fixed: spelling error on smallmap
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class MilanVariant extends WDVariant {
	public $id         = 10;
	public $mapID      = 10;
	public $name       = 'Milan';
	public $fullName   = 'Milan Diplomacy';
	public $description= 'The classic map with a new province named Milan in Italy';
	public $author     = 'John Norris';
	public $adapter    = 'Oliver Auth';
	public $version    = '1.6.2';
	public $homepage   = 'http://www.variantbank.org/results/rules/m/milan.htm';

	public $countries=array('England', 'France', 'Italy', 'Germany', 'Austria', 'Turkey', 'Russia');
	
	public function __construct() {
		parent::__construct();
		$this->variantClasses['adjudicatorPreGame'] = 'Milan';
		$this->variantClasses['drawMap']            = 'Milan';
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