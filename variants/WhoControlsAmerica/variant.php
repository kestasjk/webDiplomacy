<?php
/*
	Copyright (C) 2010 Gavin Atkinson / Oliver Auth

	This file is part of the WhoControlsAmericaV variant for webDiplomacy

	The WhoControlsAmericaV variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The WhoControlsAmerica variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	Changelog:
	1.0:   initial version
	1.0.1: small changes to rules.html
	1.0.2: borderfix
	
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class WhoControlsAmericaVariant extends WDVariant {
	public $id=43;
	public $mapID=43;
	public $name='WhoControlsAmerica';
	public $fullName='Who controls America';
	public $description='A political version of Diplomacy.';
	public $adapter='Gavin Atkinson / Oliver Auth';
	public $author='Gavin Atkinson';
	public $version='1.0.2';
	
	public $countries=array('Republican Party', 'Conservative Interests', 'Democratic Party', 'Liberal Interests', 
	'Corporate America', 'The Military', 'The Underworld', 'Secret Societies');

	public function __construct() {
		parent::__construct();
		$this->variantClasses['drawMap']            = 'WhoControlsAmerica';
		$this->variantClasses['adjudicatorPreGame'] = 'WhoControlsAmerica';
		// Winner need to control 2 special territories
		$this->variantClasses['processMembers']     = 'WhoControlsAmerica';
		// Javascript patches for custom unit-icons+names+build anywhere
		$this->variantClasses['OrderInterface']     = 'WhoControlsAmerica';
		// Build anywhere
		$this->variantClasses['processOrderBuilds'] = 'WhoControlsAmerica';
		$this->variantClasses['userOrderBuilds']    = 'WhoControlsAmerica';
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 2008);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 2008);
		};';
	}
}

?>