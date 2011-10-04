<?php
/*
	Copyright (C) 2011 Milan Mach

	This file is part of the Hussite variant for webDiplomacy

	The Hussite variant for webDiplomacy is free software: you can redistribute
	it and/or modify it under the terms of the GNU Affero General Public License
	as published by the Free Software Foundation, either version 3 of the License,
	or (at your option) any later version.

	The Hussite variant for webDiplomacy is distributed in the hope that it will
	be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of 
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
	See the GNU General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with webDiplomacy. If not, see <http://www.gnu.org/licenses/>.

	---
	
	Changelog:
	0.9: test verson
	1.0: initial release
*/

defined('IN_CODE') or die('This script can not be run by itself.');

class HussiteVariant extends WDVariant {
	public $id=47;
	public $mapID=47;
	public $name='Hussite';
	public $fullName='Hussite Wars';
	public $description='Diplomacy in the heart of Europe, amidst 15th century religious struggle.';
	public $author='Milan Mach (WebDip: Milan Mach)';
	public $version='1.0';
	public $codeVersion='1.0.5';
        
	public $countries=array('Bavaria','Catholic Landfrieden','Hungary','Kingdom of Poland','Margraviate of Brandenburg','Orebites','Praguers','Saxony','Taborites');

	public function __construct() {
		parent::__construct();
		$this->variantClasses['drawMap']            = 'Hussite';
		$this->variantClasses['adjudicatorPreGame'] = 'Hussite';
                
		// Build anywhere
		$this->variantClasses['OrderInterface']     = 'Hussite';		
		$this->variantClasses['processOrderBuilds'] = 'Hussite';
		$this->variantClasses['userOrderBuilds']    = 'Hussite';
	}
	
	public function initialize() {
		parent::initialize();
		$this->supplyCenterTarget = 24;
	}
	
	public function turnAsDate($turn) {
		if ( $turn==-1 ) return "Pre-game";
		else return ( $turn % 2 ? "Autumn, " : "Spring, " ).(floor($turn/2) + 1421);
	}

	public function turnAsDateJS() {
		return 'function(turn) {
			if( turn==-1 ) return "Pre-game";
			else return ( turn%2 ? "Autumn, " : "Spring, " )+(Math.floor(turn/2) + 1421);
		};';
	}
}

?>