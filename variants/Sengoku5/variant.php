<?php
/*
	Copyright (C) 2010 Oliver Auth

	This file is part of the Sengoku5 variant for webDiplomacy

	The Sengoku5 variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Sengoku5 variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---

	Changelog:
	1.0:   initial release
	1.0.1: fixed: wrong staring-unit-type for Takeda
	1.0.2: fixed: build anywhere notice in the variant description
	1.0.3: fixed: missing border-link
	1.0.4: fixed: wrong border-link
	1.0.5: fixed: missing SC on largemap
	1.0.6: fixed: wrong border-link
	1.0.6: fixed: smallmap glitch + missing neutral unit.
	1.0.7: small edit on rules.html
	1.0.8: fixed: wrong border-link
	1.1:   Big code-cleanup for the neutral player and minor improvements
	1.1.1: error-fix 
	1.1.2: spelling error fixed
	1.1.3: color glitch fixed
	1.2  : use now codeversion
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Sengoku5Variant extends WDVariant {
	public $id         =27;
	public $mapID      =27;
	public $name       ='Sengoku5';
	public $fullName   ='Sengoku';
	public $description='The Sengoku Variant is a historical transplant to medieval Japan.';
	public $author     ='Benjamin Hester';
	public $adapter    ='Oliver Auth';
	public $version    ='5.0';
	public $codeVersion='1.2.1';
	public $homepage   ='http://www.variantbank.org/results/rules/s/sengoku.htm';

	public $countries=array('Shimazu','Mori','Chosokabe','Asakura','Oda','Uesugi','Takeda','Hojo');
	
	public function __construct() {
		parent::__construct();

		// Setup the game
		$this->variantClasses['adjudicatorPreGame'] = 'Sengoku5';
		$this->variantClasses['drawMap']            = 'Sengoku5';

		// New medival icons for armies and fleets
		$this->variantClasses['drawMap']            = 'Sengoku5';
		$this->variantClasses['OrderInterface']     = 'Sengoku5';
	
		// Only Winner Takes All
		$this->variantClasses['processMember']      = 'Sengoku5';

		// Build anywhere
		$this->variantClasses['OrderInterface']     = 'Sengoku5';
		$this->variantClasses['processOrderBuilds'] = 'Sengoku5';
		$this->variantClasses['userOrderBuilds']    = 'Sengoku5';

		// Neutral units:
		$this->variantClasses['processMembers']     = 'Sengoku5';
		$this->variantClasses['processGame']        = 'Sengoku5';
		$this->variantClasses['OrderArchiv']        = 'Sengoku5';
		
	}
	
	// New SupplyCenter target
	public function initialize()
	{
		parent::initialize();
		$this->supplyCenterTarget = 25;
	}	
	
	// Needed for Neutral units code
	public function countryID($countryName)
	{
		if ($countryName == 'Neutral units')
			return count($this->countries)+1;		
		return parent::countryID($countryName);
	}
	
	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1570);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1570);
		};';
	}
}
