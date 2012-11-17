<?php
/*
	Copyright (C) 2012 Kaner406 / Oliver Auth

	This file is part of the KnownWorld_901 variant for webDiplomacy

	The KnownWorld_901 variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License 
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The KnownWorld_901 variant for webDiplomacy is distributed in the hope that it will be
	useful, but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. 
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---
	
	Changelog:
	1.0:   initial release
	1.0.2: fixed a color problem with disloged units.
	1.0.9: SCs to win: down to 55
	1.0.10: Better handling of the largemap-names
	1.1: Neutral units now rebuild if possible, small colorfixed on the largemap
	1.1.1: MySQL-spelling mistake fixed
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class KnownWorld_901Variant extends WDVariant {
	public $id         =57;
	public $mapID      =57;
	public $name       ='KnownWorld_901';
	public $fullName   ='Known World 901';
	public $description='Conquer the Known World of 901';
	public $author     ='David E. Cohen';
	public $adapter    ='Kaner406 & Oliver Auth';
	public $version    ='3.0';
	public $codeVersion='1.1.1';
	public $homepage  ='http://diplomiscellany.tripod.com/id20.html';
	
	public $countries=array('Arabia','Axum','Byzantinum','China','Denmark','Egypt','France','Germany','Khazaria','Russia','Spain','Turan','Srivijaya','Wagadu', 'India');	

	public function __construct() {
		parent::__construct();

		$this->variantClasses['drawMap']            = 'KnownWorld_901';
		$this->variantClasses['adjudicatorPreGame'] = 'KnownWorld_901';

		// Zoom-Map
		$this->variantClasses['panelGameBoard']     = 'KnownWorld_901';
		$this->variantClasses['drawMap']            = 'KnownWorld_901';
		
		// Build anywhere
		$this->variantClasses['OrderInterface']     = 'KnownWorld_901';		
		$this->variantClasses['processOrderBuilds'] = 'KnownWorld_901';
		$this->variantClasses['userOrderBuilds']    = 'KnownWorld_901';
		
		// Neutral units:
		$this->variantClasses['OrderArchiv']        = 'KnownWorld_901';
		$this->variantClasses['processGame']        = 'KnownWorld_901';
		$this->variantClasses['processMembers']     = 'KnownWorld_901';
		
		// Transform command
		$this->variantClasses['drawMap']               = 'KnownWorld_901';
		$this->variantClasses['OrderArchiv']           = 'KnownWorld_901';
		$this->variantClasses['OrderInterface']        = 'KnownWorld_901';
		$this->variantClasses['processOrderDiplomacy'] = 'KnownWorld_901';
		$this->variantClasses['userOrderDiplomacy']    = 'KnownWorld_901';

		// Write the CountryName in the chatbox
		$this->variantClasses['Chatbox']               = 'KnownWorld_901';
		
	}
	
	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 55;
	}

	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 901);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 901);
		};';
	}
	
	public function countryID($countryName)
	{
		if ($countryName == 'Neutral units')
			return count($this->countries)+1;
		
		return parent::countryID($countryName);
	}
	
}

?>