<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the South America 5-Player variant for webDiplomacy

	The South America 5-Player variant for webDiplomacy" is free software:
	you can redistribute it and/or modify it under the terms of the GNU Affero
	General Public License as published by the Free Software Foundation, either 
	version 3 of the License, or (at your option) any later version.

	The South America 5-Player variant for webDiplomacy is distributed in the hope
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
	1.6.2: small bugfix (occupationbar had the wrong color)
	1.6.3: small adjustments

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class SouthAmerica5Variant extends WDVariant {
	public $id         =6;
	public $mapID      =6;
	public $name       ='SouthAmerica5';
	public $fullName   ='South America (5 players)';
	public $description='A variant with a map of South America for 5 players.';
	public $author     ='Joe Janbu';
	public $adapter    ='Oliver Auth';
	public $version    ='1.6.3';
	public $homepage   ='http://www.variantbank.org/results/rules/s/southamerica51.htm';

	public $countries=array('Argentina','Brazil','Chile','Colombia','Peru');
	
	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'SouthAmerica5';
		$this->variantClasses['adjudicatorPreGame'] = 'SouthAmerica5';

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