<?php
/*
	Copyright (C) 2011 

	This file is part of the US of Amerika variant for webDiplomacy

	The US of Amerika variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The US of Amerika variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---

	Changelog:
	1.0: first install
	1.0.10: USA wins with 14 SC.
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class USofAVariant extends WDVariant {
	public $id         = 56;
	public $mapID      = 56;
	public $name       = 'USofA';
	public $fullName   = 'USA';
	public $description= 'Because it\'s not really a war without the U.S. of A.';
	public $author     = 'T. Moscal';
	public $adapter    = 'kaner406';
	public $codeVersion= '1.0.14';
	public $homepage   = 'http://www.dipwiki.com/index.php?title=USA';
	
	public $countries=array('England', 'France', 'Italy', 'Germany', 'Austria', 'Turkey', 'Russia', 'USA');

	public function __construct() {
		parent::__construct();
		$this->variantClasses['drawMap']            = 'USofA';
		$this->variantClasses['adjudicatorPreGame'] = 'USofA';
		
		// USA only needs 14 SC:
		$this->variantClasses['processMembers']     = 'USofA';
	}

	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 18;
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1999);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1999);
		};';
	}
}

?>