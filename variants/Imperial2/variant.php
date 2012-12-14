<?php
/*
	Copyright (C) 2012 Oliver Auth

	This file is part of the Imperial2 variant for webDiplomacy

	The Imperial2 variant for webDiplomacy is free software:
	you can redistribute it and/or modify it under the terms of the GNU Affero
	General Public License as published by the Free Software Foundation, either
	version 3 of the License, or (at your option) any later version.

	The Imperial2 variant for webDiplomacy is distributed in the hope
	that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
	warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.
	
	If you have questions or suggestions send me a mail: Oliver.Auth@rhoen.de

	---

	Changelog:
	1.0:   initial release
	1.0.1: color improvements
	1.0.2: territory-names improvements
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class Imperial2Variant extends WDVariant {
	public $id         = 81;
	public $mapID      = 81;
	public $name       = 'Imperial2';
	public $fullName   = 'Imperial Diplomacy II';
	public $description= 'This is a global variant set in the mid-19th Century.';
	public $author     = 'Michael David Roberts';
	public $adapter    = 'Oliver Auth (abbreviations by Captainmeme)';
	public $version    = '3.0';
	public $codeVersion= '1.0.2';
	public $homepage   = 'http://www.variantbank.org/results/rules/i/imperial2.htm';
	
	public $countries=array('Austria','Brazil','Britain','China','CSA','France','Holland','Japan','Mexico','Prussia','Russia','Turkey','USA');

	public function __construct() {
		parent::__construct();
		$this->variantClasses['drawMap']            = 'Imperial2';
		$this->variantClasses['adjudicatorPreGame'] = 'Imperial2';
		$this->variantClasses['Chatbox']            = 'Imperial2';
		$this->variantClasses['panelGameBoard']     = 'Imperial2';	
		
		// Build anywhere at a home SC
		$this->variantClasses['OrderInterface']     = 'Imperial2';		
		$this->variantClasses['processOrderBuilds'] = 'Imperial2';
		$this->variantClasses['userOrderBuilds']    = 'Imperial2';
	}

	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 70;
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1861);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1861);
		};';
	}
}

?>