<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Crowded variant for webDiplomacy

	The Crowded variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Crowded variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---
	
	Rules for the Crowded Variant by
	http://www.variantbank.org/results/rules/c/crowded.htm
	
	Changelog:
	1.0:   initial release by Carey Jensen
	1.5:   new webdip v.97 code by Oliver Auth
	1.5.1: small adjustments for the new variant.php code
	1.5.2: new color for the Balkan
	1.5.3: fixed: small spelling error on smallmap
		
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class CrowdedVariant extends WDVariant {
	public $id         =14;
	public $mapID      =14;
	public $name       ='Crowded';
	public $fullName   ='Crowded';
	public $description='The classic map for 11 players';
	public $author     ='Unknown';
	public $adapter    ='Carey Jensen / Oliver Auth';
	public $version    ='1.5.3';
	public $homepage   ='http://www.variantbank.org/results/rules/c/crowded.htm';
	
	public $countries=array('England', 'France', 'Italy', 'Germany', 'Austria', 'Turkey', 'Russia', 'Balkan', 'Lowland', 'Norway', 'Spain');	

	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'Crowded';
		$this->variantClasses['adjudicatorPreGame'] = 'Crowded';

	}

	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 18;
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