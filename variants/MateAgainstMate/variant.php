<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the MateAgainstMate variant for webDiplomacy

	The MateAgainstMate variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The MateAgainstMate variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---

	Changelog:
	1.0:   initial version
	1.0.1: small improvements
	1.0.2: color adjustments
	1.0.3: fixed some boundary bugs for East Timor	
	1.0.4: fixed some boundary bugs for Melbourne
	2.0:   revised version with additional antarctic territory and extra bonus Indonesian unit
	2.0.1: fixed error in the sea-SC code
	2.0.2: border error fixed
	2.1:   Big code-cleanup (not only) for the neutral player and minor improvements
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class MateAgainstMateVariant extends WDVariant {
	public $id         =37;
	public $mapID      =37;
	public $name       ='MateAgainstMate';
	public $fullName   ='Mate Against Mate';
	public $description='The Mate Against Mate variant is an alternate history starting in 1973 allowing 8 players to struggle for control over Australia and surrounding territories.';
	public $author     ='Gavin Atkinson';
	public $adapter    ='Gavin Atkinson / Emmanuele Ravaioli / Oliver Auth';
	public $version    ='2.1.1';

	public $countries=array(
		'Indonesia','Western Australia','South Australia','Tasmania',
		'New Zealand','Victoria','New South Wales','Queensland');
	
	public function __construct() {
		parent::__construct();

		// Setup
		$this->variantClasses['adjudicatorPreGame'] = 'MateAgainstMate';
		$this->variantClasses['drawMap']            = 'MateAgainstMate';

		// Indonesia can't build fleets at the south-coast of Jakarta
		$this->variantClasses['OrderInterface']     = 'MateAgainstMate';
		
		// SCs on Sea territories...
		$this->variantClasses['processGame']        = 'MateAgainstMate';
		
		// Neutral units:
		$this->variantClasses['processMembers']     = 'MateAgainstMate';
		$this->variantClasses['processGame']        = 'MateAgainstMate';
		$this->variantClasses['OrderArchiv']        = 'MateAgainstMate';
		
	}

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
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1973);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1973);
		};';
	}
}

?>