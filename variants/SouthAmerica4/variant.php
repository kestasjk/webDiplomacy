<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the South America 4-Player variant for webDiplomacy

	The South America 4-Player variant for webDiplomacy" is free software:
	you can redistribute it and/or modify it under the terms of the GNU Affero
	General Public License as published by the Free Software Foundation, either
	version 3 of the License, or (at your option) any later version.

	The South America 4-Player variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.

	---

	Changelog:
	1.0: initial release
	1.5: new webdip v.97 code
	1.6: minor tweaks
	1.6.1: small adjustments
	1.6.2: small adjustments
	1.6.3: wrong starting units fixed...

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class SouthAmerica4Variant extends WDVariant {
	public $id         = 7;
	public $mapID      = 7;
	public $name       = 'SouthAmerica4';
	public $fullName   = 'South America (4 players)';
	public $description= 'A variant with a map of South America for 4 players.';
	public $author     = 'Joe Janbu';
	public $adapter    = 'Oliver Auth';
	public $version    = '1.6.3';
	public $homepage   = 'http://www.variantbank.org/results/rules/s/southamerica32.htm';

	public $countries=array('Argentina','Brazil','Chile','Colombia');
	
	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'SouthAmerica4';
		$this->variantClasses['adjudicatorPreGame'] = 'SouthAmerica4';

	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 2000);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 2000);
		};';
	}
}

?>