<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the 7 Islands variant for webDiplomacy

	The 7 Islands variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The 7 Islands variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---

	Changelog:
	1.0:   initial release by Carey Jensen
	1.5:   new webdip v.98 code by Oliver Auth
	1.5.1: small adjustments for the new variant.php
	1.5.2: Disabled the custom-start, because there seems to be errors with retreats...
	1.6:   Fixed the problem with the retreat errors
	1.6.1: fixed: smallmap spelling error
	1.6.2: fixed: wrong territory-color on the largemap
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class ClassicSevenIslandsVariant extends WDVariant {
	public $id         = 18;
	public $mapID      = 18;
	public $name       = 'ClassicSevenIslands';
	public $fullName   = 'Classic - 7 Islands';
	public $description= 'The standard Diplomacy map of Europe with 7 more SCs on islands.';
	public $author     = 'Paul Bennett';
	public $adapter    = 'Carey Jensen / Oliver Auth';
	public $version    = '1.6.2';
	public $homepage   = 'http://www.diplom.org/Online/variants/7-island.html';

	public $countries=array('England', 'France', 'Italy', 'Germany', 'Austria', 'Turkey', 'Russia');

	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'ClassicSevenIslands';
		$this->variantClasses['adjudicatorPreGame'] = 'ClassicSevenIslands';
		$this->variantClasses['processGame']        = 'ClassicSevenIslands';
		
	}
	
	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 21;
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