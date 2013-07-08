<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the South America 8-Player variant for webDiplomacy

	The South America 8-Player variant for webDiplomacy" is free software:
	you can redistribute it and/or modify it under the terms of the GNU Affero
	General Public License as published by the Free Software Foundation, either 
	version 3 of the License, or (at your option) any later version.

	The South America 8-Player variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied 
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy.  If not, see <http://www.gnu.org/licenses/>.

	---
	
	Changelog:
	1.0:   initial release
	1.0.1: fixed: 2 missing supply-centers
	1.0.2: fixed: link error
	1.0.3: fixed: link and smallmap error
	1.0.4: fixed: link and smallmap error
	1.0.5: fixed: link and smallmap error
	1.0.6: fixed: smallmap error
	1.0.7: fixed: spelling error 
	1.0.8: small map improvements
	1.0.9: fixed: spelling error
	1.1:   fixed: spelling error / small improvements
	1.1.1: fixed: wrong link in rules.html

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class SouthAmerica8Variant extends WDVariant {
	public $id         =24;
	public $mapID      =24;
	public $name       ='SouthAmerica8';
	public $fullName   ='South American Supremacy';
	public $description='A variant with a map of South America for 8 players.';
	public $author     ='Benjamin Hester';
	public $adapter    ='Oliver Auth';
	public $version    ='1.1.1';
	public $homepage   ='http://www.variantbank.org/results/rules/s/sasupremacy.htm';

	public $countries=array('Argentina','Bolivia','Brazil','Chile','Colombia','Paraguay','Peru','Venezuela');
	
	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'SouthAmerica8';
		$this->variantClasses['adjudicatorPreGame'] = 'SouthAmerica8';

	}
	
	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 18;
	}
	
	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1835);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1835);
		};';
	}
}

?>