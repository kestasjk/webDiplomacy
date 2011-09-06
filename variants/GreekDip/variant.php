<?php
/*
	Copyright (C) 2011 Oliver Auth

	This file is part of the GreekDip variant for webDiplomacy

	The GreekDip variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The GreekDip variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---
	
	Changelog:
	1.0:   initial release
	1.0.5: Border issues fixed / spelling mistakes
	1.0.6: error-fix buildphase
	1.0.9: spelling mistake fixed and new icons
	1.1:   new icons, errorfixes
	1.1.1: small fix for the new icons
	1.1.5: 2 coasts added to territory Thrace

*/

defined('IN_CODE') or die('This script can not be run by itself.');

class GreekDipVariant extends WDVariant {
	public $id         = 35;
	public $mapID      = 35;
	public $name       = 'GreekDip';
	public $fullName   = 'Greek Diplomacy';
	public $description= 'War in the Greek City States time period';
	public $author     = 'Hirum Hibbert';
	public $adapter    = 'Oliver Auth';
	public $version    = '1.1.5';

	public $countries=array('Athens','Byzantinum','Macedonia','Persia','Rhodes','Sparta');

	public function __construct() {
		parent::__construct();
		
		// Setup
		$this->variantClasses['adjudicatorPreGame'] = 'GreekDip';
		$this->variantClasses['drawMap']            = 'GreekDip';

		// Bidding-start
		$this->variantClasses['drawMap']               = 'GreekDip';
		$this->variantClasses['OrderInterface']        = 'GreekDip';
		$this->variantClasses['processGame']           = 'GreekDip';
		$this->variantClasses['processOrderBuilds']    = 'GreekDip';
		$this->variantClasses['processOrderDiplomacy'] = 'GreekDip';
		$this->variantClasses['userOrderBuilds']       = 'GreekDip';
		
		// New Icons
		$this->variantClasses['drawMap']               = 'GreekDip';
		$this->variantClasses['OrderInterface']        = 'GreekDip';
		
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		if ( $turn==0  ) return "Bidding";
		if ( $turn==1  ) return "Initial Builds";		
		return ( $turn % 2 ? "Autumn, " : "Spring, " ).(-1*(floor($turn/2) - 1551))." BC.";
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			if ( turn==0  ) return "Bidding";
			if ( turn==1  ) return "Initial Builds";		
			return ( turn%2 ? "Autumn, " : "Spring, " )+(-1*(Math.floor(turn/2) - 1551)) +" BC.";
		};';
	}

	}

?>