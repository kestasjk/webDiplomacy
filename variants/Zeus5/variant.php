<?php
/*
	Copyright (C) 2012 kaner406 / Oliver Auth

	This file is part of the Zeus5 variant for webDiplomacy

	The Zeus5 variant for webDiplomacy is free software: you can
	redistribute it and/or modify it under the terms of the GNU Affero General
	Public License as published by the Free Software Foundation, either version
	3 of the License, or (at your option) any later version.

	The Zeus5 variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---

	Changelog:
	1.0: first install
	1.0.1: hotfix for the coast (will need a better solution later)
	1.0.2: finally convoys work now.
	1.1  : only one class in ProcessGame to avoid problems with the banPlayer or uncrashGames code
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Zeus5Variant extends WDVariant {
	public $id         =70;
	public $mapID      =70;
	public $name       ='Zeus5';
	public $fullName   ='Zeus 5';
	public $description='WWII from the perspective of Mt. Olympus';
	public $author     ='Chris Northcott, Fred C. Davis Jr., Tom Reinecker';
	public $adapter    ='kaner406 & Oliver Auth';
	public $homepage   ='http://www.dipwiki.com/index.php?title=Zeus';
	public $version    ='5';
	public $codeVersion='1.1';	
	
	public $countries=array('United Kingdom', 'United States', 'Italy', 'Germany', 'Japan', 'China', 'Soviet Union');	

	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'Zeus5';
		$this->variantClasses['adjudicatorPreGame'] = 'Zeus5';
		
		// Start with a build phase:
		$this->variantClasses['adjudicatorPreGame'] = 'Zeus5';
		$this->variantClasses['processGame']        = 'Zeus5';
		$this->variantClasses['processOrderBuilds'] = 'Zeus5';
		
		// Neutral unit:
		$this->variantClasses['OrderArchiv']        = 'Zeus5';
		$this->variantClasses['processGame']        = 'Zeus5';
		$this->variantClasses['processMembers']     = 'Zeus5';
		
		// Allow for some coasts to convoy
		$this->variantClasses['OrderInterface']     = 'Zeus5';
		$this->variantClasses['userOrderDiplomacy'] = 'Zeus5';
		
		// Allows USA to build in California
		$this->variantClasses['OrderInterface']     = 'Zeus5';
		$this->variantClasses['userOrderBuilds']    = 'Zeus5';

	}
	
	/* Coasts that allow convoying.
	*  Hawaii(38), Midway Isl.(61), Okinawa(71), Solomon Isl.(87)
	*/
	public $convoyCoasts = array ('38','61','71','87');

	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 21;
	}

	public function countryID($countryName)
	{
		if ($countryName == 'Neutral units')
			return count($this->countries)+1;
		
		return parent::countryID($countryName);
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