<?php
/*
	Copyright (C) 2010 Carey Jensen / Oliver Auth

	This file is part of the Modern Diplomacy II variant for webDiplomacy

	The Modern Diplomacy II variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Modern Diplomacy II variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---
	
	Changelog:
	1.0:     initial release by Carey Jensen
	1.5:     new webdip v.98 code by Oliver Auth
	1.5.1:   small adjustments for the new variant.php code
	1.5.2.1: New color for Ukraine
	1.6:     New smallmap
	1.6.0.2: Spelling error on smallmap fixed
	1.6.1:   New color-allocation
	1.6.2:   small improvements on both maps
	1.7:     NEW ORDERGENERATION-TEST
	1.7.1:   fixed: spelling mistake
	1.7.2:   fixed: spelling mistake (now for real)
	1.7.3:   Wrong territory-type fixed
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Modern2Variant extends WDVariant {
	public $id         = 19;
	public $mapID      = 19;
	public $name       = 'Modern2';
	public $fullName   = 'Modern Diplomacy II';
	public $description= 'Modern Diplomacy II is intended to be diplomacy with an updated map, circa 1994, taking place in Europe, the Middle East and North Africa.';
	public $author     = 'Vincent Mous';
	public $adapter    = 'Carey Jensen / Oliver Auth';
	public $version    = 'II';
	public $codeVersion= '1.7.4';
	public $homepage   = 'http://www.variantbank.org/results/rules/m/modern2.htm';

	public $countries=array('Britain', 'Egypt', 'France', 'Germany', 'Italy', 'Poland', 'Russia', 'Spain', 'Turkey', 'Ukraine');

	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'Modern2';
		$this->variantClasses['adjudicatorPreGame'] = 'Modern2';
		$this->variantClasses['userOrderBuilds']    = 'Modern2';
		$this->variantClasses['OrderInterface']     = 'Modern2';
		$this->variantClasses['processOrderBuilds'] = 'Modern2';
		$this->variantClasses['processOrderDiplomacy'] = 'Modern2';
		
	}

	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 33;
	}
	
	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1994);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1994);
		};';
	}
}

?>